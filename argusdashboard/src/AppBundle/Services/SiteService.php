<?php

namespace AppBundle\Services;

use AppBundle\Entity\Constant;
use AppBundle\Entity\PermissionSite;
use AppBundle\Entity\Security\SesDashboardUser;
use AppBundle\Entity\SesDashboardIndicatorDimDate;
use AppBundle\Entity\SesDashboardReportDataSource;
use AppBundle\Entity\SesDashboardSite;
use AppBundle\Entity\SesDashboardSiteRelationShip;
use AppBundle\Entity\WebApi\WebApiSite;
use AppBundle\Form\ConfigurationAbstractType;
use AppBundle\Repository\RepositoryInterface;
use AppBundle\Repository\SesDashboardSiteRelationShipRepository;
use AppBundle\Repository\SesDashboardSiteRepository;
use AppBundle\Services\DimDate\IDimDateService;
use AppBundle\Services\IndicatorsCalculation\IndicatorDimDateService;
use AppBundle\Utils\DimDateHelper;
use AppBundle\Utils\Epidemiologic;
use AppBundle\Utils\SesDashboardPermissionHelper;
use AppBundle\Entity\SesDashboardSiteAlertRecipient;
use AppBundle\Entity\Import\Sites;
use Symfony\Component\Translation\TranslatorInterface;

use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Form\FormError;

class SiteService extends BaseRepositoryService
{
    /** @var EntityManager $em */
    private $em;
    /**
     * @var SesDashboardSiteRepository
     */
    private $siteRepository;

    /**
     * @var SesDashboardSiteRelationShipRepository
     */
    private $siteRelationShipRepository;

    /**
     * @var LocaleService
     */
    private $localeService;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /** @var IndicatorDimDateService */
    private $dimDateService;

    /** @var IDimDateService */
    private $siteDimDateService;

    private $epiFirstDay;

    /**
     * @var string
     */
    private $getChildrenSitesLogLevel;

    /**
     * @var ReportDataSourceService
     */
    private $reportDataSourceService;

    /**
     * SiteService constructor.
     * @param Logger $logger
     * @param EntityManager $em
     * @param LocaleService $localeService
     * @param TranslatorInterface $translator
     * @param IndicatorDimDateService $dimDateService
     * @param IDimDateService $siteDimDateService
     * @param ReportDataSourceService $reportDataSourceService
     * @param $epiFirstDay
     * @param $getChildrenSitesLogLevel
     */
    public function __construct(Logger $logger,
                                EntityManager $em,
                                LocaleService $localeService,
                                TranslatorInterface $translator,
                                IndicatorDimDateService $dimDateService,
                                IDimDateService $siteDimDateService,
                                ReportDataSourceService $reportDataSourceService,
                                $epiFirstDay,
                                $getChildrenSitesLogLevel)
    {
        parent::__construct($logger);
        $this->em = $em;
        $this->siteRepository = $this->em->getRepository('AppBundle:SesDashboardSite');
        $this->siteRelationShipRepository = $this->em->getRepository('AppBundle:SesDashboardSiteRelationShip');
        $this->localeService = $localeService;
        $this->translator = $translator;
        $this->dimDateService = $dimDateService;
        $this->epiFirstDay = $epiFirstDay;
        $this->siteDimDateService = $siteDimDateService;
        $this->getChildrenSitesLogLevel = $getChildrenSitesLogLevel;
        $this->reportDataSourceService = $reportDataSourceService;
    }

    /**
     * @param $id
     * @return SesDashboardSite
     */
    public function findOneById($id)
    {
        return parent::findOneById($id);
    }

    /**
     * Return sites root
     *
     * @return SesDashboardSite
     */
    public function getSiteRoot()
    {
        return $this->siteRepository->getSiteRoot(null, null, DimDateHelper::getDimDateTodayId());
    }

    /**
     * Get all sites
     *
     * @return array
     */
    public function getAll()
    {
        return $this->siteRepository->findAll();
    }

    /**
     * Get site by id
     *
     * @param integer $id
     * @return null|SesDashboardSite
     */
    public function getById($id)
    {
        return $this->siteRepository->find($id);
    }

    /**
     * @param $reference
     * @return SesDashboardSite|null
     */
    public function findSiteByReference($reference)
    {
        return $this->siteRepository->findSiteByReference($reference);
    }

    /**
     * @param $reference
     * @return SesDashboardSite[]
     */
    public function findSitesByReference($reference)
    {
        return $this->siteRepository->findSitesByReference($reference);
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
        return $this->siteRepository->findSite($id, $reference, $reportDataSourceId, $reportDataSourceCode, $hydrationMode);
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
        return $this->siteRepository->findSiteId($id, $reference, $reportDataSourceId, $reportDataSourceCode, $hydrationMode);
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
        return $this->siteRepository->findOneSite($id, $reference, $reportDataSourceId, $reportDataSourceCode, $hydrationMode);
    }

    /**
     * Get site matching with id without its dependencies
     *
     * @param integer $id
     * @return SesDashboardSite|null
     */
    public function getSiteWithoutDependencies($id)
    {
        return $this->siteRepository->getSiteWithoutDependencies($id);
    }

    /**
     * Return the distinct parents of the sites given in parameter in function of the given target level.
     * If the given sites already have the given target level (or lower), they will be returned back.
     *
     * @param SesDashboardSite[] $sites
     * @param $targetLevel
     * @return array
     */
    public function getParentSites(array $sites, $targetLevel)
    {
        $finalSitesList = []; //final list that wil be returned
        $finalSiteIds = [];//used to prevent duplicate sites in $finalSitesList

        foreach ($sites as $site) {
            //this case will appear when we will start to aggregate data: we get the sites' parents
            if ($site->getLevel() > $targetLevel) {
                //get the site's parent at the corresponding level. The more we aggregate, the higher the level --> site's parent's parent's parent's....
                $parentSite = $this->getParentSite($site, $targetLevel);
                if ($parentSite != null) {
                    //at the end, the sites have a common parent: prevent duplicated sites
                    if (($key = array_search($parentSite->getId(), $finalSitesList)) === false) {
                        $finalSitesList[] = $parentSite;
                        $finalSiteIds[] = $parentSite->getId();
                    }
                }
            } else {
                if (($key = array_search($site->getId(), $finalSiteIds)) === false) {
                    $finalSitesList[] = $site;
                    $finalSiteIds[] = $site->getId();
                }
            }
        }

        return $finalSitesList;
    }

    /**
     * @param SesDashboardSite|null $site
     * @param bool $defaultLocaleIfNull
     * @return null|string
     */
    public function getSiteLocale(SesDashboardSite $site = null, $defaultLocaleIfNull = true)
    {
        if ($site === null) {
            return null;
        }

        $result = $this->siteRepository->getSiteLocale($site->getId());

        $siteLocale = null;

        if (is_array($result) && sizeof($result) == 1) {
            $row = $result[0];

            if (is_array($row) && sizeof($row) == 1) {
                $siteLocale = array_values($row)[0];
            }
        }

        if ($siteLocale === null && $defaultLocaleIfNull) {
            return $this->localeService->getDefaultLocale();
        }

        return $siteLocale;
    }

