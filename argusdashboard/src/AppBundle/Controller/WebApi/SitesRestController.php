<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 11/26/2015
 * Time: 3:43 PM
 */

namespace AppBundle\Controller\WebApi;

use AppBundle\Entity\SesDashboardReportDataSource;
use AppBundle\Entity\SesDashboardSite;
use AppBundle\Entity\WebApi\WebApiSite;

/**
 * Class SitesRestController
 * @package AppBundle\Controller\WebApi
 *
 * Web Api Controller to expose Sites
 */
class SitesRestController extends BaseRestController
{
    /**
     * Return All sites and children
     *
     * @return array
     */
    public function getSitesAction()
    {
        $siteService = $this->getSiteService();

        $sitesArray = $siteService->createHierarchy();

        // Get Permissions
        $permissions = $this->getUser() != null ? $this->getUser()->getDashboardPermissions() : [];
        $homeSite = $this->getHomeSite() ;

        // https://packagist.org/packages/bcc/auto-mapper-bundle
        return ['sites' => $this->mappSites(reset($sitesArray), $permissions, $homeSite)];
    }

    /**
     * Map SesDashboardSite to WebApiSite objects
     *
     * @param $siteArray
     * @param null $permissions
     * @param SesDashboardSite|null $homeSite
     * @return array
     */
    private function mappSites($siteArray, $permissions = null, SesDashboardSite $homeSite = null)
    {
        $siteService = $this->getSiteService();

        $results = array();

        $s = $siteArray['site'];
        $relation = $siteService->getActiveOrMostRecentRelationShipArray($siteArray['siteRelationShip']);

        $ws = new WebApiSite();
        $ws->deletedWeekly = $siteService->isRelationShipWeeklyDeleted($relation);
        $ws->id = $s['id'];
        $ws->name = $relation['name'];
        $ws->reference = $s['reference'];
        $ws->parentId = $relation['FK_ParentId'];
        $ws->latitude = $relation['latitude'];
        $ws->longitude = $relation['longitude'];
        $ws->level = $relation['level'];
        $ws->path = $relation['path'];
        $ws->home = ($homeSite != null ? $s['id'] == $homeSite->getId() : false);
        $ws->homePath = $this->getSesDashboardPermissionHelper()->areSiteOnSameBranch($ws, $homeSite);
        $ws->accessible = $this->getSesDashboardPermissionHelper()->isTreeViewNodeSelectable($ws, $homeSite, $permissions);
        $ws->export = $this->getSesDashboardPermissionHelper()->isTreeViewNodeExportable($ws, $homeSite, $permissions);

        if(!empty($s['reportDataSource']['code'])) {
            $ws->reportDataSourceCode = $s['reportDataSource']['code'];
            if($s['reportDataSource']['code'] == SesDashboardReportDataSource::CODE_EXCEL) {
                $ws->uploadableWeekly = $this->getSesDashboardPermissionHelper()->isWeeklyReportUploadEnabled($ws, $homeSite, $permissions);
            }
        }

        $children = $siteArray['children'];

        $tree = [];

        foreach ($children as $key => $child) {
            $tree = array_merge($tree, $this->mappSites($child, $permissions, $homeSite));
        }

        $ws->children = (count($tree) > 0 ? $tree : null);
        $results[] = $ws;

        return $results ;
    }
}