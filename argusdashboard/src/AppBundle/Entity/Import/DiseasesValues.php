<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 10/1/2015
 * Time: 6:46 PM
 */

namespace AppBundle\Entity\Import;

use JMS\Serializer\Annotation as JMS;

/** @JMS\XmlRoot("values") */
class DiseasesValues
{
    /**
     * @JMS\XmlList(inline = true, entry = "value")
     * @JMS\Type("array<AppBundle\Entity\SesDashboardDiseaseValue>")
     */
    private $diseasesValues;

    public function getDashboardDiseasesValues()
    {
        return $this->diseasesValues;
    }

    public function setDashboardDiseasesValues($dashboardDiseasesValues)
    {
        $this->diseasesValues = $dashboardDiseasesValues ;
    }
}