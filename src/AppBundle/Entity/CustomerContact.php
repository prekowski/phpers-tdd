<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CustomerContactRepository")
 * @ORM\Table(
 *  name="customers_accounts_pc",
 *  indexes={
 * @ORM\Index(name="i_contact_email", columns={"contact_email"}),
 * @ORM\Index(name="i_contact_phone", columns={"contact_phone"}),
 * @ORM\Index(name="i_contact_phone2", columns={"contact_phone2"}),
 * @ORM\Index(name="i_contact_mobile_phone", columns={"contact_mobile_phone"}),
 * @ORM\Index(name="i_contact_fax", columns={"contact_fax"}),
 * @ORM\Index(name="i_verificat", columns={"verificat"}),
 * @ORM\Index(name="contact_name", columns={"contact_name"})
 * })
 * @ORM\HasLifecycleCallbacks()
 * @Assert\Callback(methods={{"AppBundle\Validator\CustomerContactValidator", "isValid"}})
 */
class CustomerContact
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    const PRINT_INVOICE_TRUE = 1;
    const PRINT_INVOICE_FALSE = 0;

    const VERIFIED_TRUE = 1;
    const VERIFIED_FALSE = 0;

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer", name="customers_accounts_pc_id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true, name="contact_name")
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true, name="contact_email")
     */
    protected $email;

    /**
     * @var string
     * @ORM\Column(type="string", length=32, nullable=true, name="contact_phone")
     */
    protected $phone1;

    /**
     * @var string
     * @ORM\Column(type="string", length=32, nullable=true, name="contact_phone2")
     */
    protected $phone2;

    /**
     * @var string
     * @ORM\Column(type="string", length=64, nullable=true, name="contact_mobile_phone")
     */
    protected $phone3;

    /**
     * @var string
     * @ORM\Column(type="string", length=128, nullable=true, name="contact_fax")
     */
    protected $fax;

    /**
     * @var \DateTime
     * @ORM\Column(type="date", nullable=true, name="contact_dob")
     */
    protected $birthday;

    /**
     * @var string
     * @ORM\Column(type="string", length=1, nullable=true, name="contact_gender")
     */
    protected $gender;

    /**
     * @var string
     * @ORM\Column(type="string", length=16, nullable=true)
     */
    protected $ip;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=false, name="verificat", options={"default":0})
     */
    protected $verified = self::VERIFIED_FALSE;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true, name="system_id")
     */
    protected $systemId;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $created;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $modified;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=false, options={"default":1})
     */
    protected $status = self::STATUS_ACTIVE;

    /**
     * @var Country
     * @ORM\ManyToOne(targetEntity="Country", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", nullable=false)
     */
    protected $country;

    /**
     * @var Locale
     * @ORM\ManyToOne(targetEntity="\AppBundle\Entity\Locale", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="prefferred_locale_id", referencedColumnName="id", nullable=false)
     */
    protected $locale;

    /**
     * @var string
     * @ORM\Column(type="string", length=100, nullable=true, name="nin")
     */
    protected $nin;

    /**
     * @var string
     * @ORM\Column(type="string", length=30, nullable=false)
     */
    protected $source;

    /**
     * @var string
     * @ORM\Column(type="string", length=32, nullable=true, name="eos_key")
     */
    protected $eosKey;

    /**
     * @var int
     * @ORM\Column(type="integer", name="print_invoice", options={"default":0})
     */
    protected $printInvoice = self::PRINT_INVOICE_FALSE;

    /**
     * @var int
     * @ORM\Column(type="integer", name="subscription_id", nullable=true)
     */
    protected $subscriptionId;

    /**
     * CustomerContact constructor.
     */
    public function __construct()
    {
        $this->modified = new \DateTime();
    }

    /**
     * @param \DateTime $birthday
     *
     * @return CustomerContact
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * @param \DateTime $created
     *
     * @return CustomerContact
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param string $email
     *
     * @return CustomerContact
     */
    public function setEmail($email)
    {
        $this->email = trim($email);

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param integer $systemId
     *
     * @return CustomerContact
     */
    public function setSystemId($systemId)
    {
        $this->systemId = $systemId;

        return $this;
    }

    /**
     * @return integer
     */
    public function getSystemId()
    {
        return $this->systemId;
    }

    /**
     * @param string $fax
     *
     * @return CustomerContact
     */
    public function setFax($fax)
    {
        $this->fax = $fax;

        return $this;
    }

    /**
     * @return string
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * @param string $gender
     *
     * @return CustomerContact
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param integer $id
     *
     * @return CustomerContact
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
     * @param string $ip
     *
     * @return CustomerContact
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param \DateTime $modified
     *
     * @return CustomerContact
     */
    public function setModified($modified)
    {
        $this->modified = $modified;

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
     * @param string $name
     *
     * @return CustomerContact
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $phone1
     *
     * @return CustomerContact
     */
    public function setPhone1($phone1)
    {
        $this->phone1 = $phone1;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhone1()
    {
        return $this->phone1;
    }

    /**
     * @param string $phone2
     *
     * @return CustomerContact
     */
    public function setPhone2($phone2)
    {
        $this->phone2 = $phone2;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhone2()
    {
        return $this->phone2;
    }

    /**
     * @param string $phone3
     *
     * @return CustomerContact
     */
    public function setPhone3($phone3)
    {
        $this->phone3 = $phone3;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhone3()
    {
        return $this->phone3;
    }

    /**
     * @param int $status
     *
     * @return CustomerContact
     */
    public function setStatus($status)
    {
        $this->status = $status;

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
     * @param int $verified
     *
     * @return CustomerContact
     */
    public function setVerified($verified)
    {
        $this->verified = $verified;

        return $this;
    }

    /**
     * @return int
     */
    public function getVerified()
    {
        return $this->verified;
    }

    /**
     * Set Country entity (many to one).
     *
     * @param Country $country
     *
     * @return CustomerContact
     */
    public function setCountry(Country $country = null)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get Country entity (many to one).
     * @return Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set Locale entity (many to one).
     *
     * @param Locale $locale
     *
     * @return CustomerContact
     */
    public function setLocale(Locale $locale = null)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get Locale entity (many to one).
     * @return Locale
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $source
     *
     * @return CustomerContact
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param string $eosKey
     *
     * @return $this
     */
    public function setEosKey($eosKey)
    {
        $this->eosKey = $eosKey;

        return $this;
    }

    /**
     * @return string | null
     */
    public function getEosKey()
    {
        return $this->eosKey;
    }

    /**
     * @param string $nin
     *
     * @return $this
     */
    public function setNin($nin)
    {
        $this->nin = $nin;

        return $this;
    }

    /**
     * @return string
     */
    public function getNin()
    {
        return $this->nin;
    }

    /**
     * @return boolean
     */
    public function getPrintInvoice()
    {
        return $this->printInvoice;
    }

    /**
     * @param boolean $printInvoice
     *
     * @return CustomerContact
     */
    public function setPrintInvoice($printInvoice)
    {
        $this->printInvoice = $printInvoice;

        return $this;
    }

    /**
     * @param $subscriptionId
     * @return $this
     */
    public function setSubscriptionId($subscriptionId)
    {
        $this->subscriptionId = $subscriptionId;
        return $this;
    }

    /**
     * @return int
     */
    public function getSubscriptionId()
    {
        return $this->subscriptionId;
    }

    /**
     * @return string
     */
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

        if ($this->getPrintInvoice() === null) {
            $this->setPrintInvoice(self::PRINT_INVOICE_FALSE);
        }
    }
}
