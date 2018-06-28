<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CustomerCompanyRepository")
 * @ORM\Table(
 * name="customer_company",
 * indexes={
 * @ORM\Index(name="fk_customer_company_customer_type1_idx", columns={"type"}),
 * @ORM\Index(name="fk_customer_company_customer_country_sync1_idx", columns={"country_id"}),
 * @ORM\Index(name="fk_customer_company_customers_accounts1_idx", columns={"account_id"}),
 * @ORM\Index(name="fk_customer_company_customers_accounts_pc1_idx", columns={"contact_id"})
 * })
 * @ORM\HasLifecycleCallbacks()
 * @Assert\Callback(methods={{"AppBundle\Validator\CustomerCompanyValidator", "isValid"}})
 */
class CustomerCompany
{
    const CORPORATE_PENDING = 0;
    const CORPORATE_APPROVED = 1;
    const CORPORATE_REJECTED = 2;

    const PAYS_VAT_TRUE = 1;
    const PAYS_VAT_FALSE = 0;

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(type="string", length=45, nullable=true, name="fiscal_code")
     */
    protected $fiscalCode;

    /**
     * @var string
     * @ORM\Column(type="string", length=45, nullable=true, name="registration_number")
     */
    protected $registrationNumber;

    /**
     * @var string
     * @ORM\Column(type="string", length=64, nullable=true, name="bank_account")
     */
    protected $bankAccount;

