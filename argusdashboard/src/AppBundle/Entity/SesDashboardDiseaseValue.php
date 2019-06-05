<?php
/**
 * Disease Value
 *
 * @author eotin
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use JMS\Serializer\Annotation as JMS;

 /**
  * @ORM\Entity(repositoryClass="AppBundle\Repository\SesDashboardDiseaseValueRepository")
  * @ORM\Table(name="sesdashboard_diseasevalues", options={"collate"="utf8_general_ci"})
  *
  * @UniqueEntity(fields={"FK_DiseaseId", "value"}, errorPath="value", message="disease_value.reference.unique")
  * @UniqueEntity(fields={"FK_DiseaseId", "keyWord"}, errorPath="keyWord", message="disease_value.keyword.unique")
  *
  * @JMS\ExclusionPolicy("all")
  * @JMS\XmlRoot("value")
  */
class SesDashboardDiseaseValue
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $FK_DiseaseId;

    /**
     * @ORM\Column(type="string", length=100)
     * @JMS\Expose()
     * @JMS\SerializedName("reference")
     */
    private $value;

    /**
     * @ORM\Column(type="string", length=45)
     * @JMS\Expose()
     */
    private $period;

    /**
     * @ORM\Column(type="integer")
     * @JMS\Expose()
     */
    private $position;

    /**
     * @ORM\Column(type="string", length=45)
     * @JMS\Expose()
     * @JMS\SerializedName("type")
     */
    private $datatype;

    /**
     * @ORM\Column(type="boolean")
     */
    private $mandatory;

    /**
     * @JMS\Expose
     * @JMS\Type("string")
     * @JMS\SerializedName("mandatory")
     */
    private $mandatoryString;

    /**
     * @ORM\Column(type="string")
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $keyWord;

    /**
     * @ORM\ManyToOne(targetEntity="SesDashboardDisease", inversedBy="diseaseValues")
     * @ORM\JoinColumn(name="FK_DiseaseId", referencedColumnName="id", onDelete="CASCADE")
     */
    private $parentDisease;

    /**
     * @ORM\OneToMany(targetEntity="SesDashboardThreshold", mappedBy="diseaseValue", cascade={"persist"})
     */
    private $thresholds;

    public function __construct()
    {

    }

    public function getId()
    {
        return $this->id;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getReference()
    {
        return $this->value;
    }

    public function getFormatValue()
    {
        $result = $this->value;

        if (isset($result)) {
            if ($this->parentDisease != null && strpos($result, $this->parentDisease->getDisease()) !== FALSE) {
                $result = str_replace('-','', str_replace('_', '',str_replace($this->parentDisease->getDisease(), "", $result)));
            }
        }

        return $result;

    }

    /**
     * Returns the diseaseValue's value having the Primary priority
     *
     * @return string|null
     */
    public function getPrimaryKeyword()
    {
        return $this->keyWord;
    }

    public function getPeriod()
    {
        return $this->period;
    }

    public function setPeriod($period)
    {
        $this->period = $period;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function setPosition($position)
    {
        $this->position = $position;
    }

    public function getType()
    {
        return $this->datatype;
    }

    public function setType($type)
    {
        $this->datatype = $type;
    }

    public function getMandatory()
    {
        return $this->mandatory;
    }

    public function setMandatory($mandatory)
    {
        $this->mandatory = $mandatory;
    }

    /**
     * @return mixed
     */
    public function getKeyword()
    {
        return $this->keyWord;
    }

    public function setKeyword($keyWord)
    {
        $this->keyWord = $keyWord;
    }

    /**
     * @return SesDashboardDisease
     */
    public function getParentDisease()
    {
        return $this->parentDisease ;
    }

    public function setParentDisease($parentDisease)
    {
        $this->parentDisease = $parentDisease;
    }

    public function getDiseaseId()
    {
        return $this->FK_DiseaseId;
    }

    /**
     * @return mixed
     */
    public function getFKDiseaseId()
    {
        return $this->FK_DiseaseId;
    }

    /**
     * @param mixed $FK_DiseaseId
     */
    public function setFKDiseaseId($FK_DiseaseId)
    {
        $this->FK_DiseaseId = $FK_DiseaseId;
    }

    /**
     * @return mixed
     */
    public function getDatatype()
    {
        return $this->datatype;
    }

    /**
     * @param mixed $datatype
     */
    public function setDatatype($datatype)
    {
        $this->datatype = $datatype;
    }

    /**
     * @return mixed
     */
    public function getMandatoryString()
    {
        return $this->mandatoryString;
    }

    /**
     * @param mixed $mandatoryString
     */
    public function setMandatoryString($mandatoryString)
    {
        $this->mandatoryString = $mandatoryString;
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
        $row[] = 'Disease Value Id';
        $row[] = 'Disease Value Reference';
        $row[] = 'Period';
        $row[] = 'Position';
        $row[] = 'Type';

        return $row;
    }

    /**
     * Get CSV DiseaseValue format
     *
     * @return array
     */
    public function getCsvRow()
    {
        $row = array() ;
        $row[] = self::getId();
        $row[] = self::getReference();
        $row[] = self::getPeriod();
        $row[] = self::getPosition();
        $row[] = self::getType();

        return $row;
    }

    /**
     * @JMS\PostDeserialize
     */
    private function setMandatoryFromMandatoryString()
    {
        if ($this->mandatoryString != null) {
            if ($this->mandatoryString == "Yes") {
                $this->mandatory = true;
            } else {
                $this->mandatory = false;
            }
        }
    }

    /**
     * @JMS\PreSerialize
     */
    private function setMandatoryStringFromMandatory()
    {
        if ($this->mandatory == true){
            $this->mandatoryString = "Yes";
        }
        else{
            $this->mandatoryString = "No";
        }
    }
}
