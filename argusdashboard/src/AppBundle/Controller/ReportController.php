<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Constant;
use AppBundle\Entity\Security\SesDashboardPermissionScope;
use AppBundle\Entity\SesDashboardIndicatorDimDateType;
use AppBundle\Entity\SesDashboardSite;
use AppBundle\Entity\SesDashboardSiteRelationShip;
use AppBundle\Entity\SesFullReport;
use AppBundle\Services\DiseaseService;
use AppBundle\Services\ImportService;
use AppBundle\Services\ReportService;
use AppBundle\Services\SiteService;
use AppBundle\Services\ThresholdService;
use AppBundle\Utils\DimDateHelper;
use AppBundle\Utils\Epidemiologic;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Exception\Exception;


/**
 * Controller used to manage report contents
 *
 * @Route("/report")
 *
 */
class ReportController extends BaseController
{

    /**
     * Init index.html page
     *
     * @Route("/", name="report_index")
     * @param $selectedPath
     * @return Response
     */
    public function indexAction($selectedPath)
    {
        // Sites
        $site = null ;

        if ($selectedPath == "") {
            $site = $this->getHomeSite();
        } else {
            $site = $this->getSiteService()->findOneBy(array ('reference' => $selectedPath));
        }

        /** @var DiseaseService $diseaseService */
        $diseaseService = $this->getDiseaseService();

        // Diseases
        $diseasesWeekly = $diseaseService->getDiseases(Constant::PERIOD_WEEKLY, true, null, null);
        $diseasesMonthly = $diseaseService->getDiseases(Constant::PERIOD_MONTHLY, true, null, null);

        if ($site === null) {
            if (! $this->isGranted('ROLE_ADMIN')) {
                $logger = $this->get('logger');
                $logger->error('An error occurred : Impossible to find Site with technical name "' . $selectedPath . '"');
                throw new AccessDeniedHttpException(null, null, 403);
            } else{
                return $this->render('report/noConfig.html.twig');
            }
        } else {
            $previousDate = Epidemiologic::Timestamp2Epi(strtotime("-7 days"),$this->GetEpiFirstDay());

            return $this->render('report/index.html.twig', array(
                                                                 'diseasesWeekly' => $diseasesWeekly,
                                                                 'diseasesMonthly' => $diseasesMonthly,
                                                                 'currentWeek' => $previousDate["Week"],
                                                                 'currentYear' => $previousDate["Year"]));
        }
    }

    public function submitReportAction($fullReportId, $partReportIdToValidate)
    {
        /** @var ImportService $importService */
        $importService = $this->container->get('importService');
        /** @var SesFullReport|null $result */
        $result = $importService->validatePartReport($fullReportId, $partReportIdToValidate, $this->getUser()->getUserName());

        return $this->getJsonDataResponse($result, 'Submit Successful') ;
    }

    public function rejectReportAction($fullReportId, $partReportIdToReject)
    {
        /** @var ImportService $importService */
        $importService = $this->container->get('importService');
        /** @var SesFullReport|null $result */
        $result = $importService->rejectPartReport($fullReportId, $partReportIdToReject, $this->getUser()->getUserName());

        return $this->getJsonDataResponse($result, 'Reject Successful') ;
    }

