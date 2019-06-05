<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 1/19/2016
 * Time: 2:38 PM
 */

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class SesReportValuesRepository  extends EntityRepository
{
    public function getValues($status, $startDate, $endDate, $disease, $siteId)
    {
        $qb = $this->createQueryBuilder('rv')
            ->innerJoin('rv.report', 'r', 'WITH', 'r.isArchived = :isarchived AND r.isDeleted = :isdeleted')
            ->setParameter('isarchived', false)->setParameter('isdeleted', false)
            ->addSelect('r')
            ->leftJoin('r.report', 'pr')
            ->addSelect('pr')
            ->leftJoin('pr.fullReport', 'fr')
            ->addSelect('fr')
            ->leftJoin('fr.frontLineGroup', 's')
            ->addSelect('s');

        $qb
            ->where('1 = :un')
            ->setParameter('un', 1)
        ;

        $qb
            ->andWhere('fr.aggregate is NULL')
        ;

        if ($status != null)
        {
            $qb
                ->andWhere('pr.status = :status')
                ->setParameter('status', $status)
            ;
        }

        if ($startDate != null && $endDate != null)
        {
            $qb
                ->andWhere('fr.startDate BETWEEN :start AND :end')
                ->setParameter('start', $startDate)
                ->setParameter('end',  $endDate)
            ;
        }

        if ($disease != null)
        {
            $qb
                ->andWhere('r.disease = :disease')
                ->setParameter('disease', $disease)
            ;
        }

        if ($siteId != null)
        {
            $qb
                ->andWhere('s.id = :siteId')
                ->setParameter('siteId', $siteId)
            ;
        }

        return $qb
            ->getQuery()
            ->getResult()
            ;
    }
}