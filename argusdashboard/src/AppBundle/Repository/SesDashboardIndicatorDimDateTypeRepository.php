<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 17/11/2016
 * Time: 13:50
 */

namespace AppBundle\Repository;


use AppBundle\Entity\SesDashboardIndicatorDimDateType;
use AppBundle\Services\DbConstant;
use Doctrine\ORM\AbstractQuery;

class SesDashboardIndicatorDimDateTypeRepository extends BaseRepository
{
    /**
     * @param $code
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByCode($code) {
        $qb = $this->findByCodeQueryBuilder($code);
        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param $code
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findIdByCode($code) {
        $qb = $this->findByCodeQueryBuilder($code);
        $qb->select('i.id');

        return $qb->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);
    }

    /**
     * @param $codes
     * @return SesDashboardIndicatorDimDateType[]
     */
    public function findByCodes($codes) {
        $qb = $this->findByCodeQueryBuilder($codes);
        return $qb->getQuery()->getResult();
    }

    /**
     * @param $codes
     * @return int[]
     */
    public function findIdsByCodes($codes) {
        $qb = $this->findByCodeQueryBuilder($codes);
        $qb->select('i.id');

        return array_map('current', $qb->getQuery()->getScalarResult());
    }

    /**
     * @param $code
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function findByCodeQueryBuilder($code) {
        $qb = $this->createQueryBuilder('i');

        if($code !== null) {
            if($code === DbConstant::NULL) {
                $qb->andWhere('i.code IS NULL');
            }
            else if($code === DbConstant::NOT_NULL) {
                $qb->andWhere('i.code IS NOT NULL');
            }
            else if(is_array($code)) {
                $qb->andWhere('i.code IN (:code)');
                $qb->setParameter('code', $code);
            }
            else {
                $qb->andWhere('i.code = :code');
                $qb->setParameter('code', $code);
            }
        }

        return $qb;
    }
}