    /**
     * @param SesDashboardSite $site
     * @return array|null
     */
    public function getSiteTimeZone(SesDashboardSite $site)
    {
        if ($site === null) {
            return null;
        }

        $result = $this->siteRepository->getSiteTimeZone($site->getId());

        if (is_array($result) && sizeof($result) == 1) {
            $row = $result[0];

            if (is_array($row) && sizeof($row) == 1) {
                return array_values($row)[0];
            }
        }

        return null;
    }

    /**
     * Return the Alert Preferred Gateway of the site or recursively
     *
     * @param SesDashboardSite $site
     * @return null
     */
    public function getSiteAlertPreferredGateway(SesDashboardSite $site)
    {
        if ($site === null) {
            return null;
        }

        $result = $this->siteRepository->getSiteAlertPreferredGateway($site->getId());

        if (is_array($result) && sizeof($result) == 1) {
            $row = $result[0];

            if (is_array($row) && sizeof($row) == 1) {
                return array_values($row)[0];
            }
        }

        return null;
    }

    /**
     * @param SesDashboardSite $site
     * @return SesDashboardReportDataSource|null
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getReportDataSource(SesDashboardSite $site)
    {
        $reportDataSourceId = $this->getReportDataSourceId($site);

        if ($reportDataSourceId !== null) {
            return $this->reportDataSourceService->findOneById($reportDataSourceId);
        }
    }

    /**
     * Return the inherited report data source ID
     * @param SesDashboardSite $site
     * @return null
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getReportDataSourceId(SesDashboardSite $site)
    {
        if ($site === null) {
            return null;
        }

        $result = $this->siteRepository->getReportDataSourceId($site->getId());

        if (is_array($result) && sizeof($result) == 1) {
            $row = $result[0];

            if (is_array($row) && sizeof($row) == 1) {
                return array_values($row)[0];
            }
        }

        return null;
    }

    /**
     * Return the list of Alert recipient Sites
     *
     * @param SesDashboardSite $site
     * @return array
     */
    public function getAlertRecipientSites(SesDashboardSite $site)
    {
        $result = [];

        if ($site->isCascadingAlert()) {
            // return all recursively parent sites
            $parent = $site->getParent();

            while ($parent != null) {
                $result[] = $parent;
                $parent = $parent->getParent();
            }
        } else {
            //Get the alert recipients list for this site
            $alertRecipientsSites = $site->getAlertRecipientSites();

            /** @var SesDashboardSiteAlertRecipient $alertRecipientsSite */
            foreach ($alertRecipientsSites as $alertRecipientsSite) {
                /** @var  $alertSite */
                $alertSite = $alertRecipientsSite->getRecipientSite();
                $result[] = $alertSite;
            }
        }

        return $result;
    }

    /**
     * Return the given site's parent until the given target site level is reached.
     * If the site's level is lower than the given target level, the given site is returned.
     *
     * @param SesDashboardSite $site
     * @param $targetLevel
     * @return SesDashboardSite|null
     */
    private function getParentSite(SesDashboardSite $site, $targetLevel)
    {
        if ($site->getLevel() <= $targetLevel) {
            return $site;
        }

        $currentSite = $site;
        $parentSite = null;
        $stop = false;

        while (!$stop) {
            $parentSite = $currentSite->getParent();

            //The parent may be null if the current site has no parent. The target level may not be reached for this case
            if ($parentSite === null || $parentSite->getLevel() === $targetLevel) {
                $stop = true;
            } else {
                $currentSite = $parentSite;
            }
        }

        return $parentSite;
    }

    /**
     * Returns recursively all children sites id of the given site(s) id.
     *
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
    public function getChildrenSiteIds($siteIds, $sorted, $includeAllSites, $maxSitesRecursiveLevel = null, $includeSiteIds = true, $dimDateTypeCode = null, $dimDateFromId = null, $dimDateToId = null)
    {
        $childrenSiteIds = [];
        $sessionId = date("U");//must be a integer

        $results = $this->siteRepository->getChildrenSiteIds($sessionId, $this->getChildrenSitesLogLevel, $siteIds, $sorted, $includeAllSites, $maxSitesRecursiveLevel, $includeSiteIds, $dimDateTypeCode, $dimDateFromId, $dimDateToId);
        
        foreach ($results as $row) {
            if (!in_array($row['siteId'], $childrenSiteIds)) { // avoid duplicate ids
                $childrenSiteIds[] = $row['siteId'];
            }
        }

        return $childrenSiteIds;
    }

    /**
     * Returns recursively all leaves sites id of the given site(s) id.
     *
     * @param $siteIds
     * @param $sorted
     * @param $includeAllSites
     * @param null $maxSitesRecursiveLevel
     * @param null $dimDateTypeCode
     * @param null $dimDateFromId
     * @param null $dimDateToId
     * @param bool $includeSiteIds
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getLeafSiteIds($siteIds, $sorted, $includeAllSites, $maxSitesRecursiveLevel = null, $dimDateTypeCode = null, $dimDateFromId = null, $dimDateToId = null, $includeSiteIds = true)
    {
        $leafSiteIds = [];
        $sessionId = date("U");//must be a integer

        $results = $this->siteRepository->getLeafSiteIds($sessionId, 'INFO', $siteIds, $sorted, $includeAllSites, $maxSitesRecursiveLevel, $dimDateTypeCode, $dimDateFromId, $dimDateToId, $includeSiteIds);

        foreach ($results as $row) {
            if (!in_array($row['siteId'], $leafSiteIds)) { // avoid duplicate ids
                $leafSiteIds[] = $row['siteId'];
            }
        }

        return $leafSiteIds;
    }

    /**
     * Return number of sites having at least one contact
     *
     * @param $siteIds
     * @return int
     */
    public function getNumberOfSitesWithContacts($siteIds)
    {
        $results = $this->siteRepository->getNumberOfSitesWithContacts($siteIds);
        return count($results);
    }

    /**
     * Return $siteIds' brothers sites ids
     *
     * @param $siteIds
     * @return array
     */
    public function getBrotherSiteIds($siteIds)
    {
        $results = [];
        $ids = $this->siteRepository->getBrotherSiteIds($siteIds);

        foreach ($ids as $id) {
            $results[] = $id['id'];
        }

        return $results;
    }

