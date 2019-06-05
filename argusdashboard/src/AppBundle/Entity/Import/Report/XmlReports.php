<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 27/06/2016
 * Time: 12:25
 */

namespace AppBundle\Entity\Import\Report;

use JMS\Serializer\Annotation as JMS;

class XmlReports
{
    /**
     * @JMS\XmlList(inline = true, entry = "report")
     * @JMS\Type("array<AppBundle\Entity\Import\Report\XmlReport>")
     */
    private $report;

    public function getReports(){
        return $this->report;
    }

    public function addReport(XmlReport $report) {
        if($this->report === null) {
            $this->report = [];
        }

        $this->report[] = $report;
    }
}