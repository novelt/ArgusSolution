<?php

/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 7/22/2015
 * Time: 11:29 AM
 */

namespace AppBundle\Repository;

use AppBundle\Entity\SesDashboardSite;
use Doctrine\ORM\QueryBuilder;


class SesDashboardSiteRepository extends BaseRepository
{
    /**
     * @param bool $includeAlertRecipient
     * @return array
     */
    public function getAllSitesArray($includeAlertRecipient = false)
    {
        $qb = $this->createQueryBuilder('s', 's.id');

        $qb->leftJoin('s.reportDataSource', 'ds')
            ->addSelect('ds');

        if ($includeAlertRecipient) {
            $qb->leftJoin('s.alertRecipients', 'ar')
                ->addSelect('ar');
        }

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Return the Root site
     *
     * @param null $dimDateFrom
     * @param null $dimDateTo
     * @param $dimDateTodayId
     * @return mixed
     */
    public function getSiteRoot($dimDateFrom = null, $dimDateTo = null, $dimDateTodayId)
    {
        $qb = $this->createQueryBuilder('s')
            ->innerjoin('s.sitesRelationShip', 'srs')
            ->leftJoin('s.sitesRelationShipChildren', 'srsC')
            ->addSelect('srs')
            ->addSelect('srsC');

        $qb
            ->where('srs.FK_ParentId IS NULL');

        if ($dimDateTo == null) {
            $qb->andWhere('srs.FK_DimDateToId IS NULL')
                ->orWhere('srs.FK_DimDateToId > :dimDateTodayId')
                ->setParameter('dimDateTodayId', $dimDateTodayId);
        }

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function isLeaf($siteId)
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.sitesRelationShipChildren', 'srsC')
            ->select('s')
            ->addSelect('srsC');

        $qb
            ->where('s.id = :siteId')
            ->setParameter('siteId', $siteId);

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Return number of active site, having contacts in the period
     *
     * @param $siteIds
     * @param $dimDateFromId
     * @param $dimDateToId
     * @param $dimDateTodayId
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getWeeklyNumberOfActiveSites($siteIds, $dimDateFromId, $dimDateToId, $dimDateTodayId)
    {
        $qb = $this->createQueryBuilder('s')
            ->innerjoin('s.sitesRelationShip', 'srs')
            ->innerjoin('s.contacts', 'c');


        if ($dimDateToId == null) {
            if ($dimDateFromId == null) {
                $qb->where('srs.FK_WeekDimDateToId IS NULL OR srs.FK_WeekDimDateToId > :dimDateTodayId')
                    ->setParameter('dimDateTodayId', $dimDateTodayId);
            } else {
                $qb->where('srs.FK_WeekDimDateToId IS NULL OR srs.FK_WeekDimDateToId > :dimDateFromId')
                    ->setParameter('dimDateFromId', $dimDateFromId);
            }
        } else {
            if ($dimDateFromId == null) {
                $qb->where('srs.FK_WeekDimDateFromId IS NULL OR srs.FK_WeekDimDateFromId < :dimDateToId')
                    ->setParameter('dimDateToId', $dimDateToId);
            } else {
                $qb->where('srs.FK_WeekDimDateToId IS NULL OR srs.FK_WeekDimDateToId >= :dimDateFromId')
                    ->andWhere('srs.FK_WeekDimDateFromId IS NULL OR srs.FK_WeekDimDateFromId < :dimDateToId')
                    ->setParameter('dimDateFromId', $dimDateFromId)
                    ->setParameter('dimDateToId', $dimDateToId);
            }
        }

        $qb
            ->andWhere('s.id IN (:siteId)')
            ->setParameter('siteId', $siteIds);

        $qb->select('COUNT(srs.id)');

        return $qb
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Return number of active site, having contacts in the period
     *
     * @param $siteIds
     * @param $dimDateFromId
     * @param $dimDateToId
     * @param $dimDateTodayId
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getMonthlyNumberOfActiveSites($siteIds, $dimDateFromId, $dimDateToId, $dimDateTodayId)
    {
        $qb = $this->createQueryBuilder('s')
            ->innerjoin('s.sitesRelationShip', 'srs')
            ->innerjoin('s.contacts', 'c');


        if ($dimDateToId == null) {
            if ($dimDateFromId == null) {
                $qb->where('srs.FK_MonthDimDateToId IS NULL OR srs.FK_MonthDimDateToId > :dimDateTodayId')
                    ->setParameter('dimDateTodayId', $dimDateTodayId);
            } else {
                $qb->where('srs.FK_MonthDimDateToId IS NULL OR srs.FK_MonthDimDateToId > :dimDateFromId')
                    ->setParameter('dimDateFromId', $dimDateFromId);
            }
        } else {
            if ($dimDateFromId == null) {
                $qb->where('srs.FK_MonthDimDateFromId IS NULL OR srs.FK_MonthDimDateFromId < :dimDateToId')
                    ->setParameter('dimDateToId', $dimDateToId);
            } else {
                $qb->where('srs.FK_MonthDimDateToId IS NULL OR srs.FK_MonthDimDateToId >= :dimDateFromId')
                    ->andWhere('srs.FK_MonthDimDateFromId IS NULL OR  srs.FK_MonthDimDateFromId < :dimDateToId')
                    ->setParameter('dimDateFromId', $dimDateFromId)
                    ->setParameter('dimDateToId', $dimDateToId);
            }
        }

        $qb
            ->andWhere('s.id IN (:siteId)')
            ->setParameter('siteId', $siteIds);

        $qb->select('COUNT(srs.id)');

        return $qb
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param $reference
     * @return SesDashboardSite|null
     */
    public function findSiteByReference($reference)
    {
        $qb = $this->getSitesQueryBuilder(null, $reference, null, null);
        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param $reference
     * @return SesDashboardSite[]
     */
    public function findSitesByReference($reference)
    {
        $qb = $this->getSitesQueryBuilder(null, $reference, null, null);
        return $qb->getQuery()->getResult();
    }

    /**
     * @param null $id
     * @param null $reference
     * @param null $reportDataSourceId
     * @param null $reportDataSourceCode
     * @param null $hydrationMode
     * @return SesDashboardSite[]
     */
    public function findSite($id = null, $reference = null, $reportDataSourceId = null, $reportDataSourceCode = null, $hydrationMode = null)
    {
        $qb = $this->getSitesQueryBuilder($id, $reference, $reportDataSourceId, $reportDataSourceCode);

        return $qb->getQuery()->getResult($hydrationMode);
    }

    /**
     * @param null $id
     * @param null $reference
     * @param null $reportDataSourceId
     * @param null $reportDataSourceCode
     * @param null $hydrationMode
     * @return int[]
     */
    public function findSiteId($id = null, $reference = null, $reportDataSourceId = null, $reportDataSourceCode = null, $hydrationMode = null)
    {
        $qb = $this->getSitesQueryBuilder($id, $reference, $reportDataSourceId, $reportDataSourceCode);
        $qb->select('s.id');

        return $qb->getQuery()->getResult($hydrationMode);
    }

    /**
     * @param null $id
     * @param null $reference
     * @param null $reportDataSourceId
     * @param null $reportDataSourceCode
     * @param null $hydrationMode
     * @return SesDashboardSite|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneSite($id = null, $reference = null, $reportDataSourceId = null, $reportDataSourceCode = null, $hydrationMode = null)
    {
        $qb = $this->getSitesQueryBuilder($id, $reference, $reportDataSourceId, $reportDataSourceCode);

        return $qb->getQuery()->getOneOrNullResult($hydrationMode);
    }


    /**
     * @param null $id
     * @param $reference
     * @param null $reportDataSourceId
     * @param null $reportDataSourceCode
     * @return QueryBuilder
     */
    private function getSitesQueryBuilder($id = null, $reference = null, $reportDataSourceId = null, $reportDataSourceCode = null)
    {
        $qb = $this->createQueryBuilder('s')
            ->innerjoin('s.sitesRelationShip', 'srs')
            ->leftJoin('s.sitesRelationShipChildren', 'srsC')
            ->addSelect('srs')
            ->addSelect('srsC');

        $this->addWhere($qb, 's', 'id', $id);
        $this->addWhere($qb, 's', 'reference', $reference);
        $this->addWhere($qb, 's', 'reportDataSourceId', $reportDataSourceId);

        if ($reportDataSourceCode !== null) {
            $qb->innerjoin('s.reportDataSource', 'rds');

            $this->addWhere($qb, 'rds', 'code', $reportDataSourceCode);
        }

        return $qb;
    }

    /**
     * Get Site & children without report data
     *
     * @param $siteId
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getSiteWithoutDependencies($siteId)
    {
        $qb = $this->createQueryBuilder('s')
            ->innerjoin('s.sitesRelationShip', 'srs')
            ->leftJoin('s.sitesRelationShipChildren', 'srsC')
            ->addSelect('srs')
            ->addSelect('srsC');

        $qb
            ->where('s.id = :siteId')
            ->setParameter('siteId', $siteId);

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Return the whole hierarchy of site
     *
     * @param null $dimDateFrom
     * @param null $dimDateTo
     * @param $dimDateTodayId
     * @return array
     */
    public function findAllWithRelations($dimDateFrom = null, $dimDateTo = null, $dimDateTodayId)
    {
        $qb = $this->createQueryBuilder('s')
            ->select('s')
            ->join('s.sitesRelationShip', 'srs')
            ->addSelect('srs')
            ->leftJoin('s.reportDataSource', 'rds')
            ->addSelect('rds');
        //->leftjoin('s.sitesRelationShipChildren', 'srsC')
        //->addSelect('srsC');

        $qb
            ->addOrderBy('srs.level', 'ASC');

        if ($dimDateTo == null) {
            $qb->andWhere('srs.FK_DimDateToId IS NULL')
                ->orWhere('srs.FK_DimDateToId >= :dimDateTodayId')
                ->setParameter('dimDateTodayId', $dimDateTodayId);
        }

        $sites = $qb
            ->getQuery()
            //->setCacheable(true)
            //->setResultCacheLifetime(3600)
            ->getResult();

        $qb = $this->createQueryBuilder('s')
            ->select('s')
            ->join('s.sitesRelationShipChildren', 'srsC')
            ->addSelect('srsC');

        if ($dimDateTo == null) {
            $qb->andWhere('srsC.FK_DimDateToId IS NULL')
                ->orWhere('srsC.FK_DimDateToId > :dimDateTodayId')
                ->setParameter('dimDateTodayId', $dimDateTodayId);
        }

        $qb
            ->getQuery()
            //->setCacheable(true)
            //->setResultCacheLifetime(3600)
            ->getResult();

        return $sites;
    }

    /**
     * Returns recursively all children sites id of the given site(s) id.
     *
     * @param $sessionId
     * @param $logLevelCode
     * @param $siteIds
     * @param $sorted
     * @param $includeAllSites
     * @param null $maxSitesRecursiveLevel
     * @param bool $includeSiteIds
     * @param $dimDateTypeCode
     * @param null $dimDateFromId
     * @param null $dimDateToId
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getChildrenSiteIds($sessionId, $logLevelCode, $siteIds, $sorted, $includeAllSites, $maxSitesRecursiveLevel = null, $includeSiteIds = true, $dimDateTypeCode = null, $dimDateFromId = null, $dimDateToId = null)
    {
        $callStoredProcString = $this->callStoreProcSitesGetChildrenSites($sessionId, $logLevelCode, $dimDateTypeCode, $siteIds, $sorted, $includeAllSites, $maxSitesRecursiveLevel, $dimDateFromId, $dimDateToId, $includeSiteIds);

        $storedProc = $this->_em->getConnection()->prepare($callStoredProcString);
        $storedProc->execute();

        $callStoredProcString = sprintf("select * from tmp_children_sites");
        $storedProc = $this->_em->getConnection()->prepare($callStoredProcString);
        $storedProc->execute();

        $result = $storedProc->fetchAll();

        return $result;
    }

    /**
     * Returns recursively all leaves sites id of the given site(s) id.
     *
     * @param $sessionId
     * @param $logLevelCode
     * @param $siteIds
     * @param $sorted
     * @param $includeAllSites
     * @param null $maxSitesRecursiveLevel
     * @param $dimDateTypeCode
     * @param null $dimDateFromId
     * @param null $dimDateToId
     * @param bool $includeSiteIds
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getLeafSiteIds($sessionId, $logLevelCode, $siteIds, $sorted, $includeAllSites, $maxSitesRecursiveLevel = null, $dimDateTypeCode = null, $dimDateFromId = null, $dimDateToId = null, $includeSiteIds = true)
    {
        $callStoredProcString = $this->callStoreProcSitesGetChildrenSites($sessionId, $logLevelCode, $dimDateTypeCode, $siteIds, $sorted, $includeAllSites, $maxSitesRecursiveLevel, $dimDateFromId, $dimDateToId, $includeSiteIds);

        $storedProc = $this->_em->getConnection()->prepare($callStoredProcString);
        $storedProc->execute();

        $callStoredProcString = sprintf("SELECT * FROM tmp_children_sites WHERE isLeaf = 1");
        $storedProc = $this->_em->getConnection()->prepare($callStoredProcString);
        $storedProc->execute();

        $result = $storedProc->fetchAll();

        return $result;
    }

    /**
     * Create Store Procedure call for usp_sesdashboard_Sites_getChildrenSites
     *
     * @param $sessionId
     * @param $logLevelCode
     * @param $dimDateTypeCode
     * @param $siteIds
     * @param $sorted
     * @param $includeAllSites
     * @param null $maxSitesRecursiveLevel
     * @param null $dimDateFromId
     * @param null $dimDateToId
     * @param bool $includeSiteIds
     * @return string
     */
    private function callStoreProcSitesGetChildrenSites($sessionId, $logLevelCode, $dimDateTypeCode, $siteIds, $sorted, $includeAllSites, $maxSitesRecursiveLevel = null, $dimDateFromId = null, $dimDateToId = null, $includeSiteIds = true)
    {
        return sprintf(
            "CALL usp_sesdashboard_Sites_getChildrenSites(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
            $this->formatStoredProcedureParams($siteIds),
            $this->formatStoredProcedureParams($sorted),
            $this->formatStoredProcedureParams($includeAllSites),
            $this->formatStoredProcedureParams($includeSiteIds),
            $this->formatStoredProcedureParams($maxSitesRecursiveLevel),
            $this->formatStoredProcedureParams($dimDateFromId),
            $this->formatStoredProcedureParams($dimDateToId),
            $this->formatStoredProcedureParams($dimDateTypeCode),
            $this->formatStoredProcedureParams($sessionId),
            $this->formatStoredProcedureParams($logLevelCode)
        );
    }

    /**
     * Returns recursively all parent sites id of the given site(s) id.
     *
     * @param $sessionId
     * @param $logLevelCode
     * @param $siteIds
     * @param $includeAllSites
     * @param null $maxSitesRecursiveLevel
     * @param bool $includeSiteIds
     * @param $dimDateTypeCode
     * @param null $dimDateFromId
     * @param null $dimDateToId
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getParentSiteIds($sessionId, $logLevelCode, $siteIds, $includeAllSites, $maxSitesRecursiveLevel = null, $includeSiteIds = true, $dimDateTypeCode = null, $dimDateFromId = null, $dimDateToId = null)
    {
        $callStoredProcString = $this->callStoreProcSitesGetParentSites($sessionId, $logLevelCode, $dimDateTypeCode, $siteIds, $includeAllSites, $maxSitesRecursiveLevel, $dimDateFromId, $dimDateToId, $includeSiteIds);

        $storedProc = $this->_em->getConnection()->prepare($callStoredProcString);
        $storedProc->execute();

        $callStoredProcString = sprintf("SELECT * FROM tmp_parents_sites");
        $storedProc = $this->_em->getConnection()->prepare($callStoredProcString);
        $storedProc->execute();

        $result = $storedProc->fetchAll();

        return $result;
    }

    /**
     * Create Store Procedure call for usp_sesdashboard_Sites_getParentsSites
     *
     * @param $sessionId
     * @param $logLevelCode
     * @param $dimDateTypeCode
     * @param $siteIds
     * @param $includeAllSites
     * @param null $maxSitesRecursiveLevel
     * @param null $dimDateFromId
     * @param null $dimDateToId
     * @param bool $includeSiteIds
     * @return string
     */
    private function callStoreProcSitesGetParentSites($sessionId, $logLevelCode, $dimDateTypeCode, $siteIds, $includeAllSites, $maxSitesRecursiveLevel = null, $dimDateFromId = null, $dimDateToId = null, $includeSiteIds = true)
    {
        return sprintf(
            "CALL usp_sesdashboard_Sites_getParentsSites(%s, %s, %s, %s, %s, %s, %s, %s, %s)",
            $this->formatStoredProcedureParams($siteIds),
            $this->formatStoredProcedureParams($includeAllSites),
            $this->formatStoredProcedureParams($includeSiteIds),
            $this->formatStoredProcedureParams($maxSitesRecursiveLevel),
            $this->formatStoredProcedureParams($dimDateFromId),
            $this->formatStoredProcedureParams($dimDateToId),
            $this->formatStoredProcedureParams($dimDateTypeCode),
            $this->formatStoredProcedureParams($sessionId),
            $this->formatStoredProcedureParams($logLevelCode)
        );
    }

    /**
     * Return sites having at least one contact
     *
     * @param $siteIds
     * @return array
     */
    public function getNumberOfSitesWithContacts($siteIds)
    {
        $sites = $this->createQueryBuilder('s')
            ->innerjoin('s.contacts', 'c')
            ->andWhere('s.id IN (:siteIds)')
            ->setParameter('siteIds', $siteIds)
            ->groupBy('s.id')
            ->getQuery()
            ->getScalarResult();

        return $sites;
    }

    public function getBrotherSiteIds($siteIds)
    {
        // Get levels of Sites
        $levels = $this->createQueryBuilder('s')
            ->innerjoin('s . sitesRelationShip', 'srs')
            ->select('srs . level')
            ->andWhere('s . id IN(:siteIds)')
            ->setParameter('siteIds', $siteIds)
            ->getQuery()
            ->getScalarResult();

        $qb = $this->createQueryBuilder('s')
            ->innerjoin('s . sitesRelationShip', 'srs')
            ->select('s . id');

        $qb
            ->where('srs . level IN(:levels)')
            ->setParameter('levels', $levels);

        return $qb
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @param $siteId
     * @return array
     */
    public function getSiteLocale($siteId)
    {
        $siteIdParam = $this->formatStoredProcedureParams($siteId);

        $callStoredProcString = sprintf("CALL usp_sesdashboard_Sites_getLocale(%s)", $siteIdParam);
        $storedProc = $this->_em->getConnection()->prepare($callStoredProcString);
        $storedProc->execute();
        $result = $storedProc->fetchAll();

        return $result;
    }

    /**
     * @param $siteId
     * @return array
     */
    public function getSiteTimeZone($siteId)
    {
        $siteIdParam = $this->formatStoredProcedureParams($siteId);

        $callStoredProcString = sprintf("CALL usp_sesdashboard_Sites_getTimeZone(%s)", $siteIdParam);
        $storedProc = $this->_em->getConnection()->prepare($callStoredProcString);
        $storedProc->execute();
        $result = $storedProc->fetchAll();

        return $result;
    }

    /**
     * @param $siteId
     * @return array
     */
    public function getSiteAlertPreferredGateway($siteId)
    {
        $siteIdParam = $this->formatStoredProcedureParams($siteId);

        $callStoredProcString = sprintf("CALL usp_sesdashboard_Sites_getAlertPreferredGateway(%s)", $siteIdParam);
        $storedProc = $this->_em->getConnection()->prepare($callStoredProcString);
        $storedProc->execute();
        $result = $storedProc->fetchAll();

        return $result;
    }

    /**
     * @param $siteId
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getReportDataSourceId($siteId)
    {
        $siteIdParam = $this->formatStoredProcedureParams($siteId);

        $callStoredProcString = sprintf("CALL usp_sesdashboard_Sites_getReportDataSource(%s)", $siteIdParam);
        $storedProc = $this->_em->getConnection()->prepare($callStoredProcString);
        $storedProc->execute();
        $result = $storedProc->fetchAll();

        return $result;
    }

    public function updateSite($id = null, $reportDataSourceId = null)
    {
        $qb = $this->createQueryBuilder('s');
        $qb->update();

        $this->addWhere($qb, 's', 'id', $id);
        $this->addSet($qb, 's', 'reportDataSourceId', $reportDataSourceId);

        return $qb->getQuery()->execute();
    }
}