    /**
     * Returns recursively all parent sites id of the given site(s) id.
     *
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
    public function getParentSiteIds($siteIds, $includeAllSites, $maxSitesRecursiveLevel = null, $includeSiteIds = true, $dimDateTypeCode = null, $dimDateFromId = null, $dimDateToId = null)
    {
        $parentSiteIds = [];
        $sessionId = date("U");//must be a integer

        $results = $this->siteRepository->getParentSiteIds($sessionId, 'INFO', $siteIds, $includeAllSites, $maxSitesRecursiveLevel, $includeSiteIds, $dimDateTypeCode, $dimDateFromId, $dimDateToId);

        foreach ($results as $row) {
            $parentSiteIds[] = $row['siteId'];
        }

        return $parentSiteIds;
    }

    /**
     * Retrieve all children and parent siteIds
     * @param $siteIds
     * @param $includeAllSites
     * @param bool $includeSiteIds
     * @param null $dimDateTypeCode
     * @param null $dimDateFromId
     * @param null $dimDateToId
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getChildrenAndParentSiteIds($siteIds, $includeAllSites, $includeSiteIds = true, $dimDateTypeCode = null, $dimDateFromId = null, $dimDateToId = null)
    {
        //Never include the siteIds, even if $includeSiteIds is true: because we will have duplicates
        $parentSiteIds = $this->getParentSiteIds($siteIds, $includeAllSites, null, false, $dimDateTypeCode, $dimDateFromId, $dimDateToId);
        $childrenSiteIds = $this->getChildrenSiteIds($siteIds, false, $includeAllSites, null, false, $dimDateTypeCode, $dimDateFromId, $dimDateToId);

        $resultSiteIds = array_merge($parentSiteIds, $childrenSiteIds);

        //only at this step take $includeSiteIds into account. If true -> manually append $siteIds in the result
        if ($includeSiteIds && $siteIds !== null) {
            if (is_array($siteIds)) {
                $resultSiteIds = array_merge($resultSiteIds, $siteIds);
            } else {
                $resultSiteIds[] = $siteIds;
            }
        }

        return $resultSiteIds;
    }

    /**
     * @param $siteIds
     * @param $includeAllSites
     * @param bool $includeSiteIds
     * @param null $dimDateTypeCode
     * @param null $dimDateFromId
     * @param null $dimDateToId
     * @return SesDashboardSite[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getChildrenAndParentSites($siteIds, $includeAllSites, $includeSiteIds = true, $dimDateTypeCode = null, $dimDateFromId = null, $dimDateToId = null)
    {
        $siteIds = $this->getChildrenAndParentSiteIds($siteIds, $includeAllSites, $includeSiteIds, $dimDateTypeCode, $dimDateFromId, $dimDateToId);
        return $this->findSite($siteIds, null, null, null, null);
    }

    /**
     * @param mixed $id
     * @param null $lockMode
     * @param null $lockVersion
     * @return SesDashboardSite
     */
    public function find($id, $lockMode = null, $lockVersion = null)
    {
        return parent::find($id, $lockMode, $lockVersion);
    }

    /**
     * @param $params
     * @return null|SesDashboardSite
     */
    public function findOneBy(array $params)
    {
        return $this->siteRepository->findOneBy($params);
    }

    /**
     * Find sites by criterias
     *
     * @param array $criteria
     * @param array (optional) $orderBy
     * @param integer (optional) $limit
     * @param integer (optional) $offset
     * @return array
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->siteRepository->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Return sites with Relations & Child Relations order by level ASC
     *
     * @param null $dateFrom
     * @param null $dateTo
     * @param $dimDateTodayId
     * @return array
     */
    public function findAllWithRelations($dateFrom = null, $dateTo = null, $dimDateTodayId)
    {
        return $this->siteRepository->findAllWithRelations($dateFrom, $dateTo, $dimDateTodayId);
    }


    private function constructHierarchy($arrayAllSitesById, $children)
    {
        $result = [];

        // Find all children of this site
        /** @var SesDashboardSiteRelationShip $siteRelationShip */
        foreach ($children as $siteRelationShip) {
            $FKSiteId = $siteRelationShip->getFKSiteId();
            /** @var SesDashboardSite $childSite */
            if (key_exists($FKSiteId, $arrayAllSitesById)) {
                $childSite = $arrayAllSitesById[$FKSiteId];

                $result[$FKSiteId]["site"] = $childSite;
                $result[$FKSiteId]["siteRelationShip"] = $childSite->getSitesRelationShip();
                $result[$FKSiteId]["children"] = $this->constructHierarchy($arrayAllSitesById, $childSite->getSitesRelationShipChildren());
            } else {
                $this->logger->addError('ChildSite is null with Id:' . $FKSiteId);
            }
        }

        return $result;
    }

    /**
     * Return list of choices without RootSite
     *
     * @return array
     */
    public function getSiteListChoices()
    {
        $siteArray = [];
        $sites = self::getAll();

        /** @var SesDashboardSite $site */
        foreach ($sites as $site) {
            if ($site->getParent() != null) {
                $siteArray[$site->getId()] = $site->getPath();
            }
        }

        return $siteArray;
    }

    /**
     * Get a query object that can be used to get a list of sites
     *
     * @return \Doctrine\ORM\Query
     */
    public function getSiteListQuery()
    {
        $qb = $this->siteRepository->createQueryBuilder('s');
        return $qb->getQuery();
    }

    /**
     * Generate TreeView regarding user permissions
     *
     * @param $siteArray
     * @param $permissions
     * @param SesDashboardPermissionHelper $permissionHelper
     * @param $homeSite
     * @return array
     */
    public function getTreeView($siteArray, $permissions, $permissionHelper, $homeSite)
    {
        $site = $siteArray["site"];
        $relation = $this->getActiveOrMostRecentRelationShipArray($siteArray['siteRelationShip']);
        $children = $siteArray["children"];

        // PermissionSite instance
        $ws = new WebApiSite();
        $ws->id = $site['id'];
        $ws->path = $relation['path'];
        $ws->level = $relation['level'];

        $me = [
            'text' => $relation['name'],
            //'selectable' => $site->IsSelectable($homeSite),
            'selectable' => $permissionHelper
                ->isTreeViewNodeSelectable(
                    $ws,
                    $homeSite,
                    $permissions
                ),
            'reportable' => $permissionHelper
                ->isTreeViewNodeReportable(
                    $ws,
                    $homeSite,
                    $permissions
                ),
            'href' => $site['id'],
            'icon' => $this->getHomeIcon($ws, $homeSite),
            'home' => $this->isHome($ws, $homeSite),
            'backColor' => $this->getBackColor($ws, $homeSite),
            'state' => [
                'selected' => $this->isHome($ws, $homeSite),
                //'disabled' => $site->IsDisabled(),
                //'disabled' => SesDashboardPermissionHelper::isTreeViewNodeSelectable($site, $homeSite, $permissions),
                'expanded' => $this->IsInPath($ws, $homeSite)
            ],
            'level' => (count($children) == 0 ? 'HF' : ''),
            'export' => $permissionHelper
                ->isTreeViewNodeExportable(
                    $ws,
                    $homeSite,
                    $permissions
                ),
        ];

        if (count($children) != 0) {
            $me['nodes'] = $this->GetTreeViewChildren(
                $siteArray["children"],
                $siteArray['siteRelationShip'],
                $permissions,
                $permissionHelper,
                $homeSite
            );
        }

        return $me;
    }

    /**
     * Recursive TreeView children
     *
     * @param $children
     * @param $relations
     * @param $permissions
     * @param $permissionHelper
     * @param $homeSite
     * @return array
     */
    private function GetTreeViewChildren($children, $relations, $permissions, $permissionHelper, $homeSite)
    {
        $tree = [];

        foreach ($children as $key => $child) {
            $relation = $this->getActiveOrMostRecentRelationShipArray($relations);
            if (!$this->isRelationShipWeeklyDeleted($relation)) {
                $tree[] = $this->getTreeView($child, $permissions, $permissionHelper, $homeSite);
            }
        }

        return $tree;
    }

