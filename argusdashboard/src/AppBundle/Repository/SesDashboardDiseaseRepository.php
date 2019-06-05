<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 7/22/2015
 * Time: 11:29 AM
 */

namespace AppBundle\Repository;

use AppBundle\Entity\SesDashboardDisease;
use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\Constant;
use Doctrine\ORM\QueryBuilder;

class SesDashboardDiseaseRepository extends BaseRepository
{
    /**
     * @param $period
     * @param bool $includeAlerts
     * @param null $reportDataSourceId
     * @param null $reportDataSourceCode
     * @return SesDashboardDisease[]
     */
    public function findDiseases($period, $includeAlerts = true, $reportDataSourceId = null, $reportDataSourceCode = null)
    {
        $qb = $this->createQueryBuilder('d')
                ->innerjoin('d.diseaseValues', 'dv')
                ->select ('d')
                ->addSelect('dv');

        if(!$includeAlerts) {
            $qb
                ->andWhere('d.disease != :disease')
                ->setParameter('disease', Constant::DISEASE_ALERT)
            ;
        }

        if ($period != null) {
            $qb
                ->andWhere('dv.period = :period')
                ->setParameter('period', $period);
        }

        $this->addWhere($qb, 'd', 'reportDataSourceId', $reportDataSourceId);

        if($reportDataSourceCode !== null) {
            $qb->innerjoin('d.reportDataSource', 'rds');

            $this->addWhere($qb, 'rds', 'code', $reportDataSourceCode);
        }

        $qb
            ->orderBy('d.disease')
        ;

        return $qb
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @param $diseaseCode
     * @return SesDashboardDisease[]
     */
    public function findDiseasesByCode($diseaseCode) {
        $qb = $this->findDiseaseByCodeQueryBuilder($diseaseCode);
        return $qb->getQuery()->getResult();
    }

    /**
     * @param $diseaseCode
     * @return SesDashboardDisease
     */
    public function findDiseaseByCode($diseaseCode) {
        $qb = $this->findDiseaseByCodeQueryBuilder($diseaseCode);
        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param $diseaseCode
     * @return QueryBuilder
     */
    public function findDiseaseByCodeQueryBuilder($diseaseCode) {
        $qb = $this->createQueryBuilder('d');

        if(is_array($diseaseCode)) {
                $qb->andWhere('d.disease IN (:diseaseCode)');
                $qb->setParameter('diseaseCode', $diseaseCode);
            }
        else {
            $qb->andWhere('d.disease = :diseaseCode');
            $qb->setParameter('diseaseCode', $diseaseCode);
        }

        return $qb;
    }

    public function findDiseasesPeriodIds($period, $diseaseIds)
    {
        $qb = $this->createQueryBuilder('d')
            ->join('d.diseaseValues', 'dv')
            ->addSelect('dv');

        $qb
            ->where('dv.period = :period')
            ->setParameter('period', $period)
        ;

        $qb
            ->andWhere('d.id in (:diseaseIds)')
            ->setParameter('diseaseIds', explode(',',$diseaseIds));
        ;

        return $qb
            ->getQuery()
            ->getResult()
            ;
    }

    public function findDiseaseValues($period, $includeAlerts=true)
    {
        $qb = $this->createQueryBuilder('d')
            ->join('d.diseaseValues', 'dv')
            ->select('COUNT(dv.id)');

        if(!$includeAlerts) {
            $qb
                ->andWhere('d.disease != :disease')
                ->setParameter('disease', Constant::DISEASE_ALERT)
            ;
        }

        $qb
            ->where('dv.period = :period')
            ->setParameter('period', $period)
        ;

        $qb
            ->andWhere('dv.mandatory = :mandatory')
            ->setParameter('mandatory', 1)
        ;

        return $qb
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    public function findAlertDiseaseWithValues()
    {
        $qb = $this->createQueryBuilder('d')
            ->join('d.diseaseValues', 'dv')
            ->addSelect('dv');

        $qb
            ->andWhere('d.disease = :disease')
            ->setParameter('disease', Constant::DISEASE_ALERT)
        ;

        return $qb
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    public function getNumberOfDashboardDiseases($includeAlerts = true)
    {
        $qb = $this->createQueryBuilder('d')
            ->select('COUNT(d.disease)');

        if(!$includeAlerts) {
            $qb
                ->andWhere('d.disease != :disease')
                ->setParameter('disease', Constant::DISEASE_ALERT)
            ;
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Return the diseases having $period diseasesValues (Monthly or Weekly)
     *
     * @param $period
     * @param bool $includeAlerts
     * @return SesDashboardDisease[]
     */
    public function getDiseasesPerPeriod($period, $includeAlerts = true)
    {
        $qb = $this->createQueryBuilder('d')
            ->join('d.diseaseValues', 'dv');

        if(!$includeAlerts) {
            $qb
                ->andWhere('d.disease != :disease')
                ->setParameter('disease', Constant::DISEASE_ALERT)
            ;
        }

        $qb
            ->andWhere('dv.period = :period')
            ->setParameter('period', $period)
        ;

        return $qb
            ->getQuery()
            ->getResult()
            ;
    }
}