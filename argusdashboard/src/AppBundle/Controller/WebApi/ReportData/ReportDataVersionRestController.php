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
use AppBundle\Entity\SesDashboardIndicatorDimDateType;
use AppBundle\Entity\SesDashboardSiteRelationShip;
use AppBundle\Entity\SesFullReport;
use AppBundle\Entity\SesPartReport;
use AppBundle\Entity\SesReport;
use AppBundle\Entity\SesReportValues;
use AppBundle\Entity\WebApi\WebApiAggregation;
use AppBundle\Entity\WebApi\WebApiDisease;
use AppBundle\Entity\WebApi\WebApiDiseaseValues;
use AppBundle\Entity\WebApi\WebApiReportVersionData;
use AppBundle\Services\ReportService;

use AppBundle\Services\SiteService;
use AppBundle\Services\ThresholdService;
use AppBundle\Utils\DimDateHelper;
use FOS\RestBundle\Controller\Annotations\Get;

/**
 * Web APi used to provide report data version to R script and Angular App
 *
 * Class ReportDataRestController
 * @package AppBundle\Controller\WebApi\ReportData
 */
class ReportDataVersionRestController extends BaseController
{
    /**
     * @return array
     * @Get("/reportsVersionData")
     */
    public function getAllReportDataVersionAction()
    {
        return $this->getReportDataVersionStatusReportAction();
    }

    /**
     * @Get("/reportsVersionData/{reportId}")
     *
     * @param $reportId
     * @return array
     */
    public function getReportDataVersionReportAction($reportId)
    {
        return $this->getReportDataVersionStatusReportAction($reportId);
    }

