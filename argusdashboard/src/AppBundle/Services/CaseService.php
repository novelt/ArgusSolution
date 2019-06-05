<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 11/26/2015
 * Time: 4:35 PM
 */

namespace AppBundle\Services;

use Doctrine\ORM\EntityManager;


class CaseService
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /*
     * TODO : Do not take archived data and deleted data
     */
    public function getAll($status, $startDate, $endDate, $disease, $siteId)
    {
        $repository = $this->em->getRepository('AppBundle:SesReportValues');
        $cases = $repository->getValues($status, $startDate, $endDate, $disease, $siteId) ;

        return $cases;
    }
}