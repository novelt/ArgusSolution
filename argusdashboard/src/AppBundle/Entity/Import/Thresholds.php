<?php
/**
 * Import Threshold List
 *
 * @author fc, copied from Sites.php
 */

namespace AppBundle\Entity\Import;

use JMS\Serializer\Annotation as JMS;

/** @JMS\XmlRoot("thresholds") */
class Thresholds
{
    /**
     * @JMS\XmlList(inline = true, entry = "threshold")
     * @JMS\Type("array<AppBundle\Entity\SesDashboardThreshold>")
     */
    private $dashboardThresholds;

    public function getDashboardThresholds()
    {
        return $this->dashboardThresholds;
    }

    public function setDashboardThresholds($dashboardThresholds)
    {
        $this->dashboardThresholds = $dashboardThresholds;
    }
}