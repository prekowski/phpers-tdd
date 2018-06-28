<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * \AppBundle\Entity\Country
 * @ORM\Table(name="country")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CountryRepository")
 */
class Country
{
    const ALL = 9e9;

    const CODE_RO = 'RO';
    const ID_RO = 1;

    const CODE_BG = 'BG';
    const ID_BG = 2;

    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    const CODE_HU = 'HU';
    const ID_HU = 3;

    const CODE_PL = 'PL';
    const ID_PL = 4;

    const CALLING_CODE_RO = '40';
    const CALLING_CODE_BG = '359';
    const CALLING_CODE_HU = '36';
    const CALLING_CODE_PL = '48';

    const AREA_CODE_RO = '40';
    const AREA_CODE_BG = '359';
    const AREA_CODE_HU = '36';
    const AREA_CODE_PL = '48';

    public static $allCountries = array(
        self::ID_RO => self::CODE_RO,
        self::ID_BG => self::CODE_BG,
        self::ID_HU => self::CODE_HU,
        self::ID_PL => self::CODE_PL
    );

    public static $areaCodes = array(
        self::ID_RO => self::AREA_CODE_RO,
        self::ID_BG => self::AREA_CODE_BG,
        self::ID_HU => self::AREA_CODE_HU,
        self::ID_PL => self::AREA_CODE_PL
    );

    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=45, nullable=true, name="country_name")
     */
    protected $countryName;

    /**
     * @ORM\Column(type="string", length=45, nullable=true, name="country_name_latin")
     */
    protected $countryNameLatin;

    /**
     * @ORM\Column(type="string", length=45, nullable=true, name="country_code")
     */
    protected $countryCode;

    /**
     * @ORM\ManyToOne(targetEntity="\AppBundle\Entity\Currency")
     * @ORM\JoinColumn(name="default_currency_id", referencedColumnName="currencies_id")
     */
    protected $defaultCurrency;

    /**
     * @ORM\Column(type="integer", nullable=true, name="selling_index")
     */
    protected $sellingIndex;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $modified;

    /**
     * @ORM\Column(type="integer")
     */
    protected $status;

    /**
     * @ORM\OneToMany(targetEntity="Locality", mappedBy="country", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id")
     */
    protected $localities;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="\AppBundle\Entity\PaymentModeCountry", mappedBy="country", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", nullable=true)
     */
    protected $paymentModeCountries;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="\AppBundle\Entity\DeliveryModeCountry", mappedBy="country",
     *                                                                            fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", nullable=true)
     */
    protected $deliveryModeCountries;

    /**
     * @ORM\OneToOne(targetEntity="Locale", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="default_locale", referencedColumnName="id")
     */
    protected $defaultLocale;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Locale", mappedBy="country", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id")
     */
    protected $locales;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="\AppBundle\Entity\CurrencyCountry", mappedBy="country", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", nullable=true)
     */
    protected $currencyCountries;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="\AppBundle\Entity\CategoryCountry", mappedBy="country", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", nullable=true)
     */
    protected $categoryCountries;

    public function __construct()
    {
        $this->localities = new ArrayCollection();
        $this->locales = new ArrayCollection();
        $this->deliveryModeCountries = new ArrayCollection();
        $this->paymentModeCountries = new ArrayCollection();
        $this->currencyCountries = new ArrayCollection();
        $this->categoryCountries = new ArrayCollection();
    }

    /**
     * @return array
     */
    public static function getAllCountries()
    {
        return self::$allCountries;
    }

    /**
     * @param array $allCountries
     */
    public static function setAllCountries($allCountries)
    {
        self::$allCountries = $allCountries;
    }

    /**
     * @return array
     */
    public static function getAreaCodes()
    {
        return self::$areaCodes;
    }

    /**
     * @param array $areaCodes
     */
    public static function setAreaCodes($areaCodes)
    {
        self::$areaCodes = $areaCodes;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getCountryName()
    {
        return $this->countryName;
    }

    /**
     * @param mixed $countryName
     */
    public function setCountryName($countryName)
    {
        $this->countryName = $countryName;
    }

    /**
     * @return mixed
     */
    public function getCountryNameLatin()
    {
        return $this->countryNameLatin;
    }

    /**
     * @param mixed $countryNameLatin
     */
    public function setCountryNameLatin($countryNameLatin)
    {
        $this->countryNameLatin = $countryNameLatin;
    }

    /**
     * @return mixed
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @param mixed $countryCode
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;
    }

    /**
     * @return mixed
     */
    public function getDefaultCurrency()
    {
        return $this->defaultCurrency;
    }

    /**
     * @param mixed $defaultCurrency
     */
    public function setDefaultCurrency($defaultCurrency)
    {
        $this->defaultCurrency = $defaultCurrency;
    }

    /**
     * @return mixed
     */
    public function getSellingIndex()
    {
        return $this->sellingIndex;
    }

    /**
     * @param mixed $sellingIndex
     */
    public function setSellingIndex($sellingIndex)
    {
        $this->sellingIndex = $sellingIndex;
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
     */
    public function setCreated($created)
    {
        $this->created = $created;
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
     */
    public function setModified($modified)
    {
        $this->modified = $modified;
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
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getLocalities()
    {
        return $this->localities;
    }

    /**
     * @param mixed $localities
     */
    public function setLocalities($localities)
    {
        $this->localities = $localities;
    }

    /**
     * @return ArrayCollection
     */
    public function getPaymentModeCountries()
    {
        return $this->paymentModeCountries;
    }

    /**
     * @param ArrayCollection $paymentModeCountries
     */
    public function setPaymentModeCountries($paymentModeCountries)
    {
        $this->paymentModeCountries = $paymentModeCountries;
    }

    /**
     * @return ArrayCollection
     */
    public function getDeliveryModeCountries()
    {
        return $this->deliveryModeCountries;
    }

    /**
     * @param ArrayCollection $deliveryModeCountries
     */
    public function setDeliveryModeCountries($deliveryModeCountries)
    {
        $this->deliveryModeCountries = $deliveryModeCountries;
    }

    /**
     * @return mixed
     */
    public function getDefaultLocale()
    {
        return $this->defaultLocale;
    }

    /**
     * @param mixed $defaultLocale
     */
    public function setDefaultLocale($defaultLocale)
    {
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * @return ArrayCollection
     */
    public function getLocales()
    {
        return $this->locales;
    }

    /**
     * @param ArrayCollection $locales
     */
    public function setLocales($locales)
    {
        $this->locales = $locales;
    }

    /**
     * @return ArrayCollection
     */
    public function getCurrencyCountries()
    {
        return $this->currencyCountries;
    }

    /**
     * @param ArrayCollection $currencyCountries
     */
    public function setCurrencyCountries($currencyCountries)
    {
        $this->currencyCountries = $currencyCountries;
    }

    /**
     * @return ArrayCollection
     */
    public function getCategoryCountries()
    {
        return $this->categoryCountries;
    }

    /**
     * @param ArrayCollection $categoryCountries
     */
    public function setCategoryCountries($categoryCountries)
    {
        $this->categoryCountries = $categoryCountries;
    }
}
