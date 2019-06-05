<?php
/**
 * Import Disease Constraint List
 *
 * @author fc, copied eotin's from DiseaseValues.php
 */

namespace AppBundle\Entity\Import;

use JMS\Serializer\Annotation as JMS;

/** @JMS\XmlRoot("constraints") */
class DiseasesConstraints
{
    /**
     * @JMS\XmlList(inline = true, entry = "constraint")
     * @JMS\Type("array<AppBundle\Entity\SesDashboardDiseaseConstraint>")
     */
    private $diseasesConstraints;

    public function getDashboardDiseasesConstraints()
    {
        return $this->diseasesConstraints;
    }

    public function setDashboardDiseasesConstraints($dashboardDiseasesConstraints)
    {
        $this->diseasesConstraints = $dashboardDiseasesConstraints;
    }
}