    public function getActiveOrMostRecentRelationShipArray($relations)
    {
        usort($relations, function ($a, $b) {
            if ($a['FK_DimDateToId'] == null) {
                return -1;
            }
            if ($b['FK_DimDateToId'] == null) {
                return 1;
            }
            return ($a['FK_DimDateToId'] > $b['FK_DimDateToId'] ? -1 : 1);
        });

        $relationShip = reset($relations);

        return $relationShip;
    }

    public function isRelationShipDailyDeleted($relation)
    {
        return $this->isRelationShipDeleted($relation, 'FK_DimDateToId', DimDateHelper::getDimDateTodayId());
    }

    public function isRelationShipWeeklyDeleted($relation)
    {
        $todayNextWeek = new \DateTime();
        $todayNextWeek->add(new \DateInterval('P7D')); // Add 7 days
        $epi = Epidemiologic::Timestamp2Epi($todayNextWeek->getTimestamp(), $this->epiFirstDay);
        $firstDayOfNextWeek = Epidemiologic::GetFirstDayOfWeek($epi['Week'], $epi['Year'], $this->epiFirstDay);

        $firstDayOfNextWeekDate = new \DateTime();
        $firstDayOfNextWeekDate->setTimestamp($firstDayOfNextWeek);

        return $this->isRelationShipDeleted($relation, 'FK_WeekDimDateToId', DimDateHelper::getDimDateIdFromDateTime($firstDayOfNextWeekDate));
    }

    private function isRelationShipDeleted($relation, $property, $dimDateId)
    {
        return ($relation[$property] != null && $relation[$property] <= $dimDateId);
    }

    /**
     * Return true if site is == home Site
     *
     * @param PermissionSite $site
     * @param SesDashboardSite $homeSite
     * @return bool
     */
    private function isHome(PermissionSite $site, SesDashboardSite $homeSite)
    {
        if ($homeSite == null) {
            return false;
        }

        if ($site->getId() == $homeSite->getId()) {
            return true;
        }

        return false;
    }

    /**
     * Return the home icon
     *
     * @param PermissionSite $site
     * @param SesDashboardSite $homeSite
     * @return string
     */
    private function getHomeIcon(PermissionSite $site, SesDashboardSite $homeSite)
    {
        if ($this->isHome($site, $homeSite)) {
            return "fa fa-home";
        }

        return "";
    }

    /**
     * @param PermissionSite $site
     * @param SesDashboardSite $homeSite
     * @return string
     */
    private function getBackColor(PermissionSite $site, SesDashboardSite $homeSite)
    {
        if ($this->isHome($site, $homeSite)) {
            return "lightblue";
        }

        return "";
    }

    /**
     * Return true if home path is in current site path
     *
     * @param PermissionSite $site
     * @param SesDashboardSite $homeSite
     * @return bool
     */
    public function IsInPath(PermissionSite $site, SesDashboardSite $homeSite)
    {
        if ($homeSite == null) {
            return false;
        }

        if (strpos($homeSite->getPath(), $site->getPath()) !== false) {
            return true;
        }

        return false;
    }

    /**
     * Get Active Children relation Ships
     *
     * @param SesDashboardSite $site
     * @param $dimDateFromId
     * @param $dimDateToId
     * @return array
     */
    public function getActiveChildrenRelationShip(SesDashboardSite $site, $dimDateFromId, $dimDateToId)
    {
        $children = [];

        /** @var SesDashboardSiteRelationShip $childRelationShip */
        foreach ($site->getSitesRelationShipChildren() as $childRelationShip) {
            if ($childRelationShip->isActiveBetween($dimDateFromId, $dimDateToId)) {
                $children[] = $childRelationShip;
            }
        }

        return $children;
    }

    /**
     * Get Active Children relation Ships
     *
     * @param SesDashboardSite $site
     * @param $dimDateFromId
     * @param $dimDateToId
     * @return array
     */
    public function getActiveChildrenRelationShipWeekly(SesDashboardSite $site, $dimDateFromId, $dimDateToId)
    {
        $children = [];

        /** @var SesDashboardSiteRelationShip $childRelationShip */
        foreach ($site->getSitesRelationShipChildren() as $childRelationShip) {
            if ($childRelationShip->isWeeklyActiveBetween($dimDateFromId, $dimDateToId)) {
                $children[] = $childRelationShip;
            }
        }

        return $children;
    }

    /**
     * Get Active Children relation Ships
     *
     * @param SesDashboardSite $site
     * @param $dimDateFromId
     * @param $dimDateToId
     * @return array
     */
    public function getActiveChildrenRelationShipMonthly(SesDashboardSite $site, $dimDateFromId, $dimDateToId)
    {
        $children = [];

        /** @var SesDashboardSiteRelationShip $childRelationShip */
        foreach ($site->getSitesRelationShipChildren() as $childRelationShip) {
            if ($childRelationShip->isMonthlyActiveBetween($dimDateFromId, $dimDateToId)) {
                $children[] = $childRelationShip;
            }
        }

        return $children;
    }

    /**
     * Get Active relation Ships
     *
     * @param SesDashboardSite $site
     * @param $dimDateFromId
     * @param $dimDateToId
     * @return array
     */
    public function getActiveRelationShip(SesDashboardSite $site, $dimDateFromId, $dimDateToId)
    {
        $siteRelation = [];

        /** @var SesDashboardSiteRelationShip $relationShip */
        foreach ($site->getSitesRelationShip() as $relationShip) {
            if ($relationShip->isActiveBetween($dimDateFromId, $dimDateToId)) {
                $siteRelation[] = $relationShip;
            }
        }

        return $siteRelation;
    }

    /**
     * Get Active relation Ships
     *
     * @param SesDashboardSite $site
     * @param $dimDateFromId
     * @param $dimDateToId
     * @return array
     */
    public function getActiveRelationShipWeekly(SesDashboardSite $site, $dimDateFromId, $dimDateToId)
    {
        $siteRelation = [];

        /** @var SesDashboardSiteRelationShip $relationShip */
        foreach ($site->getSitesRelationShip() as $relationShip) {
            if ($relationShip->isWeeklyActiveBetween($dimDateFromId, $dimDateToId)) {
                $siteRelation[] = $relationShip;
            }
        }

        return $siteRelation;
    }

    /**
     * Get Active relation Ships
     *
     * @param SesDashboardSite $site
     * @param $dimDateFromId
     * @param $dimDateToId
     * @return array
     */
    public function getActiveRelationShipMonthly(SesDashboardSite $site, $dimDateFromId, $dimDateToId)
    {
        $siteRelation = [];

        /** @var SesDashboardSiteRelationShip $relationShip */
        foreach ($site->getSitesRelationShip() as $relationShip) {
            if ($relationShip->isMonthlyActiveBetween($dimDateFromId, $dimDateToId)) {
                $siteRelation[] = $relationShip;
            }
        }

        return $siteRelation;
    }

