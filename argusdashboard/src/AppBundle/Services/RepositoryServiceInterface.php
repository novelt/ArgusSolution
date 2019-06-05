<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 21/11/2016
 * Time: 16:16
 */

namespace AppBundle\Services;


use AppBundle\Repository\RepositoryInterface;

interface RepositoryServiceInterface extends RepositoryInterface
{
    /**
     * @return RepositoryInterface
     */
    public function getRepository();
    public function setRepository(RepositoryInterface $repository);
}