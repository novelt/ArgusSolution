<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 14/11/2016
 * Time: 17:53
 */

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SesDashboardIndicatorDimDateRepository")
 * @ORM\Table(name="sesdashboard_indicatordimdate", options={"collate"="utf8_general_ci"},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="sesdashboard_dimdate_idx", columns={"id"})},
 *     indexes={
 *      @ORM\Index(columns={"monthPeriodCode"}),
 *      @ORM\Index(columns={"weekPeriodCode"}),
 *      @ORM\Index(columns={"epiWeekPeriodCode"})
 *     }
  * )
 */
class SesDashboardIndicatorDimDate
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     */
    private $id;

    /**
     * @var \DateTime
     * @ORM\Column(type="date")
     */
    private $fullDate;

    /**
     * @var string
     * @ORM\Column(type="text", length=11)
     */
    private $dateName;

    /**
     * @var string
     * @ORM\Column(type="text", length=11)
     */
    private $dateNameUS;

    /**
     * @var string
     * @ORM\Column(type="text", length=11)
     */
    private $dateNameEU;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $dayOfWeek;

    /**
     * @var string
     * @ORM\Column(type="text", length=10)
     */
    private $dayNameOfWeek;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $dayOfMonth;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $dayOfYear;

    /**
     * @var string
     * @ORM\Column(type="text", length=10)
     */
    private $weekdayWeekend;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $weekOfYear;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $weekYear;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $epiYear;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $epiWeekOfYear;

    /**
     * @var string
     * @ORM\Column(type="text", length=10)
     */
    private $monthName;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $monthOfYear;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $isLastDayOfMonth;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $calendarQuarter;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $calendarYear;

    /**
     * @var string
     * @ORM\Column(type="text", length=10)
     */
    private $calendarYearMonth;

    /**
     * @var string
     * @ORM\Column(type="text", length=10)
     */
    private $calendarYearQtr;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $fiscalMonthOfYear;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $fiscalQuarter;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $fiscalYear;

    /**
     * @var string
     * @ORM\Column(type="text", length=10)
     */
    private $fiscalYearMonth;

    /**
     * @var string
     * @ORM\Column(type="text", length=10)
     */
    private $fiscalYearQtr;

    /**
     * @var @ORM\Column(type="string", length=7, nullable=true)
     */
    private $monthPeriodCode;

    /**
     * @var @ORM\Column(type="string", length=7, nullable=true)
     */
    private $weekPeriodCode;

    /**
     * @var @ORM\Column(type="string", length=7, nullable=true)
     */
    private $epiWeekPeriodCode;

    /**
     * @ORM\OneToMany(targetEntity="SesDashboardSiteRelationShip", mappedBy="dimDateFrom")
     */
    private $sitesFrom;

    /**
     * @ORM\OneToMany(targetEntity="SesDashboardSiteRelationShip", mappedBy="dimDateTo")
     */
    private $sitesTo;

    /**
     * @ORM\OneToMany(targetEntity="SesDashboardSiteRelationShip", mappedBy="weekDimDateFrom")
     */
    private $weekSitesFrom;

    /**
     * @ORM\OneToMany(targetEntity="SesDashboardSiteRelationShip", mappedBy="weekDimDateTo")
     */
    private $weekSitesTo;

    /**
     * @ORM\OneToMany(targetEntity="SesDashboardSiteRelationShip", mappedBy="monthDimDateFrom")
     */
    private $monthSitesFrom;

    /**
     * @ORM\OneToMany(targetEntity="SesDashboardSiteRelationShip", mappedBy="monthDimDateTo")
     */
    private $monthSitesTo;

    public function __construct()
    {

    }

    /**
     * @param $dimDateTypeCode
     * @return int|mixed|null
     */
    public function getPeriodCode($dimDateTypeCode) {
        switch($dimDateTypeCode) {
            case SesDashboardIndicatorDimDateType::CODE_WEEKLY:
                return $this->getWeekPeriodCode();
            case SesDashboardIndicatorDimDateType::CODE_WEEKLY_EPIDEMIOLOGIC:
                return $this->getEpiWeekPeriodCode();
            case SesDashboardIndicatorDimDateType::CODE_MONTHLY:
                return $this->getMonthPeriodCode();
            case SesDashboardIndicatorDimDateType::CODE_YEARLY:
                return $this->getCalendarYear();
            default:
                return null;
        }
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
     * @return \DateTime
     */
    public function getFullDate()
    {
        return $this->fullDate;
    }

    /**
     * @param \DateTime $fullDate
     */
    public function setFullDate($fullDate)
    {
        $this->fullDate = $fullDate;
    }

    /**
     * @return string
     */
    public function getDateName()
    {
        return $this->dateName;
    }

    /**
     * @param string $dateName
     */
    public function setDateName($dateName)
    {
        $this->dateName = $dateName;
    }

    /**
     * @return string
     */
    public function getDateNameUS()
    {
        return $this->dateNameUS;
    }

    /**
     * @param string $dateNameUS
     */
    public function setDateNameUS($dateNameUS)
    {
        $this->dateNameUS = $dateNameUS;
    }

    /**
     * @return string
     */
    public function getDateNameEU()
    {
        return $this->dateNameEU;
    }

    /**
     * @param string $dateNameEU
     */
    public function setDateNameEU($dateNameEU)
    {
        $this->dateNameEU = $dateNameEU;
    }

    /**
     * @return int
     */
    public function getDayOfWeek()
    {
        return $this->dayOfWeek;
    }

    /**
     * @param int $dayOfWeek
     */
    public function setDayOfWeek($dayOfWeek)
    {
        $this->dayOfWeek = $dayOfWeek;
    }

    /**
     * @return string
     */
    public function getDayNameOfWeek()
    {
        return $this->dayNameOfWeek;
    }

    /**
     * @param string $dayNameOfWeek
     */
    public function setDayNameOfWeek($dayNameOfWeek)
    {
        $this->dayNameOfWeek = $dayNameOfWeek;
    }

    /**
     * @return int
     */
    public function getDayOfMonth()
    {
        return $this->dayOfMonth;
    }

    /**
     * @param int $dayOfMonth
     */
    public function setDayOfMonth($dayOfMonth)
    {
        $this->dayOfMonth = $dayOfMonth;
    }

    /**
     * @return int
     */
    public function getDayOfYear()
    {
        return $this->dayOfYear;
    }

    /**
     * @param int $dayOfYear
     */
    public function setDayOfYear($dayOfYear)
    {
        $this->dayOfYear = $dayOfYear;
    }

    /**
     * @return string
     */
    public function getWeekdayWeekend()
    {
        return $this->weekdayWeekend;
    }

    /**
     * @param string $weekdayWeekend
     */
    public function setWeekdayWeekend($weekdayWeekend)
    {
        $this->weekdayWeekend = $weekdayWeekend;
    }

    /**
     * @return int
     */
    public function getWeekOfYear()
    {
        return $this->weekOfYear;
    }

    /**
     * @param int $weekOfYear
     */
    public function setWeekOfYear($weekOfYear)
    {
        $this->weekOfYear = $weekOfYear;
    }

    /**
     * @return string
     */
    public function getMonthName()
    {
        return $this->monthName;
    }

    /**
     * @param string $monthName
     */
    public function setMonthName($monthName)
    {
        $this->monthName = $monthName;
    }

    /**
     * @return int
     */
    public function getMonthOfYear()
    {
        return $this->monthOfYear;
    }

    /**
     * @param int $monthOfYear
     */
    public function setMonthOfYear($monthOfYear)
    {
        $this->monthOfYear = $monthOfYear;
    }

    /**
     * @return boolean
     */
    public function isIsLastDayOfMonth()
    {
        return $this->isLastDayOfMonth;
    }

    /**
     * @param boolean $isLastDayOfMonth
     */
    public function setIsLastDayOfMonth($isLastDayOfMonth)
    {
        $this->isLastDayOfMonth = $isLastDayOfMonth;
    }

    /**
     * @return int
     */
    public function getCalendarQuarter()
    {
        return $this->calendarQuarter;
    }

    /**
     * @param int $calendarQuarter
     */
    public function setCalendarQuarter($calendarQuarter)
    {
        $this->calendarQuarter = $calendarQuarter;
    }

    /**
     * @return int
     */
    public function getCalendarYear()
    {
        return $this->calendarYear;
    }

    /**
     * @param int $calendarYear
     */
    public function setCalendarYear($calendarYear)
    {
        $this->calendarYear = $calendarYear;
    }

    /**
     * @return string
     */
    public function getCalendarYearMonth()
    {
        return $this->calendarYearMonth;
    }

    /**
     * @param string $calendarYearMonth
     */
    public function setCalendarYearMonth($calendarYearMonth)
    {
        $this->calendarYearMonth = $calendarYearMonth;
    }

    /**
     * @return string
     */
    public function getCalendarYearQtr()
    {
        return $this->calendarYearQtr;
    }

    /**
     * @param string $calendarYearQtr
     */
    public function setCalendarYearQtr($calendarYearQtr)
    {
        $this->calendarYearQtr = $calendarYearQtr;
    }

    /**
     * @return int
     */
    public function getFiscalMonthOfYear()
    {
        return $this->fiscalMonthOfYear;
    }

    /**
     * @param int $fiscalMonthOfYear
     */
    public function setFiscalMonthOfYear($fiscalMonthOfYear)
    {
        $this->fiscalMonthOfYear = $fiscalMonthOfYear;
    }

    /**
     * @return int
     */
    public function getFiscalQuarter()
    {
        return $this->fiscalQuarter;
    }

    /**
     * @param int $fiscalQuarter
     */
    public function setFiscalQuarter($fiscalQuarter)
    {
        $this->fiscalQuarter = $fiscalQuarter;
    }

    /**
     * @return int
     */
    public function getFiscalYear()
    {
        return $this->fiscalYear;
    }

    /**
     * @param int $fiscalYear
     */
    public function setFiscalYear($fiscalYear)
    {
        $this->fiscalYear = $fiscalYear;
    }

    /**
     * @return string
     */
    public function getFiscalYearMonth()
    {
        return $this->fiscalYearMonth;
    }

    /**
     * @param string $fiscalYearMonth
     */
    public function setFiscalYearMonth($fiscalYearMonth)
    {
        $this->fiscalYearMonth = $fiscalYearMonth;
    }

    /**
     * @return string
     */
    public function getFiscalYearQtr()
    {
        return $this->fiscalYearQtr;
    }

    /**
     * @param string $fiscalYearQtr
     */
    public function setFiscalYearQtr($fiscalYearQtr)
    {
        $this->fiscalYearQtr = $fiscalYearQtr;
    }

    /**
     * @return int
     */
    public function getEpiWeekOfYear()
    {
        return $this->epiWeekOfYear;
    }

    /**
     * @param int $epiWeekOfYear
     */
    public function setEpiWeekOfYear($epiWeekOfYear)
    {
        $this->epiWeekOfYear = $epiWeekOfYear;
    }

    /**
     * @return int
     */
    public function getWeekYear()
    {
        return $this->weekYear;
    }

    /**
     * @param int $weekYear
     */
    public function setWeekYear($weekYear)
    {
        $this->weekYear = $weekYear;
    }

    /**
     * @return int
     */
    public function getEpiYear()
    {
        return $this->epiYear;
    }

    /**
     * @param int $epiYear
     */
    public function setEpiYear($epiYear)
    {
        $this->epiYear = $epiYear;
    }

    /**
     * @return mixed
     */
    public function getSitesFrom()
    {
        return $this->sitesFrom;
    }

    /**
     * @param mixed $sitesFrom
     */
    public function setSitesFrom($sitesFrom)
    {
        $this->sitesFrom = $sitesFrom;
    }

    /**
     * @return mixed
     */
    public function getSitesTo()
    {
        return $this->sitesTo;
    }

    /**
     * @param mixed $sitesTo
     */
    public function setSitesTo($sitesTo)
    {
        $this->sitesTo = $sitesTo;
    }

    /**
     * @return mixed
     */
    public function getWeekSitesFrom()
    {
        return $this->weekSitesFrom;
    }

    /**
     * @param mixed $weekSitesFrom
     */
    public function setWeekSitesFrom($weekSitesFrom)
    {
        $this->weekSitesFrom = $weekSitesFrom;
    }

    /**
     * @return mixed
     */
    public function getWeekSitesTo()
    {
        return $this->weekSitesTo;
    }

    /**
     * @param mixed $weekSitesTo
     */
    public function setWeekSitesTo($weekSitesTo)
    {
        $this->weekSitesTo = $weekSitesTo;
    }

    /**
     * @return mixed
     */
    public function getMonthSitesFrom()
    {
        return $this->monthSitesFrom;
    }

    /**
     * @param mixed $monthSitesFrom
     */
    public function setMonthSitesFrom($monthSitesFrom)
    {
        $this->monthSitesFrom = $monthSitesFrom;
    }

    /**
     * @return mixed
     */
    public function getMonthSitesTo()
    {
        return $this->monthSitesTo;
    }

    /**
     * @param mixed $monthSitesTo
     */
    public function setMonthSitesTo($monthSitesTo)
    {
        $this->monthSitesTo = $monthSitesTo;
    }

    /**
     * @return mixed
     */
    public function getMonthPeriodCode()
    {
        return $this->monthPeriodCode;
    }

    /**
     * @param mixed $monthPeriodCode
     */
    public function setMonthPeriodCode($monthPeriodCode)
    {
        $this->monthPeriodCode = $monthPeriodCode;
    }

    /**
     * @return mixed
     */
    public function getWeekPeriodCode()
    {
        return $this->weekPeriodCode;
    }

    /**
     * @param mixed $weekPeriodCode
     */
    public function setWeekPeriodCode($weekPeriodCode)
    {
        $this->weekPeriodCode = $weekPeriodCode;
    }

    /**
     * @return mixed
     */
    public function getEpiWeekPeriodCode()
    {
        return $this->epiWeekPeriodCode;
    }

    /**
     * @param mixed $epiWeekPeriodCode
     */
    public function setEpiWeekPeriodCode($epiWeekPeriodCode)
    {
        $this->epiWeekPeriodCode = $epiWeekPeriodCode;
    }
}