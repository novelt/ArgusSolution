<?php

/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 26/10/2017
 * Time: 11:54
 */
namespace AppBundle\Repository\Messages;

use AppBundle\Repository\BaseRepository;

class IncomingSmsRepository extends BaseRepository
{
    /**
     * Return all Incoming Sms order on creation date asc
     *
     * @param $status
     * @param $type
     * @param $limit
     * @param $lockMode
     *
     * @return array
     */
    public function getIncomingSms($status, $type, $limit, $lockMode = null)
    {
        $qb = $this->createQueryBuilder('sms');

        $qb
            ->where('sms.status = :status')
            ->setParameter('status', $status)
        ;

        $qb
            ->andWhere('sms.type = :type')
            ->setParameter('type', $type)
        ;

        $qb
            ->andWhere('sms.pending IS NULL or sms.pending = 0')
        ;

        $qb->orderBy("sms.creationDate", "ASC");

        $qb->setMaxResults($limit);

        $query = $qb->getQuery();

        if ($lockMode != null) {
            $query->setLockMode($lockMode);
        }

        return $query->getResult();
    }
}