    /**
     * Get the list of FullReports for the specified sites or children if site is not leaf
     *
     * @param $selectedSiteId
     * @param $display
     * @param $startDate
     * @param $endDate
     * @param $period
     * @return Response
     */
    public function listeReportAction($selectedSiteId, $display, $startDate, $endDate, $period)
    {
        /** @var ReportService $reportService */
        $reportService = $this->getReportService();

        /** @var SiteService $siteService */
        $siteService = $this->getSiteService();
        $site = $siteService->getSiteWithoutDependencies($selectedSiteId);

        /** @var SesDashboardSite $sesDashboardSite */
        $sesDashboardSite = null;

        $siteRelations = [];
        $validMode = false;
        $enableValidationAction = false;
        $enableRejectionAction = false;
        $creationActive = false;

        if ($site->isLeaf()) {
            /** @var SesDashboardSiteRelationShip $relationShip */
           // $relationShip = $site->getSitesRelationShip()[0]; // TODO Get Actives RelationsShips between $startDate - $endDate
           // $relationShipId = $relationShip->getId();
            $siteRelations = $reportService->getSitesRelationWithReports($site->getRelationShipIds(), $display, $startDate, $endDate, $period);

        } else {
            $validMode = true;

            $homeSite = $this->getHomeSite();
            $permissions = $this->getUser() != null ? $this->getUser()->getDashboardPermissions() : [];

            //Check if Auto validation Mode is enabled
            if ($this->isAutoValidationEnabled()) {
                $enableValidationAction = false;
                $enableRejectionAction = false;
            } else {
                $enableValidationAction = $this
                    ->getSesDashboardPermissionHelper()
                    ->isValidationActionEnabled($site, $homeSite, $permissions, $period);

                $enableRejectionAction = $this
                    ->getSesDashboardPermissionHelper()
                    ->isRejectionActionEnabled($site, $homeSite, $permissions, $period);
            }

            // TODO To Reactivate
            $filterSiteChildId = $this
                ->getSesDashboardPermissionHelper()
                ->getChildSiteIdToFilter(
                    $site,
                    $homeSite,
                    $permissions,
                    $period
                );

            // TODO Get Actives RelationsShips between $startDate - $endDate
            $siteRelations = $reportService->getSitesRelationWithReports($site->getChildrenRelationShipIds(), $display, $startDate, $endDate, $period);

           /* $sesDashboardSite = $reportService
                ->getSiteByIdAndFilters(
                    $selectedSiteId,
                    $filterSiteChildId,
                    $display,
                    $startDate,
                    $endDate,
                    $period
                );*/

           // $sesDashboardRelationShips = ($sesDashboardSite != null) ? $sesDashboardSite->getSitesRelationShipChildren() : [];
        }

        return $this->render('report/listeReport.html.twig',
            [
                'sesDashboardRelationShips' => $siteRelations,
                'validMode' => $validMode,
                'enableValidationAction' => $enableValidationAction,
                'enableRejectionAction' => $enableRejectionAction,
                'creationActive' => $creationActive
            ]
        );
    }

    /**
     * Get the list of PartReports for the specified full report
     *
     * @param $fullReportId
     * @return Response
     */
    public function listePartReportAction($fullReportId)
    {
        /** @var ReportService $reportService */
        $reportService = $this->getReportService();
        /** @var SesFullReport $fullReport */
        $fullReport = $reportService->getFullReport($fullReportId);

        /** @var SiteService $siteService */
        $siteService = $this->getSiteService();

        $startDate = DimDateHelper::getDimDateIdFromDateTime($fullReport->getStartDate());
        $endDate =  DimDateHelper::getDimDateIdFromDateTime($fullReport->getEndDate());

        /** @var SesDashboardSite $site */
        $site = $fullReport->getFrontLineGroup();

        $period = $fullReport->getPeriod();

        if ($period == Constant::PERIOD_WEEKLY) {
            $dimDateTypeCode = SesDashboardIndicatorDimDateType::CODE_WEEKLY_EPIDEMIOLOGIC;
            $activeChildrenRelationShip = $siteService->getActiveChildrenRelationShipWeekly($site, $startDate, $endDate) ;
        } else if ($period == Constant::PERIOD_MONTHLY) {
            $dimDateTypeCode = SesDashboardIndicatorDimDateType::CODE_MONTHLY;
            $activeChildrenRelationShip = $siteService->getActiveChildrenRelationShipMonthly($site, $startDate, $endDate) ;
        } else {
            $dimDateTypeCode = SesDashboardIndicatorDimDateType::CODE_DAILY;
            $activeChildrenRelationShip = [];
        }

        $diseases = $this->getDiseaseService()->getDiseases($period);

        /** @var ThresholdService $thresholdService */
        $thresholdService = $this->getThresholdService();
        try {
            $thresholdService->AssignThresholdToFullReport($fullReport, $diseases);
        }
        catch (Exception $ex) {
            $logger = $this->get('logger');
            $logger->error('An error occurred when trying to Assign Threshold to full report with id "' . $fullReport->getId() . '"');
        }


        $partReportsList = $fullReport->getPartReportsForDisplay();
        $leafSitesIds = 0;

        $numberOfChildrenLeaves = [];

        // For each activeChildrenRelationShip, calculate the number of leaves
        /** @var SesDashboardSiteRelationShip $childrenRelationShip */
        foreach ($activeChildrenRelationShip as $childrenRelationShip) {
            $leaves = $siteService->getLeafSiteIds(
                $childrenRelationShip->getFKSiteId(),
                false,
                true,
                null,
                $dimDateTypeCode,
                $startDate, $endDate);

            if ($period == Constant::PERIOD_WEEKLY) {
                $expectedReports = $this->getDashboardService()->getNumberOfExpectedWeeklyReportPeriod($leaves,
                    $fullReport->getStartDate()->format('Y-m-d'),
                    $fullReport->getEndDate()->format('Y-m-d'));
            } else if ($period == Constant::PERIOD_MONTHLY) {
                $expectedReports = $this->getDashboardService()->getNumberOfExpectedMonthlyReportPeriod($leaves,
                    $fullReport->getStartDate()->format('Y-m-d'),
                    $fullReport->getEndDate()->format('Y-m-d'));
            } else {
                $expectedReports = 0;
            }

            $numberOfChildrenLeaves[$childrenRelationShip->getId()] = $expectedReports;
            $leafSitesIds += $expectedReports ;
        }

        return  $this->render('report/listePartReport.html.twig', ['fullReport' => $fullReport,
                                                                        'partReports' => $partReportsList,
                                                                        'nbOfChildrenLeaf' => $leafSitesIds,
                                                                        'activeChildrenRelationShip' => $activeChildrenRelationShip,
                                                                        'numberOfChildrenLeaves' => $numberOfChildrenLeaves] );
    }

