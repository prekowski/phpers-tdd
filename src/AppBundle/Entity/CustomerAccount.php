<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * \AppBundle\Entity\CustomerAccount
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CustomerAccountRepository")
 * @ORM\Table(
 * name="customers_accounts",
 * indexes={
 * @ORM\Index(name="customers_responsabil", columns={"customers_responsabil"}),
 * @ORM\Index(name="customer_j", columns={"customer_j"}),
 * @ORM\Index(name="customer_company", columns={"customer_company"}),
 * @ORM\Index(name="customers_sold", columns={"customers_sold"}),
 * @ORM\Index(name="index1", columns={"customers_account_id", "customer_company"}),
 * @ORM\Index(name="i_clasaClient", columns={"clasa_client"}),
 * @ORM\Index(name="i_customerType", columns={"customer_type"}),
 * @ORM\Index(name="index2", columns={"customers_account_id"}),
 * @ORM\Index(name="i_customer_code", columns={"customer_code"}),
 * @ORM\Index(name="i_data_adaugat", columns={"data_adaugat"}),
 * @ORM\Index(name="fk_customers_accounts_country1_idx", columns={"country_id"})
 * })
 * @ORM\HasLifecycleCallbacks()
 */
class CustomerAccount
{
    const CLASS_DEFAULT = 0;
    const CLASS_RETAIL = 1;
    const CLASS_CORPORATE = 2;

    const STATUS_REMOVED = -1;
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    const CUSTOMER_SODEXO = 50778610;

    const DISTRIBUTION_CLIENT = 1;
    const NOT_DISTRIBUTION_CLIENT = 0;

    const TYPE_PF = 0;

    /** @var int */
    const UNCESSIONED = 0;
    /** @var int */
    const CESSIONED_ING = 1;
    /** @var int */
    const CESSIONED_BRD = 2;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="customers_account_id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="customer_company")
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=32, nullable=true, name="customer_code")
     */
    protected $fiscalCode;

    /**
     * @ORM\ManyToOne(targetEntity="\AppBundle\Entity\CustomerType", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="customer_type", referencedColumnName="id", nullable=false)
     */
    protected $customerType;

    /**
     * @ORM\Column(type="boolean", name="in_socrate")
     */
    protected $inErp;

    /**
     * @ORM\Column(type="decimal", precision=11, scale=2, nullable=true, name="customers_sold")
     */
    protected $balance;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="erp_id")
     */
    protected $erpId = false;


    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $modified;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $status = true;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Country", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", nullable=false)
     */
    protected $country;

    /**
     * CustomerAccount constructor.
     */
    public function __construct()
    {
        $this->setModified(new \DateTime());
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     *
     * @return CustomerAccount
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     *
     * @return CustomerAccount
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFiscalCode()
    {
        return $this->fiscalCode;
    }

    /**
     * @param mixed $fiscalCode
     *
     * @return CustomerAccount
     */
    public function setFiscalCode($fiscalCode)
    {
        $this->fiscalCode = $fiscalCode;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCustomerType()
    {
        return $this->customerType;
    }

    /**
     * @param mixed $customerType
     *
     * @return CustomerAccount
     */
    public function setCustomerType($customerType)
    {
        $this->customerType = $customerType;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getInErp()
    {
        return $this->inErp;
    }

    /**
     * @param mixed $inErp
     *
     * @return CustomerAccount
     */
    public function setInErp($inErp)
    {
        $this->inErp = $inErp;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * @param mixed $balance
     *
     * @return CustomerAccount
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getErpId()
    {
        return $this->erpId;
    }

    /**
     * @param mixed $erpId
     *
     * @return CustomerAccount
     */
    public function setErpId($erpId)
    {
        $this->erpId = $erpId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param mixed $created
     *
     * @return CustomerAccount
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * @param mixed $modified
     *
     * @return CustomerAccount
     */
    public function setModified($modified)
    {
        $this->modified = $modified;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     *
     * @return CustomerAccount
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     *
     * @return CustomerAccount
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }
}
