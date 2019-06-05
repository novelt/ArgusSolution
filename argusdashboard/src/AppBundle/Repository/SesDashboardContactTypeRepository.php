<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 17/11/2016
 * Time: 17:22
 */

namespace AppBundle\Repository;

use AppBundle\Entity\SesDashboardContactType;
use AppBundle\Services\DbConstant;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use AppBundle\Entity\Constant;


class SesDashboardContactTypeRepository extends BaseRepository
{
    /**
     * @return SesDashboardContactType[]
     */
    public function findAll()
    {
        return parent::findAll();
    }

    /**
     * @param $sendsReports
     * @return SesDashboardContactType[]
     */
    public function getContactTypes($sendsReports=null, $useInIndicatorsCalculation=null) {
        $qb = $this->getContactTypesQuery($sendsReports, null, $useInIndicatorsCalculation);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $reference
     * @return SesDashboardContactType[]
     */
    public function findByReference($reference) {
        $qb = $this->getContactTypesQuery(null, $reference);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $sendsReports
     * @return QueryBuilder
     */
    private function getContactTypesQuery($sendsReports=null, $reference=null, $useInIndicatorsCalculation=null) {
        $qb = $this->createQueryBuilder('c');

        if($sendsReports !== null) {
            if($sendsReports === DbConstant::NULL) {
                $qb->andWhere('c.sendsReports IS NULL');
            }
            if($sendsReports === DbConstant::NOT_NULL) {
                $qb->andWhere('c.sendsReports IS NOT NULL');
            }
            else if(is_array($sendsReports)) {
                $qb->andWhere('c.sendsReports IN (:sendsReports)');
                $qb->setParameter('sendsReports', $sendsReports);
            }
            else {
                $qb->andWhere('c.sendsReports = :sendsReports');
                $qb->setParameter('sendsReports', $sendsReports);
            }
        }

        if($reference !== null) {
            if($reference === DbConstant::NULL) {
                $qb->andWhere('c.reference IS NULL');
            }
            if($reference === DbConstant::NOT_NULL) {
                $qb->andWhere('c.reference IS NOT NULL');
            }
            else if(is_array($reference)) {
                $qb->andWhere('c.reference IN (:reference)');
                $qb->setParameter('reference', $reference);
            }
            else {
                $qb->andWhere('c.reference = :reference');
                $qb->setParameter('reference', $reference);
            }
        }

        $this->addWhere($qb, 'c', 'useInIndicatorsCalculation', $useInIndicatorsCalculation);

        return $qb;
    }
}