    /**
     * Return number of active sites during period
     *
     * @param $sitesIds
     * @param $dimDateFromId
     * @param $dimDateToId
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getWeeklyActiveSiteRelations($sitesIds, $dimDateFromId, $dimDateToId)
    {
        return $this->siteRepository->getWeeklyNumberOfActiveSites($sitesIds, $dimDateFromId, $dimDateToId, DimDateHelper::getDimDateTodayId());
    }

    /**
     * Return number of active sites during period
     *
     * @param $sitesIds
     * @param $dimDateFromId
     * @param $dimDateToId
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getMonthlyActiveSiteRelations($sitesIds, $dimDateFromId, $dimDateToId)
    {
        return $this->siteRepository->getMonthlyNumberOfActiveSites($sitesIds, $dimDateFromId, $dimDateToId, DimDateHelper::getDimDateTodayId());
    }

    /**
     * Get Active relation Ship
     *
     * @param SesDashboardSite $site
     * @param $period
     * @param \DateTime $startDate
     * @return mixed
     */
    public function getActiveRelationShipPeriod(SesDashboardSite $site, $period, \DateTime $startDate)
    {
        $format = 'Y-m-d';
        $formatLastDayOfMonth = 'Y-m-t';

        if ($period == Constant::PERIOD_WEEKLY) {
            // Last day of Week
            $endDate = clone $startDate;
            $endDate->modify('+ 6 days');
        } else { // MONTHLY
            // Last Day of Month - t return the number of day in month
            $lastDay = date($formatLastDayOfMonth, strtotime($startDate->format($format)));
            $endDate = date_create($lastDay);
        }

        $dimDateFromId = DimDateHelper::getDimDateIdFromDateTime($startDate);
        $dimDateToId = DimDateHelper::getDimDateIdFromDateTime($endDate);

        if ($period == Constant::PERIOD_WEEKLY) {
            $activeRelationShips = $this->getActiveRelationShipWeekly($site, $dimDateFromId, $dimDateToId);
        } else { // MONTHLY
            $activeRelationShips = $this->getActiveRelationShipMonthly($site, $dimDateFromId, $dimDateToId);
        }

        // Return last relation
        if (count($activeRelationShips) > 0) {
            return $activeRelationShips[count($activeRelationShips) - 1];
        } else {
            return null;
        }
    }

    /**
     * Remove All Sites
     */
    public function removeAll()
    {
        foreach ($this->getAll() as $entity) {
            $this->em->remove($entity);
        }
        $this->em->flush();
    }

    /**
     * Get Array of Site rows for CSV Export
     *
     * @return array
     */
    public function getSiteForCsvExport()
    {
        $result = [];
        $sites = self::getAll();

        $result[] = SesDashboardSite::getHeaderCsvRow();

        foreach ($sites as $site) {
            $result[] = $site->getCsvRow();
        }

        return $result;
    }


    /**
     * @param $fullPath
     * @return array|mixed
     */
    public function getPathAsArray($fullPath)
    {
        $arrayExploded = explode("|", $fullPath);

        //if the path starts with '|'
        if (0 === strpos($fullPath, '|')) {
            //remove the first element of the array, as it is empty
            array_shift($arrayExploded);
        }

        return $arrayExploded;
    }

    /**
     * Get count of enabled/disabled sites children
     **
     * @param $children
     * @return array
     */
    public function getSubSitesStatus($children)
    {
        $disabledChildren = 0;
        $enabledChildren = 0;

        if (count($children) != 0) {
            foreach ($children as $key => $child) {
                $relationChild = $this->getActiveOrMostRecentRelationShipArray($child['siteRelationShip']);
                if ($this->isRelationShipDailyDeleted($relationChild)) {
                    ++$disabledChildren;
                } else {
                    ++$enabledChildren;
                }
            }

        }

        return [
            "enabledChildren" => $enabledChildren,
            "disabledChildren" => $disabledChildren,
        ];
    }


    /**
     * Get all distint site's levels
     *
     * @param bool (optional) $desc
     * @param bool $includeNullLevel
     * @return array
     */
    public function getLevels($desc = true, $includeNullLevel = false)
    {
        return $this->siteRelationShipRepository->getLevels($desc, $includeNullLevel);
    }

    /**
     * Check if site id is a leaf site
     *
     * @param integer $siteId
     * @return bool
     */
    public function isLeaf($siteId)
    {
        /** @var SesDashboardSite $res */
        $res = $this->siteRepository->isLeaf($siteId);
        return ($res === null || sizeof($res->getSitesRelationShipChildren()) == 0);
    }


