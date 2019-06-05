<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 13/12/2017
 * Time: 16:52
 */

namespace AppBundle\Entity\Security;


class SesDashboardPermissionRessource
{
    const RESSOURCE_ANGULAR_DASHBOARD = 'ANGULAR_DASHBOARD';
    const RESSOURCE_WEEKLY_REPORT = 'WEEKLY_REPORT';
    const RESSOURCE_MONTHLY_REPORT = 'MONTHLY_REPORT';
    const RESSOURCE_DASHBOARD_REPORT = 'DASHBOARD_REPORT';
    const RESSOURCE_ALERT = 'ALERT';
    const RESSOURCE_ANY = 'ANY';

    public static function getValues()
    {
        return [
            SesDashboardPermissionRessource::RESSOURCE_ANGULAR_DASHBOARD,
            SesDashboardPermissionRessource::RESSOURCE_WEEKLY_REPORT,
            SesDashboardPermissionRessource::RESSOURCE_MONTHLY_REPORT,
            SesDashboardPermissionRessource::RESSOURCE_DASHBOARD_REPORT,
            SesDashboardPermissionRessource::RESSOURCE_ALERT,
            SesDashboardPermissionRessource::RESSOURCE_ANY,
        ];
    }
}