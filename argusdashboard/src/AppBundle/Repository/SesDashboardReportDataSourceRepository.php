<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 17/11/2016
 * Time: 17:22
 */

namespace AppBundle\Repository;

use AppBundle\Entity\SesDashboardReportDataSource;
use Doctrine\ORM\QueryBuilder;


class SesDashboardReportDataSourceRepository extends BaseRepository
{
    /**
     * @return SesDashboardReportDataSource[]
     */
    public function findAll()
    {
        return parent::findAll();
    }


    /**
     * @param $code
     * @return SesDashboardReportDataSource[]
     */
    public function findByCode($code) {
        $qb = $this->getReportDataSourceQuery($code);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param null $code
     * @return QueryBuilder
     */
    private function getReportDataSourceQuery($code=null) {
        $qb = $this->createQueryBuilder('d');

        $this->addWhere($qb, 'd', 'code', $code);

        return $qb;
    }
}