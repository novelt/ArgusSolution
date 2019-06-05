<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 12/02/2018
 * Time: 14:56
 */

namespace AppBundle\Controller\WebApi\ReportData;

use AppBundle\Controller\BaseController;
use AppBundle\Entity\Constant;
use AppBundle\Entity\Security\SesDashboardUser;
use AppBundle\Entity\SesFullReport;
use AppBundle\Entity\WebApi\WebApiReportData;
use AppBundle\Services\ReportService;

use FOS\RestBundle\Controller\Annotations\Get;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Web APi used to provide report data to R script and Angular App
 * /!\ Be careful, when we ask for site's reports, we need to show children site's reports /!\
 *
 * Class ReportDataRestController
 * @package AppBundle\Controller\WebApi\ReportData
 */
class ReportDataRestController extends BaseController
{
    /** @var ReportService */
    private $reportService;

    /**
     * @return array
     * @Get("/reportData/{reportId}")
     */
    public function getReportDataAction($reportId)
    {
        $this->reportService = $this->getReportService();

        /** @var SesDashboardUser $user */
        $user = $this->getUser();

        $reportIdParam = $this->getIntegerParameter($reportId);
        $fullReport = $this->reportService->getFullReport($reportIdParam);

        return ['reports' => $this->mappReportData([$fullReport], $user)];
    }


    /**
     * @return array
     * @Get("/reportsData")
     */
    public function getAllReportDataAction()
    {
        return $this->getReportDataSiteStatusFromToPeriodAction();
    }

    /**
     * @Get("/reportsData/{siteId}")
     *
     * @param $siteId
     * @return array
     */
    public function getReportDataSiteAction($siteId)
    {
        return $this->getReportDataSiteStatusFromToPeriodAction($siteId);
    }

    /**
     * @Get("/reportsData/{siteId}/{period}")
     * @param $siteId
     * @param $period
     * @return array
     */
    public function getReportDataSiteStatusAction($siteId, $period)
    {
        return $this->getReportDataSiteStatusFromToPeriodAction($siteId, $period);
    }

    /**
     * @Get("/reportsData/{siteId}/{period}/{startDate}/{endDate}")
     * @param $siteId
     * @param $period
     * @param $startDate
     * @param $endDate
     * @return array
     */
    public function getReportDataSiteStatusFromToAction($siteId, $period, $startDate, $endDate)
    {
        return $this->getReportDataSiteStatusFromToPeriodAction($siteId, $period, $startDate, $endDate);
    }


    /**
     * @Get("/reportsData/{siteId}/{period}/{startDate}/{endDate}/{status}")
     * @param $siteId
     * @param $period
     * @param $startDate
     * @param $endDate
     * @param $status
     * @return array
     */
    public function getReportDataSiteStatusFromToPeriodAction($siteId = null, $period = null, $startDate = null, $endDate = null, $status = null)
    {
        // Parse all args
        $siteIdParam = $this->getIntegerParameter($siteId);
        $statusParam = $this->getStringParameter($status);
        $startDateParam = $this->getDateParameter($startDate);
        $endDateParam = $this->getDateParameter($endDate);
        $periodParam = $this->getStringParameter($period);

        // Transform SiteId into children sites Ids
        $siteIds = $this->getSiteService()->getChildrenSiteIds(
            $siteIdParam,
            false,
            false,
            1,
            false);

        if (sizeof($siteIds) == 0) { // the site has no children. It is a leaf
            $siteIds[] = $siteId;
        }

        return $this->getReports($siteIds, $periodParam, $startDateParam, $endDateParam, $statusParam);
    }

    /**
     *
     *
     * @param array $siteIds
     * @param null $period
     * @param null $startDate
     * @param null $endDate
     * @param null $status
     * @return array
     */
    private function getReports($siteIds = [], $period = null, $startDate = null, $endDate = null, $status = null)
    {
        $this->reportService = $this->getReportService();

        /** @var SesDashboardUser $user */
        $user = $this->getUser();
        // Get the User connected with the token

        // Only the admin can have empty site Ids filter
        if (sizeof($siteIds) == 0) {
            if (!$user->isAdmin()) {
                return [];
            }
        }

        $reportData = $this->reportService->getFullReportData($siteIds, $status, $startDate, $endDate, $period);

        return ['reports' => $this->mappReportData($reportData, $user)];
    }

