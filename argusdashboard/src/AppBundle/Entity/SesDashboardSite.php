<?php
/**
 * Site Entity
 *
 * Important notes about 'isDeleted' and 'isDisabled' (fc 2016-04-04):
 *
 * * The private attribute $isDeleted means that the site is logically deleted.
 * * The role of $isDeleted is EXACTLY the same as in the SesDashboardContact entity.
 * * The values of $isDeleted are boolean true or false (in PHP) or tinyint 1 or 0 (in MySQL).
 * * The function "IsDisabled()" has NOTHING to do with logical deletion (i.e. with $isDeleted).
 * * "IsDisabled()" should be renamed to something more specific that corresponds to its use inside the application.
 *
 * @author Emmanuel Otin
 */

namespace AppBundle\Entity;

use AppBundle\Utils\Helper;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use JMS\Serializer\Annotation as JMS;

 /**
  * @ORM\Entity(repositoryClass="AppBundle\Repository\SesDashboardSiteRepository")
  * @ORM\Table(name="sesdashboard_sites", options={"collate"="utf8_general_ci"})
  *
  * @UniqueEntity(fields="reference", message="site.reference.unique")
  *
  * @JMS\XmlRoot("site")
  * @JMS\ExclusionPolicy("all")
  * @JMS\AccessorOrder("custom", custom = {"reference", "parentSiteReference" ,"name"})
  */
class SesDashboardSite implements PermissionSite
{
    const ROOT_REFERENCE = 'SitesRoot';

	/**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", unique=true)
     * @JMS\Expose
     * @Assert\NotBlank()
     */
    private $reference;

    /**
     * @var
     * @ORM\Column(type="integer")
     * @JMS\Expose
     * @Assert\GreaterThanOrEqual(value=0)
     */
    private $weeklyReminderOverrunMinutes;

    /**
     * @var
     * @ORM\Column(type="integer")
     * @JMS\Expose
     * @Assert\GreaterThanOrEqual(value=0)
     */
    private $monthlyReminderOverrunMinutes;

    /**
     * @var
     * @ORM\Column(type="integer")
     * @JMS\Expose
     */
    private $weeklyTimelinessMinutes;

    /**
     * @var
     * @ORM\Column(type="integer")
     * @JMS\Expose
     */
    private $monthlyTimelinessMinutes;

    /**
     * @var string
     * @ORM\Column(type="string", length=2, nullable=true)
     */
    private $locale;
    
    /**
     * @var string
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $timezone;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @JMS\Expose
     */
    private $alertPreferredGateway;

    /**
     * @ORM\OneToMany(targetEntity="SesDashboardSiteRelationShip", mappedBy="parentSite")
     */
    private $sitesRelationShipChildren;

    /**
     * @ORM\OneToMany(targetEntity="SesDashboardSiteRelationShip", mappedBy="site", cascade={"persist", "remove"})
     */
    private $sitesRelationShip;

    /**
     * @ORM\OneToMany(targetEntity="SesDashboardContact", mappedBy="site", cascade={"persist"})
     */
    private $contacts;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Security\SesDashboardUser", mappedBy="site", cascade={"persist"})
     */
    private $users;

    /**
     * @ORM\OneToMany(targetEntity="SesDashboardThreshold", mappedBy="site", cascade={"persist"})
     */
    private $thresholds;

    /**
     * @ORM\OneToMany(targetEntity="SesFullReport", mappedBy="frontLineGroup")
     */
    private $fullReports;

