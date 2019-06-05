<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 13/12/2017
 * Time: 16:50
 */

namespace AppBundle\Entity\Security;


class SesDashboardPermissionScope
{
    const SCOPE_SINGLE = 'SINGLE';
    const SCOPE_ALL = 'ALL';
    const SCOPE_NONE = 'NONE';

    public static function getValues()
    {
        return [
            SesDashboardPermissionScope::SCOPE_SINGLE,
            SesDashboardPermissionScope::SCOPE_ALL,
            SesDashboardPermissionScope::SCOPE_NONE,
        ];
    }

}