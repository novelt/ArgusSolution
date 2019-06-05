<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use AppBundle\Utils\Helper;
use JMS\Serializer\Annotation as JMS;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
  * @ORM\Entity(repositoryClass="AppBundle\Repository\SesDashboardDiseaseRepository")
  * @ORM\Table(name="sesdashboard_diseases", options={"collate"="utf8_general_ci"})
  *
  * @UniqueEntity(fields="disease", message="disease.reference.unique")
  * @UniqueEntity(fields="keyWord", message="disease.keyword.unique")
  *
  * @JMS\ExclusionPolicy("all")
  */
class SesDashboardDisease
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     * @JMS\Expose()
     * @JMS\SerializedName("reference")
     */
    private $disease;

    /**
     * @ORM\Column(type="string")
     * @JMS\Expose
     */
    private $name;

    /**
     * @ORM\Column(type="string")
     * @JMS\Expose
     */
    private $keyWord;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @JMS\Expose
     */
    private $position;

    /**
     * @var SesDashboardDiseaseValue[]
     * @ORM\OneToMany(targetEntity="SesDashboardDiseaseValue", mappedBy="parentDisease", cascade={"persist"})
     * @JMS\Expose
     * @JMS\SerializedName("values")
     * @JMS\Type("AppBundle\Entity\Import\DiseasesValues")
     */
    private $diseaseValues;

    /**
     * @ORM\OneToMany(targetEntity="SesDashboardDiseaseConstraint", mappedBy="parentDisease", cascade={"persist"})
     * @JMS\Expose
     * @JMS\SerializedName("constraints")
     * @JMS\Type("AppBundle\Entity\Import\DiseasesConstraints")
     */
    private $diseaseConstraints;

    /**
     * @ORM\OneToMany(targetEntity="SesDashboardThreshold", mappedBy="disease", cascade={"persist"})
     */
    private $thresholds;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $reportDataSourceId;

    /**
     * @var SesDashboardReportDataSource
     * @ORM\ManyToOne(targetEntity="SesDashboardReportDataSource", inversedBy="diseases")
     * @ORM\JoinColumn(name="reportDataSourceId", referencedColumnName="id")
     */
    private $reportDataSource;

    public function __construct()
    {
    }

    /**
     * Returns the diseaseValue's value having the Primary priority
     * @return string|null
     */
    public function getPrimaryKeyword()
    {
        return $this->getKeyword();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getDisease()
    {
        return $this->disease;
    }

    public function setDisease($disease)
    {
        $this->disease = Helper::cleanForReference($disease);
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getKeyword()
    {
        return $this->keyWord;
    }

    /**
     * @param $keyWord
     */
    public function setKeyword($keyWord)
    {
        $this->keyWord = $keyWord;
    }

    /**
     * @return mixed
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param mixed $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return SesDashboardDiseaseValue[]
     */
    public function getDiseaseValues()
    {
        return $this->diseaseValues;
    }

    public function setDiseaseValues($diseaseValues)
    {
        $this->diseaseValues = $diseaseValues;
    }

    public function getDiseaseValueCount()
    {
        return $this->diseaseValues->count();
    }

    public function getDiseaseConstraints()
    {
        return $this->diseaseConstraints;
    }

    public function setDiseaseConstraints($diseaseConstraints)
    {
        $this->diseaseConstraints = $diseaseConstraints;
    }

    public function getDiseaseConstraintCount()
    {
        return $this->diseaseConstraints->count();
    }

    /**
     * @return mixed
     */
    public function getThresholds()
    {
        return $this->thresholds;
    }

    /**
     * @param mixed $thresholds
     */
    public function setThresholds($thresholds)
    {
        $this->thresholds = $thresholds;
    }

    /**
     * Get CSV Headers
     *
     * @return array
     */
    public static function getHeaderCsvRow()
    {
        $row = array() ;
        $row[] = 'Disease Id';
        $row[] = 'Disease Reference';
        $row[] = 'Disease Name';

        $result = array_merge($row, SesDashboardDiseaseValue::getHeaderCsvRow());

        return $result;
    }

    /**
     * Get CSV Disease format
     *
     * @return array
     */
    public function getCsvRow()
    {
        $result = array();

        $row = array() ;
        $row[] = self::getId();
        $row[] = self::getDisease();
        $row[] = self::getName();

        $diseaseValues = self::getDiseaseValues();
        foreach($diseaseValues as $diseaseValue) {
            $result[] = array_merge($row, $diseaseValue->getCsvRow());
        }

        return $result;
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
}
