<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 27/06/2016
 * Time: 12:25
 */

namespace AppBundle\Entity\Import\Report;

use JMS\Serializer\Annotation as JMS;

class XmlAlerts
{
    /**
     * @JMS\XmlList(inline = true, entry = "alert")
     * @JMS\Type("array<AppBundle\Entity\Import\Report\XmlAlert>")
     */
    private $xmlAlert;

    public function getAlerts(){
        return $this->xmlAlert;
    }
}