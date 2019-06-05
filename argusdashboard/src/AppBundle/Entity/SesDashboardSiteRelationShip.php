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

use AppBundle\Utils\DimDateHelper;
use AppBundle\Utils\Epidemiologic;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use AppBundle\Utils\Helper;
use JMS\Serializer\Annotation as JMS;

 /**
  * @ORM\Entity(repositoryClass="AppBundle\Repository\SesDashboardSiteRelationShipRepository")
  * @ORM\Table(name="sesdashboard_sites_relationship", options={"collate"="utf8_general_ci"})
  *
  */
class SesDashboardSiteRelationShip
{
	/**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     * @JMS\Expose
     */
    private $name;

    /**
     * @var
     * @ORM\Column(type="decimal", precision=11, scale=8, nullable=true)
     * @Assert\LessThanOrEqual(value=180.0)
     * @Assert\GreaterThanOrEqual(value=-180.0)
     */
    private $longitude;

    /**
     * @var
     * @ORM\Column(type="decimal", precision=10, scale=8, nullable=true)
     * @Assert\LessThanOrEqual(value=90.0)
     * @Assert\GreaterThanOrEqual(value=-90.0)
     */
    private $latitude;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\GreaterThanOrEqual(value=0)
     */
    private $level;

    /**
     * @ORM\Column(type="string", length=2000, nullable=true)
     */
    private $path;

    /**
     * @ORM\Column(type="string", length=2000, nullable=true)
     */
    private $pathName;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $FK_ParentId;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $FK_SiteId;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $FK_DimDateFromId;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $FK_DimDateToId;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $FK_WeekDimDateFromId;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $FK_WeekDimDateToId;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $FK_MonthDimDateFromId;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $FK_MonthDimDateToId;

    /**
     * @ORM\ManyToOne(targetEntity="SesDashboardIndicatorDimDate", inversedBy="sitesFrom")
     * @ORM\JoinColumn(name="FK_DimDateFromId", referencedColumnName="id")
     */
    private $dimDateFrom;

    /**
     * @ORM\ManyToOne(targetEntity="SesDashboardIndicatorDimDate", inversedBy="sitesTo")
     * @ORM\JoinColumn(name="FK_DimDateToId", referencedColumnName="id")
     */
    private $dimDateTo;

    /**
     * @ORM\ManyToOne(targetEntity="SesDashboardIndicatorDimDate", inversedBy="weekSitesFrom")
     * @ORM\JoinColumn(name="FK_WeekDimDateFromId", referencedColumnName="id")
     */
    private $weekDimDateFrom;

    /**
     * @ORM\ManyToOne(targetEntity="SesDashboardIndicatorDimDate", inversedBy="weekSitesTo")
     * @ORM\JoinColumn(name="FK_WeekDimDateToId", referencedColumnName="id")
     */
    private $weekDimDateTo;

    /**
     * @ORM\ManyToOne(targetEntity="SesDashboardIndicatorDimDate", inversedBy="monthSitesFrom")
     * @ORM\JoinColumn(name="FK_MonthDimDateFromId", referencedColumnName="id")
     */
    private $monthDimDateFrom;

    /**
     * @ORM\ManyToOne(targetEntity="SesDashboardIndicatorDimDate", inversedBy="monthSitesTo")
     * @ORM\JoinColumn(name="FK_MonthDimDateToId", referencedColumnName="id")
     */
    private $monthDimDateTo;

    /**
     * @var SesDashboardSite|null
     * @ORM\ManyToOne(targetEntity="SesDashboardSite", inversedBy="sitesRelationShipChildren")
     * @ORM\JoinColumn(name="FK_ParentId", referencedColumnName="id")
     */
    private $parentSite;

    /**
     * @var SesDashboardSite|null
     * @ORM\ManyToOne(targetEntity="SesDashboardSite", inversedBy="sitesRelationShip")
     * @ORM\JoinColumn(name="FK_SiteId", referencedColumnName="id")
     */
    private $site;

    /**
     * @ORM\OneToMany(targetEntity="SesFullReport", mappedBy="siteRelationShip")
     */
    private $fullReports;

    /**
     * @ORM\OneToMany(targetEntity="SesAlert", mappedBy="siteRelationShip")
     */
    private $alerts;


    public function __construct()
    {

    }

    /**
     * @param SesDashboardSite $site
     * @param SesDashboardSite|null $parentSite
     * @param $siteName
     * @param $longitude
     * @param $latitude
     * @param $dimDateFrom
     * @param $weekDimDateFrom
     * @param $monthDimDateFrom
     * @return SesDashboardSiteRelationShip
     */
    public static function createNewInstance(SesDashboardSite $site,
                                             SesDashboardSite $parentSite = null,
                                             $siteName,
                                             $longitude,
                                             $latitude,
                                             $dimDateFrom,
                                             $weekDimDateFrom,
                                             $monthDimDateFrom)

