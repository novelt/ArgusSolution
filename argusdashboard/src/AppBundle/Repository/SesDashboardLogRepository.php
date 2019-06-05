<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 11/01/2017
 * Time: 15:48
 */

namespace AppBundle\Repository;


use AppBundle\Entity\SesDashboardLog;

class SesDashboardLogRepository extends BaseRepository
{
    /**
     * @param $calculationSessionId
     * @return SesDashboardLog[]
     */
    public function getLogs($calculationSessionId) {
        $qb = $this->createQueryBuilder('l');

        $qb->where('l.calculationSessionId = :calculationSessionId');
        $qb->setParameter('calculationSessionId', $calculationSessionId);

        $qb->orderBy('l.id');

        $result = $qb->getQuery()->getResult();
        return $result;
    }
}