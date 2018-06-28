<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * AppBundle\Entity\Locality
 * @ORM\Entity(repositoryClass="AppBundle\Repository\LocalityRepository")
 * @ORM\Table(name="locality", indexes={@ORM\Index(name="fk_locality_country1_idx", columns={"country_id"})})
 */
class Locality
{
    const REPOSITORY = 'LocalityBundle:Locality';

    const BUCHAREST = 'Bucuresti';
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     */
    protected $region1;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     */
    protected $region2;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     */
    protected $region3;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     */
    protected $region4;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=60, nullable=true, name="region1_latin")
     */
    protected $region1Latin;

    /**
     * @ORM\Column(type="string", length=60, nullable=true, name="region2_latin")
     */
    protected $region2Latin;

    /**
     * @ORM\Column(type="string", length=60, nullable=true, name="region3_latin")
     */
    protected $region3Latin;

    /**
     * @ORM\Column(type="string", length=60, nullable=true, name="region4_latin")
     */
    protected $region4Latin;

    /**
     * @ORM\Column(type="string", length=60, nullable=true, name="name_latin")
     */
    protected $nameLatin;

    /**
     * @ORM\Column(name="geoid", type="bigint", nullable=true)
     */
    protected $geoId;

    /**
     * @ORM\Column(type="\DateTime", nullable=true)
     */
    protected $created;

    /**
     * @ORM\Column(type="\DateTime", nullable=true)
     */
    protected $modified;

    /**
     * @ORM\Column(type="integer")
     */
    protected $status;

    /**
     * @ORM\OneToMany(targetEntity="PostalCode", mappedBy="locality", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="locality_id", referencedColumnName="id")
     */
    protected $postalCodes;

    /**
     * @ORM\ManyToOne(targetEntity="Country", inversedBy="localities", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id")
     */
    protected $country;

    protected $sameDay;

    public function __construct()
    {
        $this->postalCodes = new ArrayCollection();
    }

    /**
     * Set the value of id.
     *
     * @param integer $id
     *
     * @return Locality
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of id.
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of region1.
     *
     * @param string $region1
     *
     * @return Locality
     */
    public function setRegion1($region1)
    {
        $this->region1 = $region1;

        return $this;
    }

    /**
     * Get the value of region1.
     * @return string
     */
    public function getRegion1()
    {
        return $this->region1;
    }

    /**
     * Set the value of region2.
     *
     * @param string $region2
     *
     * @return Locality
     */
    public function setRegion2($region2)
    {
        $this->region2 = $region2;

        return $this;
    }

    /**
     * Get the value of region2.
     * @return string
     */
    public function getRegion2()
    {
        return $this->region2;
    }

    /**
     * Set the value of region3.
     *
     * @param string $region3
     *
     * @return Locality
     */
    public function setRegion3($region3)
    {
        $this->region3 = $region3;

        return $this;
    }

    /**
     * Get the value of region3.
     * @return string
     */
    public function getRegion3()
    {
        return $this->region3;
    }

    /**
     * Set the value of region4.
     *
     * @param string $region4
     *
     * @return Locality
     */
    public function setRegion4($region4)
    {
        $this->region4 = $region4;

        return $this;
    }

    /**
     * Get the value of region4.
     * @return string
     */
    public function getRegion4()
    {
        return $this->region4;
    }

    /**
     * Set the value of name.
     *
     * @param string $name
     *
     * @return Locality
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of name.
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of region1Latin.
     *
     * @param string $region1Latin
     *
     * @return Locality
     */
    public function setRegion1Latin($region1Latin)
    {
        $this->region1Latin = $region1Latin;

        return $this;
    }

    /**
     * Get the value of region1Latin.
     * @return string
     */
    public function getRegion1Latin()
    {
        return $this->region1Latin;
    }

    /**
     * Set the value of region2Latin.
     *
     * @param string $region2Latin
     *
     * @return Locality
     */
    public function setRegion2Latin($region2Latin)
    {
        $this->region2Latin = $region2Latin;

        return $this;
    }

    /**
     * Get the value of region2Latin.
     * @return string
     */
    public function getRegion2Latin()
    {
        return $this->region2Latin;
    }

    /**
     * Set the value of region3Latin.
     *
     * @param string $region3Latin
     *
     * @return Locality
     */
    public function setRegion3Latin($region3Latin)
    {
        $this->region3Latin = $region3Latin;

        return $this;
    }

    /**
     * Get the value of region3Latin.
     * @return string
     */
    public function getRegion3Latin()
    {
        return $this->region3Latin;
    }

    /**
     * Set the value of region4Latin.
     *
     * @param string $region4Latin
     *
     * @return Locality
     */
    public function setRegion4Latin($region4Latin)
    {
        $this->region4Latin = $region4Latin;

        return $this;
    }

    /**
     * Get the value of region4Latin.
     * @return string
     */
    public function getRegion4Latin()
    {
        return $this->region4Latin;
    }

    /**
     * Set the value of nameLatin.
     *
     * @param string $nameLatin
     *
     * @return Locality
     */
    public function setNameLatin($nameLatin)
    {
        $this->nameLatin = $nameLatin;

        return $this;
    }

    /**
     * Get the value of nameLatin.
     * @return string
     */
    public function getNameLatin()
    {
        return $this->nameLatin;
    }

    /**
     * Set the value of geoId.
     *
     * @param integer $geoId
     *
     * @return Locality
     */
    public function setGeoId($geoId)
    {
        $this->geoId = $geoId;

        return $this;
    }

    /**
     * Get the value of geoId.
     * @return integer
     */
    public function getGeoId()
    {
        return $this->geoId;
    }

    /**
     * Set the value of created.
     *
     * @param \DateTime $created
     *
     * @return Locality
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get the value of created.
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set the value of modified.
     *
     * @param \DateTime $modified
     *
     * @return Locality
     */
    public function setModified($modified)
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * Get the value of modified.
     * @return \DateTime
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Set the value of status.
     *
     * @param integer $status
     *
     * @return Locality
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the value of status.
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set Country entity (many to one).
     *
     * @param Country $country
     *
     * @return Locality
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
     * @return mixed
     */
    public function isSameDay()
    {
//        return $this->sameDay;

        return true;
    }

    /**
     * @param mixed $sameDay
     */
    public function setSameDay($sameDay)
    {
        $this->sameDay = $sameDay;
    }

    public function __sleep()
    {
        return array(
            'id',
            'region1',
            'region2',
            'region3',
            'region4',
            'name',
            'region1Latin',
            'region2Latin',
            'region3Latin',
            'region4Latin',
            'nameLatin',
            'geoId',
            'created',
            'modified',
            'status',
            'country'
        );
    }

    /**
     * @return string
     */
    public function getLocalityName()
    {
        try {
            switch ($this->getCountry()->getId()) {
                case Country::ID_BG: // Bulgaria
                    // TODO: Format for Bulgaria
                    return "{$this->region2} / {$this->region3} ({$this->name})";
                default: // Romania
                    return "{$this->region2} / {$this->region3} ({$this->name})";
            }
        } catch (\Exception $e) {
            return "{$this->region2} / {$this->region3} ({$this->name})";
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getLocalityName();
    }
}
