<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 7/22/2015
 * Time: 11:29 AM
 */

namespace AppBundle\Repository;

use Doctrine\ORM\QueryBuilder;


class SesDashboardSiteRelationShipRepository extends BaseRepository
{
    /**
     * Return All sites relation ships as array
     *
     * @param null|string $search
     * @return array
     */
    public function getAllSiteRelationShipsArray($search = null)
    {
        $qb = $this->createQueryBuilder('srs', 'srs.id');

        if ($search != null) {
            $qb->where("srs.path LIKE :search")
            ->orWhere("srs.name LIKE :search")
            ->setParameter(":search", "%" . $search . "%");
        }

        $qb->orderBy('srs.level', 'ASC');
        return $qb->getQuery()->getArrayResult();
    }


    public function findSitesRelationWithReports($siteRelationShipIds, $display, $startDate, $endDate, $period)
    {
        $qb = $this->createQueryBuilder(BaseRepository::SITE_RELATION_SHIP_ALIAS)
            ->innerjoin(BaseRepository::SITE_RELATION_SHIP_ALIAS.'.fullReports', BaseRepository::FULL_REPORT_ALIAS)
            ->addSelect(BaseRepository::FULL_REPORT_ALIAS)
            ->leftJoin(BaseRepository::FULL_REPORT_ALIAS.'.partReports', BaseRepository::PART_REPORT_ALIAS)
            ->addSelect(BaseRepository::PART_REPORT_ALIAS)
            ->leftJoin(
                BaseRepository::PART_REPORT_ALIAS.'.reports',
                BaseRepository::REPORT_ALIAS,
                'WITH',
                BaseRepository::REPORT_ALIAS.'.isArchived = :isarchived 
                AND '.BaseRepository::REPORT_ALIAS.'.isDeleted = :isdeleted'
            )
            ->setParameter('isarchived', false)
            ->setParameter('isdeleted', false)
            ->addSelect(BaseRepository::REPORT_ALIAS)
            ->leftJoin(BaseRepository::REPORT_ALIAS.'.reportValues', BaseRepository::REPORT_VALUES_ALIAS)
            ->addSelect(BaseRepository::REPORT_VALUES_ALIAS);

        $this->ApplyFilters($qb, $siteRelationShipIds, null, $display, $startDate, $endDate, $period, null, null);

         return $qb
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * Returns all distinct site levels
     *
     * @param boolean $desc
     * @param bool $includeNullLevel
     * @return array
     */
    public function getLevels($desc = true, $includeNullLevel = false) {
        $query = $this->createQueryBuilder('srs')
            ->select('srs.level')
            ->distinct()
            ->orderBy('srs.level', ($desc ? 'DESC':'ASC'));

        if($includeNullLevel === false) {
            $query->andWhere('srs.level IS NOT NULL');
        }

        $result = $query->getQuery()->getResult();

        $levels = array();
        foreach($result as $res) {
            $levels[] = $res["level"];
        }

        return $levels;
    }

    private function ApplyFilters(QueryBuilder $qb, $siteRelationShipIds, $filterSiteId, $display, $startDate, $endDate, $period, $dimDateFromId, $dimDateToId)
    {
        $qb
            ->where('srs.id IN (:siteRelationShipIds)')
            ->setParameter('siteRelationShipIds', $siteRelationShipIds)
        ;

        /*if ($filterSiteId != null) {
            $qb
                ->andWhere('s2.id = :filterSiteId')
                ->setParameter('filterSiteId', $filterSiteId);
        }*/

        // Status Filters
        $this->whereStatus($qb, $display);

        //  Time filters
        $this->whereTime($qb, $startDate, $endDate);

        // Period Filters
        $this->wherePeriod($qb, $period);

        // Period Filters
        //$this->whereSiteDimDate($qb, $dimDateFromId, $dimDateToId);

        // Order by
        $qb
            ->addOrderBy('fr.startDate','DESC')
            ->addOrderBy('pr.id','DESC')
            ->addOrderBy('r.disease', 'ASC')
        ;
    }
}