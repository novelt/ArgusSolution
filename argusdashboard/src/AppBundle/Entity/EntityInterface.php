<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 25/10/2016
 * Time: 14:29
 */

namespace AppBundle\Entity;


interface EntityInterface
{
    public function getId();
    public function setId($id);
    public function getCreationDate();
    public function setCreationDate($creationDate);
}