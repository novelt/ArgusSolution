<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 27/06/2016
 * Time: 14:44
 */

namespace AppBundle\Entity\Import\Report;

use JMS\Serializer\Annotation as JMS;

class XmlReportValues
{
    /**
     * @var XmlReportValue[]
     * @JMS\XmlList(inline = true, entry = "value")
     * @JMS\Type("array<AppBundle\Entity\Import\Report\XmlReportValue>")
     */
    private $value;

    public function addReportValue($value) {
        if($this->value === null) {
            $this->value = [];
        }

        $this->value[] = $value;
    }

    public function getReportValues(){
        return $this->value;
    }
}