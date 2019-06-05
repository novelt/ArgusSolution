<?php
/**
 * Threshold Entity Class
 *
 * @author François Cardinaux, inspired by Emmanuel Otin's SesDashboardSite.php
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Entity\SesDashboardSite;
use AppBundle\Services\SiteService;
use AppBundle\Services\DiseaseService;
use AppBundle\Utils\Helper;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SesDashboardThresholdRepository")
 * @ORM\Table(name="sesdashboard_thresholds", options={"collate"="utf8_general_ci"})
 *
 *
 * @JMS\XmlRoot("threshold")
 * @JMS\ExclusionPolicy("all")
 */
class SesDashboardThreshold
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * Site reference
     *
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $siteReference;

    /**
     * Disease reference
     *
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $diseaseReference;

    /**
     * Disease Value reference
     *
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $valueReference;

    /**
     * @ORM\Column(type="string")
     * @JMS\Expose
     */
    private $period;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @JMS\Expose
     * @Assert\LessThanOrEqual(value=53)
     * @Assert\GreaterThanOrEqual(value=1)
     */
    private $weekNumber;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @JMS\Expose
     * @Assert\LessThanOrEqual(value=12)
     * @Assert\GreaterThanOrEqual(value=1)
     */
    private $monthNumber;

    /**
     * @ORM\Column(type="integer")
     * @JMS\Expose
     * @Assert\GreaterThanOrEqual(value=2010)
     */
    private $year;

    /**
     * Maximal value
     *
     * "MAXVALUE" is a reserved word in MariaDB
     * (https://mariadb.com/kb/en/mariadb/reserved-words/)
     *
     * @ORM\Column(type="integer", name="maximalValue")
     * @Assert\GreaterThanOrEqual(value=1)
     * @JMS\Expose
     */
    private $maxValue;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $FK_SiteId;

    /**
     * @var SesDashboardSite
     * @ORM\ManyToOne(targetEntity="SesDashboardSite", inversedBy="thresholds")
     * @ORM\JoinColumn(name="FK_SiteId", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $site;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $FK_DiseaseId;

    /**
     * @var SesDashboardDisease
     * @ORM\ManyToOne(targetEntity="SesDashboardDisease", inversedBy="thresholds")
     * @ORM\JoinColumn(name="FK_DiseaseId", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $disease;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $FK_DiseaseValueId;

    /**
     * @var SesDashboardDiseaseValue
     * @ORM\ManyToOne(targetEntity="SesDashboardDiseaseValue", inversedBy="thresholds")
     * @ORM\JoinColumn(name="FK_DiseaseValueId", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $diseaseValue;

    public function getId()
    {
        return $this->id;
    }

    public function getSiteReference()
    {
        return $this->siteReference;
    }

    public function getDiseaseReference()
    {
        return $this->diseaseReference;
    }

    public function getValueReference()
    {
        return $this->valueReference;
    }

    public function getPeriod()
    {
        return $this->period;
    }

    public function setPeriod($period)
    {
        $this->period = $period;
    }

    public function getWeekNumber()
    {
        return $this->weekNumber;
    }

    public function setWeekNumber($weekNumber)
    {
        $this->weekNumber = $weekNumber;
    }

    public function getMonthNumber()
    {
        return $this->monthNumber;
    }

    public function setMonthNumber($monthNumber)
    {
        $this->monthNumber = $monthNumber;
    }

    public function getYear()
    {
        return $this->year;
    }

    public function setYear($year)
    {
        $this->year = $year;
    }

    public function getMaxValue()
    {
        return $this->maxValue;
    }

    public function setMaxValue($maxValue)
    {
        $this->maxValue = $maxValue;
    }

    public function getName()
    {
        $out = array(
            ($this->site != null ? $this->site->getReference() : " -"),
            ($this->disease != null ?  $this->disease->getName() : ""),
            $this->getYear(),
            $this->getPeriod()
        );

        return implode(' / ', $out);
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

    public function getDiseaseId()
    {
        return $this->FK_DiseaseId;
    }

    public function setDiseaseId($diseaseId)
    {
        $this->FK_DiseaseId = $diseaseId;
    }

    public function getDisease()
    {
        return $this->disease;
    }

    public function setDisease(SesDashboardDisease $disease)
    {
        $this->disease = $disease;
        $this->diseaseReference = $disease->getDisease();
    }

    public function getDiseaseValue()
    {
        return $this->diseaseValue;
    }

    public function setDiseaseValue($diseaseValue)
    {
        $this->diseaseValue = $diseaseValue;
    }

    public function getDiseaseValueId()
    {
        return $this->FK_DiseaseValueId;
    }

    /**
     * Get CSV Headers
     *
     * @return array
     */
    public static function getHeaderCsvRow()
    {
        $row = array() ;
        $row[] = 'Threshold Id';
        $row[] = 'Period';
        $row[] = 'Month Number';
        $row[] = 'Week Number';
        $row[] = 'Year';
        $row[] = 'Max Value';
        $row[] = 'Disease Id';
        $row[] = 'Disease Value Id';
        $row[] = 'Site Id';

        return $row;
    }

    /**
     * Get CSV Threshold format
     *
     * @return array
     */
    public function getCsvRow()
    {
        $row = array() ;
        $row[] = self::getId();
        $row[] = self::getPeriod();
        $row[] = self::getMonthNumber();
        $row[] = self::getWeekNumber();
        $row[] = self::getYear();
        $row[] = self::getMaxValue();
        $row[] = self::getDiseaseId();
        $row[] = self::getDiseaseValueId();
        $row[] = self::getSiteId();

        return [$row];
    }

    /**
     * @JMS\PreSerialize
     */
    public function prepareSerialization()
    {
        if ($this->disease != null) {
            $this->diseaseReference = $this->disease->getDisease();
        }
        if ($this->site != null) {
            $this->siteReference = $this->site->getReference();
        }
        if ($this->diseaseValue != null) {
            $this->valueReference = $this->diseaseValue->getValue();
        }
    }
}