    /**
     * Import Sites from Xml file
     *
     * @param Sites $sites
     * @param $weeklyTimelinessMinutes
     * @param $monthlyTimelinessMinutes
     *
     * @return array
     */
    public function importSites(Sites $sites, $weeklyTimelinessMinutes, $monthlyTimelinessMinutes)
    {
        // Error list to display
        $errors = [];

        //Create Epi Time
        $ts = (new \DateTime())->getTimestamp();
        $epiTime = Epidemiologic::Timestamp2Epi($ts, $this->epiFirstDay);

        $sitesFromDataBase = $this->siteRepository->findAllWithRelations(null, null, DimDateHelper::getDimDateTodayId());
        $sitesReferences = [];
        $parentSiteReferences = [];
        $sitesToLoad = [];
        $this->createSitesWithReference($sitesFromDataBase, $sitesReferences, $parentSiteReferences);

        // Alert Recipients
        $alertRecipientReferences = [];

        // First, create and persist the root site if not exists
        if (!array_key_exists(SesDashboardSite::ROOT_REFERENCE, $sitesReferences)) {
            $rootEntity = $this->createRootSite();
            $sitesReferences[SesDashboardSite::ROOT_REFERENCE] = $rootEntity;
            $this->persist($rootEntity);
        }

        // Persist all new sites, and compare if sites are already in db
        /** @var SesDashboardSite $site */
        foreach ($sites->getDashboardSites() as $site) {
            /** @var SesDashboardSite $currentSite */
            $currentSite = null;
            $newParentReference = $site->getParentReferenceForImport();
            $newName = $site->getNameForImport();

            // Check if Site has a parent Reference
            if ($newParentReference == null || $newParentReference == '') {
                $errors[] = new FormError(sprintf("Site with reference [%s] has no parent Reference", $site->getReference()));
                break;
            }

            // Check if Site already exist or not (check on reference)
            if (!array_key_exists($site->getReference(), $sitesReferences)) {
                // Create a new Site
                $currentSite = $site;
            } else {
                $currentSite = $sitesReferences[$site->getReference()];

                // update data
                $currentSite->setParentSiteReferenceForSerialization($newParentReference);
                $currentSite->setNameForSerialization($newName);
                $currentSite->setWeeklyReminderOverrunMinutes($site->getWeeklyReminderOverrunMinutes());
                $currentSite->setMonthlyReminderOverrunMinutes($site->getMonthlyReminderOverrunMinutes());
                $currentSite->setWeeklyTimelinessMinutes($site->getWeeklyTimelinessMinutes());
                $currentSite->setMonthlyTimelinessMinutes($site->getMonthlyTimelinessMinutes());
                $currentSite->setCascadingAlert($site->isCascadingAlert());
            }

            // Check if there is a change in the hierarchy which is not allowed for the moment
            if ($newParentReference != null && $currentSite->getParent() != null && ($newParentReference != $currentSite->getParent()->getReference())) {
                $errors[] = new FormError(sprintf("Site with reference [%s] has a new parent Reference [%s]", $currentSite->getReference(), $newParentReference));
                break;
            }

            # Take default values in the config file if values are not defined in the import Xml file
            if ($currentSite->getWeeklyTimelinessMinutes() == null) {
                $currentSite->setWeeklyTimelinessMinutes($weeklyTimelinessMinutes);
            }

            if ($currentSite->getMonthlyTimelinessMinutes() == null) {
                $currentSite->setMonthlyTimelinessMinutes($monthlyTimelinessMinutes);
            }

            // Remove actual alert References
            /** @var SesDashboardSiteAlertRecipient $recipientSite */
            if ($currentSite->getAlertRecipientSites() != null) {
                foreach ($currentSite->getAlertRecipientSites() as $recipientSite) {
                    $this->remove($recipientSite);
                }
            }

            // Alert Recipients from Xml file
            $ormSiteAlertRecipients = $site->getAlertRecipientSites();

            if ($ormSiteAlertRecipients != null) {
                $currentSite->setAlertRecipientSites(null);
                $alertRecipientReferences[$currentSite->getReference()] = $ormSiteAlertRecipients;
            }

            // Update our references tables
            $sitesReferences[$currentSite->getReference()] = $currentSite;
            $sitesToLoad[$currentSite->getReference()] = $currentSite;
            if ($newParentReference != null) {
                $parentSiteReferences[$newParentReference][$currentSite->getReference()] = $currentSite;
            }

            $this->persist($currentSite);
        }

        // Order Sites : SitesRoots -> Children -> Children , etc..
        $sitesReferencesSorted = [];
        $this->orderSites($sitesReferencesSorted, $sitesReferences, $parentSiteReferences);
        /**
         * @var  $reference
         * @var  SesDashboardSite $site
         */
        foreach ($sitesReferencesSorted as $reference => $site) {
            if (!array_key_exists($reference, $sitesToLoad)) {
                continue;
            }

            $newName = $site->getNameForImport();
            $newParentReference = $site->getParentReferenceForImport();

            //check if i have an existing parent Reference
            if (!empty($newParentReference) && !array_key_exists($newParentReference, $sitesReferencesSorted)) {
                $errors[] = new FormError(sprintf("Site with reference [%s] has an un existing parent Reference [%s]", $site->getReference(), $newParentReference));
                break;
            }

            $parentSite = $sitesReferences[$newParentReference];
            $activeRelationShip = $site->getActiveOrMostRecentSiteRelationShip();

            if ($activeRelationShip == null || $activeRelationShip->isDeprecated($epiTime, $newName, $newParentReference)) { // Create a new RelationShip
                if ($activeRelationShip != null) {
                    // and we need to close the active relation ship
                    $this->disableSite($site);
                }

                $currentRelationShip = $this->createSiteRelationShipInstance($site, $parentSite, $newName, null, null);
                $this->persist($currentRelationShip);
            }

            // Check if there are alertRecipientReferences
            if (array_key_exists($site->getReference(), $alertRecipientReferences)) {
                $alertRecipients = $alertRecipientReferences[$site->getReference()];
                /** @var SesDashboardSiteAlertRecipient $alertRecipient */
                foreach ($alertRecipients as $alertRecipient) {
                    $alertRecipient->setSite($site);
                    $alertRecipient->setRecipientSite($sitesReferences[$alertRecipient->getRecipientSiteReference()]);
                    $site->addAlertRecipientSite($alertRecipient);
                    $this->persist($alertRecipient);
                }
            }
        }

        if (count($errors) == 0) {
            $this->saveChanges();
        }

        return $errors;
    }

    public function createRootSite()
    {
        return $this->createSiteInstance(SesDashboardSite::ROOT_REFERENCE, SesDashboardSite::ROOT_REFERENCE);
    }

    public function createSiteInstance($reference, $name, SesDashboardSite $parentSite = null, $longitude = null, $latitude = null)
    {
        $site = SesDashboardSite::createNewInstance($reference);
        $relationShip = $this->createSiteRelationShipInstance($site, $parentSite, $name, $longitude, $latitude);
        $site->addSiteRelationShip($relationShip);

        return $site;
    }

    public function createSiteRelationShipInstance(SesDashboardSite $site, SesDashboardSite $parentSite = null, $siteName, $longitude, $latitude)
    {
        /** @var SesDashboardIndicatorDimDate $dimDateFrom */
        $dimDateFrom = $this->siteDimDateService->getDimDateFrom(DimDateHelper::getDimDateTodayId());

        /** @var  SesDashboardIndicatorDimDate $weekDimDateFrom */
        $weekDimDateFrom = $this->siteDimDateService->getWeekDimDateFrom($dimDateFrom->getId());

        /** @var  SesDashboardIndicatorDimDate $monthDimDateFrom */
        $monthDimDateFrom = $this->siteDimDateService->getMonthDimDateFrom($dimDateFrom->getId());

        return SesDashboardSiteRelationShip::createNewInstance(
            $site,
            $parentSite,
            $siteName,
            $longitude,
            $latitude,
            $dimDateFrom,
            $weekDimDateFrom,
            $monthDimDateFrom);
    }

    /**
     * Order Sites and put orphans at the end
     *
     * @param $sitesReferencesSorted
     * @param $sitesReferences
     * @param $parentSiteReferences
     */
    private function orderSites(&$sitesReferencesSorted, &$sitesReferences, &$parentSiteReferences)
    {
        $sitesReferencesSorted[SesDashboardSite::ROOT_REFERENCE] = $sitesReferences[SesDashboardSite::ROOT_REFERENCE];
        $this->orderSiteReferenceTable($sitesReferencesSorted, SesDashboardSite::ROOT_REFERENCE, $parentSiteReferences);

        // Put all others sites in the $sitesReferencesSorted table
        foreach ($sitesReferences as $reference => $site) {
            if (!isset($sitesReferencesSorted[$reference])) {
                $sitesReferencesSorted[$reference] = $site;
            }
        }
    }

    /**
     * Return arrays with the site reference & parent Site Reference as a key
     *
     * @param $sites
     * @param $sitesReferences
     * @param $parentSiteReferences
     */
    private function createSitesWithReference($sites, &$sitesReferences, &$parentSiteReferences)
    {
        /** @var SesDashboardSite $site */
        foreach ($sites as $site) {
            $sitesReferences[$site->getReference()] = $site;
            if ($site->getParentSiteReference() != null) {
                $parentSiteReferences[$site->getParentSiteReference()][$site->getReference()] = $site;
            }
        }
    }

    /**
     * Recursive function to order sites like SitesRoots -> Children -> Children , etc..
     *
     * @param $sitesReferencesSorted
     * @param $reference
     * @param $parentSiteReferences
     */
    private function orderSiteReferenceTable(&$sitesReferencesSorted, $reference, &$parentSiteReferences)
    {
        if (array_key_exists($reference, $parentSiteReferences)) {
            $children = $parentSiteReferences[$reference];

            if (isset($children) && count($children) > 0) {
                /** @var SesDashboardSite $child */
                foreach ($children as $child) {
                    $sitesReferencesSorted[$child->getReference()] = $child;
                }

                foreach ($children as $child) {
                    $this->orderSiteReferenceTable($sitesReferencesSorted, $child->getReference(), $parentSiteReferences);
                }
            }
        }
    }