    /**
     * @var string
     * @JMS\Expose
     * @JMS\SerializedName("cascading_alert")
     * @JMS\Type("string")
     */
    private $cascadingAlertJms ;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=true)
     *
     */
    private $cascadingAlert;

    /**
     * @var SesDashboardSiteAlertRecipient[]
     * @ORM\OneToMany(targetEntity="SesDashboardSiteAlertRecipient", mappedBy="site", cascade={"persist"})
     *
     * @JMS\Expose
     * @JMS\SerializedName("alert_recipients")
     * @JMS\XmlList(entry = "recipient_site_reference")
     */
    private $alertRecipients;

    /**
     * @ORM\OneToMany(targetEntity="SesDashboardSiteAlertRecipient", mappedBy="recipientSite", cascade={"persist"})
     */
    private $alertRecipientsOwner;

    /**
     * @ORM\OneToMany(targetEntity="SesAlert", mappedBy="frontLineGroup")
     */
    private $alerts;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $reportDataSourceId;

    /**
     * @var SesDashboardReportDataSource
     * @ORM\ManyToOne(targetEntity="SesDashboardReportDataSource", inversedBy="sites")
     * @ORM\JoinColumn(name="reportDataSourceId", referencedColumnName="id")
     */
    private $reportDataSource;

    public function __construct()
    {
        $this->contacts = new ArrayCollection();
        $this->sitesRelationShip = new ArrayCollection();
        $this->sitesRelationShipChildren = new ArrayCollection();
    }

    /**
     * @return bool
     */
    public function isCascadingAlert()
    {
        return $this->cascadingAlert;
    }

    /**
     * @param bool $cascadingAlert
     */
    public function setCascadingAlert($cascadingAlert)
    {
        $this->cascadingAlert = $cascadingAlert;
    }

    /**
     * Return collection of Children RelationShip
     *
     * @return mixed
     */
    public function getSitesRelationShipChildren()
    {
        return $this->sitesRelationShipChildren;
    }

    /**
     * @param mixed $sitesRelationShipChildren
     */
    public function setSitesRelationShipChildren($sitesRelationShipChildren)
    {
        $this->sitesRelationShipChildren = $sitesRelationShipChildren;
    }

    /**
     * @param mixed $sitesRelationShipChild
     */
    public function addSiteRelationShipChild($sitesRelationShipChild)
    {
        if ($this->sitesRelationShipChildren == null) {
            $this->sitesRelationShipChildren = new ArrayCollection();
        }

        $this->sitesRelationShipChildren->add($sitesRelationShipChild);
    }

    /**
     * Return collection of RelationShip
     *
     * @return mixed
     */
    public function getSitesRelationShip()
    {
        return $this->sitesRelationShip;
    }

    /**
     * @param mixed $sitesRelationShip
     */
    public function addSiteRelationShip($sitesRelationShip)
    {
        if ($this->sitesRelationShip == null) {
            $this->sitesRelationShip = new ArrayCollection();
        }

        $this->sitesRelationShip->add($sitesRelationShip);
    }

    /**
     * Return the latest Active Relation Ship between DimDateFrom and DimDateTo
     *
     * @param null $dimDateFromId
     * @param null $dimDateToId
     * @return SesDashboardSiteRelationShip|null
     * @throws \Exception
     */
    public function getActiveSiteRelationShip($dimDateFromId = null, $dimDateToId = null)
    {
        $relationShips = $this->sitesRelationShip;

        if (isset($relationShips) && $relationShips != null) {
            /** @var SesDashboardSiteRelationShip $relationShip */
            foreach ($relationShips as $relationShip) {
                if ($relationShip->isActiveBetween($dimDateFromId, $dimDateToId)) {
                    return $relationShip;
                }
            }
        }

        return null ;
    }

    /**
     * Return Active or most recent SiteRelationShip
     *
     * @return SesDashboardSiteRelationShip|null
     */
    public function getActiveOrMostRecentSiteRelationShip()
    {
        $relationShip = $this->getActiveSiteRelationShip();

        if ($relationShip == null) {
            $relationShips = $this->sitesRelationShip;

            if ($relationShips == null || count($relationShips) == 0) {
                return null;
            }

            $arrayRelationShip = $relationShips->toArray();

            usort($arrayRelationShip, function(SesDashboardSiteRelationShip $a, SesDashboardSiteRelationShip $b) {
                if ($a->getFKDimDateToId() == null) {
                    return -1;
                }
                if ($b->getFKDimDateToId() == null) {
                    return 1;
                }
                return ($a->getFKDimDateToId() > $b->getFKDimDateToId() ? - 1 : 1);
            });

            $relationShip = $arrayRelationShip[0];
        }

        return $relationShip;
    }

    /**
     * Return the path of the site
     *
     * @return mixed|string
     */
    public function getPath()
    {
        $path = "";
        /** @var SesDashboardSiteRelationShip $lastSiteRelationShip */
        $lastSiteRelationShip = $this->getActiveOrMostRecentSiteRelationShip();

        if ($lastSiteRelationShip != null) {
            $path = $lastSiteRelationShip->getPath();
        }

        return $path;
    }

    /**
     * Return the pathName of the site
     *
     * @param null $dimDateFromId
     * @param null $dimDateToId
     * @return mixed|string
     */
    public function getPathName($dimDateFromId = null, $dimDateToId = null)
    {
        $pathName = "";
        /** @var SesDashboardSiteRelationShip $lastSiteRelationShip */
        $lastSiteRelationShip = $this->getActiveSiteRelationShip($dimDateFromId, $dimDateToId);

        if ($lastSiteRelationShip != null) {
            $pathName = $lastSiteRelationShip->getPathName();
        }

        return $pathName;
    }

    /**
     * Return the Id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the Site Reference
     *
     * @return mixed
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Set the Site Reference
     *
     * @param $reference
     */
    public function setReference($reference)
    {
        $this->reference =  Helper::cleanForReference($reference);
    }

    /**
     * Return the name of the site
     *
     * @param null $dimDateFromId
     * @param null $dimDateToId
     * @return mixed|string
     */
	public function getName($dimDateFromId = null, $dimDateToId = null)
    {
        $name = "";
        /** @var SesDashboardSiteRelationShip $lastSiteRelationShip */
        $lastSiteRelationShip = $this->getActiveSiteRelationShip($dimDateFromId, $dimDateToId);

        if ($lastSiteRelationShip == null) {
            $lastSiteRelationShip = $this->getActiveOrMostRecentSiteRelationShip();
        }

        if ($lastSiteRelationShip != null) {
            $name = $lastSiteRelationShip->getName();
        }

        return $name ;
    }

    /**
     * Return longitude
     *
     * @param null $dimDateFromId
     * @param null $dimDateToId
     * @return mixed|null
     */
    public function getLongitude($dimDateFromId = null, $dimDateToId = null)
    {
        $longitude = null;

        /** @var SesDashboardSiteRelationShip $lastSiteRelationShip */
        $lastSiteRelationShip =  $this->getActiveSiteRelationShip($dimDateFromId, $dimDateToId);

        if ($lastSiteRelationShip != null) {
            $longitude = $lastSiteRelationShip->getLongitude();
        }

        return $longitude;
    }

    /**
     * Return latitude
     *
     * @param null $dimDateFromId
     * @param null $dimDateToId
     * @return mixed|null
     */
    public function getLatitude($dimDateFromId = null, $dimDateToId = null)
    {
        $latitude = null;

        /** @var SesDashboardSiteRelationShip $lastSiteRelationShip */
        $lastSiteRelationShip = $this->getActiveSiteRelationShip($dimDateFromId, $dimDateToId);

        if ($lastSiteRelationShip != null) {
            $latitude = $lastSiteRelationShip->getLatitude();
        }

        return $latitude;
    }

    public function getWeeklyReminderOverrunMinutes()
    {
        return $this->weeklyReminderOverrunMinutes;
    }

    public function getMonthlyReminderOverrunMinutes()
    {
        return $this->monthlyReminderOverrunMinutes;
    }

    public function setWeeklyReminderOverrunMinutes($weeklyReminderOverrunMinutes)
    {
        $this->weeklyReminderOverrunMinutes = $weeklyReminderOverrunMinutes;
    }

    public function setMonthlyReminderOverrunMinutes($monthlyReminderOverrunMinutes)
    {
        $this->monthlyReminderOverrunMinutes = $monthlyReminderOverrunMinutes;
    }

    public function setWeeklyTimelinessMinutes($weeklyTimelinessMinutes)
    {
        $this->weeklyTimelinessMinutes = $weeklyTimelinessMinutes;
    }

    public function getWeeklyTimelinessMinutes()
    {
        return $this->weeklyTimelinessMinutes ;
    }

    public function setMonthlyTimelinessMinutes($monthlyTimelinessMinutes)
    {
        $this->monthlyTimelinessMinutes = $monthlyTimelinessMinutes;
    }

    public function getMonthlyTimelinessMinutes()
    {
        return $this->monthlyTimelinessMinutes ;
    }

    /**
     * Return true if site is deleted
     *
     *
     * @return bool
     */
    public function IsDeleted()
    {
        /** @var SesDashboardSiteRelationShip $relationShip */
        $relationShip =  $this->getActiveOrMostRecentSiteRelationShip();

        if ($relationShip != null) {
            return $relationShip->isDeleted();
        }

        return true;
    }

    public function getDeleted()
    {
        return $this->IsDeleted();
    }


    /**
     * Return the Parent Site reference
     *
     * @param null $dimDateFromId
     * @param null $dimDateToId
     * @return mixed
     */
    public function getParentSiteReference($dimDateFromId = null, $dimDateToId = null)
    {
        $parentReference = null;

        /** @var SesDashboardSiteRelationShip $lastSiteRelationShip */
        $lastSiteRelationShip =  $this->getActiveSiteRelationShip($dimDateFromId, $dimDateToId);

        if ($lastSiteRelationShip != null) {
            $parentSite = $lastSiteRelationShip->getParentSite();
            if ($parentSite != null) {
                $parentReference = $parentSite->getReference();
            }

        }

        return $parentReference;
    }

    public function getFullReports()
    {
        return $this->fullReports;
    }

    /**
     * @return SesDashboardSiteAlertRecipient[]
     */
    public function getAlertRecipientSites()
    {
        return $this->alertRecipients;
    }

    public function setAlertRecipientSites($alertRecipientSites)
    {
        $this->alertRecipients = $alertRecipientSites;
    }

    public function addAlertRecipientSite($alertRecipientSite)
    {
        if ($this->alertRecipients === null) {
            $this->alertRecipients = new ArrayCollection();
        }

        $this->alertRecipients[] = $alertRecipientSite;
    }

    /**
     * @param SesDashboardContact $contact
     */
    public function addContact(SesDashboardContact $contact) {
        if($this->contacts === null) {
            $this->contacts = new ArrayCollection();
        }

        $this->contacts[] = $contact;
    }

    public function getAlertRecipientCount()
    {
        return $this->alertRecipients->count();
    }

    public function getAlerts()
    {
        return $this->alerts;
    }

	public function getUniqueNumber()
	{
		return md5($this->id);
	}

    /**
     * Return Parent Id
     *
     * @return mixed|null
     */
    public function getParentId()
    {
        if ($this->getParent() != null) {
            return $this->getParent()->getId();
        }

        return null;
    }


    /**
     * Return SesDashboardSite
     *
     * @deprecated
     *
     * @return SesDashboardSite[]
     */
    public function getChildren()
    {
        $children = [];

        /** @var SesDashboardSiteRelationShip $childRelationShip */
        foreach ($this->getSitesRelationShipChildren() as $childRelationShip) {
            $children[] = $childRelationShip->getSite();
        }

        return $children;
    }


    public function getChildrenRelationShipIds()
    {
        $relationShipIds = [];

        /** @var SesDashboardSiteRelationShip $childRelationShip */
        foreach ($this->getSitesRelationShipChildren() as $childRelationShip) {
            $relationShipIds[] = $childRelationShip->getId();
        }

        return $relationShipIds;
    }

    public function getRelationShipIds()
    {
        $relationShipIds = [];

        /** @var SesDashboardSiteRelationShip $relationShip */
        foreach ($this->getSitesRelationShip() as $relationShip) {
            $relationShipIds[] = $relationShip->getId();
        }

        return $relationShipIds;
    }

    /**
     * @return SesDashboardContact[]
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * Get Parent
     *
     * @param null $dimDateFromId
     * @param null $dimDateToId
     * @return SesDashboardSite|null
     */
    public function getParent($dimDateFromId = null, $dimDateToId = null)
    {
        $parentSite = null;

        /** @var SesDashboardSiteRelationShip $lastSiteRelationShip */
        $lastSiteRelationShip = $this->getActiveSiteRelationShip($dimDateFromId, $dimDateToId);

        if ($lastSiteRelationShip != null) {
            $parentSite = $lastSiteRelationShip->getParentSite();
        }

        return $parentSite;
    }

    /**
     * Return number of Children
     */
    public function getNbChildren()
    {
        if ($this->getSitesRelationShipChildren() != null) {
            return count($this->getSitesRelationShipChildren());
        }

        return 0;
    }

    public function getNbContacts()
    {
        return count($this->contacts);
    }


    public function isLeaf()
    {
        return $this->getNbChildren() == 0;
    }

    /**
     * Get Level
     *
     * @return mixed|string
     */
    public function getLevel()
    {
        $level = "";
        /** @var SesDashboardSiteRelationShip $lastSiteRelationShip */
        $lastSiteRelationShip = $this->getActiveOrMostRecentSiteRelationShip();

        if ($lastSiteRelationShip != null) {
            $level = $lastSiteRelationShip->getLevel();
        }

        return $level;
    }

    /**
     * Set Level
     *
     * @param $level
     */
    public function setLevel($level)
    {
        /** @var SesDashboardSiteRelationShip $lastSiteRelationShip */
        $lastSiteRelationShip = $this->getSitesRelationShip()[0];

        if ($lastSiteRelationShip != null) {
            $lastSiteRelationShip->setLevel($level);
        }
    }

    /**
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * @param string $timezone
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
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

    /**
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return int
     */
    public function getReportDataSourceId()
    {
        return $this->reportDataSourceId;
    }

    /**
     * @param int $reportDataSourceId
     */
    public function setReportDataSourceId($reportDataSourceId)
    {
        $this->reportDataSourceId = $reportDataSourceId;
    }

    /**
     * @return SesDashboardReportDataSource
     */
    public function getReportDataSource()
    {
        return $this->reportDataSource;
    }

    /**
     * @param SesDashboardReportDataSource $reportDataSource
     */
    public function setReportDataSource($reportDataSource)
    {
        $this->reportDataSource = $reportDataSource;
    }

    /***************************************
     *****    Home Site functions       ****
     ***************************************/

    /**
     * Return true if site is Selectable
     *
     * @param SesDashboardSite $homeSite
     * @return bool
     */
    public function IsSelectable(SesDashboardSite $homeSite)
    {
        if ($homeSite == null) {
            return false;
        }

        if (strpos($this->getPath(),  $homeSite->getPath()) !== false) {
            return true;
        }

       return false ;
    }

    /***************************************
     *****    Home Site functions END  *****
     ***************************************/

    /*** Static Methods ***/

    /**
     * Create a new Site
     *
     * @param $reference
     * @return SesDashboardSite
     */
    public static function createNewInstance($reference)
    {
        $instance = new SesDashboardSite();
        $instance->setReference($reference);
        $instance->setWeeklyReminderOverrunMinutes(0);
        $instance->setMonthlyReminderOverrunMinutes(0);
        $instance->setWeeklyTimelinessMinutes(0);
        $instance->setMonthlyTimelinessMinutes(0);

        return $instance;
    }

    /**************** JMS Property **************/
    /********************************************/

    /**
     * @JMS\Expose()
     * @JMS\Accessor(getter="getParentSiteReferenceForDeSerialization", setter="setParentSiteReferenceForSerialization")
     * @JMS\SerializedName("parent_site_reference")
     * @JMS\Type("string")
     */
    private $parentSiteReference;

    /**
     *
     * @return string
     */
    public function getParentSiteReferenceForDeSerialization()
    {
        $parentSite = $this->getActiveOrMostRecentSiteRelationShip()->getParentSite();

        if ($parentSite != null) {
            return $parentSite->getReference();
        }

        return null ;
    }

    /**
     *
     * @param $parentSiteReference
     */
    public function setParentSiteReferenceForSerialization($parentSiteReference)
    {
        $this->parentSiteReference = $parentSiteReference;
    }

    /**
     * Return the parent Reference from the Xml deserialization
     *
     * @return mixed
     */
    public function getParentReferenceForImport()
    {
        return $this->parentSiteReference;
    }

    /**
     * @JMS\Expose()
     * @JMS\Accessor(getter="getNameForDeSerialization", setter="setNameForSerialization")
     * @JMS\Type("string")
     */
    private $name;

    /**
     *
     * @return string
     */
    public function getNameForDeSerialization()
    {
        return $this->getActiveOrMostRecentSiteRelationShip()->getName();
    }

    /**
     *
     * @param $name
     */
    public function setNameForSerialization($name)
    {
        $this->name = $name;
    }

    /**
     * Return the name from the Xml deserialization
     *
     * @return mixed
     */
    public function getNameForImport()
    {
        return $this->name;
    }

    /**
     * @JMS\PreSerialize
     */
    public function prepareSerialization()
    {
        if ($this->cascadingAlert) {
            $this->cascadingAlertJms = Constant::JMS_YES;
        } else {
            $this->cascadingAlertJms = Constant::JMS_NO;
        }
    }

    /**
     * @JMS\PostDeserialize
     */
    public function postDeserialization()
    {
        if ($this->cascadingAlertJms == Constant::JMS_YES) {
            $this->cascadingAlert = true ;
        } else {
            $this->cascadingAlert = false ;
        }
    }


    /**
     * Get CSV Headers
     *
     * @return array
     */
    public static function getHeaderCsvRow()
    {
        $row = array() ;
        $row[] = 'Site Id';
        $row[] = 'Site Reference';
        $row[] = 'Name';
        $row[] = 'Longitude';
        $row[] = 'Latitude';
        $row[] = 'Level';
        $row[] = 'Path';
        $row[] = 'Parent Site Id';

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
        $row[] = self::getReference();
        $row[] = self::getName();
        $row[] = self::getLongitude();
        $row[] = self::getLatitude();
        $row[] = self::getLevel();
        $row[] = self::getPath();
        $row[] = self::getParentId();

        return $row;
    }
}

