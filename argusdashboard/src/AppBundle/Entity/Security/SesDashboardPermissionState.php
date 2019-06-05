<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 13/12/2017
 * Time: 16:53
 */

namespace AppBundle\Entity\Security;


class SesDashboardPermissionState
{
    const STATE_VALIDATED = 'VALIDATED';
    const STATE_REJECTED = 'REJECTED';
    const STATE_PENDING = 'PENDING';
    const STATE_ANY = 'ANY';

    public static function getValues()
    {
        return [
            SesDashboardPermissionState::STATE_VALIDATED,
            SesDashboardPermissionState::STATE_REJECTED,
            SesDashboardPermissionState::STATE_PENDING,
            SesDashboardPermissionState::STATE_ANY,
        ];
    }
}