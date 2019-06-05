<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 28/03/2018
 * Time: 17:55
 */

namespace AppBundle\Entity;


interface PermissionSite
{
    public function getId();
    public function getPath();
    public function getLevel();
}