    /**
     * @Get("/reportsVersionData/{reportId}/{statuses}")
     *
     * @param $reportId
     * @param $statuses
     * @return array
     */
    public function getReportDataVersionStatusReportAction($reportId = null, $statuses = null)
    {
        /** @var ReportService $reportService */
        $reportService = $this->getReportService();

        /** @var SiteService $siteService */
        $siteService = $this->getSiteService();

        // Get the User connected with the token

        // Parse all args
        $reportIdParam = $this->getIntegerParameter($reportId);
        $statusParam = $this->getStringParameter($statuses);

        //Retrieve Full Report
        /** @var  SesFullReport $fullReport */
        $fullReport = $reportService->getFullReport($reportIdParam);

        $diseases = $this->getDiseaseService()->getDiseases($fullReport->getPeriod());

        /** @var ThresholdService $thresholdService */
        $thresholdService = $this->getThresholdService();
        $thresholdService->AssignThresholdToFullReport($fullReport, $diseases);

        // Retrieve Versions
        //$reportVersionData = $reportService->getPartReportData($reportIdParam, $statusParam);
        $reportVersionData = $fullReport->getPartReportsForDisplay();

        // Retrieve Aggregations
        $startDate = DimDateHelper::getDimDateIdFromDateTime($fullReport->getStartDate());
        $endDate =  DimDateHelper::getDimDateIdFromDateTime($fullReport->getEndDate());
        $site = $fullReport->getFrontLineGroup();

        $period = $fullReport->getPeriod();

        $dimDateTypeCode = SesDashboardIndicatorDimDateType::CODE_DAILY;

        $activeChildrenRelationShip = [];
        if ($period == Constant::PERIOD_WEEKLY) {
            $activeChildrenRelationShip = $siteService->getActiveChildrenRelationShipWeekly($site, $startDate, $endDate) ;
            $dimDateTypeCode = SesDashboardIndicatorDimDateType::CODE_WEEKLY_EPIDEMIOLOGIC;
        } else if ($period == Constant::PERIOD_MONTHLY) {
            $activeChildrenRelationShip = $siteService->getActiveChildrenRelationShipMonthly($site, $startDate, $endDate) ;
            $dimDateTypeCode = SesDashboardIndicatorDimDateType::CODE_MONTHLY;
        }

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
                $startDate,
                $endDate);

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
        }

        return ['reportVersions' => $this->mappReportVersionData($reportVersionData, $activeChildrenRelationShip, $numberOfChildrenLeaves)];
    }

    /**
     * Map PartReport to WebApiReportVersionData objects
     *
     * @param $reportVersionData
     * @param $activeChildrenRelationShip
     * @param $numberOfChildrenLeaves
     * @return array
     */
    private function mappReportVersionData($reportVersionData, $activeChildrenRelationShip, $numberOfChildrenLeaves)
    {
        $results = array();
        $firstReport = null ; // keep first report and compare values with the following

        $partReportCount = 0;
        /** @var SesPartReport $report */
        foreach($reportVersionData as $report) {

            $partReportCount ++; // Only more recent report (First in list) can be validated or rejected

            $wrvd = new WebApiReportVersionData();
            $wrvd->id = $report->getId();
            $wrvd->FK_ReportId = $report->getFK_FullReportId();
            $wrvd->status = $report->getStatus();
            $wrvd->contactName = $report->getContactName();
            $wrvd->contactPhoneNumber = $report->getContactPhoneNumber();
            $wrvd->aggregate = $report->isAggregate();
            $wrvd->firstRejectionDate = $report->getFirstRejectionDate();
            $wrvd->firstValidationDate = $report->getFirstValidationDate();
            $wrvd->createdDate =  $report->getCreatedDate();
            $wrvd->canReject = $report->canBeRejected() && $partReportCount == 1;
            $wrvd->canValidate = $report->canBeValidated() && $partReportCount == 1;
            $wrvd->diseases = [];
            $wrvd->aggregations = [];

            /** @var SesReport $disease */
            foreach ($report->getReports() as $disease) {
                $wd = new WebApiDisease();
                $wd->disease = $disease->getDisease();
                $wd->id = $disease->getId();
                $wd->name = $disease->getDiseaseName();
                $wd->diseaseValues = [];

                /** @var SesReportValues $value */
                foreach ($disease->getReportValues() as $value) {
                    $wrv = new WebApiDiseaseValues();
                    $wrv->id = $value->getId();
                    $wrv->name = $value->getKey();
                    $wrv->value = $value->getValue();
                    $wrv->thresholdMaxValue =  $value->getThresholdMaxValue();

                    $wd->diseaseValues[] = $wrv;
                }

                $wrvd->diseases[] = $wd;

                if ($partReportCount == 2) { // compare first report with the following (which is the previously validated of refused)
                    $this->highlight($firstReport, $wd);
                }
            }

            // Sum total of participating HF for this version
            $totalNbOfParticipatingHC = 0;
            $totalNbOfHC = 0;

            /** @var SesDashboardSiteRelationShip $child */
            foreach ($activeChildrenRelationShip as $child) {
                $wa = new WebApiAggregation();
                $wa->siteName = $child->getName();
                $totalNbOfParticipatingHC += $wa->nbOfParticipatingHC = $report->getNbOfParticipatingHC($child->getSite());
                $totalNbOfHC += $wa->nbOfTotalHC = $numberOfChildrenLeaves[$child->getId()];

                $wrvd->aggregations[] = $wa;
            }

            $wrvd->totalNbOfParticipatingHC = $totalNbOfParticipatingHC;
            $wrvd->totalNbOfHC = $totalNbOfHC;

            $results[] = $wrvd;
            $firstReport = $wrvd;
        }

        return $results ;
    }

    /**
     * Compare diseases & value of on report with the disease $disease and mark the value is it is different
     *
     * @param WebApiReportVersionData $report
     * @param WebApiDisease $disease
     */
    private function highlight(WebApiReportVersionData $report, WebApiDisease $disease)
    {
        if ($report == null || $disease == null) {
            return ;
        }

        /** @var WebApiDisease $foundDisease */
        $foundDisease = null ;
        /** @var WebApiDisease $rDisease */
        foreach ($report->diseases as $rDisease) {
            if ($rDisease->disease == $disease->disease) {
                $foundDisease = $rDisease;
                break;
            }
        }

        if ($foundDisease == null) {
            return ;
        }

        /** @var WebApiDiseaseValues $dValue */
        foreach ($foundDisease->diseaseValues as $dValue) {
            $sValueFound = null ;

            /** @var WebApiDiseaseValues $sValue */
            foreach ($disease->diseaseValues as $sValue) {
                if ($dValue->name == $sValue->name && $dValue->period == $sValue->period) {
                    $sValueFound = $sValue;
                    break;
                }
            }

            if ($sValueFound == null || ($dValue->value != $sValueFound->value)) { // new Value or different
                $dValue->isDifferent = true ;

                if ($sValueFound) {
                    $dValue->isMore = $dValue->value > $sValueFound->value ;
                    $dValue->isLess = $dValue->value < $sValueFound->value ;
                }
            }
        }
    }
}