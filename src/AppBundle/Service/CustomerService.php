<?php

namespace AppBundle\Service;

use AppBundle\Entity\Country;
use AppBundle\Entity\CustomerAccount;
use AppBundle\Entity\CustomerAddress;
use AppBundle\Entity\CustomerCompany;
use AppBundle\Entity\CustomerContact;
use AppBundle\Entity\CustomerType;
use AppBundle\Entity\Locale;
use AppBundle\Entity\Locality;
use AppBundle\Exception\OrderAddFailException;
use AppBundle\Repository\LocaleRepository;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\DependencyInjection\Container;

class CustomerService
{
    const CUSTOMER_TYPE_PF = 0;
    const ID_PLATFORM_GROUP = 1;
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     */
    protected $doctrine;

    /**
     * @param Container $container
     *
     * @throws \Exception
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->doctrine = $this->getContainer()->get('doctrine');
        $this->syncAccounts = new ArrayCollection();
    }

    /**
     * @return Registry
     */
    protected function getDoctrine()
    {
        return $this->doctrine;
    }

    /**
     * @return Container
     */
    protected function getContainer()
    {
        return $this->container;
    }

    /**
     * Processes a customer synchronization message from EOS and returns the synchronized contact object
     * (it may not have an id here, this method doesn't flush the contact)
     *
     * @param array $arrContactMessage
     *
     * @return CustomerContact
     *
     * @throws \Exception
     */
    public function syncContactFromEos($arrContactMessage)
    {
        if ((!isset($arrContactMessage['contact']['status']))) {
            throw new \Exception('Invalid contact status');
        }

        if ((intval($arrContactMessage['contact']['status']) !== 0) && (intval(
                    $arrContactMessage['contact']['status']
                ) !== 1)) {
            throw new \Exception('Invalid contact status');
        }

        /** @var EntityManager $manager */
        $manager = $this->getDoctrine()->getManager();

        /** @var \Monolog\Logger $logger */
        $logger = $this->getContainer()->get('logger');

        /** @var CountryService $countryService */
        $countryService = $this->container->get('locality.country');

        $entityRepository = new EntityRepository($manager, $manager->getClassMetadata('AppBundle:CustomerContact'));

        /** @var LocaleRepository $localeRepository */
        $localeRepository = $manager->getRepository('AppBundle:Locale');

        $countryId = $this->container->getParameter('default_country_id');
        if (isset($arrContactMessage['contact']['country_id'])) {
            $countryId = intval($arrContactMessage['contact']['country_id']);
        }

        /** @var \AppBundle\Entity\Country $country */
        $country = $countryService->getCountry($countryId);

        $localeId = $countryId;
        if (isset($arrContactMessage['contact']['prefferred_locale_id'])) {
            $localeId = intval($arrContactMessage['contact']['prefferred_locale_id']);
        }

        /** @var Locale $locale */
        $locale = $localeRepository->getLocale($localeId);

        $lockKey = 'customer_sync_contact_' . md5($countryId . '_' . $arrContactMessage['contact']['email']);
        $logger->addInfo('Acquired lock: ' . $lockKey);

        /** @var CustomerContact $contact */
        $contact = $this->findContactByMessageArr($arrContactMessage['contact'], $country);

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        // apply contact details from message
        $this->applyContactDetailsFromMessageArr($contact, $arrContactMessage['contact'], $country, $locale);

        /** @var CustomerContactService $customerContactService */
        $customerContactService = $this->getContainer()->get('customer.customer_contact');

        try {
            $customerContactService->saveContact($contact);
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), '"' . $arrContactMessage['contact']['email'] . '"') !== false) {
                // contact already exists, continue processing
                $logger->addInfo(
                    sprintf("Customer contact with email '%s' already exists.", $arrContactMessage['contact']['email'])
                );

                /** @var \\CustomerBundle\Repository\CustomerContactRepository $contactRepository */
                $contactRepository = $manager->getRepository('CustomerBundle:CustomerContact');
                $criteria = [
                    'email'   => $arrContactMessage['contact']['email'],
                    'country' => $country,
                ];
                $contact = $contactRepository->findOneBy(
                    $criteria,
                    ['id' => 'asc']
                );

                // apply contact details from message
                $this->applyContactDetailsFromMessageArr($contact, $arrContactMessage['contact'], $country, $locale);
                $customerContactService->saveContact($contact);
            } else {
                $logger->addInfo('Released lock: ' . $lockKey);

                throw new \Exception($e->getMessage(), $e->getCode(), $e);
            }
        }

        $manager->flush();

        $contactId = $contact->getId();
        $logger->addInfo(sprintf('Saved customer contact with id %d', $contactId));

        /** @var \\CustomerBundle\Repository\CustomerTypeRepository $customerTypeRepository */
        $customerTypeRepository = $manager->getRepository('AppBundle:CustomerType');

        $naturalPersonAdded = false;
        foreach ($arrContactMessage['companies'] as $arrCompany) {
            // First treat the case where the company is deleted.
            // In this case only system_id and status 0 are sent
            if (isset($arrCompany['status']) && intval($arrCompany['status']) == 0) {
                /** @var \AppBundle\Repository\CustomerCompanyRepository $customerCompanyRepository */
                $customerCompanyRepository = $manager->getRepository('CustomerBundle:CustomerCompany');
                $customerCompanyRepository->inactivateCompanyBySystemId(intval($arrCompany['system_id']));
                continue;
            }

            if (!isset($arrCompany['type'])) {
                $logger->addInfo('Do not process company without type');
                continue;
            }

            /** @var \AppBundle\Entity\CustomerType $type */
            $type = $customerTypeRepository->findOneBy(['id' => intval($arrCompany['type'])]);
            if (null == $type) {
                $logger->addInfo(sprintf('Customer type with id %d not found', intval($arrCompany['type'])));
                continue;
            }

            if ($type->getId() == 0) {
                $lockKey = 'customer_sync_company_' . md5($countryId . '_' . $contact->getEmail());
            } else {
                $lockKey = 'customer_sync_company_' . md5($countryId . '_' . $arrCompany['fiscal_code']);
            }

            $logger->addInfo('Acquired lock: ' . $lockKey);

            /** @var CustomerCompany $company */
            $company = $this->findCompanyByMessageArr($arrCompany, $contact, $country);

            if (null == $company) {
                $logger->addInfo("Creating new company");
                /** @var CustomerCompany $company */
                $company = new CustomerCompany();
                $company->setCorporateApproved(CustomerCompany::CORPORATE_PENDING);
            }

            $this->setCompanyDetails($company, $arrCompany, $contact, $type, $country, $naturalPersonAdded);

            $manager->flush();

            try {
                $logger->addInfo('Released lock: ' . $lockKey);
            } catch (\Exception $e) {
                $logger->addWarning('Release lock failed: ' . $e->getMessage() . $e->getTraceAsString());
            }
        }

        $logger->addInfo('Saved companies');

        $systemIds = [];
        $uniqueHashes = [];

        foreach ($arrContactMessage['addresses'] as $key => $arrAddress) {
            try {
                $this->setCustomerAddresses($key, $arrAddress, $systemIds, $uniqueHashes, $contact, $country);
            } catch (OrderAddFailException $e) {
                throw new \Exception($e->getMessage());
            } catch (\Exception $e) {
                if (strpos($e->getMessage(), 'unique_stuff') !== false) {
                    $logger->addInfo("Found duplicate entry on 'unique_stuff' index.");

                    $this->getDoctrine()->resetManager();

                    /** @var CountryService $countryService */
                    $countryService = $this->container->get('locality.country');
                    /** @var \AppBundle\Entity\Country $country */
                    $country = $countryService->getCountry($countryId);

                    /** @var \\CustomerBundle\Service\CustomerContactService $customerContactService */
                    $customerContactService = $this->container->get('.customer.customer_contact');
                    /** @var CustomerContact $contact */
                    $contact = $customerContactService->findContact($contactId);
                }

                $logger->addInfo($e->getMessage());
                continue;
            }
        }

        $logger->addInfo('Saved addresses');

        return $contact;
    }

    /**
     * @param string|int      $key
     * @param array           $arrAddress
     * @param array           $systemIds
     * @param array           $uniqueHashes
     * @param CustomerContact $contact
     * @param Country         $country
     *
     * @throws \Exception
     * @throws \Exception
     */
    protected function setCustomerAddresses($key, $arrAddress, &$systemIds, &$uniqueHashes, $contact, $country)
    {
        /** @var \Doctrine\ORM\EntityManager $manager */
        $manager = $this->getDoctrine()->getManager();

        /** @var \Monolog\Logger $logger */
        $logger = $this->getContainer()->get('logger');

        // First check if address is showroom
        // Ignore showroom addresses
        if (isset($arrAddress['is_showroom']) && intval($arrAddress['is_showroom']) === 1) {
            throw new \Exception('Do not process showroom addresses');
        }

        if (!is_int($key) && in_array($key, ['vendor_billing', 'vendor_delivery'])) {
            throw new \Exception('Do not process vendor addresses');
        }

        $localityId = intval($arrAddress['locality_id']);
        if (empty($localityId)) {
            throw new \Exception('Address locality id not set');
        }

        if (in_array(intval($arrAddress['system_id']), $systemIds)) {
            throw new \Exception(
                sprintf('Address already processed with systemId #%d', intval($arrAddress['system_id']))
            );
        }

        $uniqueHash = md5(
            sprintf(
                '%d_%s_%s_%s',
                $localityId,
                mb_strtolower($arrAddress['address'], 'UTF-8'),
                mb_strtolower($arrAddress['name'], 'UTF-8'),
                $arrAddress['phone']
            )
        );
        if (in_array($uniqueHash, $uniqueHashes)) {
            throw new \Exception(sprintf('Address already processed with uniqueHash (%s)', $uniqueHash));
        }

        $uniqueHashes[] = $uniqueHash;

        if (intval($arrAddress['system_id']) !== 0) {
            $systemIds[] = intval($arrAddress['system_id']);
        }

        /** @var \AppBundle\Repository\LocalityRepository $localityRepository */
        $localityRepository = $manager->getRepository('AppBundle:Locality');
        /** @var Locality $locality */
        $locality = $localityRepository->getLocality($localityId);

        if (null == $locality) {
            throw new OrderAddFailException('Address locality not found.');
        }

        $addressStatus = CustomerAddress::STATUS_ACTIVE;
        if (isset($arrAddress['status'])) {
            $addressStatus = (int)$arrAddress['status'];
        }

        $lockKey = 'customer_sync_address_' . $uniqueHash;
        $logger->addInfo('Acquired lock: ' . $lockKey);

        $addressDetails = [];

        if (isset($arrAddress['name'])) {
            $addressDetails['name'] = $arrAddress['name'];
        }

        if (isset($arrAddress['phone'])) {
            $addressDetails['phone'] = $arrAddress['phone'];
        }

        /** @var CustomerAddress $address */
        $address = $this->findAddressByMessageArr($arrAddress, $contact, $country, $locality, $addressDetails);

        if (null == $address) {
            $logger->addInfo("Creating new address");
            $address = new CustomerAddress();
        }

        $extraInfo = [];
        if (!empty($arrAddress['extra_info'])) {
            $extraInfo = $arrAddress['extra_info'];
        }

        $address
            ->setAddress($address)
            ->setCustomerContact($contact)
            ->setCountry($country)
            ->setStatus($addressStatus)
            ->setExtraInfo($extraInfo);

        if (isset($arrAddress['name']) && !empty($arrAddress['name'])) {
            $address->setCustomerName($arrAddress['name']);
        }

        if (isset($arrAddress['phone']) && !empty($arrAddress['phone'])) {
            $address->setCustomerPhone($arrAddress['phone']);
        }

        $manager->persist($address);

        try {
            $manager->flush($address);
        } catch (\Exception $e) {
            $logger->addInfo('Released lock: ' . $lockKey);

            throw $e;
        }

        $logger->addInfo('Released lock: ' . $lockKey);
    }

    /**
     * @param array           $arrAddress
     * @param CustomerContact $contact
     * @param Country         $country
     * @param Locality        $locality
     * @param array           $addressDetails
     *
     * @return CustomerAddress|null
     *
     * @throws \Exception
     */
    public function findAddressByMessageArr(array $arrAddress, CustomerContact $contact, Country $country, Locality $locality, array $addressDetails = [])
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        /** @var \Monolog\Logger $logger */
        $logger = $this->getContainer()->get('logger');

        /** @var  \\CustomerBundle\Repository\CustomerAddressRepository $addressRepository */
        $addressRepository = $em->getRepository('CustomerBundle:CustomerAddress');

        /** @var CustomerAddress $address */
        $address = null;

        $criteria = [
            'country'         => $country,
            'customerContact' => $contact,
            'locality'        => $locality,
            'address'         => $arrAddress['address'],
        ];

        if (!empty($addressDetails['name'])) {
            $criteria['customerName'] = $addressDetails['name'];
        }

        if (!empty($addressDetails['phone'])) {
            $criteria['customerPhone'] = $addressDetails['phone'];
        }

        $address = $addressRepository->findOneBy(
            $criteria,
            ['id' => 'asc']
        );

        if (null != $address) {
            $logger->addInfo(
                sprintf(
                    "Found customer address by '%d-%d-%d-%s'",
                    $country->getId(),
                    $locality->getId(),
                    $contact->getId(),
                    $arrAddress['address']
                )
            );

            return $address;
        }

        if (isset($arrAddress['system_id']) && intval($arrAddress['system_id']) > 0) {
            $criteria = [
                'customerContact' => $contact,
                'systemId'        => intval($arrAddress['system_id']),
                'country'         => $country,
            ];

            $address = $addressRepository->findOneBy(
                $criteria,
                ['id' => 'asc']
            );
        }

        if (null != $address) {
            $logger->addInfo(sprintf("Found customer address by system_id %d", intval($arrAddress['system_id'])));

            return $address;
        }

        if (isset($arrAddress['_id']) && (intval($arrAddress['_id']) > 0)) {
            $criteria = [
                'customerContact' => $contact,
                'id'              => intval($arrAddress['_id']),
                'country'         => $country,
            ];

            $address = $addressRepository->findOneBy(
                $criteria,
                ['id' => 'asc']
            );
        }

        if (null != $address) {
            $logger->addInfo(sprintf("Found customer address by _id %d", intval($arrAddress['_id'])));

            return $address;
        }

        return null;
    }

    /**
     * @param CustomerContact $contact
     * @param array           $arrContact
     * @param Country         $country
     * @param Locale          $locale
     */
    public function applyContactDetailsFromMessageArr(CustomerContact $contact, array $arrContact, Country $country, Locale $locale)
    {
        $contactStatus = CustomerContact::STATUS_ACTIVE;
        if (isset($arrContact['status'])) {
            $contactStatus = (int)$arrContact['status'];
        }

        $contact->setSystemId(intval($arrContact['system_id']))
            ->setCountry($country)
            ->setLocale($locale)
            ->setName($arrContact['name'])
            ->setGender(strtolower($arrContact['gender']))
            ->setPhone1($arrContact['phone_1'])
            ->setPhone2($arrContact['phone_2'])
            ->setPhone3($arrContact['phone_3'])
            ->setFax($arrContact['fax'])
            ->setEmail($arrContact['email'])
            ->setSource($arrContact['source'])
            ->setEosKey($arrContact['eos_key'])
            ->setStatus($contactStatus);

        $nin = trim($arrContact['nin']);
        if (!empty($nin) && $nin != 'null') {
            $contact->setNin($nin);
        }

        if (!empty($arrContact['birthday'])) {
            $eosBirthday = new \DateTime($arrContact['birthday']);
            if ($eosBirthday != $contact->getBirthday()) {
                $contact->setBirthday($eosBirthday);
            }
        }
    }

    /**
     * @param CustomerCompany $company
     * @param array           $arrCompany
     * @param CustomerContact $contact
     * @param CustomerType    $type
     * @param Country         $country
     * @param bool            $naturalPersonAdded
     * @param bool            $forceUpdateCustomerAccount
     *
     * @throws \Exception
     */
    protected function setCompanyDetails(
        CustomerCompany $company,
        $arrCompany,
        CustomerContact $contact,
        CustomerType $type,
        Country $country,
        &$naturalPersonAdded,
        $forceUpdateCustomerAccount = false
    ) {
        /** @var EntityManager $manager */
        $manager = $this->getDoctrine()->getManager();

        $companyStatus = true;
        if (isset($arrCompany['status'])) {
            $companyStatus = intval($arrCompany['status']) === 1;
        }

        $flb = null;
        if (isset($arrCompany['flb'])) {
            $flb = $arrCompany['flb'];
        }

        $company->setSystemId(intval($arrCompany['system_id']))
            ->setCustomerType($type)
            ->setCustomerContact($contact)
            ->setCountry($country);

        if ($type->getId() == self::CUSTOMER_TYPE_PF) {
            $company->setName($contact->getName())
                ->setFiscalCode($contact->getNin())
                ->setEmail($contact->getEmail())
                ->setPhone($contact->getPhone1());
        } else {
            $company->setName($arrCompany['name'])
                ->setFiscalCode($arrCompany['fiscal_code'])
                ->setRegistrationNumber($arrCompany['registration_number'])
                ->setPaysVat($arrCompany['pays_vat'])
                ->setBankAccount($arrCompany['bank_account'])
                ->setBankName($arrCompany['bank_name'])
                ->setPhone($contact->getPhone1())
                ->setWebsite($arrCompany['website'])
                ->setFlb($flb);
        }

        if ($type->getId() == self::CUSTOMER_TYPE_PF && $naturalPersonAdded) {
            $companyStatus = false;
        }

        $company->setStatus($companyStatus);
        $this->updateCustomerAccount($company, $contact, $forceUpdateCustomerAccount);
        $manager->persist($company);

        if ($companyStatus && $type->getId() === self::CUSTOMER_TYPE_PF) {
            $naturalPersonAdded = true;
        }
    }

    /**
     * @param array   $arrContact
     * @param Country $country
     *
     * @return CustomerContact
     *
     * @throws \Exception
     */
    public function findContactByMessageArr(array $arrContact, Country $country)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        /** @var \Monolog\Logger $logger */
        $logger = $this->getContainer()->get('logger');

        /** @var \AppBundle\Repository\CustomerContactRepository $contactRepository */
        $contactRepository = $em->getRepository('AppBundle:CustomerContact');

        /** @var CustomerContact $contact */
        $contact = null;

        if (isset($arrContact['email']) && !empty($arrContact['email'])) {
            $criteria = [
                'email'   => $arrContact['email'],
                'country' => $country,
            ];

            $contact = $contactRepository->findOneBy(
                $criteria,
                ['id' => 'asc']
            );
        }

        if (null != $contact) {
            $logger->addInfo(sprintf("Found customer contact by email '%s'", $arrContact['email']));

            return $contact;
        }


        if (isset($arrContact['system_id'])
            && (intval($arrContact['system_id']) > 0)
            && isset($arrContact['system_id'])
            && (intval($arrContact['system_id']) > 0)
        ) {
            $criteria = [
                'systemId' => intval($arrContact['system_id']),
                'id'       => intval($arrContact['system_id']),
                'country'  => $country,
            ];
            $contact = $contactRepository->findOneBy(
                $criteria,
                ['id' => 'asc']
            );
        }

        if (null != $contact) {
            $logger->addInfo(
                sprintf(
                    "Found customer contact by system_id %d and system_id %d",
                    intval($arrContact['system_id']),
                    intval($arrContact['system_id'])
                )
            );

            return $contact;
        }


        if ((isset($arrContact['system_id'])) && (intval($arrContact['system_id']) > 0)) {
            $criteria = [
                'systemId' => intval($arrContact['system_id']),
                'country'  => $country,
            ];
            $contact = $contactRepository->findOneBy(
                $criteria,
                ['id' => 'asc']
            );
        }

        if (null != $contact) {
            $logger->addInfo(sprintf("Found customer contact by system_id %d", intval($arrContact['system_id'])));

            return $contact;
        }


        if ((isset($arrContact['system_id'])) && (intval($arrContact['system_id']) > 0)) {
            $criteria = [
                'id'      => intval($arrContact['system_id']),
                'country' => $country,
            ];
            $contact = $contactRepository->findOneBy(
                $criteria,
                ['id' => 'asc']
            );
        }

        if (null != $contact) {
            $logger->addInfo(sprintf("Found customer contact by system_id %d", intval($arrContact['system_id'])));

            return $contact;
        }

        return null;
    }

    /**
     * Creates or updates the customer account in backend and ERP.
     * It also performs association logic to avoid doubles.
     *
     * @param CustomerCompany $company
     * @param CustomerContact $contact
     * @param bool            $forceAccountOverwrite
     * @param bool            $withFlush
     *
     * @throws \Exception
     */
    public function updateCustomerAccount(CustomerCompany $company, CustomerContact $contact, $forceAccountOverwrite = false, $withFlush = true)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $account = $company->getCustomerAccount();


        $account->setModified(new \DateTime());

        $em->persist($account);
        if (true === $withFlush) {
            $em->flush();
        }

        if ($account->getErpId() == null) {
            $account->setErpId($this->buildAccountErpId($account));
            $em->persist($account);
            if (true === $withFlush) {
                $em->flush();
            }
        }

        $em->persist($account);
        if (true === $withFlush) {
            $em->flush();
        }
    }

    /**
     * @param CustomerAccount $account
     *
     * @return string
     */
    public function buildAccountErpId(CustomerAccount $account)
    {
        switch ($account->getCountry()->getCountryCode()) {
            case Country::CODE_BG:
            case Country::CODE_HU:
                $erpId = $account->getCountry()->getCountryCode() . $account->getId();
                break;
            case Country::CODE_RO:
            default:
                $erpId = $account->getId();
                break;
        }

        return $erpId;
    }

    /**
     * @param array           $arrCompany
     * @param CustomerContact $contact
     * @param Country         $country
     *
     * @return CustomerCompany
     *
     * @throws \Exception
     */
    private function findCompanyByMessageArr(array $arrCompany, CustomerContact $contact, Country $country)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        /** @var \Monolog\Logger $logger */
        $logger = $this->getContainer()->get('logger');

        /** @var \AppBundle\Repository\CustomerCompanyRepository $companyRepository */
        $companyRepository = $em->getRepository('AppBundle:CustomerCompany');

        /** @var CustomerCompany $company */
        $company = null;

        if ((isset($arrCompany['front_id'])) && (intval($arrCompany['front_id']) > 0)) {
            $company = $companyRepository->findCompanyBySystemIdAccountAndContact(
                intval($arrCompany['front_id']),
                $contact,
                $country
            );
        }

        if (null != $company) {
            $logger->addInfo(sprintf("Found company by front_id %d", intval($arrCompany['front_id'])));

            return $company;
        }

        $typeId = intval($arrCompany['type']);

        if ($typeId == self::CUSTOMER_TYPE_PF) {
            $email = $contact->getEmail();
            if (!empty($email)) {
                $company = $companyRepository->findCompanyByEmailAndContact($contact->getEmail(), $contact, $country);

                if (null != $company) {
                    $logger->addInfo(sprintf("Found company by email '%s'", $contact->getEmail()));

                    return $company;
                }
            } else {
                $company = $companyRepository->findCompanyByNameAndContact($contact->getName(), $contact, $country);

                if (null != $company) {
                    $logger->addInfo(sprintf("Found company by name '%s'", $contact->getName()));

                    return $company;
                }
            }
        } else {
            $company = $companyRepository->findCompanyByFiscalCodeAndContact(
                $arrCompany['fiscal_code'],
                $contact,
                $country
            );

            if (null != $company) {
                $logger->addInfo(sprintf("Found company by fiscal_code '%s'", $arrCompany['fiscal_code']));

                return $company;
            }
        }

        if ((isset($arrCompany['system_id'])) && (intval($arrCompany['system_id']) > 0)) {
            $company = $companyRepository->findCompanyByIdAndAccountAndContact(
                intval($arrCompany['system_id']),
                $contact,
                $country
            );
        }

        if (null != $company) {
            $logger->addInfo(sprintf("Found company by system_id %d", intval($arrCompany['system_id'])));

            return $company;
        }

        return null;
    }
}
