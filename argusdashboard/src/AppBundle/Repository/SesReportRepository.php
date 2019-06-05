<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 9/6/2016
 * Time: 10:40 AM
 */

namespace AppBundle\Repository;

use AppBundle\Entity\Constant;
use Doctrine\ORM\EntityRepository;


class SesReportRepository extends EntityRepository
{
    /**
     * Get the latest Report received ever
     *
     * @param $siteId
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getLastReportReceivedEver($siteId)
    {
        $qb = $this->createQueryBuilder('r')
                ->join('r.report', 'pr')
                ->join('pr.fullReport', 'fr');

        $qb
            ->where('fr.FK_SiteId = :siteId')
            ->setParameter('siteId', $siteId);

        $qb
            ->orderBy('r.receptionDate', 'DESC');

        $qb
            ->setMaxResults(1);

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }
}