    /**
     * Create a new Site with its first relationShip
     *
     * @param SesDashboardSite $site
     * @param $siteName
     * @param $parentSiteId
     * @param $longitude
     * @param $latitude
     * @param $reportDataSourceId
     * @param SitesSameBranchDataSourceConfigConflictDTO $dto
     */
    public function createNewSite(SesDashboardSite $site, $siteName, $parentSiteId, $longitude, $latitude, $reportDataSourceId, SitesSameBranchDataSourceConfigConflictDTO $dto = null)
    {
        $this->beginTransaction();

        $parentSite = $this->getSiteWithoutDependencies($parentSiteId);

        $this->createSiteRelationShipInstance($site, $parentSite, $siteName, $longitude, $latitude);

        if ($reportDataSourceId !== null) {
            $reportDataSource = $this->reportDataSourceService->findOneById($reportDataSourceId);
            $site->setReportDataSource($reportDataSource);
        }

        //if some sites have been in conflicts
        if ($dto !== null && $dto->isConflict() && !empty($dto->getSiteIds())) {
            //we remove their data source configuration --> set the resportDataSourceId to null for all the dto's siteIds
            $this->updateSite($dto->getSiteIds(), DbConstant::NULL);
        }

        $this->persist($site);
        $this->saveChanges();

        $this->commit();
    }

    /**
     * Edit an existing Site
     *
     * @param SesDashboardSite $siteEntity
     * @param $siteName
     * @param $longitude
     * @param $latitude
     * @param $reportDataSourceId
     * @param SitesSameBranchDataSourceConfigConflictDTO $dto
     */
    public function editSite(SesDashboardSite $siteEntity, $siteName, $longitude, $latitude, $reportDataSourceId, SitesSameBranchDataSourceConfigConflictDTO $dto = null)
    {
        $this->beginTransaction();

        $relationShip = $siteEntity->getActiveOrMostRecentSiteRelationShip();

        if ($reportDataSourceId !== null) {
            $reportDataSource = $this->reportDataSourceService->findOneById($reportDataSourceId);
            $siteEntity->setReportDataSource($reportDataSource);
        } else {
            $siteEntity->setReportDataSourceId(null);
            $siteEntity->setReportDataSource(null);
        }

        $relationShip->setName($siteName);
        $relationShip->setLatitude($latitude);
        $relationShip->setLongitude($longitude);

        //if some sites have been in conflicts
        if ($dto !== null && $dto->isConflict() && !empty($dto->getSiteIds())) {
            //we remove their data source configuration --> set the resportDataSourceId to null for all the dto's siteIds
            $this->updateSite($dto->getSiteIds(), DbConstant::NULL);
        }

        $this->saveChanges();
        $this->commit();
    }

    /**
     * Disable or Enable Site
     *
     * @param SesDashboardSite $site
     * @param $enableSite
     */
    public function enableOrDisableSite(SesDashboardSite $site, $enableSite = null)
    {
        $relationShip = $site->getActiveOrMostRecentSiteRelationShip();

        if ($enableSite === null) {
            if ($relationShip->getDimDateTo() == null) {
                $enableSite = false;
                $this->disableSite($site);
            } else {
                $enableSite = true;
                $this->enabledSite($site);
            }
        } else {
            if ($enableSite) {
                if ($relationShip->isDeleted()) { // only enable site if site was disabled
                    $this->enabledSite($site);
                }
            } else {
                if (!$relationShip->isDeleted()) { // only disable site if site was enabled (to avoid override previous disabled dates)
                    $this->disableSite($site);
                }
            }
        }

        // Call recursively for children
        /** @var SesDashboardSiteRelationShip $childRelationShip */
        foreach ($site->getSitesRelationShipChildren() as $childRelationShip) {
            $this->enableOrDisableSite($childRelationShip->getSite(), $enableSite);
        }

        $this->saveChanges();
    }

    /**
     * Disable a site
     *
     * @param SesDashboardSite $site
     */
    private function disableSite(SesDashboardSite $site)
    {
        $relationShip = $site->getActiveOrMostRecentSiteRelationShip();

        $dimDateTo = $this->siteDimDateService->getDimDateTo(DimDateHelper::getDimDateTodayId());
        $weekDimDateTo = $this->siteDimDateService->getWeekDimDateTo($dimDateTo);
        $monthDimDateTo = $this->siteDimDateService->getMonthDimDateTo($dimDateTo);

        if ($relationShip->getDimDateFrom() != null
            && $dimDateTo != null &&
            ($dimDateTo->getFullDate() < $relationShip->getDimDateFrom()->getFullDate())) {
            $dimDateTo = $relationShip->getDimDateFrom();
        }

        if ($relationShip->getWeekDimDateFrom() != null
            && $weekDimDateTo != null &&
            ($weekDimDateTo->getFullDate() < $relationShip->getWeekDimDateFrom()->getFullDate())) {
            $weekDimDateTo = $relationShip->getWeekDimDateFrom();
        }

        if ($relationShip->getMonthDimDateFrom() != null
            && $monthDimDateTo != null &&
            ($monthDimDateTo->getFullDate() < $relationShip->getMonthDimDateFrom()->getFullDate())) {
            $monthDimDateTo = $relationShip->getMonthDimDateFrom();
        }

        $relationShip->setDimDateTo($dimDateTo);
        $relationShip->setWeekDimDateTo($weekDimDateTo);
        $relationShip->setMonthDimDateTo($monthDimDateTo);
    }

    /**
     * Enable a site
     *
     * @param SesDashboardSite $site
     */
    private function enabledSite(SesDashboardSite $site)
    {
        //Create Epi Time
        $ts = (new \DateTime())->getTimestamp();
        $epiTime = Epidemiologic::Timestamp2Epi($ts, $this->epiFirstDay);

        $relationShip = $site->getActiveOrMostRecentSiteRelationShip();

        if ($relationShip->needNewRelationShip($epiTime, $relationShip->getDimDateTo())) {
            /** @var SesDashboardSiteRelationShip $newRelationShip */
            $newRelationShip = clone $relationShip;

            $dimDateFrom = $this->siteDimDateService->getDimDateFrom(DimDateHelper::getDimDateTodayId());
            $weekDimDateFrom = $this->siteDimDateService->getWeekDimDateFrom($dimDateFrom);
            $monthDimDateFrom = $this->siteDimDateService->getMonthDimDateFrom($dimDateFrom);

            $newRelationShip->setDimDateFrom($dimDateFrom);
            $newRelationShip->setWeekDimDateFrom($weekDimDateFrom);
            $newRelationShip->setMonthDimDateFrom($monthDimDateFrom);

            $newRelationShip->setDimDateTo(null);
            $newRelationShip->setWeekDimDateTo(null);
            $newRelationShip->setMonthDimDateTo(null);

            $site->addSiteRelationShip($newRelationShip);

            $this->persist($newRelationShip);
        } else {
            $relationShip->setDimDateTo(null);
            $relationShip->setWeekDimDateTo(null);
            $relationShip->setMonthDimDateTo(null);
        }
    }

