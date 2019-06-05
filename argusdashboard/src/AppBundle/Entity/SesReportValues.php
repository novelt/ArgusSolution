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
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SesReportValuesRepository")
 * @ORM\Table(name="sesdashboard_reportvalues", options={"collate"="utf8_general_ci"})
 */
class SesReportValues
{
	/**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

	/**
     * @ORM\Column(type="string", name="`Key`", length=45)
     */
    private $key;

	/**
     * @ORM\Column(type="integer", name="`Value`")
     */
    private $value;

	/**
     * @ORM\Column(type="integer")
     */
    private $FK_ReportId;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isArchived;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isDeleted;

	/**
    * @ORM\ManyToOne(targetEntity="SesReport", inversedBy="reportValues", cascade={"persist"})
    * @ORM\JoinColumn(name="FK_ReportId", referencedColumnName="id", onDelete="CASCADE")
    */
	private $report;

    // Specific ThresholdMaxValue per Site, Disease, week or month & year
    private $thresholdMaxValue;

    public function __construct()
    {
    }

    /**
     * @param $key
     * @param $value
     * @return SesReportValues
     */
    public static function create($key, $value)
    {
        $instance = new self();
        $instance->setKey($key);
        $instance->setValue($value);

        return $instance ;
    }

    public function getId()
    {
        return $this->id;
    }
	
	public function getKey()
    {
        return $this->key;
    }

    public function getKeyForDisplay()
    {
        if ($this->report != null) {
            if (strlen($this->key) > strlen($this->report->getDisease())) {
                if (stristr($this->key,$this->report->getDisease()) !== FALSE) {
                    return str_replace('-','', str_replace('_', '', str_replace($this->report->getDisease(),'',$this->key )));
                }
            }
        }

        return $this->key;
    }
	
	public function getValue()
    {
        return $this->value;
    }
	
	public function getReportId()
    {
        return $this->FK_ReportId;
    }
	
	public function getReport()
    {
		return $this->report;
    }

    public function setKey($key)
    {
        $this->key = $key;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

	public function setReport(SesReport $report)
    {
		$this->report = $report;
		return $this;
    }

    public function setThresholdMaxValue($maxValue)
    {
        $this->thresholdMaxValue = $maxValue ;
    }

    public function getThresholdMaxValue()
    {
        return $this->thresholdMaxValue ;
    }

    public function getThresholdMaxValueForDisplay()
    {
        if (isset($this->thresholdMaxValue)) {
            return $this->thresholdMaxValue;
        }

        return '-';
    }

    /**
     * return true if value >= thresholdMaxValue
     *
     * @return bool
     */
    public function surpassThresholdMaxValue()
    {
        if (! isset($this->thresholdMaxValue)) {
            return false;
        }

        return ($this->value >= $this->thresholdMaxValue) ;
    }

    /**
     * Get CSV Headers
     *
     * @return array
     */
    public static function getHeaderCsvRow()
    {
        $row = array() ;
        $row[] = 'Value Id';
        $row[] = 'Indicator';
        $row[] = 'Value';

        return $row;
    }

    /**
     * Get CSV ReportValues format
     *
     * @return array
     */
    public function getCsvRow()
    {
        $row = array() ;
        $row[] = self::getId();
        $row[] = self::getKey();
        $row[] = self::getValue();

        return $row ;
    }
}
