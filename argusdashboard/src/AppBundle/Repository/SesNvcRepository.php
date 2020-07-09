<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class SesNvcRepository extends EntityRepository
{
    public function getGlobalKeywords()
    {
        $keys = ['alert', 'android', 'disease', 'month', 'report', 'week', 'year'];
        $keys = array_map(function($key) { return 'global_keyword_' . $key; }, $keys);

        $qb = $this->createQueryBuilder('n', 'n.key')
            ->select('n.key, n.valueString');
        $qb
            ->where('n.key IN (:keys)')
            ->setParameter('keys', $keys);

        return $qb
            ->getQuery()
            ->getResult()
            ;
    }
}
