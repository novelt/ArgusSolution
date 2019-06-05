<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 4/8/2016
 * Time: 4:55 PM
 */

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;


class SesAlertRepository extends EntityRepository
{
    public function getAlerts($siteIds, $startDate, $endDate){
        $qb = $this->createQueryBuilder('al')
            ->innerJoin('al.frontLineGroup', 's')
            ->addSelect('s');

        $qb
            ->where('al.FK_SiteId IN (:siteIds)')
            ->setParameter('siteIds', $siteIds)
        ;

        $qb
            ->andWhere('al.receptionDate BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
        ;

        $qb
            ->addOrderBy('al.receptionDate','DESC')
        ;

        return $qb
            ->getQuery()
            ->getResult()
            ;
    }

    public function getNewAlerts($siteIds, $limit)
    {
        return $this->get($siteIds, false, $limit);
    }

    public function getOldAlerts($siteIds, $limit)
    {
        return $this->get($siteIds, true, $limit);
    }

    /**
     * Create the query used by the export Data functionality
     * No join authorized as the query is used to iterate on results
     *
     * @param $startDate
     * @param $endDate
     * @return \Doctrine\ORM\Query
     */
    public function getExportQuery($startDate, $endDate)
    {
        $qb = $this->createQueryBuilder('al');

        if ($startDate != null && $endDate != null) {
            $qb
                ->Where('al.receptionDate BETWEEN :startDate AND :endDate')
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);
        }

        $qb
            ->addOrderBy('al.receptionDate','DESC')
        ;

        return $qb->getQuery();
    }

    private function get($siteIds, $isRead, $limit)
    {
        $qb = $this->createQueryBuilder('al')
            ->innerJoin('al.frontLineGroup', 's')
            ->addSelect('s');

        $qb
            ->where('al.FK_SiteId IN (:siteIds)')
            ->setParameter('siteIds', $siteIds)
        ;

        if ($isRead == false){
            $qb
                ->andWhere('al.isRead = :isRead or al.isRead is null')
                ->setParameter('isRead', $isRead)
            ;
        }
        else{
            $qb
                ->andWhere('al.isRead = :isRead')
                ->setParameter('isRead', $isRead)
            ;
        }

        $qb
            ->addOrderBy('al.receptionDate','DESC')
        ;

        return $qb
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
            ;
    }
}