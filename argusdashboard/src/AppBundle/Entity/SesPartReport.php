<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 7/20/2015
 * Time: 4:49 PM
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as GEDMO; // gedmo annotations

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SesPartReportRepository")
 * @ORM\Table(name="sesdashboard_partreport", options={"collate"="utf8_general_ci"})
 * @ORM\HasLifecycleCallbacks()
 * @GEDMO\Loggable
 */
class SesPartReport
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $contactName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $contactPhoneNumber;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @GEDMO\Versioned
     */
    private $status;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $aggregate;

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
     * @GEDMO\Timestampable(on="update", field="status")
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
     * @ORM\Column(type="integer")
     */
    private $FK_FullReportId;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $androidReportId;

    /**
     * @ORM\ManyToOne(targetEntity="SesFullReport", inversedBy="partReports")
     * @ORM\JoinColumn(name="FK_FullReportId", referencedColumnName="id", onDelete="CASCADE")
     */
    private $fullReport;

    /**
     * @ORM\OneToMany(targetEntity="SesAggregatePartReport", mappedBy="partReportOwner", cascade={"persist"} )
     */
    private $aggregatePartReports;

    /**
     * @ORM\OneToMany(targetEntity="SesReport", mappedBy="report", cascade={"persist"})
     */
    private $reports;

    public function __construct()
    {
        $this->reports = new ArrayCollection();
        $this->aggregatePartReports = new ArrayCollection();
    }

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
            $this->createdDate = new \DateTime();
            $this->createdBy = null;
            $this->statusModifiedBy = null;
            $this->statusModifiedDate = null;
            $this->firstRejectionDate = null;
            $this->firstValidationDate = null;

            // Clone of reports
            $reportsClone = new ArrayCollection();

            /** @var SesReport $report */
            foreach ($this->reports as $report) {
                $itemClone = clone $report;
                $itemClone->setPartReport($this);
                $reportsClone->add($itemClone);
            }
            $this->reports = $reportsClone;

            // Clone of Aggregate Part Reports
            $aggregateClone = new ArrayCollection();

            /** @var SesAggregatePartReport $aggregatePartReport */
            foreach ($this->aggregatePartReports as $aggregatePartReport) {
                $itemClone = clone $aggregatePartReport;
                $itemClone->setPartReportOwner($this);
                $itemClone->setPartReport($aggregatePartReport->getPartReport());
                $aggregateClone->add($itemClone);
            }
            $this->aggregatePartReports = $aggregateClone;
        }
    }

    /**
     * Create SesPartReport
     *
     * @param $contactName
     * @param $contactPhoneNumber
     * @param $isAggregate
     * @param $androidReportId
     *
     * @return SesPartReport
     */
    public static function create($contactName, $contactPhoneNumber, $isAggregate, $androidReportId = null)
    {
        $instance = new self();
        $instance->setContactName($contactName);
        $instance->setContactPhoneNumber($contactPhoneNumber);
        $instance->setAggregate($isAggregate);
        $instance->setAndroidReportId($androidReportId);
        $instance->setStatus(Constant::STATUS_PENDING);

        return $instance ;
    }

    public function getId()
    {
        if (null != $this->id)
        {
            return $this->id;
        }

        return 0;
    }

    public function getContactName()
    {
        return $this->contactName;
    }

    public function setContactName($contactName)
    {
        $this->contactName = $contactName;
    }

    public function getContactPhoneNumber()
    {
        return $this->contactPhoneNumber;
    }

    public function setContactPhoneNumber($phoneNumber)
    {
        $this->contactPhoneNumber = $phoneNumber;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        return $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getFirstValidationDate()
    {
        return $this->firstValidationDate;
    }

    /**
     * @param mixed $firstValidationDate
     */
    public function setFirstValidationDate($firstValidationDate)
    {
        $this->firstValidationDate = $firstValidationDate;
    }

    /**
     * @return mixed
     */
    public function getFirstRejectionDate()
    {
        return $this->firstRejectionDate;
    }

    /**
     * @param mixed $firstRejectionDate
     */
    public function setFirstRejectionDate($firstRejectionDate)
    {
        $this->firstRejectionDate = $firstRejectionDate;
    }

    /**
     * @return mixed
     */
    public function getFK_FullReportId()
    {
        return $this->FK_FullReportId;
    }

    /**
     * @param mixed $FK_FullReportId
     */
    public function setFK_FullReportId($FK_FullReportId)
    {
        $this->FK_FullReportId = $FK_FullReportId;
    }


    /**
     * @return int|null
     */
    public function getAndroidReportId()
    {
        return $this->androidReportId;
    }

    /**
     * @param int|null $androidReportId
     */
    public function setAndroidReportId($androidReportId)
    {
        $this->androidReportId = $androidReportId;
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

    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    /**
     * @return SesFullReport
     */
    public function getFullReport()
    {
        return $this->fullReport;
    }

    public function setFullReport($fullReport)
    {
        $this->fullReport = $fullReport;
        return $this;
    }

    public function getReports()
    {
        return $this->reports;
    }

    /**
     * Get Order Reports by Disease position and then by Disease KeyWord
     *
     * @return array
     */
    public function getOrderReports()
    {
        $reports = $this->reports->toArray();

        usort($reports, function(SesReport $a, SesReport $b) {
            $aDiseaseEntity = $a->getDiseaseEntity();
            $bDiseaseEntity = $b->getDiseaseEntity();

            if ($aDiseaseEntity != null && $bDiseaseEntity != null) {
                $aPosition = $aDiseaseEntity->getPosition();
                $bPosition = $bDiseaseEntity->getPosition();

                if ($aPosition != null || $bPosition != null) {
                    $aPosition = $aPosition == null ? 1000 : $aPosition;
                    $bPosition = $bPosition == null ? 1000 : $bPosition;
                    return ($aPosition > $bPosition);
                } else {
                    return ($aDiseaseEntity->getKeyword() > $bDiseaseEntity->getKeyword());
                }
            }

            return ($a->getDisease() < $b->getDisease());
        });

        return $reports;
    }

    public function addReport(SesReport $report)
    {
        $this->reports[] = $report;
        $report->setPartReport($this);
        return $this;
    }

    public function removeAllReports()
    {
        return $this->reports = array();
    }

    public function getAggregatePartReports()
    {
        return  $this->aggregatePartReports;
    }

    public function addAggregatePartReport($sesAggregatePartReport)
    {
        return $this->aggregatePartReports[] = $sesAggregatePartReport ;
    }

    public function removeAggregatePartReport($sesAggregatePartReport)
    {
        return $this->aggregatePartReports->removeElement($sesAggregatePartReport);
    }

    public function isExhaustive()
    {
        if ($this->fullReport != null && $this->reports != null) {
            return ($this->reports->count() == $this->fullReport->getNbOfDisease()) &&
                    ($this->getNbOfValues() == $this->fullReport->getNbOfDiseaseValues());
        }

        return false;
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

    public function canBeValidated()
    {
        if ($this->getStatus() != Constant::STATUS_VALIDATED && $this->isExhaustive()) {
            return true;
        }

        return false ;
    }

    public function canBeRejected()
    {
        if ($this->getStatus() != Constant::STATUS_REJECTED) {
            if ($this->isAggregate()) {
                return true ;
            }

            if ($this->isExhaustive()) {
                return true ;
            }

            // Timer calculated on Last received SMS to not be able to reject the report too soon
            $dateOfFirstReport = $this->getDateOfFirstReport();

            if ($dateOfFirstReport == null) {
                return true;
            }

            $timeLastReport = $dateOfFirstReport->getTimeStamp();
            $timeNow = strtotime('now');
            $diff = abs($timeLastReport - $timeNow);
            $minutes = round($diff / 60);

            if ($minutes >= $this->getFullReport()->getNbMinutesBeforeRejectingReport()) {
                return true;
            }
        } else {
            // Sometimes the report is rejected but not the full report. Doesn't know why exactly yet.
            return $this->getFullReport()->getStatus() == Constant::STATUS_REJECTED_FROM_ABOVE ;
        }

        return false ;
    }

    public function getCss()
    {
        if ($this->isValidated()) {
            return "success";
        }

        if ($this->isRejected()) {
            return "danger";
        }

        if ($this->isExhaustive()) {
            if ($this->isPending()) {
                return "info";
            }
        }

        return "default";
    }

    public function getDisplayStatus()
    {
        if ($this->getStatus() == "" || $this->getStatus() == null) {
            return Constant::STATUS_PENDING;
        }

        return $this->getStatus();
    }

    /**
     * Return recursively the number of PartReport aggregation
     * // TODO : Replace with a recursive store procedure
     * // TODO : Or Store the number of report composing the aggregation
     *
     * @return int
     */
    public function getNbAggregateReports()
    {
        $nbCsReportsIncluded = 0 ;

        if ($this->aggregatePartReports->count() > 0) {
            /** @var SesAggregatePartReport $aggregat */
            foreach($this->aggregatePartReports as $aggregat) {
                $pReport = $aggregat->getPartReport() ;
                if ($pReport != null) {
                    if ($pReport->isAggregate()) {
                        $nbCsReportsIncluded += $pReport->getNbAggregateReports() ;
                    } else {
                        $nbCsReportsIncluded += 1 ;
                    }
                }
            }
        }

        return $nbCsReportsIncluded;
    }

    public function getNbOfParticipatingHC($site)
    {
        if ($this->aggregatePartReports->count() > 0) {
            foreach ($this->aggregatePartReports as $aggregate) {
                if (isset($aggregate) && $aggregate != null
                    && $aggregate->getPartReport() != null
                    && $aggregate->getPartReport()->getFullReport() != null
                    && $aggregate->getPartReport()->getFullReport()->getFK_SiteId() == $site->getId()) {
                    if ($aggregate->getPartReport()->getFullReport()->getFrontlineGroup() != null && $aggregate->getPartReport()->getFullReport()->getFrontlineGroup()->isLeaf())
                        return 1;
                    else
                        return $aggregate->getPartReport()->getNbAggregateReports();
                }
            }
        }

        return 0;
    }

    public function getCheckBoxStatusForValidation()
    {
        if ($this->isExhaustive() && !$this->isValidated()) {
            return "checked";
        }
    }

    public function getCheckBoxStatusForRejection()
    {
        if (!$this->isRejected()) {
            return "checked" ;
        }
    }

    public function isFirstVisible()
    {
        if ($this->getFullReport()->getNbPartReports() == 1) {
            return true;
        }

        if ($this->getFullReport()->isConflicting()) {
            if ($this->isPending()) {
                return true;
            }

            return false ;
        }

        if ($this->isValidated()) {
            return true;
        }

        return false ;
    }

    public function getNbOfValues()
    {
        $count = 0;
        /** @var SesReport $report */
        foreach($this->reports as $report) {
            $count += $report->getNbValues();
        }

        return $count;
    }

    public function getDateOfFirstReport()
    {
        $firstReport = PHP_INT_MAX;
        $firstReportDateTime = null;

        /** @var SesReport $report */
        foreach($this->reports as $report) {
            if ($firstReport && $report->getReceptionDate() && $report->getReceptionDate()->format('U') < $firstReport) {
                $firstReport = $report->getReceptionDate()->format('U');
                $firstReportDateTime = $report->getReceptionDate() ;
            }
        }

        return $firstReportDateTime;
    }

    public function getDateOfLastReport()
    {
        $lastReport = 0;
        $lastReportDateTime = null;

        /** @var SesReport $report */
        foreach($this->reports as $report) {
            if ($report->getReceptionDate() && $report->getReceptionDate()->format('U') > $lastReport) {
                $lastReport = $report->getReceptionDate()->format('U');
                $lastReportDateTime = $report->getReceptionDate();
            }
        }

        return $lastReportDateTime;
    }

    public function resetAllValues()
    {
        /** @var SesReport $report */
        foreach($this->reports as $report) {
            /** @var SesReportValues $reportValue */
            foreach($report->getReportValues() as $reportValue) {
                $reportValue->setValue(0);
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
        $row[] = 'PartReport Id';
        $row[] = 'PartReport Status';
        //$row[] = 'Contact Name';
        $row[] = 'Contact PhoneNumber';

        $result = array_merge($row, SesReport::getHeaderCsvRow());

        return $result;
    }

    /**
     * Get CSV PartReport format
     *
     * @return array
     */
    public function getCsvRow()
    {
        $result = array();

        $row = array() ;
        $row[] = self::getId();
        $row[] = self::getStatus();
        //$row[] = self::getContactName();
        $row[] = self::getContactPhoneNumber();

        $reports = self::getReports();
        foreach($reports as $report) {
            if (! $report->isArchived() && ! $report->isDeleted()) {
                $reportRow = $report->getCsvRow();

                for ($i = 0 ; $i< count($reportRow) ; $i++) {
                    $result[] = array_merge($row, $reportRow[$i]);
                }
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
