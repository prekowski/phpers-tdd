<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * AppBundle\CustomerAddress
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CustomerAddressRepository")
 * @ORM\Table(
 * name="customer_address",
 * indexes={
 * @ORM\Index(name="fk_customer_address_locality1_idx", columns={"locality_id"}),
 * @ORM\Index(name="fk_customer_address_postal_code1_idx", columns={"postal_code_id"}),
 * @ORM\Index(name="fk_customer_address_country1_idx", columns={"country_id"}),
 * @ORM\Index(name="fk_customer_address_customers_accounts_pc1_idx", columns={"contact_id"})
 * })
 * @ORM\HasLifecycleCallbacks()
 * @Assert\Callback(methods={{"AppBundle\Validator\CustomerAddressValidator", "isValid"}})
 */
class CustomerAddress
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    const DEFAULT_TRUE = 1;
    const DEFAULT_FALSE = 0;

    const EXTRA_INFO_BG = '{"floor":{"label":"Eтаж"},"quarter":{"label":"Квартал"}}';

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $address;

    /**
     * @var string
     * @ORM\Column(type="string", length=32, nullable=true, name="customer_phone")
     * @Assert\Length(max=32,maxMessage="This value is too long. It should have {{ limit }} character or less.")
     * @Assert\Regex(pattern="/^[0-9\(\+]{1}[0-9\(\)\/\+ \-]{1,32}[0-9\)]$/", message="Invalid phone number on customer address")
     */
    protected $customerPhone;

    /**
     * @var string
     * @ORM\Column(type="string", length=64, nullable=true, name="customer_name")
     * @Assert\Length(max=64,maxMessage="This value is too long. It should have {{ limit }} character or less.")
     */
    protected $customerName;

    /**
     * @var string
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $landmark;

    /**
     * @var int
     * @ORM\Column(type="boolean", nullable=true, name="is_default_for_billing", options={"default":0})
     */
    protected $isDefaultForBilling = self::DEFAULT_FALSE;

    /**
     * @var int
     * @ORM\Column(type="boolean", nullable=true, name="is_default_for_delivery", options={"default":0})
     */
    protected $isDefaultForDelivery = self::DEFAULT_FALSE;

    /**
     * @var \DateTime
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
     * @var int
     * @ORM\Column(type="boolean", nullable=true, options={"default":1})
     */
    protected $status = self::STATUS_ACTIVE;

    /**
     * @var \AppBundle\Entity\CustomerContact
     * @ORM\ManyToOne(targetEntity="\AppBundle\Entity\CustomerContact", inversedBy="customerAddresses", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="customers_accounts_pc_id", nullable=false)
     */
    protected $customerContact;

    /**
     * @var \AppBundle\Entity\Country
     * @ORM\ManyToOne(targetEntity="\AppBundle\Entity\Country", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id")
     */
    protected $country;

    /**
     * @var string
     * @ORM\Column(type="string", name="extra_info")
     */
    protected $extraInfo = array();

    /**
     * @param integer $id
     *
     * @return \AppBundle\Entity\CustomerAddress
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $address
     *
     * @return CustomerAddress
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $landmark
     *
     * @return CustomerAddress
     */
    public function setLandmark($landmark)
    {
        $this->landmark = $landmark;

        return $this;
    }

    /**
     * @return string
     */
    public function getLandmark()
    {
        return $this->landmark;
    }

    /**
     * @return string
     */
    public function getCustomerPhone()
    {
        return $this->customerPhone;
    }

    /**
     * @param string $customerPhone
     */
    public function setCustomerPhone($customerPhone)
    {
        $this->customerPhone = $customerPhone;
    }

    /**
     * @return string
     */
    public function getCustomerName()
    {
        return $this->customerName;
    }

    /**
     * @param string $customerName
     */
    public function setCustomerName($customerName)
    {
        $this->customerName = $customerName;
    }

    /**
     * @return int
     */
    public function getisDefaultForBilling()
    {
        return $this->isDefaultForBilling;
    }

    /**
     * @param int $isDefaultForBilling
     */
    public function setIsDefaultForBilling($isDefaultForBilling)
    {
        $this->isDefaultForBilling = $isDefaultForBilling;
    }

    /**
     * @return int
     */
    public function getisDefaultForDelivery()
    {
        return $this->isDefaultForDelivery;
    }

    /**
     * @param int $isDefaultForDelivery
     *
     * @return CustomerAddress
     */
    public function setIsDefaultForDelivery($isDefaultForDelivery)
    {
        $this->isDefaultForDelivery = $isDefaultForDelivery;

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
     * @return CustomerAddress
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * @param \DateTime $modified
     *
     * @return CustomerAddress
     */
    public function setModified($modified)
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     *
     * @return CustomerAddress
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return CustomerContact
     */
    public function getCustomerContact()
    {
        return $this->customerContact;
    }

    /**
     * @param CustomerContact $customerContact
     *
     * @return CustomerAddress
     */
    public function setCustomerContact($customerContact)
    {
        $this->customerContact = $customerContact;

        return $this;
    }

    /**
     * @return Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param Country $country
     *
     * @return CustomerAddress
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return string
     */
    public function getExtraInfo()
    {
        return $this->extraInfo;
    }

    /**
     * @param string $extraInfo
     *
     * @return CustomerAddress
     */
    public function setExtraInfo($extraInfo)
    {
        $this->extraInfo = $extraInfo;

        return $this;
    }
}