    /**
     * @var string
     * @ORM\Column(type="string", length=128, nullable=true, name="bank_name")
     */
    protected $bankName;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true, name="system_id")
     */
    protected $systemId;

    /**
     * @var string
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    protected $website;

    /**
     * @var string
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    protected $email;

    /**
     * @var string
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    protected $phone;

    /**
     * @var string
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    protected $fax;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true, name="financially_liable_person")
     */
    protected $flb;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true, name="has_history", options={"default":0})
     */
    protected $hasHistory = 0;

    /**
     * @var CustomerType
     * @ORM\ManyToOne(targetEntity="\AppBundle\Entity\CustomerType", inversedBy="customerCompanies", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="type", referencedColumnName="id")
     */
    protected $customerType;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true, name="pays_vat", options={"default":0})
     */
    protected $paysVat = self::PAYS_VAT_FALSE;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=false, name="corporate_approved", options={"default":0})
     */
    protected $corporateApproved = self::CORPORATE_PENDING;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $created;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $modified;

    /**
     * @ORM\Column(type="boolean", options={"default":1})
     */
    protected $status = self::STATUS_ACTIVE;

    /**
     * @var CustomerContact
     * @ORM\ManyToOne(targetEntity="\AppBundle\Entity\CustomerContact", inversedBy="customerCompanies", fetch="EXTRA_LAZY", cascade={"persist"})
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="customers_accounts_pc_id")
     */
    protected $customerContact;

    /**
     * @var CustomerAccount
     * @ORM\ManyToOne(targetEntity="\AppBundle\Entity\CustomerAccount", inversedBy="customerCompanies", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="customers_account_id", nullable=false)
     */
    protected $customerAccount;

    /**
     * @var Country
     * @ORM\ManyToOne(targetEntity="Country", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id")
     */
    protected $country;

    public function __construct()
    {
        $this->modified = new \DateTime();
    }

    /**
     * Get id
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return CustomerCompany
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set fiscalCode
     *
     * @param string $fiscalCode
     *
     * @return CustomerCompany
     */
    public function setFiscalCode($fiscalCode)
    {
        $this->fiscalCode = $fiscalCode;

        return $this;
    }

    /**
     * Get fiscalCode
     * @return string
     */
    public function getFiscalCode()
    {
        return $this->fiscalCode;
    }

    /**
     * Set registrationNumber
     *
     * @param string $registrationNumber
     *
     * @return CustomerCompany
     */
    public function setRegistrationNumber($registrationNumber)
    {
        $this->registrationNumber = $registrationNumber;

        return $this;
    }

    /**
     * Get registrationNumber
     * @return string
     */
    public function getRegistrationNumber()
    {
        return $this->registrationNumber;
    }

    /**
     * Set bankAccount
     *
     * @param string $bankAccount
     *
     * @return CustomerCompany
     */
    public function setBankAccount($bankAccount)
    {
        $this->bankAccount = $bankAccount;

        return $this;
    }

    /**
     * Get bankAccount
     * @return string
     */
    public function getBankAccount()
    {
        return $this->bankAccount;
    }

    /**
     * Set bankName
     *
     * @param string $bankName
     *
     * @return CustomerCompany
     */
    public function setBankName($bankName)
    {
        $this->bankName = $bankName;

        return $this;
    }

    /**
     * Get bankName
     * @return string
     */
    public function getBankName()
    {
        return $this->bankName;
    }

    /**
     * Set eosId
     *
     * @param integer $systemId
     *
     * @return CustomerCompany
     */
    public function setSystemId($systemId)
    {
        $this->systemId = $systemId;

        return $this;
    }

    /**
     * Get eosId
     * @return integer
     */
    public function getSystemId()
    {
        return $this->systemId;
    }

    /**
     * Set website
     *
     * @param string $website
     *
     * @return CustomerCompany
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return CustomerCompany
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return CustomerCompany
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set fax
     *
     * @param string $fax
     *
     * @return CustomerCompany
     */
    public function setFax($fax)
    {
        $this->fax = $fax;

        return $this;
    }

    /**
     * Get fax
     * @return string
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * Set flb
     *
     * @param string $flb
     *
     * @return CustomerCompany
     */
    public function setFlb($flb)
    {
        $this->flb = $flb;

        return $this;
    }

    /**
     * Get flb
     * @return string
     */
    public function getFlb()
    {
        return $this->flb;
    }

    /**
     * Set hasHistory
     *
     * @param integer $hasHistory
     *
     * @return CustomerCompany
     */
    public function setHasHistory($hasHistory)
    {
        $this->hasHistory = $hasHistory;

        return $this;
    }

    /**
     * Get hasHistory
     * @return integer
     */
    public function getHasHistory()
    {
        return $this->hasHistory;
    }

    /**
     * Set paysVat
     *
     * @param integer $paysVat
     *
     * @return CustomerCompany
     */
    public function setPaysVat($paysVat)
    {
        $this->paysVat = $paysVat;

        return $this;
    }

    /**
     * Get paysVat
     * @return integer
     */
    public function getPaysVat()
    {
        return $this->paysVat;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return CustomerCompany
     */
    public function setCreated(\DateTime $created = null)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set modified
     *
     * @param \DateTime $modified
     *
     * @return CustomerCompany
     */
    public function setModified(\DateTime $modified = null)
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * Get modified
     * @return \DateTime
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Set status
     *
     * @param int $status
     *
     * @return CustomerCompany
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set customerContact
     *
     * @param \AppBundle\Entity\CustomerContact $customerContact
     *
     * @return CustomerCompany
     */
    public function setCustomerContact(CustomerContact $customerContact = null)
    {
        $this->customerContact = $customerContact;

        return $this;
    }

    /**
     * Get customerContact
     * @return \AppBundle\Entity\CustomerContact
     */
    public function getCustomerContact()
    {
        return $this->customerContact;
    }

    /**
     * Set country
     *
     * @param Country $country
     *
     * @return CustomerCompany
     */
    public function setCountry(Country $country = null)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     * @return Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        if ($this->getCreated() === null) {
            $this->setCreated(new \DateTime('now'));
        }
        if ($this->getModified() === null) {
            $this->setModified(new \DateTime('now'));
        }
        if ($this->getCorporateApproved() === null) {
            $this->setCorporateApproved(self::CORPORATE_PENDING);
        }
    }

    /**
     * Set corporateApproved
     *
     * @param integer $corporateApproved
     *
     * @return CustomerCompany
     */
    public function setCorporateApproved($corporateApproved)
    {
        $this->corporateApproved = $corporateApproved;

        return $this;
    }

    /**
     * Get corporateApproved
     * @return integer
     */
    public function getCorporateApproved()
    {
        return (int)$this->corporateApproved;
    }

    /**
     * @return CustomerAccount
     */
    public function getCustomerAccount()
    {
        return $this->customerAccount;
    }

    /**
     * @param CustomerAccount $customerAccount
     *
     * @return CustomerCompany
     */
    public function setCustomerAccount($customerAccount)
    {
        $this->customerAccount = $customerAccount;

        return $this;
    }

    /**
     * @return CustomerType
     */
    public function getCustomerType()
    {
        return $this->customerType;
    }

    /**
     * @param CustomerType $customerType
     *
     * @return CustomerCompany
     */
    public function setCustomerType($customerType)
    {
        $this->customerType = $customerType;

        return $this;
    }
}
