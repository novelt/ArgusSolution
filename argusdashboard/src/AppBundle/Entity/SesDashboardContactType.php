<?php
/**
 * Contact Entity Class
 *
 * Important notes about 'enabled' and 'isDeleted' (fc 2016-04-04):
 *
 * * The private attributes $isDeleted and $enabled are the exact opposites of each other (voluntary redundancy).
 * * Indeed, $isDeleted means that the contact is logically deleted, i.e. not enabled.
 * * The role of $isDeleted is EXACTLY the same as in the SesDashboardSite entity.
 * * The name of "$enabled" and its values are conform to the XML file ('Yes' and 'No', see xsd definition).
 * * On the other side, the values of $isDeleted are boolean true or false (in PHP) or tinyint 1 or 0 (in MySQL).
 *
 * @author François Cardinaux, inspired by Emmanuel Otin's SesDashboardSite.php
 */

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SesDashboardContactTypeRepository")
 * @ORM\Table(name="sesdashboard_contacttype", options={"collate"="utf8_general_ci"})
 *
 * @UniqueEntity(fields="reference", message="This reference already exists.")
 *
 * @JMS\XmlRoot("contact_type")
 * @JMS\ExclusionPolicy("all")
 */
class SesDashboardContactType extends BaseEntity
{
    /**
     * @var string
     * @ORM\Column(type="string")
     * @JMS\Expose
     */
    private $name;

    /**
     * @ORM\Column(type="string", unique=true)
     * @Assert\NotBlank()
     * @JMS\Expose
     * @JMS\Type("string")
     *
     */
    private $reference;

    /**
     * @var string
     * @ORM\Column(type="string", name="`desc`", nullable=true)
     * @JMS\Expose
     */
    private $desc;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     * @JMS\Expose
     */
    private $sendsReports;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     * @JMS\Expose
     */
    private $useInIndicatorsCalculation;

    /**
     * @var SesDashboardContact[]
     * @ORM\OneToMany(targetEntity="SesDashboardContact", mappedBy="contactType")
     */
    private $contacts;

    public function __construct()
    {
        parent::__construct();
        $this->contacts = new ArrayCollection();
    }

    /**
     * @param $reference
     * @param $name
     * @param bool $useInIndicatorsCalculation
     * @param bool $sendsReports
     *
     * @return SesDashboardContactType
     */
    public static function createNewInstance($reference, $name, $useInIndicatorsCalculation = false, $sendsReports = false)
    {
        $instance = new SesDashboardContactType();
        $instance->setName($name);
        $instance->setReference($reference);
        $instance->setUseInIndicatorsCalculation($useInIndicatorsCalculation);
        $instance->setSendsReports($sendsReports);

        return $instance;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDesc()
    {
        return $this->desc;
    }

    /**
     * @param string $desc
     */
    public function setDesc($desc)
    {
        $this->desc = $desc;
    }


    /**
     * @return SesDashboardContact[]
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * @param SesDashboardContact[] $contacts
     */
    public function setContacts($contacts)
    {
        $this->contacts = $contacts;
    }

    /**
     * @return boolean
     */
    public function isSendsReports()
    {
        return $this->sendsReports;
    }

    /**
     * @param boolean $sendsReports
     */
    public function setSendsReports($sendsReports)
    {
        $this->sendsReports = $sendsReports;
    }

    /**
     * @param $reference
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
    }

    /**
     * @return mixed
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @return int
     */
    public function getNbContacts()
    {
        return count($this->getContacts());
    }

    /**
     * @return bool
     */
    public function isUseInIndicatorsCalculation()
    {
        return $this->useInIndicatorsCalculation;
    }

    /**
     * @param bool $useInIndicatorsCalculation
     */
    public function setUseInIndicatorsCalculation($useInIndicatorsCalculation)
    {
        $this->useInIndicatorsCalculation = $useInIndicatorsCalculation;
    }
}