    /**
     * Load Filter Type zone regarding disease configuration and user permissions
     *
     * @param $siteId
     * @return Response
     */
    public function filterTypeAction($siteId)
    {
        // Diseases
        $diseasesWeekly = $this
            ->getDiseaseService()
            ->getDiseases(Constant::PERIOD_WEEKLY);
        $diseasesMonthly = $this
            ->getDiseaseService()
            ->getDiseases(Constant::PERIOD_MONTHLY);

        $site = $this->getSiteService()->getSiteWithoutDependencies($siteId);
        $homeSite = $this->getHomeSite();
        $permissions = $this->getUser() != null ? $this->getUser()->getDashboardPermissions() : [];

        $enableWeelkyReport = $this
            ->getSesDashboardPermissionHelper()
            ->isWeeklyReportEnabled($site, $homeSite, $permissions);
        $enableMonthlyReport = $this
            ->getSesDashboardPermissionHelper()
            ->isMonthlyReportEnabled($site, $homeSite, $permissions);

        return $this->render('report/filters-type.html.twig',
            [
                'enabledWeeklyReport' => (
                    isset($diseasesWeekly)
                    && count($diseasesWeekly) > 0
                    && $enableWeelkyReport
                ),
                'enabledMonthlyReport' => (
                    isset($diseasesMonthly)
                    && count($diseasesMonthly) > 0
                    && $enableMonthlyReport
                )
            ]
        );
    }

    /**
     * Load Filter Status zone regarding user permissions
     *
     * @param $siteId
     * @param $period
     * @return Response
     */
    public function filterStatusAction($siteId, $period)
    {
        $site = $this->getSiteService()->getSiteWithoutDependencies($siteId);
        $homeSite = $this->getHomeSite();
        $permissions = ($this->getUser() != null ? $this->getUser()->getDashboardPermissions() : []);

        $enablePendingStatus = $this
            ->getSesDashboardPermissionHelper()
            ->isPendingStatusEnabled(
                $site,
                $homeSite,
                $permissions,
                $period
            );
        $enableValidatedStatus = $this
            ->getSesDashboardPermissionHelper()
            ->isValidatedStatusEnabled(
                $site,
                $homeSite,
                $permissions,
                $period
            );
        $enableRejectedStatus = $this
            ->getSesDashboardPermissionHelper()
            ->isRejectedStatusEnabled(
                $site,
                $homeSite,
                $permissions,
                $period
            );
        $enableConflictingStatus = $enablePendingStatus
            && $enableValidatedStatus
            && $enableRejectedStatus;
        $enableAllStatus = $enablePendingStatus
            && $enableValidatedStatus
            && $enableRejectedStatus
            && $enableConflictingStatus;

        return $this->render('report/filters-status.html.twig',
            [
                'enablePendingStatus' => $enablePendingStatus,
                'enableValidatedStatus' => $enableValidatedStatus,
                'enableRejectedStatus' => $enableRejectedStatus,
                'enableConflictingStatus' => $enableConflictingStatus,
                'enableAllStatus' => $enableAllStatus
            ]
        );
    }

