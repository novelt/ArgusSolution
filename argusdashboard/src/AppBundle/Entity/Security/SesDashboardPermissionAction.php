<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 13/12/2017
 * Time: 16:52
 */

namespace AppBundle\Entity\Security;


class SesDashboardPermissionAction
{
    const ACTION_VIEW = 'VIEW';
    const ACTION_VALIDATE = 'VALIDATE';
    const ACTION_REJECT = 'REJECT';
    const ACTION_DOWNLOAD = 'DOWNLOAD';
    const ACTION_UPLOAD = 'UPLOAD';

    public static function getValues()
    {
        return [
            SesDashboardPermissionAction::ACTION_VIEW,
            SesDashboardPermissionAction::ACTION_VALIDATE,
            SesDashboardPermissionAction::ACTION_REJECT,
            SesDashboardPermissionAction::ACTION_DOWNLOAD,
            SesDashboardPermissionAction::ACTION_UPLOAD,
        ];
    }
}