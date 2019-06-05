<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 10/1/2015
 * Time: 6:46 PM
 */

namespace AppBundle\Entity\Import;

use JMS\Serializer\Annotation as JMS;

/** @JMS\XmlRoot("sites") */
class Sites
{
    /**
     * @JMS\XmlList(inline = true, entry = "site")
     * @JMS\Type("array<AppBundle\Entity\SesDashboardSite>")
     */
    private $dashboardSites;

    public function getDashboardSites()
    {
        return $this->dashboardSites;
    }

    public function setDashboardSites($dashboardSites)
    {
        $this->dashboardSites = $dashboardSites;
    }
}