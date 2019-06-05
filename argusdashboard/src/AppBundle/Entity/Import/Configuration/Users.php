<?php

namespace AppBundle\Entity\Import\Configuration;

use JMS\Serializer\Annotation as JMS;

/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 09/11/2017
 * Time: 13:13
 */
/** @JMS\XmlRoot("users") */
class Users
{
    /**
     * @JMS\XmlList(inline = true, entry = "user")
     * @JMS\Type("array<AppBundle\Entity\Security\SesDashboardUser>")
     */
    private $dashboardUsers;

    public function getDashboardUsers()
    {
        return $this->dashboardUsers;
    }

    public function setDashboardUsers($dashboardUsers)
    {
        $this->dashboardUsers = $dashboardUsers;
    }
}