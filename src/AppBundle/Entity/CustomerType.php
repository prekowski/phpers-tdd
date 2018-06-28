<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CustomerTypeRepository")
 * @ORM\Table(name="customer_type")
 * @ORM\HasLifecycleCallbacks()
 */
class CustomerType
{
    const CUSTOMER_TYPE_PF = 0;
    const CUSTOMER_TYPE_PJ = 1;
    const CUSTOMER_TYPE_ONG = 10;
    const CUSTOMER_TYPE_SELF_EMPLOYED = 11;
    const CUSTOMER_TYPE_SA = 2;

    const CUSTOMER_CODE_PF = 'PF';
    const CUSTOMER_CODE_PJ = 'PJ';
    const CUSTOMER_CODE_SA = 'SA';

    const COMPANY_TYPE_CODE_SELF_EMPLOYED = 'PFA';
    const COMPANY_TYPE_CODE_COMPANY = 'COMPANY';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=3)
     */
    protected $code;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $name;

    /**
     * @ORM\Column(type="\DateTime", nullable=true)
     */
    protected $created;

    /**
     * @ORM\Column(type="\DateTime", nullable=true)
     */
    protected $modified;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $status = true;

    /**
     * @ORM\OneToMany(targetEntity="\AppBundle\Entity\CustomerCompany", mappedBy="customerType", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="type", referencedColumnName="id")
     */
    protected $customerCompanies;

    public function __construct()
    {
        $this->customerCompanies = new ArrayCollection();
    }

    /**
     * Set the value of id.
     *
     * @param integer $id
     *
     * @return \AppBundle\Entity\CustomerType
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
     * Set the value of code.
     *
     * @param string $code
     *
     * @return \AppBundle\Entity\CustomerType
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get the value of code.
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set the value of name.
     *
     * @param string $name
     *
     * @return \AppBundle\Entity\CustomerType
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
     * Set the value of created.
     *
     * @param \DateTime $created
     *
     * @return \AppBundle\Entity\CustomerType
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
     * @return \AppBundle\Entity\CustomerType
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
     * @param boolean $status
     *
     * @return \AppBundle\Entity\CustomerType
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the value of status.
     * @return boolean
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Add CustomerCompany entity to collection (one to many).
     *
     * @param \AppBundle\Entity\CustomerCompany $customerCompany
     *
     * @return \AppBundle\Entity\CustomerType
     */
    public function addCustomerCompany(CustomerCompany $customerCompany)
    {
        $this->customerCompanies[] = $customerCompany;

        return $this;
    }

    /**
     * Get CustomerCompany entity collection (one to many).
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCustomerCompanies()
    {
        return $this->customerCompanies;
    }

    public function __sleep()
    {
        return array(
            'id',
            'code',
            'name',
            'created',
            'modified',
            'status'
        );
    }

    public function __toString()
    {
        return $this->getCode() . ' - ' . $this->getName();
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->setCreated(new \DateTime());
    }
}