    /**
     * Return the list of Alerts (old & new ones)
     *
     * @param $selectedSiteId
     * @return Response
     */
    public function listeAlertAction($selectedSiteId)
    {
        /** @var SiteService $siteService */
        $siteService = $this->getSiteService();

        /** @var ReportService $reportService */
        $reportService = $this->getReportService();

        $site = $siteService->getById($selectedSiteId);
        $homeSite = $this->getHomeSite();
        $permissions = $this->getUser() != null ? $this->getUser()->getDashboardPermissions() : [];

        $alertScope = $this
            ->getSesDashboardPermissionHelper()
            ->getAlertScope(
                $site,
                $homeSite,
                $permissions
            );

        $oldAlerts = null;
        $newAlerts = null;
        $sites = [];

        if ($alertScope == SesDashboardPermissionScope::SCOPE_ALL) {
            $sites = $siteService->getLeafSiteIds(
                $selectedSiteId, false, true, null, SesDashboardIndicatorDimDateType::CODE_DAILY, null, null, true);
        } else if ($alertScope == SesDashboardPermissionScope::SCOPE_SINGLE) {
            $sites = $siteService->getLeafSiteIds(
                $homeSite->getId(), false, true, null, SesDashboardIndicatorDimDateType::CODE_DAILY, null, null, true);
        }

        if ($alertScope == SesDashboardPermissionScope::SCOPE_ALL || $alertScope == SesDashboardPermissionScope::SCOPE_SINGLE) {
            $oldAlerts = $reportService
                ->getOldAlerts($sites, $this->getNbMaxOldAlert());
            $newAlerts = $reportService
                ->getNewAlerts($sites, $this->getNbMaxNewAlert());
        }

        return $this->render('report/listeAlerts.html.twig',
            [
                'newAlerts' => $newAlerts,
                'oldAlerts' => $oldAlerts
            ]
        );
    }

    public function createDashboardJsonAction($selectedSiteId, $period, $weekNumber, $monthNumber, $year)
    {
        $dashboardService =  $this->getDashboardService();
        $translator = $this->getTranslator();

        $site = $this->getSiteService()->getById($selectedSiteId);
        $autoValidationEnabled = $this->isAutoValidationEnabled();
        $user = $this->getUser();

        // Add Monitoring Logs
        $this->LogInfoAction(
            "Dashboard Download",
            "Site: ".$site->getName().
            ", Week: ".$weekNumber.
            ", Year: ".$year
        );

        return $dashboardService->createDashboardJsonFile($user, $site, $period, $weekNumber, $monthNumber, $year, $autoValidationEnabled, $translator);
    }

    /**
     * Set the status of the alert to read
     *
     * @param $alertId
     * @return JsonResponse
     */
    public function readAlertAction($alertId)
    {
        $reportService = $this->container->get('reportService');
        $reportService->readAlert($alertId);

        return new JsonResponse();
    }

    /****  Function used to manually enter report values *******/

    /*
    public function addEditReportAction($period, $selectedPath)
    {
        $reportService = $this->container->get('reportService');

        $fullReport = $reportService->getTemplateFullReport($period);
        $fullReport->setPeriod($period);
        $fullReport->setStartDate(new \DateTime());
        $fullReport->setFrontlineGroup(
            $reportService
                ->getFrontLineGroupByPath(
                    Helper::decryptString($selectedPath)
                )
        );

        $form = $this
            ->get('form.factory')
            ->create(
                new SesFullReportType(),
                $fullReport
            );

        return $this->render('report/_formAddEditReport.html.twig',
            ['form' => $form->createView()]
        );
    }

    public function createReportAction(Request $request)
    {
        //This is optional. Do not do this check if you want to call the same action using a regular request.
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(
                ['message' => 'You can access this only using Ajax!'],
                JsonResponse::HTTP_BAD_REQUEST
                );
        }

        $period = $request
            ->request
            ->get('SesFullReportForm')["periodHidden"]; // Get period
        $encodedPath = $request
            ->request
            ->get('SesFullReportForm')["siteHidden"]; // Get site
        $reportService = $this->container->get('reportService');
        $fullReport = $reportService->getTemplateFullReport($period);
        $fullReport
            ->setFrontlineGroup(
                $reportService
                    ->getFrontLineGroupByPath(
                        Helper::decryptString($encodedPath)
                    )
            );

        $form = $this
            ->get('form.factory')
            ->create(new SesFullReportType(), $fullReport);
        $form->handleRequest($request);

        if ($form->isValid())
        {
            $reportService
                ->createFullReport(
                    $fullReport,
                    $period,
                    Helper::decryptString($encodedPath),
                    $fullReport->getStartDate()
                );

            return new JsonResponse(
                ['message' => 'Report created with success!'],
                JsonResponse::HTTP_OK
            );
        } else {
            //return new JsonResponse(array('message' => 'Error!'), 400);
            $response = new JsonResponse(
                [
                    'message' => 'Error',
                    'form' => $this
                        ->renderView(
                            'report/_formAddEditReport.html.twig',
                            ['form' => $form->createView()]
                        )
                ], JsonResponse::HTTP_BAD_REQUEST
            );

            return $response;
        }
    }
    */
}
