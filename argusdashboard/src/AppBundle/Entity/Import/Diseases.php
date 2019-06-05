<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 10/1/2015
 * Time: 6:46 PM
 */

namespace AppBundle\Entity\Import;

use JMS\Serializer\Annotation as JMS;

/** @JMS\XmlRoot("diseases") */
class Diseases
{
    /**
     * @JMS\XmlList(inline = true, entry = "disease")
     * @JMS\Type("array<AppBundle\Entity\SesDashboardDisease>")
     */
    private $dashboardDiseases;

    public function getDashboardDiseases()
    {
        return $this->dashboardDiseases;
    }

    public function setDashboardDiseases($dashboardDiseases)
    {
        $this->dashboardDiseases = $dashboardDiseases;

        foreach ($this->dashboardDiseases as $disease)
        {
            $values = $disease->getDiseaseValues();
            $jmsValues = new DiseasesValues();
            $jmsValues->setDashboardDiseasesValues($values);
            $disease->setDiseaseValues($jmsValues);

            $constraints = $disease->getDiseaseConstraints();
            $jmsConstraints = new DiseasesConstraints();
            $jmsConstraints->setDashboardDiseasesConstraints($constraints);
            $disease->setDiseaseConstraints($jmsConstraints);
        }
    }
}