    /**
     * @param $siteId
     * @param $dimDateTypeCode
     * @param $dimDateFromId
     * @param $dimDateToId
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     */
    public function isSiteActive($siteId, $dimDateTypeCode, $dimDateFromId, $dimDateToId)
    {
        $enabledSiteId = $this->getChildrenSiteIds($siteId, false, false, 0, true, $dimDateTypeCode, $dimDateFromId, $dimDateToId);

        return (!empty($enabledSiteId) && $enabledSiteId[0] == $siteId);
    }

    /**
     * @param $siteIds
     * @param $dimDateTypeCode
     * @param $dimDateFromId
     * @param $dimDateToId
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getActiveSites($siteIds, $dimDateTypeCode, $dimDateFromId, $dimDateToId)
    {
        $enabledSiteIds = $this->getChildrenSiteIds($siteIds, false, false, 0, true, $dimDateTypeCode, $dimDateFromId, $dimDateToId);

        return $enabledSiteIds;
    }


    /**
     * @param bool $includeAlertRecipient
     * @return mixed
     */
    public function createHierarchy($includeAlertRecipient = false)
    {
        $allSites = $this->getAllSitesArray($includeAlertRecipient);
        $allRelationsShips = $this->getAllSiteRelationShipsArray(null);

        $allRelations = $this->createRelationTable($allRelationsShips, 'FK_SiteId');
        $childRelations = $this->createRelationTable($allRelationsShips, 'FK_ParentId');

        $rootRelationShip = reset($allRelationsShips);
        $idRootSite = $rootRelationShip['FK_SiteId'];
        $rootSite = $allSites[$idRootSite];

        $hierarchy[$idRootSite]["site"] = $rootSite;
        $hierarchy[$idRootSite]["siteRelationShip"] = $allRelations[$idRootSite];
        $hierarchy[$idRootSite]["children"] = $this->createChildHierarchy($idRootSite, $allSites, $allRelations, $childRelations);

        return $hierarchy;
    }

    /**
     * @param $idSite
     * @param $allSites
     * @param $relations
     * @param $children
     * @return array
     */
    private function createChildHierarchy($idSite, $allSites, $relations, $children)
    {
        $result = [];

        if (array_key_exists($idSite, $children)) {

            foreach ($children[$idSite] as $child) {
                $FKSiteId = $child['FK_SiteId'];

                $childSite = $allSites[$FKSiteId];

                $result[$FKSiteId]["site"] = $childSite;
                $result[$FKSiteId]["siteRelationShip"] = $relations[$FKSiteId];
                $result[$FKSiteId]["children"] = $this->createChildHierarchy($FKSiteId, $allSites, $relations, $children);
            }
        }

        return $result;
    }

    /**
     * @param $isNewSite
     * @param $siteId
     * @param $reportDataSourceId
     * @return SitesSameBranchDataSourceConfigConflictDTO
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getSitesSameBranchDataSourceConfigConflictDTO($isNewSite, $siteId, $reportDataSourceId)
    {
        $dto = new SitesSameBranchDataSourceConfigConflictDTO();

        $reportDataSource = $this->reportDataSourceService->findOneById($reportDataSourceId);
        $dto->setReportDataSource($reportDataSource);

        if ($reportDataSource !== null && $reportDataSource->isCheckConfigurationConflict()) {
            if ($isNewSite) {
                //if we are doing a check for a site that is being created, look at its parents sites only
                $sitesIds = $this->getParentSiteIds($siteId, false, null, true, null, null, null);
            } else {
                $sitesIds = $this->getChildrenAndParentSiteIds($siteId, false, false, null, null, null);
            }

            //then filter using the $reportDataSourceId, and retrieve sites instances
            $sites = $this->findSite($sitesIds, null, $reportDataSourceId, null, null);

            $dto->setSites($sites);
            $dto->setConflict(!empty($sites));

            if ($dto->isConflict()) {
                $dto->setSiteIds($sitesIds);

                $siteNames = [];
                foreach ($sites as $site) {
                    $siteNames[] = $site->getName();
                }

                $dto->setMessage($this->getTranslator()->trans('Configuration.FormItems.Site.DialogOverwriteReportDataSource.Content.' . $reportDataSource->getCode(), ['%site_name%' => implode(', ', $siteNames)], ConfigurationAbstractType::TRANSLATION_DOMAIN));
            }
        }

        return $dto;
    }

    /**
     * Return the home site of the given user.
     * If the user has admin permissions, returns the root site.
     * Returns null if no site found.
     * @param SesDashboardUser $user
     * @return SesDashboardSite|null
     */
    public function getHomeSite(SesDashboardUser $user)
    {
        $homeSite = null;

        if ($user != null) {
            $homeSite = $user->getSite();
        }

        if (empty($homeSite) || $homeSite == null) {
            if ($user !== null && $user->isAdmin()) {
                $homeSite = $this->getSiteRoot();
            }
        }

        return $homeSite;
    }

    /**
     * @param null $id
     * @param null $reportDataSourceId
     * @return mixed
     */
    public function updateSite($id = null, $reportDataSourceId = null)
    {
        return $this->siteRepository->updateSite($id, $reportDataSourceId);
    }

    /**
     * Get an array containing all sites
     *
     * @return array
     */
    private function getAllSitesArray($includeAlertRecipient)
    {
        return $this->siteRepository->getAllSitesArray($includeAlertRecipient);
    }

    /**
     * @param null $search
     * @return array
     */
    public function getAllSiteRelationShipsArray($search = null)
    {
        return $this->siteRelationShipRepository->getAllSiteRelationShipsArray($search);
    }

    /**
     * @param $allRelationsShips
     * @param $property
     * @return array
     */
    private function createRelationTable($allRelationsShips, $property)
    {
        $result = [];

        foreach ($allRelationsShips as $relationsShip) {
            $result[$relationsShip[$property]][] = $relationsShip;
        }

        return $result;
    }

    /**
     * Get Site repository
     * @return SesDashboardSiteRepository|\Doctrine\ORM\EntityRepository
     */
    public function getRepository()
    {
        return $this->siteRepository;
    }

    /**
     * Set site repository
     *
     * @param RepositoryInterface $repository
     */
    public function setRepository(RepositoryInterface $repository)
    {
        $this->siteRepository = $repository;
    }

    /**
     * @return LocaleService
     */
    public function getLocaleService()
    {
        return $this->localeService;
    }

    /**
     * @param LocaleService $localeService
     */
    public function setLocaleService($localeService)
    {
        $this->localeService = $localeService;
    }

    /**
     * @return ReportDataSourceService
     */
    public function getReportDataSourceService()
    {
        return $this->reportDataSourceService;
    }

    /**
     * @param ReportDataSourceService $reportDataSourceService
     */
    public function setReportDataSourceService($reportDataSourceService)
    {
        $this->reportDataSourceService = $reportDataSourceService;
    }

    /**
     * @return string
     */
    public function getGetChildrenSitesLogLevel()
    {
        return $this->getChildrenSitesLogLevel;
    }

    /**
     * @param string $getChildrenSitesLogLevel
     */
    public function setGetChildrenSitesLogLevel($getChildrenSitesLogLevel)
    {
        $this->getChildrenSitesLogLevel = $getChildrenSitesLogLevel;
    }

    /**
     * @return TranslatorInterface
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * @param TranslatorInterface $translator
     */
    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }
}
