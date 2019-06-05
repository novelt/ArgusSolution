<?php
/**
 * Disease Constraint
 *
 * @author fc, inspired by eotin's SesDashboardDiseaseValue
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

use JMS\Serializer\Annotation as JMS;

 /**
 * @ORM\Entity()
 * @ORM\Table(name="sesdashboard_diseaseconstraints", options={"collate"="utf8_general_ci"})
 * @JMS\ExclusionPolicy("all")
 * @JMS\XmlRoot("constraint")
 */
class SesDashboardDiseaseConstraint
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
     * @JMS\SerializedName("referencevalue_from")
     */
    private $referenceValueFrom;

    /**
     * @ORM\Column(type="string", length=20)
     * @JMS\Expose()
     */
    private $operator;

    /**
     * @ORM\Column(type="string", length=100)
     * @JMS\Expose()
     * @JMS\SerializedName("referencevalue_to")
     */
    private $referenceValueTo;

    /**
     * @ORM\Column(type="string", length=45)
     * @JMS\Expose()
     */
    private $period;

    /**
     * @ORM\ManyToOne(targetEntity="SesDashboardDisease", inversedBy="diseaseConstraints")
     * @ORM\JoinColumn(name="FK_DiseaseId", referencedColumnName="id", onDelete="CASCADE")
     */
    private $parentDisease;


    public function __construct()
    {

    }

    public function getId()
    {
        return $this->id;
    }

    public function getReferenceValueFrom()
    {
        return $this->referenceValueFrom;
    }

    public function setReferenceValueFrom($referenceValueFrom)
    {
        $this->referenceValueFrom = $referenceValueFrom;
    }

    public function getOperator()
    {
        return $this->operator;
    }

    public function setOperator($operator)
    {
        $this->operator = $operator;
    }

    public function getReferenceValueTo()
    {
        return $this->referenceValueTo;
    }

    public function setReferenceValueTo($referenceValueTo)
    {
        $this->referenceValueTo = $referenceValueTo;
    }

    public function getPeriod()
    {
        return $this->period;
    }

    public function setPeriod($period)
    {
        $this->period = $period;
    }

    public function setParentDisease($parentDisease)
    {
        $this->parentDisease = $parentDisease;
    }

    public function getDiseaseId()
    {
        return $this->FK_DiseaseId;
    }
}
