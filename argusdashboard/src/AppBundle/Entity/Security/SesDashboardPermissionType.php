<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 13/12/2017
 * Time: 16:53
 */

namespace AppBundle\Entity\Security;


class SesDashboardPermissionType
{
    const TYPE_ALLOW = 'ALLOW';
    const TYPE_DENY = 'DENY';

    public static function getValues()
    {
        return [
            SesDashboardPermissionType::TYPE_ALLOW,
            SesDashboardPermissionType::TYPE_DENY,
        ];
    }
}