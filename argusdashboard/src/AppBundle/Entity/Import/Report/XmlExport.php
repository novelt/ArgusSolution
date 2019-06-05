<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 23/06/2016
 * Time: 16:37
 */

namespace AppBundle\Entity\Import\Report;

use JMS\Serializer\Annotation as JMS;

/** @JMS\XmlRoot("export") */
class XmlExport
{
    /**
     * @JMS\Type("AppBundle\Entity\Import\Report\XmlReports")
     */
    private $reports;

    /**
     * @JMS\Type("AppBundle\Entity\Import\Report\XmlAlerts")
     */
    private $alerts;

    public function addReport(XmlReport $report) {
        if($this->reports === null) {
            $this->reports = new XmlReports();
        }
        $this->reports->addReport($report);
    }

    /**
     * @return XmlReports
     */
    public function getReports()
    {
        return $this->reports ;
    }

    /**
     * @return XmlAlerts
     */
    public function getAlerts()
    {
        return $this->alerts ;
    }
}