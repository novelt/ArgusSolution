<?php
/**
 * Threshold Repository
 *
 * @author fc, inspired by SesDashboardSiteRepository.php
 */

namespace AppBundle\Repository;

use AppBundle\Entity\Constant;
use Doctrine\ORM\EntityRepository;


class SesDashboardThresholdRepository extends EntityRepository
{
    /**
     * Return Threshold
     *
     * @param $siteId
     * @param $diseaseId
     * @param $period
     * @param $weekNumber
     * @param $monthNumber
     * @param $year
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    //    public function getThreshold($siteId, $diseaseId, $period, $weekNumber, $monthNumber, $year)
//    {
//        $qb = $this->createQueryBuilder('th');
//
//        $qb
//            ->where('th.FK_SiteId = :fkSiteId')
//            ->setParameter('fkSiteId', $siteId)
//        ;
//
//        $qb
//            ->andWhere('th.FK_DiseaseId = :diseaseId')
//            ->setParameter('diseaseId', $diseaseId)
//        ;
//
//        $qb
//            ->andWhere('th.period = :period')
//            ->setParameter('period', $period)
//        ;
//
//        switch($period){
//            case Constant::PERIOD_WEEKLY:
//                if (null != $weekNumber){
//                    $qb
//                        ->andWhere('th.weekNumber = :weekNumber')
//                        ->setParameter('weekNumber', $weekNumber)
//                    ;
//                }
//                break;
//            case Constant::PERIOD_MONTHLY:
//                if (null != $monthNumber){
//                    $qb
//                        ->andWhere('th.monthNumber = :monthNumber')
//                        ->setParameter('monthNumber', $monthNumber)
//                    ;
//                }
//                break;
//        }
//
//        $qb
//            ->andWhere('th.year = :year')
//            ->setParameter('year', $year)
//        ;
//
//        $result = $qb
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//
//        return $result;
//    }

    /**
     * Return concerned Thresholds for year and sites ids
     *
     * @param $siteIds
     * @param $year
     *
     * @return array
     */
    public function getAssociatedThresholds($siteIds, $year)
    {
        $qb = $this->createQueryBuilder('th');

        $qb
            ->where('th.FK_SiteId IN (:fkSiteIds)')
            ->setParameter('fkSiteIds', $siteIds)
        ;

        $qb
            ->andWhere('th.year = :year')
            ->setParameter('year', $year)
        ;

        $result = $qb
            ->getQuery()
            ->getResult()
        ;

        return $result;
    }

    /**
     * @param $diseaseIds
     * @param $periods
     * @param $years
     * @return array
     */
    public function getThresholdsData($diseaseIds, $periods, $years)
    {
        $qb = $this->createQueryBuilder('th');

        if ($diseaseIds != null) {
            $qb
                ->andWhere('th.FK_DiseaseId IN (:diseaseIds)')
                ->setParameter('diseaseIds', $diseaseIds)
            ;
        }

        if ($periods != null) {
            $qb
                ->andWhere('th.period IN (:periods)')
                ->setParameter('periods', $periods)
            ;
        }

        if ($years != null) {
            $qb
                ->andWhere('th.year IN (:years)')
                ->setParameter('years', $years)
            ;
        }

        $result = $qb
            ->getQuery()
            ->getResult()
        ;

        return $result;
    }
}