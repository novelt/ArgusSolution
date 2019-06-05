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

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SesDashboardContactRepository")
 * @ORM\Table(name="sesdashboard_contacts", options={"collate"="utf8_general_ci"})
 *
 * @UniqueEntity(fields="phoneNumber", message="This phoneNumber already exists.")
 *
 * @JMS\XmlRoot("contact")
 * @JMS\ExclusionPolicy("all")
 * @JMS\AccessorOrder("custom", custom = {"phoneNumber", "siteReference", "name", "imei", "imei2", "enabled", "email", "note", "alertPreferredGateway", "contactTypeReference"})
 */
class SesDashboardContact
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", unique=true)
     * @JMS\Expose
     */
    private $phoneNumber;

    /**
     * Site reference
     *
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $siteReference;

    /**
     * @ORM\Column(type="string")
     * @JMS\Expose
     */
    private $name;

    /**
     * A string 'Yes' or 'No'.
     *
     * Equivalent to NOT $isDeleted, but for the xml file.
     *
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $enabled;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @JMS\Expose
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email.",
     *     checkMX = false
     * )
     */
    private $email;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @JMS\Expose
     */
    private $note;

    /**
     * Equivalent to NOT $enabled, but for the database.
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isDeleted;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $FK_SiteId;

    /**
     * @var SesDashboardSite
     * @ORM\ManyToOne(targetEntity="SesDashboardSite", inversedBy="contacts")
     * @ORM\JoinColumn(name="FK_SiteId", referencedColumnName="id", nullable=false)
     */
    private $site;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $contactTypeId;

    /**
     * @var SesDashboardContactType
     * @ORM\ManyToOne(targetEntity="SesDashboardContactType", inversedBy="contacts")
     * @ORM\JoinColumn(name="contactTypeId", referencedColumnName="id")
     */
    private $contactType;

    /**
     * @var string
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $contactTypeReference;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @JMS\Expose
     */
    private $imei;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @JMS\Expose
     */
    private $imei2;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @JMS\Expose
     */
    private $alertPreferredGateway;

    public function __construct()
    {
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getSiteReference()
    {
        return $this->siteReference;
    }

    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    public function getImei()
    {
        return $this->imei;
    }

    public function setImei($imei)
    {
        $this->imei = $imei;
    }

    public function getImei2()
    {
        return $this->imei2;
    }

    public function setImei2($imei2)
    {
        $this->imei2 = $imei2;
    }

    /**
     * @return mixed
     */
    public function getAlertPreferredGateway()
    {
        return $this->alertPreferredGateway;
    }

    /**
     * @param mixed $alertPreferredGateway
     */
    public function setAlertPreferredGateway($alertPreferredGateway)
    {
        $this->alertPreferredGateway = $alertPreferredGateway;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getNote()
    {
        return $this->note;
    }

    public function setNote($note)
    {
        $this->note = $note;
    }

    public function getSiteId()
    {
        return $this->FK_SiteId;
    }

    public function setSiteId($siteId)
    {
        $this->FK_SiteId = $siteId;
    }

    public function getSite()
    {
        return $this->site;
    }

    public function setSite(SesDashboardSite $site)
    {
        $this->site = $site;

        // Set reference
        $this->siteReference = $site->getReference();
    }

    public function unsetSite() {
        $this->site = null;

        $this->siteReference = '';
    }

    public function getDeleted()
    {
        // Synonym
        return $this->IsDeleted();
    }

    /**
     * @return int
     */
    public function getContactTypeId()
    {
        return $this->contactTypeId;
    }

    /**
     * @param int $contactTypeId
     */
    public function setContactTypeId($contactTypeId)
    {
        $this->contactTypeId = $contactTypeId;
    }

    /**
     * @return SesDashboardContactType
     */
    public function getContactType()
    {
        return $this->contactType;
    }

    /**
     * @param SesDashboardContactType $contactType
     */
    public function setContactType($contactType)
    {
        $this->contactType = $contactType;
    }

    /**
     * @return mixed
     */
    public function getContactTypeReference()
    {
        return $this->contactTypeReference;
    }

    /**
     * Set the 'deleted' status
     *
     * See also setEnabled.
     *
     * Intended use: enable/disable switch buttons on the contact list page.
     *
     * @param $value
     */
    public function setDeleted($value)
    {
        // Voluntary redundancy:
        $this->enabled = $value ? Constant::JMS_NO : Constant::JMS_YES;

        $this->isDeleted = (boolean) $value;
    }

    public function IsDeleted()
    {
        return $this->isDeleted;
    }

    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set the 'enabled' status
     *
     * See also setDeleted.
     *
     * Intended use: when loading contacts from xml.
     *
     * @param $enabled
     */
    public function setEnabled($enabled)
    {
        // Voluntary redundancy:
        $this->enabled = $enabled;

        $this->isDeleted = (Constant::JMS_NO === $enabled);
    }


    /**
     * Create a new Contact
     *
     * @param $phoneNumber
     * @param $name
     * @param SesDashboardSite $site
     * @param null $email
     * @param null $note
     * @param bool $isDeleted
     * @param null $imei
     * @param null $imei2
     * @param SesDashboardContactType|null $contactType
     *
     * @return SesDashboardContact
     */
    public static function createNewInstance($phoneNumber, $name, SesDashboardSite $site, $email = null, $note = null, $isDeleted = false, $imei = null, $imei2 = null, SesDashboardContactType $contactType=null)
    {
        $instance = new SesDashboardContact();
        $instance->setPhoneNumber($phoneNumber);
        $instance->setName($name);
        $instance->setSite($site);
        $instance->setEmail($email);
        $instance->setNote($note);
        $instance->setDeleted($isDeleted);
        $instance->setImei($imei);
        $instance->setImei2($imei2);
        $instance->setContactType($contactType);

        return $instance;
    }


    /**
     * Get CSV Headers
     *
     * @return array
     */
    public static function getHeaderCsvRow()
    {
        $row = array() ;
        $row[] = 'Contact Id';
        $row[] = 'Name';
        $row[] = 'PhoneNumber';
        $row[] = 'Email';
        $row[] = 'Note';
        $row[] = 'Site Id';

        return $row;
    }

    /**
     * Get CSV Site format
     *
     * @return array
     */
    public function getCsvRow()
    {
        $row = array() ;
        $row[] = self::getId();
        $row[] = self::getName();
        $row[] = self::getPhoneNumber();
        $row[] = self::getEmail();
        $row[] = self::getNote();
        $row[] = self::getSiteId();

        return $row;
    }

    /**
     * @JMS\PreSerialize
     */
    public function prepareSerialization()
    {
        if ($this->site != null) {
            $this->siteReference = $this->site->getReference();
        }

        if ($this->isDeleted) {
            $this->enabled = Constant::JMS_NO;
        } else {
            $this->enabled = Constant::JMS_YES;
        }

        if ($this->contactType != null) {
            $this->contactTypeReference = $this->contactType->getReference();
        }
    }

    /**
     * @JMS\PostDeserialize
     */
    public function postDeserialization()
    {
        if ($this->enabled == Constant::JMS_NO) {
            $this->isDeleted = true ;
        } else {
            $this->isDeleted = false ;
        }
    }

    public function addNote($note, $appendDate = true) {
        $separator = "";

        if(!empty($this->note)) {
            $separator = "\r\n";
        }

        if($appendDate) {
            $note = sprintf("%s - %s", date('Y-m-d H:i:s'), $note);
        }

        $this->note .= ($separator.$note);
    }
}