    /**
     * @Get("/todoReportsData")
     * Return all reports accessible to current user (permissions) not validated or rejected
     *
     * @return array
     */
    public function getTodoReportsDataAction()
    {
        $statuses = [Constant::STATUS_PENDING, Constant::STATUS_CONFLICTING, Constant::STATUS_REJECTED_FROM_ABOVE] ;

        /** @var SesDashboardUser $user */
        $user = $this->getUser();
        if ($user->isAdmin()){
            return $this->getReports(
                null,
                null,
                null,
                null,
                $statuses);
        }

        // Is User not admin .
        // Get Home Site
        $homeSite = $user->getSite();
        $permissions = $user->getDashboardPermissions();
        $permissionHelper = $this->getSesDashboardPermissionHelper();
        $siteScopes = $permissionHelper->getSitesScopes($permissions);

        $siteIds = [];
        $brothersIds = [];

        if ($siteScopes['homeSingle'] === true) {
            $siteIds = $this->getSiteService()->getChildrenSiteIds(
                $homeSite->getId(),
                false,
                false,
                1,
                false);
            //$siteIds[] = $homeSite->getId() ;
        }

        if ($siteScopes['homeBrothers'] === true) {
            $siteIds = $this->getSiteService()->getChildrenSiteIds(
                $homeSite->getId(),
                false,
                false,
                1,
                false);

            $brothersIds = $this->getSiteService()->getBrotherSiteIds($siteIds);
            $siteIds = array_merge($siteIds, $brothersIds);
        }

        if ($siteScopes['childrenSingle'] !== null) {

            $childrenIds = $this->getSiteService()->getChildrenSiteIds(
                $homeSite->getId(),
                false,
                false,
                1,
                false);

            $childrenSingleIds = $this->getSiteService()->getChildrenSiteIds(
                $childrenIds,
                false,
                false,
                abs($siteScopes['childrenSingle']),
                false);

            $siteIds = array_merge($siteIds, $childrenSingleIds);
        }

        // TODO
      /*  if ($siteScopes['childrenBrothers'] !== null) {

            if (count($brothersIds) == 0) {
                $brothersIds = $this->getSiteService()->getBrotherSiteIds($homeSite->getId());
            }

            $childrenBrotherIds = $this->getSiteService()->getChildrenSiteIds(
                array_merge([$homeSite->getId()], $brothersIds) ,
                false,
                false,
                abs($siteScopes['childrenSingle']),
                null,
                null,
                false);

            $siteIds = array_merge($siteIds, $childrenBrotherIds);
        }*/

        // TODO : Add 2 last conditions Parent Ids & Brother Parent Ids

        return $this->getReports(
            $siteIds,
            null,
            null,
            null,
            $statuses);
    }

    /**
     * @Get("/reportsData/validate/{reportId}/{reportVersionId}")
     *
     * @param $reportId
     * @param $reportVersionId
     * @return null|JsonResponse
     */
    public function validateReportAction($reportId, $reportVersionId)
    {
        $importService = $this->getImportService();
        /** @var SesFullReport $result */
        $result = $importService->validatePartReport($reportId, $reportVersionId, $this->getUser()->getUserName());

        return $this->getJsonDataResponse($result, 'Submit Successful') ;
    }

    /**
     * @Get("/reportsData/reject/{reportId}/{reportVersionId}")
     *
     * @param $reportId
     * @param $reportVersionId
     * @return null|JsonResponse
     */
    public function rejectReportAction($reportId, $reportVersionId)
    {
        $importService = $this->getImportService();
        /** @var SesFullReport $result */
        $result = $importService->rejectPartReport($reportId, $reportVersionId, $this->getUser()->getUserName());

        return $this->getJsonDataResponse($result, 'Reject Successful') ;
    }

    /**
     * Map FullReport to WebApiReportData objects
     *
     * @param array $reportData
     * @param SesDashboardUser $user
     * @return array
     */
    private function mappReportData($reportData, $user)
    {
        $results = array();

        /** @var SesFullReport $report */
        foreach($reportData as $report) {

            $wrd = new WebApiReportData();
            $wrd->id = $report->getId();
            $wrd->period = $report->getPeriod();
            $wrd->FK_SiteId = $report->getFK_SiteId();
            $wrd->FK_SiteName = $report->getFrontLineGroup()->getName(); // TODO : Get Name of Site Relation Ship
            $wrd->FK_SiteRelationShipId = $report->getFK_SiteRelationShipId();
            $wrd->startDate = $report->getStartDate();
            $wrd->endDate = $report->getEndDate();
            $wrd->weekNumber = $report->getWeekNumber();
            $wrd->monthNumber = $report->getMonthNumber();
            $wrd->year = $report->getYear();
            $wrd->status = $report->getStatus();
            $wrd->aggregate = $report->isAggregate();
            $wrd->firstValidationDate = $report->getFirstValidationDate();
            $wrd->firstRejectionDate = $report->getFirstRejectionDate();
            $wrd->createdDate = $report->getCreatedDate();
            $wrd->canValidate = $this
                                ->getSesDashboardPermissionHelper()
                                // Validation checked with Parent Site
                                ->isValidationActionEnabled(
                                    $report->getFrontLineGroup() != null ? $report->getFrontLineGroup()->getParent() : null,
                                    $user->getSite(),
                                    $user->getDashboardPermissions(),
                                    $report->getPeriod());
            $wrd->canReject = $this
                                ->getSesDashboardPermissionHelper()
                                // Rejection checked with Parent Site
                                ->isRejectionActionEnabled(
                                    $report->getFrontLineGroup() != null ? $report->getFrontLineGroup()->getParent() : null,
                                    $user->getSite(),
                                    $user->getDashboardPermissions(),
                                    $report->getPeriod());

            $results[] = $wrd;
        }

        return $results ;
    }

}