<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AppBundle\Entity\Locale
 *
 * @ORM\Table(name="locale")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\LocaleRepository")
 */
class Locale
{
    const ID_RO = 1;
    const LOCALE_ID_RO_RO = 1;
    const LOCALE_ID_BG_BG = 2;
    const LOCALE_ID_HU_HU = 3;
    const LOCALE_ID_PL_PL = 4;
    const LOCALE_NAME_RO = 'RO';
    const LOCALE_NAME_BG = 'BG';
    const LOCALE_NAME_HU = 'HU';
    const LOCALE_NAME_PL = 'PL';

    /**
     * @ORM\Id
     * @ORM\Column(type="smallint")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=3, name="language_code")
     */
    protected $languageCode;

    /**
     * @ORM\Column(type="string", length=3, nullable=true, name="country_code")
     */
    protected $countryCode;

    /**
     * !Important: EAGER fetch mode is required
     *
     * @var Country
     *
     * @ORM\ManyToOne(targetEntity="Country", inversedBy="locales", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id")
     */
    protected $country;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $status;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $modified;

    public function __toString()
    {
        return "{$this->languageCode}_{$this->countryCode}";
    }

    /**
     * Set the value of id.
     *
     * @param integer $id
     *
     * @return Locale
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of id.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of languageCode.
     *
     * @param string $languageCode
     *
     * @return Locale
     */
    public function setLanguageCode($languageCode)
    {
        $this->languageCode = $languageCode;

        return $this;
    }

    /**
     * Get the value of languageCode.
     *
     * @return string
     */
    public function getLanguageCode()
    {
        return $this->languageCode;
    }

    /**
     * Set the value of countryCode.
     *
     * @param string $countryCode
     *
     * @return Locale
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * Get the value of countryCode.
     *
     * @return string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * Set the value of status.
     *
     * @param integer $status
     *
     * @return Locale
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the value of status.
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the value of created.
     *
     * @param \DateTime $created
     *
     * @return Locale
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get the value of created.
     *
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
     * @return Locale
     */
    public function setModified($modified)
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * Get the value of modified.
     *
     * @return \DateTime
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * @param Country $country
     * @return Locale
     */
    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @return Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    public function __sleep()
    {
        return array(
            'id',
            'languageCode',
            'countryCode',
            'status',
            'created',
            'modified'
        );
    }
}