    {
        $relationShip = new SesDashboardSiteRelationShip();

        $relationShip->setName($siteName);
        $relationShip->setLongitude($longitude);
        $relationShip->setLatitude($latitude);
        $relationShip->setParentSite($parentSite);
        $relationShip->setSite($site);

        $level = -1;
        $path = null;
        $pathName = null ;

        if ($parentSite != null) {
            $activeOrMostRecentSiteRelationShip = $parentSite->getActiveOrMostRecentSiteRelationShip();
            $level = $activeOrMostRecentSiteRelationShip->getLevel();
            $path = $activeOrMostRecentSiteRelationShip->getPath();
            $pathName = $activeOrMostRecentSiteRelationShip->getPathName();

            $parentSite->addSiteRelationShipChild($relationShip);
        }

        $relationShip->setLevel($level + 1);
        $relationShip->setPath($path .'|'. $site->getReference());
        $relationShip->setPathName($pathName . '|'. $siteName);
        $relationShip->setDimDateFrom($dimDateFrom);
        $relationShip->setWeekDimDateFrom($weekDimDateFrom);
        $relationShip->setMonthDimDateFrom($monthDimDateFrom);

        $site->addSiteRelationShip($relationShip);

        return $relationShip ;
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
     */
    public function setId($id)
    {
        $this->id = $id;
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
     */
    public function setName($name)
    {
        $this->name = $name;
        $this->updatePathName();
    }

    /**
     * @return mixed
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @param mixed $longitude
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }

    /**
     * @return mixed
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @param mixed $latitude
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }

    /**
     * @return mixed
     */
    public function getFKParentId()
    {
        return $this->FK_ParentId;
    }

    /**
     * @param mixed $FK_ParentId
     */
    public function setFKParentId($FK_ParentId)
    {
        $this->FK_ParentId = $FK_ParentId;
    }

    /**
     * @return mixed
     */
    public function getFKSiteId()
    {
        return $this->FK_SiteId;
    }

    /**
     * @param mixed $FK_SiteId
     */
    public function setFKSiteId($FK_SiteId)
    {
        $this->FK_SiteId = $FK_SiteId;
    }

    /**
     * @return mixed
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param mixed $level
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return mixed
     */
    public function getPathName()
    {
        return $this->pathName;
    }

    /**
     * @param mixed $pathName
     */
    public function setPathName($pathName)
    {
        $this->pathName = $pathName;
    }


    /**
     * @return SesDashboardSite|null
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @param SesDashboardSite|null $site
     */
    public function setSite($site)
    {
        $this->site = $site;
    }

    /**
     * @return SesDashboardSite|null
     */
    public function getParentSite()
    {
        return $this->parentSite;
    }

    /**
     * @param SesDashboardSite|null $parentSite
     */
    public function setParentSite($parentSite)
    {
        if ($parentSite != null) {
            $this->parentSite = $parentSite;
        }
    }

    /**
     * @return mixed
     */
    public function getFKDimDateFromId()
    {
        return $this->FK_DimDateFromId;
    }

    /**
     * @param mixed $FK_DimDateFromId
     */
    public function setFKDimDateFromId($FK_DimDateFromId)
    {
        $this->FK_DimDateFromId = $FK_DimDateFromId;
    }

    /**
     * @return mixed
     */
    public function getFKDimDateToId()
    {
        return $this->FK_DimDateToId;
    }

    /**
     * @param mixed $FK_DimDateToId
     */
    public function setFKDimDateToId($FK_DimDateToId)
    {
        $this->FK_DimDateToId = $FK_DimDateToId;
    }

    /**
     * @return SesDashboardIndicatorDimDate|null
     */
    public function getDimDateFrom()
    {
        return $this->dimDateFrom;
    }

    /**
     * @param mixed $dimDateFrom
     */
    public function setDimDateFrom($dimDateFrom)
    {
        $this->dimDateFrom = $dimDateFrom;
    }

    /**
     * @return mixed
     */
    public function getDimDateTo()
    {
        return $this->dimDateTo;
    }

    /**
     * @param mixed $dimDateTo
     */
    public function setDimDateTo($dimDateTo)
    {
        $this->dimDateTo = $dimDateTo;
    }

    /**
     * @return mixed
     */
    public function getFullReports()
    {
        return $this->fullReports;
    }

    /**
     * @param mixed $fullReports
     */
    public function setFullReports($fullReports)
    {
        $this->fullReports = $fullReports;
    }

    /**
     * Return true if relation is over now
     *
     * @return bool
     */
    public function isDeleted()
    {
        return ($this->getDimDateTo() != null && $this->getDimDateTo()->getId() <= DimDateHelper::getDimDateTodayId()) ;
    }

    /**
     * @return mixed
     */
    public function getFKWeekDimDateFromId()
    {
        return $this->FK_WeekDimDateFromId;
    }

    /**
     * @param mixed $FK_WeekDimDateFromId
     */
    public function setFKWeekDimDateFromId($FK_WeekDimDateFromId)
    {
        $this->FK_WeekDimDateFromId = $FK_WeekDimDateFromId;
    }

    /**
     * @return mixed
     */
    public function getFKWeekDimDateToId()
    {
        return $this->FK_WeekDimDateToId;
    }

    /**
     * @param mixed $FK_WeekDimDateToId
     */
    public function setFKWeekDimDateToId($FK_WeekDimDateToId)
    {
        $this->FK_WeekDimDateToId = $FK_WeekDimDateToId;
    }

    /**
     * @return SesDashboardIndicatorDimDate|null
     */
    public function getWeekDimDateFrom()
    {
        return $this->weekDimDateFrom;
    }

    /**
     * @param mixed $weekDimDateFrom
     */
    public function setWeekDimDateFrom($weekDimDateFrom)
    {
        $this->weekDimDateFrom = $weekDimDateFrom;
    }

    /**
     * @return mixed
     */
    public function getWeekDimDateTo()
    {
        return $this->weekDimDateTo;
    }

    /**
     * @param mixed $weekDimDateTo
     */
    public function setWeekDimDateTo($weekDimDateTo)
    {
        $this->weekDimDateTo = $weekDimDateTo;
    }

    /**
     * @return mixed
     */
    public function getAlerts()
    {
        return $this->alerts;
    }

    /**
     * @param mixed $alerts
     */
    public function setAlerts($alerts)
    {
        $this->alerts = $alerts;
    }

    /**
     * Update Path Name and children path Name when name is updated
     *
     */
    private function updatePathName()
    {
        $pathName = null;
        $parentSite = $this->getParentSite();

        if ($parentSite === null) {
            $pathName = "|".$this->getName();
        } else {
            $pathName = $parentSite->getActiveOrMostRecentSiteRelationShip()->getPathName().'|'.$this->getName();
        }

        $this->setPathName($pathName);

        if ($this->getSite() != null) {
            $childrenRelationShips = $this->getSite()->getSitesRelationShipChildren();

            /** @var SesDashboardSiteRelationShip $childRelationShip */
            foreach ($childrenRelationShips as $childRelationShip) {
                $childRelationShip->updatePathName();
            }
        }
    }

    /**
     * Return true if Relation is active between those 2 dates
     * // TODO : To Unit Test
     *
     * @param $dimDateFromId
     * @param $dimDateToId
     * @return bool
     */
    public function isActiveBetween($dimDateFromId, $dimDateToId)
    {
        $dimDateTodayId = DimDateHelper::getDimDateTodayId();

        if ($dimDateToId == null) {
            if ($dimDateFromId == null) {
                if ($this->getFKDimDateToId() == null || $this->getFKDimDateToId() > $dimDateTodayId) { // && DiMDateFrom <= Today
                    return true;
                }
            } else {
                if ($this->getFKDimDateToId() == null || $this->getFKDimDateToId() > $dimDateFromId) { // > ? >= ?
                    return true;
                }
            }
        } else {
            if ($dimDateFromId == null) {
                if ($this->getFKDimDateFromId() == null || $this->getFKDimDateFromId() < $dimDateToId) {
                    return true;
                }
            } else {
                if (($this->getFKDimDateToId() >= $dimDateFromId || $this->getFKDimDateToId() == null) && ($this->getFKDimDateFromId() < $dimDateToId || $this->getFKDimDateFromId() == null)) {
                    return true ;
                }
            }
        }

        return false ;
    }

    /**
     * Return true if Relation is active between those 2 dates
     * // TODO : To Unit Test
     *
     * @param $dimDateFromId
     * @param $dimDateToId
     * @return bool
     */
    public function isWeeklyActiveBetween($dimDateFromId, $dimDateToId)
    {
        $dimDateTodayId = DimDateHelper::getDimDateTodayId();

        if ($dimDateToId == null) {
            if ($dimDateFromId == null) {
                if ($this->getFKWeekDimDateToId() == null || $this->getFKWeekDimDateToId() > $dimDateTodayId) { // && DiMDateFrom <= Today
                    return true;
                }
            } else {
                if ($this->getFKWeekDimDateToId() == null || $this->getFKWeekDimDateToId() > $dimDateFromId) { // > ? >= ?
                    return true;
                }
            }
        } else {
            if ($dimDateFromId == null) {
                if ($this->getFKWeekDimDateFromId() == null || $this->getFKWeekDimDateFromId() < $dimDateToId) {
                    return true;
                }
            } else {
                if (($this->getFKWeekDimDateToId() >= $dimDateFromId || $this->getFKWeekDimDateToId() == null) && ($this->getFKWeekDimDateFromId() < $dimDateToId || $this->getFKWeekDimDateFromId() == null)) {
                    return true ;
                }
            }
        }

        return false ;
    }

    /**
     * Return true if Relation is active between those 2 dates
     * // TODO : To Unit Test
     *
     * @param $dimDateFromId
     * @param $dimDateToId
     * @return bool
     */
    public function isMonthlyActiveBetween($dimDateFromId, $dimDateToId)
    {
        $dimDateTodayId = DimDateHelper::getDimDateTodayId();

        if ($dimDateToId == null) {
            if ($dimDateFromId == null) {
                if ($this->getFKMonthDimDateToId() == null || $this->getFKMonthDimDateToId() > $dimDateTodayId) { // && DiMDateFrom <= Today
                    return true;
                }
            } else {
                if ($this->getFKMonthDimDateToId() == null || $this->getFKMonthDimDateToId() > $dimDateFromId) { // > ? >= ?
                    return true;
                }
            }
        } else {
            if ($dimDateFromId == null) {
                if ($this->getFKMonthDimDateFromId() == null || $this->getFKMonthDimDateFromId() < $dimDateToId) {
                    return true;
                }
            } else {
                if (($this->getFKMonthDimDateToId() >= $dimDateFromId || $this->getFKMonthDimDateToId() == null) && ($this->getFKMonthDimDateFromId() < $dimDateToId || $this->getFKMonthDimDateFromId() == null)) {
                    return true ;
                }
            }
        }

        return false ;
    }


    /**
     * TODO : Unit Test
     * TODO : Complete this function
     *
     * @param $epiTime
     * @param $name
     * @param null $parentReference
     * @param null $longitude
     * @param null $latitude
     * @return bool
     */
    public function isDeprecated($epiTime, $name, $parentReference = null, $longitude = null, $latitude = null)
    {
        $oldName = $this->getName();
        $oldReference = $this->getParentSite() != null ? $this->getParentSite()->getReference() : null;
        $oldLongitude = $this->getLongitude();
        $oldLatitude = $this->getLatitude();

        if ($oldName != $name
            || $oldReference != $parentReference
            || floatval($oldLongitude) != floatval($longitude)
            || floatval($oldLatitude) != floatval($latitude)) {

            return $this->needNewRelationShip($epiTime, $this->getDimDateFrom());
        }

       return false ;
    }

    /**
     * Return true if  dimDate and today are not in the same week
     *
     * @param $epiTime
     * @param SesDashboardIndicatorDimDate|null $dimDate
     * @return bool
     */
    public function needNewRelationShip($epiTime, $dimDate)
    {
        if ($dimDate == null) {
            return true;
        }

        // Minimum difference to create a new RelationShip 1 epiWeek
        $epiWeek = $dimDate->getEpiWeekOfYear();
        $year = $dimDate->getEpiYear();

        $todayEpiWeek = $epiTime["Week"];
        $todayEpiYear = $epiTime["Year"];

        if ($epiWeek != $todayEpiWeek || $year != $todayEpiYear) {
            return true;
        }

        return false ;
    }

    /**
     * @return mixed
     */
    public function getFKMonthDimDateFromId()
    {
        return $this->FK_MonthDimDateFromId;
    }

    /**
     * @param mixed $FK_MonthDimDateFromId
     */
    public function setFKMonthDimDateFromId($FK_MonthDimDateFromId)
    {
        $this->FK_MonthDimDateFromId = $FK_MonthDimDateFromId;
    }

    /**
     * @return mixed
     */
    public function getFKMonthDimDateToId()
    {
        return $this->FK_MonthDimDateToId;
    }

    /**
     * @param mixed $FK_MonthDimDateToId
     */
    public function setFKMonthDimDateToId($FK_MonthDimDateToId)
    {
        $this->FK_MonthDimDateToId = $FK_MonthDimDateToId;
    }

    /**
     * @return SesDashboardIndicatorDimDate|null
     */
    public function getMonthDimDateFrom()
    {
        return $this->monthDimDateFrom;
    }

    /**
     * @param mixed $monthDimDateFrom
     */
    public function setMonthDimDateFrom($monthDimDateFrom)
    {
        $this->monthDimDateFrom = $monthDimDateFrom;
    }

    /**
     * @return mixed
     */
    public function getMonthDimDateTo()
    {
        return $this->monthDimDateTo;
    }

    /**
     * @param mixed $monthDimDateTo
     */
    public function setMonthDimDateTo($monthDimDateTo)
    {
        $this->monthDimDateTo = $monthDimDateTo;
    }
}
