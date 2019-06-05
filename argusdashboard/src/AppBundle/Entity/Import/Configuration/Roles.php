<?php

namespace AppBundle\Entity\Import\Configuration;

use JMS\Serializer\Annotation as JMS;

/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 09/11/2017
 * Time: 13:13
 */
/** @JMS\XmlRoot("roles") */
class Roles
{
    /**
     * @JMS\XmlList(inline = true, entry = "role")
     * @JMS\Type("array<AppBundle\Entity\Security\SesDashboardRole>")
     */
    private $dashboardRoles;

    public function getDashboardRoles()
    {
        return $this->dashboardRoles;
    }

    public function setDashboardRoles($dashboardRoles)
    {
        $this->dashboardRoles = $dashboardRoles;
    }
}