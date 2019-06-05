<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * Defines the properties of the Post entity to represent the blog posts.
 * See http://symfony.com/doc/current/book/doctrine.html#creating-an-entity-class
 *
 * Tip: if you have an existing database, you can generate these entity class automatically.
 * See http://symfony.com/doc/current/cookbook/doctrine/reverse_engineering.html
 *
 */
 
 /**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SesReportRepository")
 * @ORM\Table(name="sesdashboard_report", options={"collate"="utf8_general_ci"})
 */
class SesReport
{
	/**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

	/**
     * @ORM\Column(type="string", length=100)
     */
    private $disease;

    /**
     * @ORM\Column(type="datetime")
     */
    private $receptionDate;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(type="boolean", options={"default" = false})
     */
    private $isArchived = false;

    /**
     * @ORM\Column(type="boolean", options={"default" = false})
     */
    private $isDeleted = false;

	/**
    * @ORM\OneToMany(targetEntity="SesReportValues", mappedBy="report", cascade={"persist"})
    */
    private $reportValues;

	/**
    * @ORM\ManyToOne(targetEntity="SesPartReport", inversedBy="reports")
    * @ORM\JoinColumn(name="FK_PartReportId", referencedColumnName="id", onDelete="CASCADE")
    */
	private $report;

    /** @var  SesDashboardDisease */
    private $diseaseEntity;

    public function __construct()
    {
        $this->reportValues = new ArrayCollection();
    }

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;

            $this->reportValues = new ArrayCollection();
        }
    }

    /**
     * @param $disease
     * @param $receptionDate
     * @return SesReport
     */
    public static function create($disease, $receptionDate)
    {
        $instance = new self();
        $instance->setDisease($disease);
        $instance->setReceptionDate($receptionDate);

        return $instance ;
    }

    public function getId()
    {
        return $this->id;
    }

	public function getDisease()
    {
        return $this->disease;
    }

    public function getDiseaseName()
    {
        if ($this->diseaseEntity != null) {
            return $this->diseaseEntity->getName() ;
        }

        return $this->disease;
    }

    /**
     * @param SesDashboardDisease|null $entity
     */
    public function setDiseaseEntity(SesDashboardDisease $entity)
    {
        $this->diseaseEntity = $entity;
    }

    public function getDiseaseEntity()
    {
        return $this->diseaseEntity;
    }

    public function getReceptionDate()
    {
        return $this->receptionDate;
    }

    public function setReceptionDate($receptionDate)
    {
        $this->receptionDate = $receptionDate;
    }

    public function setPartReport(SesPartReport $partReport)
    {
        $this->report = $partReport;
    }

    public function getPartReport()
    {
        return $this->report ;
    }

	public function getReportValues()
    {
        return $this->reportValues;
    }
	
	public function addReportValues(SesReportValues $reportValues)
    {
		$this->reportValues[] = $reportValues;
		$reportValues->setReport($this);
		return $this;
    }

    public function removeAllReportValues()
    {
        $this->reportValues = new ArrayCollection();
    }

	public function getNbValues()
    {
		if ($this->reportValues != null) {
            return $this->reportValues->count();
        } else {
            return -1;
        }
    }

    public function getStatus ()
    {
        return $this->status;
    }

    public function IsValidated()
    {
        if ($this->status != null) {
            if ($this->status == 'ACCEPTED') {
                return true ;
            }
        }

        return false;
    }

    public function IsRefused()
    {
        if ($this->status != null) {
            if ($this->status == 'REFUSED') {
                return true ;
            }
        }

        return false;
    }

    public function Validate()
    {
        $this->status = 'ACCEPTED';
    }

    public function Refuse()
    {
        $this->status = 'REFUSED';
    }

    public function setDisease($disease)
    {
        $this->disease = $disease;
    }

    public function isArchived()
    {
        return $this->isArchived;
    }

    public function isDeleted()
    {
        return $this->isDeleted;
    }

    public function Delete()
    {
        $this->isDeleted = true ;
    }

    public function Archive()
    {
        $this->isArchived = true ;
    }

    /**
     * Return true if one of the reportValue->value >= the threshold max value
     *
     * @return bool
     */
    public function surpassThresholdMaxValue()
    {
        $result = false ;

        foreach($this->reportValues as $reportValue) {
            if ($reportValue->surpassThresholdMaxValue()) {
                return true;
            }
        }

        return $result;
    }

    /**
     * Get CSV Headers
     *
     * @return array
     */
    public static function getHeaderCsvRow()
    {
        $row = array() ;
        $row[] = 'Report Id';
        $row[] = 'Disease Name';
        $row[] = 'Reception Date';

        $result = array_merge($row, SesReportValues::getHeaderCsvRow());

        return $result;
    }

    /**
     * Get CSV Report format
     *
     * @return array
     */
    public function getCsvRow()
    {
        $result = array();

        $row = array() ;
        $row[] = self::getId();
        $row[] = self::getDiseaseName();
        $row[] = self::getReceptionDate()->format('d-m-Y H:i:s');

        $reportsValues = self::getReportValues();
        /** @var SesReportValues $reportValue */
        foreach($reportsValues as $reportValue) {
            $reportRow = $reportValue->getCsvRow();
            $result[] = array_merge($row, $reportRow);
        }

        return $result;
    }
}
