<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 7/20/2015
 * Time: 4:44 PM
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="sesdashboard_aggregatepartreport", options={"collate"="utf8_general_ci"})
 */
class SesAggregatePartReport
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
    private $FK_PartReportOwnerId;

    /**
     * @ORM\Column(type="integer")
     */
    private $FK_PartReportId;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isDeleted;

    /**
     * @ORM\ManyToOne(targetEntity="SesPartReport", inversedBy="aggregatePartReports")
     * @ORM\JoinColumn(name="FK_PartReportOwnerId", referencedColumnName="id", onDelete="CASCADE")
     */
    private $partReportOwner;

    /**
     * @ORM\ManyToOne(targetEntity="SesPartReport")
     * @ORM\JoinColumn(name="FK_PartReportId", referencedColumnName="id")
     */
    private $partReport;


    /**
     * Creation of an Aggregate Part Report
     *
     * @param $partReportOwner
     * @param $partReport
     * @return SesAggregatePartReport
     */
    public static function create($partReportOwner, $partReport)
    {
        $instance = new self() ;
        $instance->setPartReportOwner($partReportOwner);
        $instance->setPartReport($partReport);

        return $instance ;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return SesPartReport
     */
    public function getPartReportOwner()
    {
        return $this->partReportOwner ;
    }

    public function setPartReportOwner(SesPartReport $partReportOwner)
    {
        $this->partReportOwner = $partReportOwner ;
    }

    /**
     * @return SesPartReport
     */
    public function getPartReport()
    {
        return $this->partReport ;
    }

    public function setPartReport(SesPartReport $partReport)
    {
        $this->partReport = $partReport ;
    }
}
