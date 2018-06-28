<?php

namespace AppBundle\Service;

use AppBundle\Entity\Country;
use AppBundle\Entity\CustomerCompany;
use AppBundle\Entity\CustomerContact;
use AppBundle\Repository\CustomerContactRepository;
use Symfony\Component\DependencyInjection\Container;

class CustomerContactService
{
    const ID = 'eis.customer.customer_contact';

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
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return \Doctrine\Bundle\DoctrineBundle\Registry
     */
    public function getDoctrine()
    {
        return $this->doctrine;
    }

    /**
     * @param CustomerContact $entity
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function saveContact(CustomerContact $entity)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $isNew = $entity->getId() === null;
        if ($isNew) {
            $this->checkIfEmailExists($entity->getEmail(), $entity->getCountry());
        }
        $em->persist($entity);

        /** @var \AppBundle\Service\CustomerCompanyService $companyService */
        $companyService = $this->getContainer()->get('customer.customer_company');

        if ($isNew) {
            /** @var \AppBundle\Entity\CustomerType $type */
            $type = $em->getReference(
                'AppBundle:CustomerType',
                CustomerService::CUSTOMER_TYPE_PF
            );

            $company = new CustomerCompany();
            $company->setCustomerType($type)
                ->setName($entity->getName())
                ->setCountry($entity->getCountry())
                ->setCorporateApproved(CustomerCompany::CORPORATE_PENDING)
                ->setStatus(CustomerCompany::STATUS_ACTIVE)
                ->setCustomerContact($entity)
                ->setFiscalCode($entity->getNin())
                ->setEmail($entity->getEmail())
                ->setPhone($entity->getPhone1())
                ->setFax($entity->getFax());

            $companyService->saveCompany($company);
        } else {
            /** @var \AppBundle\Repository\CustomerCompanyRepository $companyRepository */
            $companyRepository = $em->getRepository('AppBundle:CustomerCompany');
            /** @var CustomerCompany[] $companies */
            $companies = $companyRepository->findBy(
                array(
                    'customerContact' => $entity->getId(),
                    'customerType' => CustomerService::CUSTOMER_TYPE_PF
                ),
                array('id' => 'asc')
            );

            if (!empty($companies)) {
                foreach ($companies as $company) {
                    if (($company->getName() != $entity->getName()) ||
                        $company->getFiscalCode() != $entity->getNin() ||
                        $company->getEmail() != $entity->getEmail() ||
                        $company->getPhone() != $entity->getPhone1() ||
                        $company->getFax() != $entity->getFax()
                    ) {
                        $company->setName($entity->getName())
                            ->setFiscalCode($entity->getNin())
                            ->setEmail($entity->getEmail())
                            ->setPhone($entity->getPhone1())
                            ->setFax($entity->getFax())
                            ->setModified(new \DateTime());

                        $companyService->saveCompany($company);
                    }
                }
            }
        }
    }

    /**
     * @param string  $email
     * @param Country $country
     *
     * @throws \Exception
     */
    public function checkIfEmailExists($email, Country $country)
    {
        if (!empty($email)) {
            $criteria = array(
                'email' => $email,
                'country' => $country,
            );
            $existingContact = $this->getRepository()
                ->findOneBy($criteria);

            if (null != $existingContact) {
                $message = $this->getContainer()->get('translator')->trans('api.contact.exists');
                throw new \Exception(sprintf($message, $email));
            }
        }
    }

    /**
     * @return CustomerContactRepository
     */
    public function getRepository()
    {
        return $this->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:CustomerContact');
    }
}
