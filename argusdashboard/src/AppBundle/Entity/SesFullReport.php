<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 7/20/2015
 * Time: 4:44 PM
 */

namespace AppBundle\Entity;

use AppBundle\Utils\Epidemiologic;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as GEDMO; // gedmo annotations

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SesFullReportRepository")
 * @ORM\Table(name="sesdashboard_FullReport", options={"collate"="utf8_general_ci"})
 * @ORM\HasLifecycleCallbacks()
 * @GEDMO\Loggable
 */
class SesFullReport
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $period;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $FK_SiteId;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $FK_SiteRelationShipId;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    private $startDate;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $weekNumber;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $monthNumber;

    /**
     * @ORM\Column(type="integer")
     */
    private $year;

    /**
     * @ORM\Column(type="string")
     */
    private $import_SiteName;

    /**
     * @GEDMO\Versioned
     * @ORM\Column(type="string", nullable=true)
     */
    private $status;

    /**
     * @GEDMO\Timestampable(on="create")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdDate;

    /**
     * @GEDMO\Blameable(on="create")
     * @ORM\Column(type="string", nullable=true)
     */
    private $createdBy;

    /**
     * @GEDMO\Blameable(on="update", field="status")
     * @ORM\Column(type="string", nullable=true)
     */
    private $statusModifiedBy;

    /**
     * @Gedmo\Timestampable(on="update", field="status")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $statusModifiedDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $firstValidationDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $firstRejectionDate;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $aggregate;

    /**
     * @ORM\ManyToOne(targetEntity="SesDashboardSite", inversedBy="fullReports")
     * @ORM\JoinColumn(name="FK_SiteId", referencedColumnName="id")
     */
    private $frontLineGroup;

    /**
     * @ORM\ManyToOne(targetEntity="SesDashboardSiteRelationShip", inversedBy="fullReports")
     * @ORM\JoinColumn(name="FK_SiteRelationShipId", referencedColumnName="id")
     */
    private $siteRelationShip;

    /**
     * @ORM\OneToMany(targetEntity="SesPartReport", mappedBy="fullReport", cascade={"persist"} )
     */
    private $partReports;


    private $nbOfDisease;

    private $nbOfDiseaseValues;

    private $epiFistDay;

    private $minutesBeforeRejectingReport;

    public function __construct()
    {
        $this->partReports = new ArrayCollection();
        //$this->createdBy = "System";
    }

    /**
     * Creation of a FullReport with Pending status
     *
     * @param SesDashboardSite $site
     * @param SesDashboardSiteRelationShip $relationShip
     * @param $importSiteName
     * @param $period
     * @param $startDate
     * @param $weekNumber
     * @param $monthNumber
     * @param $year
     * @param $isAggregate
     *
     * @return SesFullReport
     */
    public static function create(SesDashboardSite $site, SesDashboardSiteRelationShip $relationShip, $importSiteName, $period, $startDate, $weekNumber, $monthNumber, $year, $isAggregate)
    {
        $instance = new self();
        $instance->setPeriod($period);
        $instance->setFrontLineGroup($site);
        $instance->setSiteRelationShip($relationShip);
        $instance->setStartDate($startDate);
        $instance->setWeekNumber(empty($weekNumber) ? null : $weekNumber);
        $instance->setMonthNumber(empty($monthNumber) ? null : $monthNumber);
        $instance->setYear($year);
        $instance->setImportSiteName($importSiteName);
        $instance->setAggregate($isAggregate);
        $instance->setStatus(Constant::STATUS_PENDING);

        return $instance ;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getFK_SiteId()
    {
        return $this->FK_SiteId;
    }

    public function setFK_SiteId($siteId)
    {
        $this->FK_SiteId = $siteId ;
    }

    /**
     * @return mixed
     */
    public function getFK_SiteRelationShipId()
    {
        return $this->FK_SiteRelationShipId;
    }

    /**
     * @param mixed $FK_SiteRelationShipId
     */
    public function setFK_SiteRelationShipId($FK_SiteRelationShipId)
    {
        $this->FK_SiteRelationShipId = $FK_SiteRelationShipId;
    }

    public function getPeriod()
    {
        return $this->period;
    }

    public function setPeriod($period)
    {
        $this->period = $period;
    }

    public function setImportSiteName($siteName)
    {
        $this->import_SiteName = $siteName;
    }

    public function getDisplayPeriod()
    {
        if ($this->getPeriod() != null) {
            if ($this->getPeriod() == Constant::PERIOD_WEEKLY)
                 return "Week";

            if ($this->getPeriod() == Constant::PERIOD_MONTHLY)
                return "Month";

            return $this->getPeriod();
        }

       return "";
    }

    /**
     * @return string
     * Depends on first day of week
     */
    public function getWeekNumber()
    {
        if ($this->getPeriod() != Constant::PERIOD_WEEKLY) {
            return "";
        } else {
            if ($this->weekNumber != null) {
                return $this->weekNumber ;
            } else {
                // Get TimeStamp from StartDate
                $ts = $this->startDate->getTimeStamp();

                $result = Epidemiologic::Timestamp2Epi($ts, $this->getEpiFirstDay());
                return $result['Week'];
            }
        }
    }

    public function setWeekNumber($weekNumber)
    {
        $this->weekNumber = $weekNumber ;
    }

    public function getYear()
    {
        if ($this->year != null) {
            return $this->year;
        } else {
            if ($this->getPeriod() == Constant::PERIOD_WEEKLY) {
                // Get TimeStamp from StartDate
                $ts = $this->startDate->getTimeStamp();

                $result = Epidemiologic::Timestamp2Epi($ts, $this->getEpiFirstDay());
                return $result['Year'];
            } else if ($this->getPeriod() == Constant::PERIOD_MONTHLY) {
                return $this->startDate->format("Y");
            }
        }
    }

    public function setYear($year)
    {
        $this->year = $year ;
    }

    public function getMonthName()
    {
        if ($this->getPeriod() != Constant::PERIOD_MONTHLY) {
            return "";
        }

        $month = $this->startDate->format("F");
        return $month;
    }

    public function getMonthNumber()
    {
        if ($this->getPeriod() != Constant::PERIOD_MONTHLY) {
            return "";
        } else {
            if ($this->monthNumber != null) {
                return $this->monthNumber ;
            } else {
                $monthNumber = $this->startDate->format("m");
                return $monthNumber;
            }
        }
    }

    public function setMonthNumber($monthNumber)
    {
        $this->monthNumber = $monthNumber ;
    }

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    public function getEndDate()
    {
        $endDate = clone $this->startDate;

        if ($this->period == Constant::PERIOD_WEEKLY) {
            $endDate->modify("+ 6 days");
        } else if ($this->period == Constant::PERIOD_MONTHLY) {
            $lastDay = date('Y-m-t', strtotime($this->startDate->format('Y-m-d')));
            $endDate = date_create($lastDay);
        }
        return $endDate;
    }

    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    public function getFirstValidationDate()
    {
        return $this->firstValidationDate;
    }

    public function getFirstRejectionDate()
    {
        return $this->firstRejectionDate;
    }

    public function getFirstAddressedDate()
    {
        if ($this->getFirstValidationDate() == null && $this->getFirstRejectionDate() == null) {
            return null;
        }

        if ($this->getFirstValidationDate() == null) {
            return $this->getFirstRejectionDate();
        }

        if ($this->getFirstRejectionDate() == null) {
            return $this->getFirstValidationDate();
        }

        return ($this->getFirstValidationDate() < $this->getFirstRejectionDate() ? $this->getFirstValidationDate() : $this->getFirstRejectionDate());
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getDisplayStatus()
    {
        if ($this->getStatus() == "" || $this->getStatus() == null ) {
            return Constant::STATUS_PENDING;
        }

        return $this->getStatus();
    }

    public function setStatus($status)
    {
        return $this->status = $status;
    }

    public function setStatusModifiedBy($modifiedBy)
    {
        $this->statusModifiedBy = $modifiedBy;
    }

    /**
     * @return SesDashboardSite
     */
    public function getFrontLineGroup()
    {
        return $this->frontLineGroup;
    }

    public function setFrontLineGroup(SesDashboardSite $frontLineGroup)
    {
        return $this->frontLineGroup = $frontLineGroup;
    }

    /**
     * @return mixed
     */
    public function getSiteRelationShip()
    {
        return $this->siteRelationShip;
    }

    /**
     * @param mixed $siteRelationShip
     */
    public function setSiteRelationShip($siteRelationShip)
    {
        $this->siteRelationShip = $siteRelationShip;
    }

    public function getPartReports()
    {
        return $this->partReports;
    }

    public function getSortPartReports($sortFunction)
    {
        $iterator = $this->partReports->getIterator();
        $iterator->uasort($sortFunction);
        return new ArrayCollection(iterator_to_array($iterator));
    }

    public function getPartReportsForDisplay()
    {
        $result = new ArrayCollection();

        if ($this->partReports != null && count($this->partReports) > 0) {
            if ($this->isValidated()) // Full Report is validated
            {
                // get the last report validated
                foreach ($this->partReports as $report) {
                    if ($report->isValidated()) {
                        $result->add($report);
                        break;
                    }
                }
            } else if ($this->isRejected() || $this->isRejectedFromAbove()) {// Full Report is rejected
                $result = $this->partReports;
            } else {

                // last report is pending or incomplete
                for($i = 0 ; $i < count($this->partReports) ; $i++) {
                    if ($i==0) {
                        $result->add($this->partReports[0]); // pending report
                    } else  {
                        if ($this->partReports[$i]->isValidated()) {
                            $result->clear();
                            $result->add($this->partReports[0]);
                            $result->add($this->partReports[$i]);
                            break ;
                        } else {
                            $result->add($this->partReports[$i]);
                        }
                    }
                }
            }
        }

        return $result;
    }

    public function getNbPartReports()
    {
        if ($this->partReports != null) {
            return count($this->partReports);
        }

        return 0;
    }

    public function removeAllPartReports()
    {
        return $this->partReports = array();
    }

    /**
     * @param SesPartReport $sesPartReport
     * @return $this
     */
    public function addPartReport(SesPartReport $sesPartReport)
    {
        $this->partReports[] = $sesPartReport ;
        $sesPartReport->setFullReport($this);
        return $this;
    }

    /**
     * @param SesPartReport $sesPartReport
     * @return $this
     */
    public function removePartReport(SesPartReport $sesPartReport)
    {
        $this->partReports->removeElement($sesPartReport);
        $sesPartReport->setFullReport(null);
        return $this;
    }

    public function setAggregate($value)
    {
        $this->aggregate = $value;
    }

    public function isAggregate()
    {
        if ($this->aggregate != null) {
            return $this->aggregate;
        }

        return false ;
    }

    public function setCreatedDate($createdDate)
    {
        $this->createdDate = $createdDate;
    }

    public function getNbOfDisease()
    {
        return $this->nbOfDisease;
    }

    /*
     * Set from DbConstant Service
     */
    public function setNbOfDisease($nbDisease)
    {
        return $this->nbOfDisease = $nbDisease;
    }

    public function getNbOfDiseaseValues()
    {
        return $this->nbOfDiseaseValues;
    }

    /*
     * Set from DbConstant Service
     */
    public function setNbOfDiseaseValues($nbDiseaseValues)
    {
        return $this->nbOfDiseaseValues = $nbDiseaseValues;
    }

    public function getEpiFirstDay()
    {
        return $this->epiFistDay;
    }

    /*
     * Set from DbConstant Service
     */
    public function setEpiFirstDay($epiFirstDay)
    {
        return $this->epiFistDay = $epiFirstDay;
    }

    /*
     * Set from DbConstant Service
     */
    public function setNbMinutesBeforeRejectingReport($value)
    {
        $this->minutesBeforeRejectingReport = $value ;
    }

    public function getNbMinutesBeforeRejectingReport()
    {
        return $this->minutesBeforeRejectingReport;
    }

    public function getCss()
    {
        if ($this->isValidated()) {
            return "success";
        }

        if ($this->isRejected()) {
            return "danger";
        }

        if ($this->isPending()) {

            if ($this->isExhaustive()) {
                return "info";
            }

            return "default";
        }

        if ($this->isRejectedFromAbove() || $this->isConflicting()) {
            return "warning";
        }

        return "default";
    }

    public function isValidated()
    {
        if ($this->status != null) {
            if ($this->status == Constant::STATUS_VALIDATED) {
                return true;
            }
        }

        return false;
    }

    public function isRejected()
    {
        if ($this->status != null) {
            if ($this->status == Constant::STATUS_REJECTED) {
                return true;
            }
        }

        return false;
    }

    public function isPending()
    {
        if ($this->status != null) {
            if ($this->status == Constant::STATUS_PENDING) {
                return true;
            }
        }

        return false;
    }

    public function isExhaustive()
    {
        /** @var SesPartReport $partReport */
        foreach($this->partReports as $partReport) {
            if (!$partReport->isExhaustive() && !$partReport->isRejected()) // && !$partReport->isValidated() && !$partReport->isRejected()
                return false;
        }

        return true ;
    }

    public function isRejectedFromAbove()
    {
        if ($this->status != null) {
            if ($this->status == Constant::STATUS_REJECTED_FROM_ABOVE) {
                return true;
            }
        }

        return false;
    }

    public function isConflicting()
    {
        if ($this->status != null) {
            if ($this->status == Constant::STATUS_CONFLICTING) {
                return true;
            }
        }

        return false;
    }

    public function canBeValidated()
    {
        if ($this->getNbPartReports() > 0) {
            /** @var SesPartReport $lastPartReport */
            $lastPartReport = $this->getPartReports()[0];

            return $lastPartReport->canBeValidated();
        }

        return false ;
    }

    public function canBeRejected()
    {
        if ($this->getNbPartReports() > 0) {

            if ($this->isRejectedFromAbove()) {
                return true;
            }

            /** @var SesPartReport $lastPartReport */
            $lastPartReport = $this->getPartReports()[0];

            return ($lastPartReport != null && $lastPartReport->canBeRejected());
        }

        return false ;
    }

    public function setDateOfReceivedReport($date)
    {
        /** @var SesPartReport $partReport */
        foreach(self::getPartReports() as $partReport) {
            /** @var SesReport $reports */
            foreach($partReport->getReports() as $reports) {
                $reports->setReceptionDate($date);
            }
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
        $row[] = 'FullReport Id';
        $row[] = 'Period';
        $row[] = 'Start Date';
        $row[] = 'Month Number';
        $row[] = 'Week Number';
        $row[] = 'Year';
        $row[] = 'FullReport Status';
        $row[] = 'Site Id';
        $row[] = 'Site Name';

        $result = array_merge($row, SesPartReport::getHeaderCsvRow());

        return $result;
    }

    /**
     * Get CSV FullReport format
     *
     * @return array
     */
    public function getCsvRow()
    {
        $result = array();

        $row = array() ;
        $row[] = self::getId();
        $row[] = self::getPeriod();
        $row[] = self::getStartDate()->format('d-m-Y');
        $row[] = self::getMonthNumber();
        $row[] = self::getWeekNumber();
        $row[] = self::getYear();
        $row[] = self::getStatus();
        $row[] = self::getFK_SiteId();
        $row[] = self::getFrontLineGroup() != null ? self::getFrontLineGroup()->getName() : "";

        $partReports = self::getPartReports();
        foreach($partReports as $partReport) {
            $partReportRow = $partReport->getCsvRow();

            for ($i = 0 ; $i< count($partReportRow) ; $i++) {
                $result[] = array_merge($row, $partReportRow[$i]);
            }
        }

        return $result;
    }

    /* Doctrine LifeCycleCallbacks */

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        self::updateFirstValidationDate();
        self::updateFirstRejectionDate();
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        self::updateFirstValidationDate();
        self::updateFirstRejectionDate();
    }

    /**
     * Update first validation date
     */
    private function updateFirstValidationDate()
    {
        if ($this->firstValidationDate == null) {
            if ($this->status == Constant::STATUS_VALIDATED) {
                $this->firstValidationDate = new \DateTime() ;
            }
        }
    }

    /**
     * Update first rejection date
     */
    private function updateFirstRejectionDate()
    {
        if ($this->firstRejectionDate == null) {
            if ($this->status == Constant::STATUS_REJECTED) {
                $this->firstRejectionDate = new \DateTime() ;
            }
        }
    }
}