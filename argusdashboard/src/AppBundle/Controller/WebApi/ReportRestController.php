<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 3/8/2016
 * Time: 9:57 AM
 */

namespace AppBundle\Controller\WebApi;

use AppBundle\Constant\PhpReportConstant;
use AppBundle\Controller\BaseController;
use AppBundle\Entity\Gateway\GatewayQueue;
use AppBundle\Entity\SesAlert;
use AppBundle\Entity\SesDashboardContact;
use AppBundle\Entity\SesDashboardDisease;
use AppBundle\Entity\SesDashboardDiseaseValue;
use AppBundle\Entity\SesDashboardIndicatorDimDateType;
use AppBundle\Entity\SesDashboardSite;
use AppBundle\PhpReport\Header\RollupHeader;
use AppBundle\PhpReport\Header\ChartHeader;
use AppBundle\Services\DiseaseService;
use AppBundle\Services\Gateway\GatewayQueueService;
use AppBundle\Services\SiteService;
use AppBundle\Utils\DimDateHelper;
use AppBundle\Utils\Epidemiologic;
use AppBundle\Entity\Constant;

use FOS\RestBundle\Controller\Annotations\Get;

/**
 * Class ReportRestController
 * @package AppBundle\Controller\WebApi
 *
 * Web Api Controller to expose Data for Reports
 */
class ReportRestController extends BaseController
{
    /**
     * Return sprintf format for percentage decimal
     *
     * @return string
     */
    private function getPercentDecimalFormat()
    {
        $decimal_number = $this->getArgusReportPercentDecimalNumber();
        return '%0.' . $decimal_number . 'f';
    }

    public function getReportAction()
    {
        $result = array();
        $rows = array();

        $rows[] = array(
            'Charge Id' => '1',
            'Amount' => '1000',
            'Date' => '2016-15-12'
        );

        $rows[] = array(
            'Charge Id' => '2',
            'Amount' => '300',
            'Date' => '2017-15-12'
        );

        $rollup = new RollupHeader();
        $rollup->addRollupColumn('Charge Id', 'Total');
        $rollup->addRollupColumn('Amount', '{{sum}}');

        $result[] = array("title" => "MON TITRE", "warningMesssage" => 'WARNING', 'headers' => array($rollup->getHeader()), "rows" => $rows);

        return $result;
    }

//    function microtime_float()
//    {
//        list($usec, $sec) = explode(" ", microtime());
//        return ((float)$usec + (float)$sec);
//    }

    /**
     * GET Route annotation.
     * @Get("/reportRest/{reportType}/{startDate}/{endDate}/{siteId}/{diseaseIds}/{period}")
     *
     * @param $reportType
     * @param $startDate
     * @param $endDate
     * @param $siteId
     * @param $diseaseIds
     * @param $period
     * @return array
     */
    public function getReportParamAction($reportType, $startDate, $endDate, $siteId, $diseaseIds, $period)
    {
        // $time_start = $this->microtime_float();

        $result = array();

        switch ($reportType) {
            // Reports used for the Weekly Dashboard

            case "DashDiseasesWeeklyBySite" :
                $rollup = new RollupHeader();
                $rollup->addRollupColumn($this->trans(PhpReportConstant::COLUMNS_SITE), $this->trans(PhpReportConstant::COLUMNS_TOTAL_PERIOD));
                $rollup->addRollupColumn($this->trans(PhpReportConstant::COLUMNS_HEALTH_FACILITY_PARTICIPATING), '-');
                $rollup->addDefaultSum();

                // Multiple Dataset
                $result = $this->getWeeklyDiseasesReportDashboardBySite($startDate, $endDate, $siteId, $diseaseIds);

                for ($i = 0; $i < count($result); $i++) {
                    $result[$i]['headers'] = array($rollup->getHeader());
                }

                break;

            case "DashDiseasesWeeklyAggr" :
                return $this->getWeeklyDiseasesReportDashboardAggregation($startDate, $endDate, $siteId, $diseaseIds);
                break;

            case "DashAlerts" :
                return $this->getAlertsDashboard($startDate, $endDate, $siteId);
                break;

            // To override the Chart of ValidateCompletenessTimelinessByPeriod
            case "DashValidateCompletenessTimelinessByPeriod":
                switch ($period) {
                    case Constant::PERIOD_WEEKLY :
                        $dataSet = $this->getValidateWeeklyCompletenessTimelinessByWeek($startDate, $endDate, $siteId, false);
                        $title = $this->trans(PhpReportConstant::Number_of_Reports);
                        $columnTitle = PhpReportConstant::COLUMNS_WEEK;
                        break;
                    case Constant::PERIOD_MONTHLY :
                        $dataSet = $this->getValidateMonthlyCompletenessTimelinessByMonth($startDate, $endDate, $siteId, false);
                        $title = $this->trans(PhpReportConstant::Number_of_Reports);
                        $columnTitle = PhpReportConstant::COLUMNS_MONTH;
                        break;
                }

                // Add Dynamic Chart Header
                $chart = new ChartHeader(ChartHeader::LINE_CHART, '');
                $chart->addColumn($this->trans($columnTitle));
                $chart->addColumn($this->trans(PhpReportConstant::COLUMNS_COMPLETENESS_PERCENT));
                $chart->addColumn($this->trans(PhpReportConstant::COLUMNS_TIMELINESS_PERCENT), ChartHeader::LINE_DASHSTYLE_DASH);
                $chart->setAbscissaTitle($this->trans(PhpReportConstant::CHART_EPIDEMIOLOGIC_WEEK));
                $chart->setOrdinateTitle('%');
                $chart->setOrdinateMinValue(0);
                $chart->setOrdinateMaxValue(100);

                $result[] = array('title' => $title, 'headers' => array($chart->getHeader()), "rows" => $dataSet);

                break;

            // To override the Title of ValidateCompletenessTimelinessBySite
            case "DashValidateCompletenessTimelinessBySite" :
                switch ($period) {
                    case Constant::PERIOD_WEEKLY :
                        $dataSet = $this->getValidateWeeklyCompletenessTimelinessBySite($startDate, $endDate, $siteId);
                        break;
                    case Constant::PERIOD_MONTHLY :
                        $dataSet = $this->getValidateMonthlyCompletenessTimelinessBySite($startDate, $endDate, $siteId);
                        break;
                }

                // Add Dynamic Rollup Header
                $rollup = new RollupHeader();
                $rollup->addRollupColumn($this->trans(PhpReportConstant::COLUMNS_SITE), $this->trans(PhpReportConstant::COLUMNS_GLOBAL_PERFORMANCE));
                $rollup->addRollupColumn($this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED), $this->trans(PhpReportConstant::SUM_TWIG));
                $rollup->addRollupColumn($this->trans(PhpReportConstant::COLUMNS_ADDRESSED_REPORTS), $this->trans(PhpReportConstant::SUM_TWIG));
                $rollup->addRollupColumn($this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED_ON_TIME), $this->trans(PhpReportConstant::SUM_TWIG));
                $rollup->addRollupColumn($this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED_ADDRESSED_ON_TIME), $this->trans(PhpReportConstant::SUM_TWIG));
                $rollup->addRollupPercentColumn(
                    $this->trans(PhpReportConstant::COLUMNS_COMPLETENESS_PERCENT),
                    $this->trans(PhpReportConstant::COLUMNS_ADDRESSED_REPORTS),
                    $this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED),
                    $this->getArgusReportPercentDecimalNumber()
                );
                $rollup->addRollupPercentColumn(
                    $this->trans(PhpReportConstant::COLUMNS_TIMELINESS_PERCENT),
                    $this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED_ADDRESSED_ON_TIME),
                    $this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED_ON_TIME),
                    $this->getArgusReportPercentDecimalNumber()
                );

                $result[] = array('title' => '', 'headers' => array($rollup->getHeader()), "rows" => $dataSet);

                break;

            // To override the Title of SendCompletenessTimelinessBySite
            case "DashSendCompletenessTimelinessBySite" :
                switch ($period) {
                    case Constant::PERIOD_WEEKLY :
                        $dataSet = $this->getSendWeeklyCompletenessTimelinessBySite($startDate, $endDate, $siteId);
                        break;
                    case Constant::PERIOD_MONTHLY :
                        $dataSet = $this->getSendMonthlyCompletenessTimelinessBySite($startDate, $endDate, $siteId);
                        break;
                }

                // Add Dynamic Rollup Header
                $rollup = new RollupHeader();
                $rollup->addRollupColumn($this->trans(PhpReportConstant::COLUMNS_SITE), $this->trans(PhpReportConstant::COLUMNS_GLOBAL_PERFORMANCE));
                $rollup->addRollupColumn($this->trans(PhpReportConstant::COLUMNS_EXPECTED_REPORTS), $this->trans(PhpReportConstant::SUM_TWIG));
                $rollup->addRollupColumn($this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED), $this->trans(PhpReportConstant::SUM_TWIG));
                $rollup->addRollupColumn($this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED_ON_TIME), $this->trans(PhpReportConstant::SUM_TWIG));
                $rollup->addRollupPercentColumn(
                    $this->trans(PhpReportConstant::COLUMNS_COMPLETENESS_PERCENT),
                    $this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED),
                    $this->trans(PhpReportConstant::COLUMNS_EXPECTED_REPORTS),
                    $this->getArgusReportPercentDecimalNumber()
                );
                $rollup->addRollupPercentColumn(
                    $this->trans(PhpReportConstant::COLUMNS_TIMELINESS_PERCENT),
                    $this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED_ON_TIME),
                    $this->trans(PhpReportConstant::COLUMNS_EXPECTED_REPORTS),
                    $this->getArgusReportPercentDecimalNumber()
                );

                $result[] = array('title' => '', 'headers' => array($rollup->getHeader()), "rows" => $dataSet);
                break;

            // To override the Chart of SendCompletenessTimelinessByPeriod
            case "DashSendCompletenessTimelinessByPeriod" :
                switch ($period) {
                    case Constant::PERIOD_WEEKLY :
                        $dataSet = $this->getSendWeeklyCompletenessTimelinessByWeek($startDate, $endDate, $siteId, false);
                        $columnTitle = PhpReportConstant::COLUMNS_WEEK;
                        break;
                    case Constant::PERIOD_MONTHLY :
                        $dataSet = $this->getSendMonthlyCompletenessTimelinessByMonth($startDate, $endDate, $siteId, false);
                        $columnTitle = PhpReportConstant::COLUMNS_MONTH;
                        break;
                }

                // Add Dynamic Chart Header
                $chart = new ChartHeader(ChartHeader::LINE_CHART, '');
                $chart->addColumn($this->trans($columnTitle));
                $chart->addColumn($this->trans(PhpReportConstant::COLUMNS_COMPLETENESS_PERCENT));
                $chart->addColumn($this->trans(PhpReportConstant::COLUMNS_TIMELINESS_PERCENT), ChartHeader::LINE_DASHSTYLE_DASH);
                $chart->setAbscissaTitle($this->trans(PhpReportConstant::CHART_EPIDEMIOLOGIC_WEEK));
                $chart->setOrdinateTitle('%');
                $chart->setOrdinateMinValue(0);
                $chart->setOrdinateMaxValue(100);

                $result[] = array('title' => '', 'headers' => array($chart->getHeader()), "rows" => $dataSet);

                break;

            case "DashNumberOfCasesPerDiseaseByPeriod" :
                // Chart Header
                $chart = new ChartHeader(ChartHeader::LINE_CHART, '');
                $chart->setOrdinateTitle($this->trans(PhpReportConstant::CHART_EVENT_NUMBER));
                $chart->enabledDashStyle();

                switch ($period) {
                    case Constant::PERIOD_WEEKLY :
                        $chart->setAbscissaTitle($this->trans(PhpReportConstant::CHART_EPIDEMIOLOGIC_WEEK));
                        break;
                    case Constant::PERIOD_MONTHLY :
                        $chart->setAbscissaTitle($this->trans(PhpReportConstant::CHART_MONTH));
                        break;
                }

                $dataSet = $this->getNumberOfCasesPerDisease($startDate, $endDate, $siteId, $diseaseIds, $period, false);
                $result[] = array('title' => '', 'headers' => array($chart->getHeader()), "rows" => $dataSet);

                break;

            // Argus Reports :
            case "SendCompletenessTimelinessBySite" : // Send Completeness Timeliness Weekly & Monthly

                switch ($period) {
                    case Constant::PERIOD_WEEKLY :
                        $dataSet = $this->getSendWeeklyCompletenessTimelinessBySite($startDate, $endDate, $siteId);
                        $title = $this->trans(PhpReportConstant::Weekly_Reports);
                        $chartTitle = PhpReportConstant::Weekly_Completeness_And_Timeliness;
                        break;
                    case Constant::PERIOD_MONTHLY :
                        $dataSet = $this->getSendMonthlyCompletenessTimelinessBySite($startDate, $endDate, $siteId);
                        $title = $this->trans(PhpReportConstant::Monthly_Reports);
                        $chartTitle = PhpReportConstant::Monthly_Completeness_And_Timeliness;
                        break;
                }

                // Add Dynamic Rollup Header
                $rollup = new RollupHeader();
                $rollup->addRollupColumn($this->trans(PhpReportConstant::COLUMNS_SITE), $this->trans(PhpReportConstant::COLUMNS_GLOBAL_PERFORMANCE));
                $rollup->addRollupColumn($this->trans(PhpReportConstant::COLUMNS_EXPECTED_REPORTS), $this->trans(PhpReportConstant::SUM_TWIG));
                $rollup->addRollupColumn($this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED), $this->trans(PhpReportConstant::SUM_TWIG));
                $rollup->addRollupColumn($this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED_ON_TIME), $this->trans(PhpReportConstant::SUM_TWIG));
                $rollup->addRollupPercentColumn(
                    $this->trans(PhpReportConstant::COLUMNS_COMPLETENESS_PERCENT),
                    $this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED),
                    $this->trans(PhpReportConstant::COLUMNS_EXPECTED_REPORTS),
                    $this->getArgusReportPercentDecimalNumber()
                );
                $rollup->addRollupPercentColumn(
                    $this->trans(PhpReportConstant::COLUMNS_TIMELINESS_PERCENT),
                    $this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED_ON_TIME),
                    $this->trans(PhpReportConstant::COLUMNS_EXPECTED_REPORTS),
                    $this->getArgusReportPercentDecimalNumber()
                );

                // Add Dynamic Chart Header
                $chart = new ChartHeader(ChartHeader::COLUMN_CHART, $this->trans($chartTitle));
                $chart->addColumn($this->trans(PhpReportConstant::COLUMNS_SITE));
                $chart->addColumn($this->trans(PhpReportConstant::COLUMNS_COMPLETENESS_PERCENT));
                $chart->addColumn($this->trans(PhpReportConstant::COLUMNS_TIMELINESS_PERCENT));
                $chart->setAbscissaTitle($this->trans(PhpReportConstant::COLUMNS_SITE));
                $chart->setOrdinateTitle('%');
                $chart->setOrdinateMinValue(0);
                $chart->setOrdinateMaxValue(100);

                $result[] = array('title' => $title, 'headers' => array($chart->getHeader(), $rollup->getHeader()), "rows" => $dataSet);
                break;

            case "SendCompletenessTimelinessByPeriod" : // Send Completeness Timeliness by Period
                switch ($period) {
                    case Constant::PERIOD_WEEKLY :
                        $dataSet = $this->getSendWeeklyCompletenessTimelinessByWeek($startDate, $endDate, $siteId, true);
                        $title = $this->trans(PhpReportConstant::Number_of_Reports);
                        $columnTitle = PhpReportConstant::COLUMNS_WEEK;
                        $chartTitle = PhpReportConstant::Weekly_Completeness_And_Timeliness;
                        break;
                    case Constant::PERIOD_MONTHLY :
                        $dataSet = $this->getSendMonthlyCompletenessTimelinessByMonth($startDate, $endDate, $siteId, true);
                        $title = $this->trans(PhpReportConstant::Number_of_Reports);
                        $columnTitle = PhpReportConstant::COLUMNS_MONTH;
                        $chartTitle = PhpReportConstant::Monthly_Completeness_And_Timeliness;
                        break;
                }

                // Add Dynamic Rollup Header
                $rollup = new RollupHeader();
                $rollup->addRollupColumn($this->trans($columnTitle), $this->trans(PhpReportConstant::COLUMNS_GLOBAL_PERFORMANCE));
                $rollup->addRollupColumn($this->trans(PhpReportConstant::COLUMNS_EXPECTED_REPORTS), $this->trans(PhpReportConstant::SUM_TWIG));
                $rollup->addRollupColumn($this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED), $this->trans(PhpReportConstant::SUM_TWIG));
                $rollup->addRollupColumn($this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED_ON_TIME), $this->trans(PhpReportConstant::SUM_TWIG));
                $rollup->addRollupPercentColumn(
                    $this->trans(PhpReportConstant::COLUMNS_COMPLETENESS_PERCENT),
                    $this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED),
                    $this->trans(PhpReportConstant::COLUMNS_EXPECTED_REPORTS),
                    $this->getArgusReportPercentDecimalNumber()
                );
                $rollup->addRollupPercentColumn(
                    $this->trans(PhpReportConstant::COLUMNS_TIMELINESS_PERCENT),
                    $this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED_ON_TIME),
                    $this->trans(PhpReportConstant::COLUMNS_EXPECTED_REPORTS),
                    $this->getArgusReportPercentDecimalNumber()
                );

                // Add Dynamic Chart Header
                $chart = new ChartHeader(ChartHeader::LINE_CHART, $this->trans($chartTitle));
                $chart->addColumn($this->trans($columnTitle));
                $chart->addColumn($this->trans(PhpReportConstant::COLUMNS_COMPLETENESS_PERCENT));
                $chart->addColumn($this->trans(PhpReportConstant::COLUMNS_TIMELINESS_PERCENT), ChartHeader::LINE_DASHSTYLE_DASH);
                $chart->setAbscissaTitle($this->trans($columnTitle));
                $chart->setOrdinateTitle('%');
                $chart->setOrdinateMinValue(0);
                $chart->setOrdinateMaxValue(100);

                $result[] = array('title' => $title, 'headers' => array($chart->getHeader(), $rollup->getHeader()), "rows" => $dataSet);

                break;

            case "ValidateCompletenessTimelinessBySite" :

                switch ($period) {
                    case Constant::PERIOD_WEEKLY :
                        $dataSet = $this->getValidateWeeklyCompletenessTimelinessBySite($startDate, $endDate, $siteId);
                        $title = $this->trans(PhpReportConstant::Weekly_Reports);
                        $chartTitle = PhpReportConstant::Weekly_Completeness_And_Timeliness;
                        break;
                    case Constant::PERIOD_MONTHLY :
                        $dataSet = $this->getValidateMonthlyCompletenessTimelinessBySite($startDate, $endDate, $siteId);
                        $title = $this->trans(PhpReportConstant::Monthly_Reports);
                        $chartTitle = PhpReportConstant::Monthly_Completeness_And_Timeliness;
                        break;
                }

                // Add Dynamic Rollup Header
                $rollup = new RollupHeader();
                $rollup->addRollupColumn($this->trans(PhpReportConstant::COLUMNS_SITE), $this->trans(PhpReportConstant::COLUMNS_GLOBAL_PERFORMANCE));
                $rollup->addRollupColumn($this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED), $this->trans(PhpReportConstant::SUM_TWIG));
                $rollup->addRollupColumn($this->trans(PhpReportConstant::COLUMNS_ADDRESSED_REPORTS), $this->trans(PhpReportConstant::SUM_TWIG));
                $rollup->addRollupColumn($this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED_ON_TIME), $this->trans(PhpReportConstant::SUM_TWIG));
                $rollup->addRollupColumn($this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED_ADDRESSED_ON_TIME), $this->trans(PhpReportConstant::SUM_TWIG));
                $rollup->addRollupPercentColumn(
                    $this->trans(PhpReportConstant::COLUMNS_COMPLETENESS_PERCENT),
                    $this->trans(PhpReportConstant::COLUMNS_ADDRESSED_REPORTS),
                    $this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED),
                    $this->getArgusReportPercentDecimalNumber()
                );
                $rollup->addRollupPercentColumn(
                    $this->trans(PhpReportConstant::COLUMNS_TIMELINESS_PERCENT),
                    $this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED_ADDRESSED_ON_TIME),
                    $this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED_ON_TIME),
                    $this->getArgusReportPercentDecimalNumber()
                );

                // Add Dynamic Chart Header
                $chart = new ChartHeader(ChartHeader::COLUMN_CHART, $this->trans($chartTitle));
                $chart->addColumn($this->trans(PhpReportConstant::COLUMNS_SITE));
                $chart->addColumn($this->trans(PhpReportConstant::COLUMNS_COMPLETENESS_PERCENT));
                $chart->addColumn($this->trans(PhpReportConstant::COLUMNS_TIMELINESS_PERCENT));
                $chart->setAbscissaTitle($this->trans(PhpReportConstant::COLUMNS_SITE));
                $chart->setOrdinateTitle('%');
                $chart->setOrdinateMinValue(0);
                $chart->setOrdinateMaxValue(100);

                $result[] = array('title' => $title, 'headers' => array($chart->getHeader(), $rollup->getHeader()), "rows" => $dataSet);

                break;

            case "ValidateCompletenessTimelinessByPeriod" :
                switch ($period) {
                    case Constant::PERIOD_WEEKLY :
                        $dataSet = $this->getValidateWeeklyCompletenessTimelinessByWeek($startDate, $endDate, $siteId, true);
                        $title = $this->trans(PhpReportConstant::Number_of_Reports);
                        $columnTitle = PhpReportConstant::COLUMNS_WEEK;
                        $chartTitle = PhpReportConstant::Weekly_Completeness_And_Timeliness;
                        break;
                    case Constant::PERIOD_MONTHLY :
                        $dataSet = $this->getValidateMonthlyCompletenessTimelinessByMonth($startDate, $endDate, $siteId, true);
                        $title = $this->trans(PhpReportConstant::Number_of_Reports);
                        $columnTitle = PhpReportConstant::COLUMNS_MONTH;
                        $chartTitle = PhpReportConstant::Monthly_Completeness_And_Timeliness;
                        break;
                }

                // Add Dynamic Rollup Header
                $rollup = new RollupHeader();
                $rollup->addRollupColumn($this->trans($columnTitle), $this->trans(PhpReportConstant::COLUMNS_GLOBAL_PERFORMANCE));
                $rollup->addRollupColumn($this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED), $this->trans(PhpReportConstant::SUM_TWIG));
                $rollup->addRollupColumn($this->trans(PhpReportConstant::COLUMNS_ADDRESSED_REPORTS), $this->trans(PhpReportConstant::SUM_TWIG));
                $rollup->addRollupColumn($this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED_ON_TIME), $this->trans(PhpReportConstant::SUM_TWIG));
                $rollup->addRollupColumn($this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED_ADDRESSED_ON_TIME), $this->trans(PhpReportConstant::SUM_TWIG));
                $rollup->addRollupPercentColumn(
                    $this->trans(PhpReportConstant::COLUMNS_COMPLETENESS_PERCENT),
                    $this->trans(PhpReportConstant::COLUMNS_ADDRESSED_REPORTS),
                    $this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED),
                    $this->getArgusReportPercentDecimalNumber()
                );
                $rollup->addRollupPercentColumn(
                    $this->trans(PhpReportConstant::COLUMNS_TIMELINESS_PERCENT),
                    $this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED_ADDRESSED_ON_TIME),
                    $this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED_ON_TIME),
                    $this->getArgusReportPercentDecimalNumber()
                );

                // Add Dynamic Chart Header
                $chart = new ChartHeader(ChartHeader::LINE_CHART, $this->trans($chartTitle));
                $chart->addColumn($this->trans($columnTitle));
                $chart->addColumn($this->trans(PhpReportConstant::COLUMNS_COMPLETENESS_PERCENT));
                $chart->addColumn($this->trans(PhpReportConstant::COLUMNS_TIMELINESS_PERCENT), ChartHeader::LINE_DASHSTYLE_DASH);
                $chart->setAbscissaTitle($this->trans($columnTitle));
                $chart->setOrdinateTitle('%');
                $chart->setOrdinateMinValue(0);
                $chart->setOrdinateMaxValue(100);

                $result[] = array('title' => $title, 'headers' => array($chart->getHeader(), $rollup->getHeader()), "rows" => $dataSet);

                break;

            case "NumberOfCasesPerDiseaseByPeriod":

                /** @var SiteService $siteService */
                $siteService = $this->getSiteService();

                $dimDateStartId = DimDateHelper::getDimDateIdFromString($startDate);
                $dimDateEndId = DimDateHelper::getDimDateIdFromString($endDate);

                // Participating Percent
                $site = $this->getSiteService()->getById($siteId);

                if (!$site->isLeaf()) {
                    $sitesLeaf = $siteService->getLeafSiteIds($siteId, false, true, null, SesDashboardIndicatorDimDateType::CODE_DAILY, $dimDateStartId, $dimDateEndId, true);
                    $nbOfLeaves = count($sitesLeaf);
                    $nbOfParticipatingReports = $this->getDashboardService()->getNumberOfParticipatingReportPeriod($sitesLeaf, $startDate, $endDate, $period);
                }

                // Add Dynamic Rollup Header
                $rollup = new RollupHeader();

                switch ($period) {
                    case Constant::PERIOD_WEEKLY:
                        $weekNumber = Epidemiologic::GetNumberOfWeeks($startDate, $endDate, $epiFirstDay = $this->GetEpiFirstDay());

                        if (!$site->isLeaf() && $nbOfLeaves != 0) {
                            $participatingHF = round($nbOfParticipatingReports / ($nbOfLeaves * $weekNumber) * 100) . ' % (' . $nbOfParticipatingReports . '/' . ($nbOfLeaves * $weekNumber) . ')';
                        }

                        $rollup->addRollupColumn($this->trans(PhpReportConstant::COLUMNS_WEEK), $this->trans(PhpReportConstant::COLUMNS_GLOBAL_PERFORMANCE));

                        // Add Dynamic Chart Header
                        $chart = new ChartHeader(ChartHeader::COLUMN_CHART, $this->trans(PhpReportConstant::Number_of_Cases_per_Disease_per_Week));
                        $chart->setAbscissaTitle($this->trans(PhpReportConstant::COLUMNS_WEEK));
                        break;
                    case Constant::PERIOD_MONTHLY:
                        $monthNumber = Epidemiologic::GetNumberOfMonths($startDate, $endDate);

                        if (!$site->isLeaf() && $nbOfLeaves != 0) {
                            $participatingHF = round($nbOfParticipatingReports / ($nbOfLeaves * $monthNumber) * 100) . ' % (' . $nbOfParticipatingReports . '/' . ($nbOfLeaves * $monthNumber) . ')';
                        }

                        $rollup->addRollupColumn($this->trans(PhpReportConstant::COLUMNS_MONTH), $this->trans(PhpReportConstant::COLUMNS_GLOBAL_PERFORMANCE));

                        // Add Dynamic Chart Header
                        $chart = new ChartHeader(ChartHeader::COLUMN_CHART, $this->trans(PhpReportConstant::Number_of_Cases_per_Disease_per_Month));
                        $chart->setAbscissaTitle($this->trans(PhpReportConstant::COLUMNS_MONTH));
                        break;
                }

                $rollup->addDefaultSum();

                if (!$site->isLeaf()) {
                    $chart->setSubTitle($participatingHF . ' ' . $this->trans(PhpReportConstant::DESCRIPTION_HEALTH_FACILITY_PARTICIPATING));
                }
                $chart->setColumnToLine(2);
                $chart->setEnableLabels(true);
                $chart->setOrdinateTitle($this->trans(PhpReportConstant::CHART_CASES));

                $dataSet = $this->getNumberOfCasesPerDisease($startDate, $endDate, $siteId, $diseaseIds, $period, true);
                $title = '';
                $result[] = array('title' => $title, 'headers' => array($chart->getHeader(), $rollup->getHeader()), "rows" => $dataSet);

                break;

            // SMS traffic:
            case "SMSTrafficByWeek":

                // Add Dynamic Rollup Header
                $rollup = new RollupHeader();
                $rollup->addRollupColumn($this->trans(PhpReportConstant::COLUMNS_WEEK), $this->trans(PhpReportConstant::COLUMNS_GLOBAL_PERFORMANCE));
                $rollup->addDefaultSum();

                // Add Dynamic Chart Header
                $chart = new ChartHeader(ChartHeader::COLUMN_CHART, $this->trans(PhpReportConstant::SMS_Traffic_per_Gateway_per_Week));
                $chart->setAbscissaTitle($this->trans(PhpReportConstant::COLUMNS_WEEK));
                $chart->setColumnToLine(2);
                $chart->setEnableLabels(true);
                $chart->setOrdinateTitle($this->trans(PhpReportConstant::CHART_SMS));

                $dataSet = $this->getSMSTrafficPerGateway($startDate, $endDate, $siteId, $period);

                $title = $this->trans(PhpReportConstant::SMS_Traffic_per_Gateway_per_Week);
                $result[] = array('title' => $title, 'headers' => array($chart->getHeader(), $rollup->getHeader()), "rows" => $dataSet);

                break;

            default:
                return array('Error' => 'InvalidReportName');
        }

        //$time_end = $this->microtime_float();
        //$time = $time_end - $time_start;
        //$this->getLogger()->addError("$reportType Executed in $time seconds");

        return $result;
    }

    /**
     * ARGUS Send Weekly Completeness & Timeliness by Site
     *
     * @param $startDate
     * @param $endDate
     * @param $siteId
     * @return array
     */
    private function getSendWeeklyCompletenessTimelinessBySite($startDate, $endDate, $siteId)
    {
        // DashSendCompletenessTimelinessBySite Executed in 2.9561660289764 seconds
        // DashSendCompletenessTimelinessBySite Executed in 1.3806462287903 seconds

        /** @var SiteService $siteService */
        $siteService = $this->getSiteService();

        $dimDateStartId = DimDateHelper::getDimDateIdFromString($startDate);
        $dimDateEndId = DimDateHelper::getDimDateIdFromString($endDate);

        if (!$siteService->isLeaf($siteId)) {
            $siteList = $siteService->getChildrenSiteIds($siteId, false, true, 1, false, null, $dimDateStartId, $dimDateEndId);
        } else {
            $siteList[] = $siteId;
        }

        $rows = array();

        for ($s = 0; $s < count($siteList); $s++) {
            $siteId = $siteList[$s];

            $line = array();
            /** @var SesDashboardSite $site */
            $site = $siteService->find($siteId);
            $line[$this->trans(PhpReportConstant::COLUMNS_SITE)] = $site->getName();

            // Number of Active Leaves under this site or this site if it is a leaf
            $leafsIds = $siteService->getLeafSiteIds($siteId, false, true, null, SesDashboardIndicatorDimDateType::CODE_WEEKLY_EPIDEMIOLOGIC, $dimDateStartId, $dimDateEndId, true);

            $expectedReports = $this->getDashboardService()->getNumberOfExpectedWeeklyReportPeriod($leafsIds, $startDate, $endDate);
            $line[$this->trans(PhpReportConstant::COLUMNS_EXPECTED_REPORTS)] = $expectedReports;

            // Number of received reports for each leaf
            $receivedReports = $this->getDashboardService()->getNumberOfReceivedWeeklyReportPeriod($leafsIds, $startDate, $endDate);
            $line[$this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED)] = $receivedReports;

            //Number of received report ON TIME for each leaf
            $receivedOnTimeReports = $this->getDashboardService()->getNumberOfReceivedOnTimeWeeklyReportPeriod($leafsIds, $startDate, $endDate);
            $line[$this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED_ON_TIME)] = $receivedOnTimeReports;

            //  Timeliness / Completeness
            $completeness = $timeliness = '-';
            if ($expectedReports != null && $expectedReports > 0) {
                $timeliness = round($receivedOnTimeReports / $expectedReports * 100, 2);
                $completeness = round($receivedReports / $expectedReports * 100, 2);
            }

            $line[$this->trans(PhpReportConstant::COLUMNS_COMPLETENESS_PERCENT)] = sprintf($this->getPercentDecimalFormat(), $completeness);
            $line[$this->trans(PhpReportConstant::COLUMNS_TIMELINESS_PERCENT)] = sprintf($this->getPercentDecimalFormat(), $timeliness);

            $rows[] = $line;
        }

        return $rows;
    }

    /**
     * ARGUS Send Monthly Completeness & Timeliness by Site
     *
     * @param $startDate
     * @param $endDate
     * @param $siteId
     * @return array
     */
    private function getSendMonthlyCompletenessTimelinessBySite($startDate, $endDate, $siteId)
    {
        /** @var SiteService $siteService */
        $siteService = $this->getSiteService();

        $dimDateStartId = DimDateHelper::getDimDateIdFromString($startDate);
        $dimDateEndId = DimDateHelper::getDimDateIdFromString($endDate);

        if (!$siteService->isLeaf($siteId)) {
            $siteList = $siteService->getChildrenSiteIds($siteId, false, true, 1, false, null, $dimDateStartId, $dimDateEndId);
        } else {
            $siteList[] = $siteId;
        }

        $rows = array();

        for ($s = 0; $s < count($siteList); $s++) {
            $siteId = $siteList[$s];

            /** @var SesDashboardSite $site */
            $site = $siteService->find($siteId);

            $line = array();
            $line[$this->trans(PhpReportConstant::COLUMNS_SITE)] = $site->getName();

            // Number of Active Leaves under this site or this site if it is a leaf
            $leafsIds = $siteService->getLeafSiteIds($siteId, false, true, null, SesDashboardIndicatorDimDateType::CODE_MONTHLY, $dimDateStartId, $dimDateEndId, true);

            $expectedReports = $this->getDashboardService()->getNumberOfExpectedMonthlyReportPeriod($leafsIds, $startDate, $endDate);
            $line[$this->trans(PhpReportConstant::COLUMNS_EXPECTED_REPORTS)] = $expectedReports;

            // Number of received reports for each leaf
            $receivedReports = $this->getDashboardService()->getNumberOfReceivedMonthlyReportPeriod($leafsIds, $startDate, $endDate);
            $line[$this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED)] = $receivedReports;

            //Number of received report ON TIME for each leaf
            $receivedOnTimeReports = $this->getDashboardService()->getNumberOfReceivedOnTimeMonthlyReportPeriod($leafsIds, $startDate, $endDate);
            $line[$this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED_ON_TIME)] = $receivedOnTimeReports;

            //  Timeliness / Completeness
            $completeness = $timeliness = '-';
            if ($expectedReports != null && $expectedReports > 0) {
                $timeliness = round($receivedOnTimeReports / $expectedReports * 100, 2);
                $completeness = round($receivedReports / $expectedReports * 100, 2);
            }

            $line[$this->trans(PhpReportConstant::COLUMNS_COMPLETENESS_PERCENT)] = sprintf($this->getPercentDecimalFormat(), $completeness);
            $line[$this->trans(PhpReportConstant::COLUMNS_TIMELINESS_PERCENT)] = sprintf($this->getPercentDecimalFormat(), $timeliness);

            $rows[] = $line;
        }

        return $rows;
    }

    /**
     * ARGUS Send Weekly Completeness & Timeliness by Week
     *
     * @param $startDate
     * @param $endDate
     * @param $siteId
     * @param bool $displayYear
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    private function getSendWeeklyCompletenessTimelinessByWeek($startDate, $endDate, $siteId, $displayYear = true)
    {
        // DashSendCompletenessTimelinessByPeriod Executed in 2.9583580493927 seconds
        // DashSendCompletenessTimelinessByPeriod Executed in 1.671707868576 seconds

        /** @var SiteService $siteService */
        $siteService = $this->getSiteService();

        $dimDateStartId = DimDateHelper::getDimDateIdFromString($startDate);
        $dimDateEndId = DimDateHelper::getDimDateIdFromString($endDate);

        $epiFirstDay = $this->GetEpiFirstDay();
        $date = strtotime($startDate);

        // Number of Active Leaf under this site during this period
        $leafsIds = $siteService->getLeafSiteIds($siteId, false, true, null, SesDashboardIndicatorDimDateType::CODE_WEEKLY_EPIDEMIOLOGIC, $dimDateStartId, $dimDateEndId, false);

        if (sizeof($leafsIds) == 0) {
            // no leaves, I'm the leaf
            $leafsIds[] = $siteId;
        }

        $rows = array();

        while ($date <= strtotime($endDate)) {
            $epi = Epidemiologic::Timestamp2Epi($date, $epiFirstDay);
            $weekNumber = $epi['Week'];
            $year = $epi['Year'];

            $startDate = date('Y-m-d', $date);
            $enDate = date('Y-m-d', strtotime("+6 days", $date));

            $expectedReports = $this->getDashboardService()->getNumberOfExpectedWeeklyReportPeriod($leafsIds, $startDate, $enDate);
            $line = array();
            $line[$this->trans(PhpReportConstant::COLUMNS_WEEK)] = $this->trans(PhpReportConstant::WORD_WEEK)
                . $weekNumber
                . ($displayYear ? ' - ' . $year : '');

            // Expected Reports
            $line[$this->trans(PhpReportConstant::COLUMNS_EXPECTED_REPORTS)] = $expectedReports;

            // Received Reports
            $nbReceivedReport = $this->getDashboardService()->getNumberOfReceivedWeeklyReportPeriod($leafsIds, $startDate, $enDate);
            $line[$this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED)] = $nbReceivedReport;

            // Received On Time Reports
            $nbReceivedReportOnTime = $this->getDashboardService()->getNumberOfReceivedOnTimeWeeklyReportPeriod($leafsIds, $startDate, $enDate);
            $line[$this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED_ON_TIME)] = $nbReceivedReportOnTime;

            // Timeliness & Completeness
            $completeness = $timeliness = '-';
            if ($expectedReports != null && $expectedReports > 0) {
                $timeliness = round($nbReceivedReportOnTime / $expectedReports * 100, 2);
                $completeness = round($nbReceivedReport / $expectedReports * 100, 2);
            }

            $line[$this->trans(PhpReportConstant::COLUMNS_COMPLETENESS_PERCENT)] = sprintf($this->getPercentDecimalFormat(), $completeness);
            $line[$this->trans(PhpReportConstant::COLUMNS_TIMELINESS_PERCENT)] = sprintf($this->getPercentDecimalFormat(), $timeliness);

            $rows[] = $line;
            $date = strtotime("+7 days", $date);
        }

        return $rows;
    }

    /**
     * ARGUS Send Monthly Completeness & Timeliness by Month
     *
     * @param $startDate
     * @param $endDate
     * @param $siteId
     * @param bool $displayYear
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    private function getSendMonthlyCompletenessTimelinessByMonth($startDate, $endDate, $siteId, $displayYear = true)
    {
        /** @var SiteService $siteService */
        $siteService = $this->getSiteService();

        $dimDateStartId = DimDateHelper::getDimDateIdFromString($startDate);
        $dimDateEndId = DimDateHelper::getDimDateIdFromString($endDate);

        $date = strtotime($startDate);

        // Number of Active Leaf under this site during this period
        $leafsIds = $siteService->getLeafSiteIds($siteId, false, true, null, SesDashboardIndicatorDimDateType::CODE_MONTHLY, $dimDateStartId, $dimDateEndId, false);

        if (sizeof($leafsIds) == 0) {
            // no leaves, I'm the leaf
            $leafsIds[] = $siteId;
        }

        $rows = array();

        while ($date <= strtotime($endDate)) {
            $line = array();

            $startDate = date('Y-m-d', $date);
            $enDate = date('Y-m-d', strtotime("- 1 day", strtotime("+1 month", $date)));

            $expectedReports = $this->getDashboardService()->getNumberOfExpectedMonthlyReportPeriod($leafsIds, $startDate, $enDate);

            $line[$this->trans(PhpReportConstant::COLUMNS_MONTH)] = $this->getTranslator()->trans(Epidemiologic::GetMonthName($date))
                . ($displayYear ? ' - ' . Epidemiologic::GetYear($date) : '');

            // Expected Reports
            $line[$this->trans(PhpReportConstant::COLUMNS_EXPECTED_REPORTS)] = $expectedReports;

            // Received Reports
            $nbReceivedReport = $this->getDashboardService()->getNumberOfReceivedMonthlyReportPeriod($leafsIds, $startDate, $enDate);
            $line[$this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED)] = $nbReceivedReport;

            // Received On Time Reports
            $nbReceivedReportOnTime = $this->getDashboardService()->getNumberOfReceivedOnTimeMonthlyReportPeriod($leafsIds, $startDate, $enDate);
            $line[$this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED_ON_TIME)] = $nbReceivedReportOnTime;

            // Timeliness & Completeness
            $completeness = $timeliness = '-';
            if ($expectedReports != null && $expectedReports > 0) {
                $timeliness = round($nbReceivedReportOnTime / $expectedReports * 100, 2);
                $completeness = round($nbReceivedReport / $expectedReports * 100, 2);
            }

            $line[$this->trans(PhpReportConstant::COLUMNS_COMPLETENESS_PERCENT)] = sprintf($this->getPercentDecimalFormat(), $completeness);
            $line[$this->trans(PhpReportConstant::COLUMNS_TIMELINESS_PERCENT)] = sprintf($this->getPercentDecimalFormat(), $timeliness);

            $rows[] = $line;
            $date = strtotime("+1 month", $date);
        }

        return $rows;
    }

    /**
     * ARGUS Validate Weekly Completeness & Timeliness by Site
     *
     * @param $startDate
     * @param $endDate
     * @param $siteId
     * @return array
     */
    private function getValidateWeeklyCompletenessTimelinessBySite($startDate, $endDate, $siteId)
    {
        /** @var SiteService $siteService */
        $siteService = $this->getSiteService();

        $dimDateStartId = DimDateHelper::getDimDateIdFromString($startDate);
        $dimDateEndId = DimDateHelper::getDimDateIdFromString($endDate);

        /** @var SesDashboardSite $site */
        $site = $siteService->getById($siteId);
        $validationDelay = $site->getWeeklyTimelinessMinutes();

        if (!$siteService->isLeaf($siteId)) {
            $siteList = $siteService->getChildrenSiteIds($siteId, false, true, 1, false, null, $dimDateStartId, $dimDateEndId);
        } else {
            $siteList[] = $siteId;
        }

        $rows = array();
        $line = array();
        $line[$this->trans(PhpReportConstant::COLUMNS_SITE)] = $site->getName();

        // Number of received reports
        $receivedReports = $this->getDashboardService()->getNumberOfReceivedWeeklyReportPeriod($siteList, $startDate, $endDate);
        // Number of addressed received reports (validated or rejected)
        $addressedReports = $this->getDashboardService()->getNumberOfAddressedWeeklyReportPeriod($siteList, $startDate, $endDate);

        // If One children is leaf, others are leaves too.
        if ($siteService->isLeaf($siteList[0])) {
            $receivedOnTimeReports = $this->getDashboardService()->getNumberOfReceivedOnTimeWeeklyReportPeriod($siteList, $startDate, $endDate);
            $receivedAndAddressedOnTimeReports = $this->getDashboardService()->getNumberOfReceivedAndAddressedOnTimeWeeklyReportPeriod($siteList, $startDate, $endDate, $validationDelay);
        } else {
            $receivedOnTimeReports = $this->getDashboardService()->getNumberOfCreatedWeeklyReportOnTimePeriod($siteList, $startDate, $endDate);
            $receivedAndAddressedOnTimeReports = $this->getDashboardService()->getNumberOfCreatedAndAddressedWeeklyReportOnTimePeriod($siteList, $startDate, $endDate, $validationDelay);
        }

        $line[$this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED)] = $receivedReports;
        $line[$this->trans(PhpReportConstant::COLUMNS_ADDRESSED_REPORTS)] = $addressedReports;
        $line[$this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED_ON_TIME)] = $receivedOnTimeReports;
        $line[$this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED_ADDRESSED_ON_TIME)] = $receivedAndAddressedOnTimeReports;

        //  Timeliness / Completeness
        $completeness = $timeliness = '-';
        if ($receivedReports != null && $receivedReports > 0) {
            $completeness = round($addressedReports / $receivedReports * 100, 2);
            $completeness = sprintf($this->getPercentDecimalFormat(), $completeness);
        }
        if ($receivedOnTimeReports != null && $receivedOnTimeReports > 0) {
            $timeliness = round($receivedAndAddressedOnTimeReports / $receivedOnTimeReports * 100, 2);
            $timeliness = sprintf($this->getPercentDecimalFormat(), $timeliness);
        }

        $line[$this->trans(PhpReportConstant::COLUMNS_COMPLETENESS_PERCENT)] = $completeness;
        $line[$this->trans(PhpReportConstant::COLUMNS_TIMELINESS_PERCENT)] = $timeliness;

        $rows[] = $line;

        return $rows;
    }

    /**
     * ARGUS Validate Monthly Completeness & Timeliness by Site
     *
     * @param $startDate
     * @param $endDate
     * @param $siteId
     * @return array
     */
    private function getValidateMonthlyCompletenessTimelinessBySite($startDate, $endDate, $siteId)
    {
        /** @var SiteService $siteService */
        $siteService = $this->getSiteService();

        $dimDateStartId = DimDateHelper::getDimDateIdFromString($startDate);
        $dimDateEndId = DimDateHelper::getDimDateIdFromString($endDate);

        /** @var SesDashboardSite $site */
        $site = $siteService->getById($siteId);
        $validationDelay = $site->getMonthlyTimelinessMinutes();

        if (!$siteService->isLeaf($siteId)) {
            $siteList = $siteService->getChildrenSiteIds($siteId, false, true, 1, false, null, $dimDateStartId, $dimDateEndId);
        } else {
            $siteList[] = $siteId;
        }

        $rows = array();
        $line = array();
        $line[$this->trans(PhpReportConstant::COLUMNS_SITE)] = $site->getName();

        // Number of received reports
        $receivedReports = $this->getDashboardService()->getNumberOfReceivedMonthlyReportPeriod($siteId, $startDate, $endDate);
        // Number of addressed received reports (validated or rejected)
        $addressedReports = $this->getDashboardService()->getNumberOfAddressedMonthlyReportPeriod($siteId, $startDate, $endDate);

        // If One children is leaf, others are leaves too.
        if ($siteService->isLeaf($siteList[0])) {
            $receivedOnTimeReports = $this->getDashboardService()->getNumberOfReceivedOnTimeMonthlyReportPeriod($siteList, $startDate, $endDate);
            $receivedAndAddressedOnTimeReports = $this->getDashboardService()->getNumberOfReceivedAndAddressedOnTimeMonthlyReportPeriod($siteList, $startDate, $endDate, $validationDelay);
        } else {
            $receivedOnTimeReports = $this->getDashboardService()->getNumberOfCreatedMonthlyReportOnTimePeriod($siteList, $startDate, $endDate);
            $receivedAndAddressedOnTimeReports = $this->getDashboardService()->getNumberOfCreatedAndAddressedMonthlyReportOnTimePeriod($siteList, $startDate, $endDate, $validationDelay);
        }

        $line[$this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED)] = $receivedReports;
        $line[$this->trans(PhpReportConstant::COLUMNS_ADDRESSED_REPORTS)] = $addressedReports;
        $line[$this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED_ON_TIME)] = $receivedOnTimeReports;
        $line[$this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED_ADDRESSED_ON_TIME)] = $receivedAndAddressedOnTimeReports;

        //  Timeliness / Completeness
        $completeness = $timeliness = '-';
        if ($receivedReports != null && $receivedReports > 0) {
            $completeness = round($addressedReports / $receivedReports * 100, 2);
            $completeness = sprintf($this->getPercentDecimalFormat(), $completeness);
        }
        if ($receivedOnTimeReports != null && $receivedOnTimeReports > 0) {
            $timeliness = round($receivedAndAddressedOnTimeReports / $receivedOnTimeReports * 100, 2);
            $timeliness = sprintf($this->getPercentDecimalFormat(), $timeliness);
        }

        $line[$this->trans(PhpReportConstant::COLUMNS_COMPLETENESS_PERCENT)] = $completeness;
        $line[$this->trans(PhpReportConstant::COLUMNS_TIMELINESS_PERCENT)] = $timeliness;

        $rows[] = $line;

        return $rows;
    }

    /**
     * ARGUS Validate Weekly Completeness & Timeliness by Week
     *
     * @param $startDate
     * @param $endDate
     * @param $siteId
     * @param bool $displayYear
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    private function getValidateWeeklyCompletenessTimelinessByWeek($startDate, $endDate, $siteId, $displayYear = true)
    {
        /** @var SiteService $siteService */
        $siteService = $this->getSiteService();

        $epiFirstDay = $this->GetEpiFirstDay();
        $date = strtotime($startDate);

        /** @var SesDashboardSite $site */
        $site = $siteService->getById($siteId);
        $validationDelay = $site->getWeeklyTimelinessMinutes();

        $dimDateStartId = DimDateHelper::getDimDateIdFromString($startDate);
        $dimDateEndId = DimDateHelper::getDimDateIdFromString($endDate);

        if (!$siteService->isLeaf($siteId)) {
            $siteList = $siteService->getChildrenSiteIds($siteId, false, true, 1, false, null, $dimDateStartId, $dimDateEndId);
        } else {
            $siteList[] = $siteId;
        }

        $rows = array();

        while ($date <= strtotime($endDate)) {
            $periodStart = date('Y-m-d', $date);
            $periodEnd = date('Y-m-d', strtotime("+6 days", $date));

            $epi = Epidemiologic::Timestamp2Epi($date, $epiFirstDay);
            $weekNumber = $epi['Week'];
            $year = $epi['Year'];

            $line = array();
            $line[$this->trans(PhpReportConstant::COLUMNS_WEEK)] = $this->trans(PhpReportConstant::WORD_WEEK)
                . $weekNumber
                . ($displayYear ? ' - ' . $year : '');

            // Received Reports
            $receivedReports = $this->getDashboardService()->getNumberOfReceivedWeeklyReportPeriod($siteList, $periodStart, $periodEnd);
            $line[$this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED)] = $receivedReports;

            // Number of addressed received reports (validated or rejected)
            $addressedReports = $this->getDashboardService()->getNumberOfAddressedWeeklyReportPeriod($siteList, $periodStart, $periodEnd);
            $line[$this->trans(PhpReportConstant::COLUMNS_ADDRESSED_REPORTS)] = $addressedReports;

            // If One children is leaf, others are leaves too.
            // Number of received report ON TIME
            // Number of received and addressed report ON TIME
            if ($siteService->isLeaf($siteList[0])) {
                $receivedOnTimeReports = $this->getDashboardService()->getNumberOfReceivedOnTimeWeeklyReportPeriod($siteList, $periodStart, $periodEnd);
                $receivedAndAddressedOnTimeReports = $this->getDashboardService()->getNumberOfReceivedAndAddressedOnTimeWeeklyReportPeriod($siteList, $periodStart, $periodEnd, $validationDelay);
            } else {
                $receivedOnTimeReports = $this->getDashboardService()->getNumberOfCreatedWeeklyReportOnTimePeriod($siteList, $periodStart, $periodEnd);
                $receivedAndAddressedOnTimeReports = $this->getDashboardService()->getNumberOfCreatedAndAddressedWeeklyReportOnTimePeriod($siteList, $periodStart, $periodEnd, $validationDelay);
            }

            $line[$this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED_ON_TIME)] = $receivedOnTimeReports;
            $line[$this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED_ADDRESSED_ON_TIME)] = $receivedAndAddressedOnTimeReports;

            //  Timeliness / Completeness
            $completeness = $timeliness = '-';
            if ($receivedReports != null && $receivedReports > 0) {
                $completeness = round($addressedReports / $receivedReports * 100, 2);
                $completeness = sprintf($this->getPercentDecimalFormat(), $completeness);
            }
            if ($receivedOnTimeReports != null && $receivedOnTimeReports > 0) {
                $timeliness = round($receivedAndAddressedOnTimeReports / $receivedOnTimeReports * 100, 2);
                $timeliness = sprintf($this->getPercentDecimalFormat(), $timeliness);
            }

            $line[$this->trans(PhpReportConstant::COLUMNS_COMPLETENESS_PERCENT)] = $completeness;
            $line[$this->trans(PhpReportConstant::COLUMNS_TIMELINESS_PERCENT)] = $timeliness;

            $rows[] = $line;
            $date = strtotime("+7 days", $date);
        }

        return $rows;
    }

    /**
     * ARGUS Validate Monthly Completeness & Timeliness by Month
     *
     * @param $startDate
     * @param $endDate
     * @param $siteId
     * @param bool $displayYear
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    private function getValidateMonthlyCompletenessTimelinessByMonth($startDate, $endDate, $siteId, $displayYear = true)
    {
        /** @var SiteService $siteService */
        $siteService = $this->getSiteService();

        $date = strtotime($startDate);

        /** @var SesDashboardSite $site */
        $site = $this->getSiteService()->getById($siteId);
        $validationDelay = $site->getMonthlyTimelinessMinutes();

        $dimDateStartId = DimDateHelper::getDimDateIdFromString($startDate);
        $dimDateEndId = DimDateHelper::getDimDateIdFromString($endDate);

        if (!$siteService->isLeaf($siteId)) {
            $siteList = $siteService->getChildrenSiteIds($siteId, false, true, 1, false, null, $dimDateStartId, $dimDateEndId);
        } else {
            $siteList[] = $siteId;
        }

        $rows = array();

        while ($date <= strtotime($endDate)) {
            $periodStart = date('Y-m-d', $date);
            $periodEnd = date('Y-m-d', strtotime("+27 days", $date));

            $line = array();

            $line[$this->trans(PhpReportConstant::COLUMNS_MONTH)] = $this->getTranslator()->trans(Epidemiologic::GetMonthName($date))
                . ($displayYear ? ' - ' . Epidemiologic::GetYear($date) : '');

            // Received Reports
            $receivedReports = $this->getDashboardService()->getNumberOfReceivedMonthlyReportPeriod($siteList, $periodStart, $periodEnd);
            $line[$this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED)] = $receivedReports;

            // Number of addressed received reports (validated or rejected)
            $addressedReports = $this->getDashboardService()->getNumberOfAddressedMonthlyReportPeriod($siteList, $periodStart, $periodEnd);
            $line[$this->trans(PhpReportConstant::COLUMNS_ADDRESSED_REPORTS)] = $addressedReports;

            // If One children is leaf, others are leaves too.
            // Number of received report ON TIME
            // Number of received and addressed report ON TIME
            if ($siteService->isLeaf($siteList[0])) {
                $receivedOnTimeReports = $this->getDashboardService()->getNumberOfReceivedOnTimeMonthlyReportPeriod($siteList, $periodStart, $periodEnd);
                $receivedAndAddressedOnTimeReports = $this->getDashboardService()->getNumberOfReceivedAndAddressedOnTimeMonthlyReportPeriod($siteList, $periodStart, $periodEnd, $validationDelay);
            } else {
                $receivedOnTimeReports = $this->getDashboardService()->getNumberOfCreatedMonthlyReportOnTimePeriod($siteList, $periodStart, $periodEnd);
                $receivedAndAddressedOnTimeReports = $this->getDashboardService()->getNumberOfCreatedAndAddressedMonthlyReportOnTimePeriod($siteList, $periodStart, $periodEnd, $validationDelay);
            }

            $line[$this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED_ON_TIME)] = $receivedOnTimeReports;
            $line[$this->trans(PhpReportConstant::COLUMNS_REPORTS_RECEIVED_ADDRESSED_ON_TIME)] = $receivedAndAddressedOnTimeReports;

            //  Timeliness / Completeness
            $completeness = $timeliness = '-';
            if ($receivedReports != null && $receivedReports > 0) {
                $completeness = round($addressedReports / $receivedReports * 100, 2);
                $completeness = sprintf($this->getPercentDecimalFormat(), $completeness);
            }
            if ($receivedOnTimeReports != null && $receivedOnTimeReports > 0) {
                $timeliness = round($receivedAndAddressedOnTimeReports / $receivedOnTimeReports * 100, 2);
                $timeliness = sprintf($this->getPercentDecimalFormat(), $timeliness);
            }

            $line[$this->trans(PhpReportConstant::COLUMNS_COMPLETENESS_PERCENT)] = $completeness;
            $line[$this->trans(PhpReportConstant::COLUMNS_TIMELINESS_PERCENT)] = $timeliness;

            $rows[] = $line;
            $date = strtotime("+1 month", $date);
        }

        return $rows;
    }

    /**
     * Translate key coming from "reports" translation files
     *
     * @param $key
     * @return mixed
     */
    private function trans($key)
    {
        return $this->getTranslator()->trans($key, array(), 'reports');
    }

    /**
     * Get Weekly Disease
     * If Level District (Leaf +1) has validated the report, then data are considered as validated.
     *
     * @param $startDate
     * @param $endDate
     * @param $siteId
     * @param $diseaseIds
     * @return array
     */
    private function getWeeklyDiseasesReportDashboardBySite($startDate, $endDate, $siteId, $diseaseIds)
    {
        // DashDiseasesWeeklyBySite Executed in 2.0048539638519 seconds
        // DashDiseasesWeeklyBySite Executed in 1.8737900257111 seconds
        $cacheSiteNames = [];
        $cacheLeafsIds = [];
        $cacheNumberOfParticipatingReports = [];

        /** @var SiteService $siteService */
        $siteService = $this->getSiteService();
        /** @var DiseaseService $diseaseService */
        $diseaseService = $this->getDiseaseService();

        $dimDateStartId = DimDateHelper::getDimDateIdFromString($startDate);
        $dimDateEndId = DimDateHelper::getDimDateIdFromString($endDate);

        $epiFirstDay = $this->GetEpiFirstDay();
        $epiTo = Epidemiologic::Timestamp2Epi(strtotime($endDate), $epiFirstDay);
        $weekNumberTo = $epiTo['Week'];
        $yearTo = $epiTo['Year'];

        $additionalColumn = true;
        if ($siteService->isLeaf($siteId)) {    // Case HF
            $additionalColumn = false;
            $siteList[] = $siteId;
        } else {
            $siteList = $siteService->getChildrenSiteIds($siteId, false, true, 1, false, null, $dimDateStartId, $dimDateEndId);
        }

        $diseasesAll = $diseaseService->getDiseasesPerPeriod(Constant::PERIOD_WEEKLY, false);
        $diseasesList = $this->getSplitDiseasesList($diseasesAll, 14);

        $result = array();
        for ($i = 1; $i <= count($diseasesList); $i++) {
            $rows = array();
            $diseases = $diseasesList[$i];

            for ($s = 0; $s < count($siteList); $s++) {
                $siteId = $siteList[$s];

                $line = array();
                // Cache siteNames
                if (!isset($cacheSiteNames[$siteId])) {
                    $cacheSiteNames[$siteId] = $siteService->getById($siteId)->getName();
                }
                $line[$this->trans(PhpReportConstant::COLUMNS_SITE)] = $cacheSiteNames[$siteId];

                // Cache Leaves
                if (!isset($cacheLeafsIds[$siteId])) {
                    $cacheLeafsIds[$siteId] = $siteService->getLeafSiteIds($siteId, false, false, null, SesDashboardIndicatorDimDateType::CODE_WEEKLY_EPIDEMIOLOGIC, $dimDateStartId, $dimDateEndId, true);
                }
                $leafsIds = $cacheLeafsIds[$siteId];

                // Add line if site is not a Leaf with % of participating HF
                if ($additionalColumn) {
                    $nbOfHF = count($leafsIds);
                    // Cache Number of Participating Reports
                    if (!isset($cacheNumberOfParticipatingReports[$siteId])) {
                        $cacheNumberOfParticipatingReports[$siteId] = $this->getDashboardService()->getNumberOfParticipatingReportPeriod($leafsIds, $startDate, $endDate, Constant::PERIOD_WEEKLY);
                    }
                    $nbOfParticipatingReports = $cacheNumberOfParticipatingReports[$siteId];

                    $hfParticipating = "-";
                    if (isset($nbOfHF) && $nbOfHF > 0) {
                        $hfParticipating = $nbOfParticipatingReports . '/' . $nbOfHF . ' (' . round($nbOfParticipatingReports * 100 / $nbOfHF) . '%)';
                    }

                    $line[$this->trans(PhpReportConstant::COLUMNS_HEALTH_FACILITY_PARTICIPATING)] = $hfParticipating;
                }

                /** @var SesDashboardDisease $disease */
                foreach ($diseases as $disease) {
                    foreach ($disease->getDiseaseValues() as $diseaseValue) {
                        $line[$disease->getName() . ' [' . $diseaseValue->getFormatValue() . ']'] =
                            $this->getDashboardService()->getNumberOfValidatedDiseaseValues($leafsIds, $disease->getDisease(), $diseaseValue->getValue(), $weekNumberTo, null, Constant::PERIOD_WEEKLY, $yearTo);
                    }
                }

                $rows[] = $line;
            }

            $result[] = array("title" => "", "rows" => $rows);
        }

        return $result;
    }

    /**
     * Total of events compared to previous year
     * If Level District (Leaf +1) has validated the report, then data are considered as validated for the whole country
     *
     * @param $startDate
     * @param $endDate
     * @param $siteId
     * @param $diseaseId
     * @return array
     */
    private function getWeeklyDiseasesReportDashboardAggregation($startDate, $endDate, $siteId, $diseaseId)
    {
        /** @var SiteService $siteService */
        $siteService = $this->getSiteService();

        $dimDateStartId = DimDateHelper::getDimDateIdFromString($startDate);
        $dimDateEndId = DimDateHelper::getDimDateIdFromString($endDate);

        $epiFirstDay = $this->GetEpiFirstDay();

        $epiTo = Epidemiologic::Timestamp2Epi(strtotime($endDate), $epiFirstDay);
        $weekNumberTo = $epiTo['Week'];
        $yearTo = $epiTo['Year'];

        $epiFrom = Epidemiologic::Timestamp2Epi(strtotime($startDate), $epiFirstDay);
        $weekNumberFrom = $epiFrom['Week'];
        $yearFrom = $epiFrom['Year'];

        $site = $siteService->getById($siteId);

        $leafsIds = $siteService->getLeafSiteIds($siteId, false, true, null, SesDashboardIndicatorDimDateType::CODE_WEEKLY_EPIDEMIOLOGIC, $dimDateStartId, $dimDateEndId, true);
        $nbOfHF = $this->getDashboardService()->getNumberOfExpectedWeeklyReportPeriod($leafsIds, $startDate, $endDate);

        $diseasesAll = $this->getDiseaseService()->getAll();
        $additionalColumn = true;
        $nbOfParticipatingReports = 0;
        $nbOfParticipatingReportsPreviousYear = 0;

        if ($site->isLeaf() || ($site->getParent() != null && $site->getParent()->isLeaf())) { // Case HF and District
            $additionalColumn = false;
        } else {
            $startDateParam = $startDate;
            $endDateParam = $endDate;
            $nbOfParticipatingReports = $this->getDashboardService()->getNumberOfParticipatingReportPeriod($leafsIds, $startDateParam, $endDateParam, Constant::PERIOD_WEEKLY);

            $yearPrevious = $yearTo - 1;
            $startDateParamPrevious = Epidemiologic::GetTimeStampForFirstDayOfWeekOne($epiFirstDay, $yearPrevious);
            $startDateParamPrevious = date('Y-m-d', strtotime('+' . (($weekNumberFrom - 1) * 7) . ' days', $startDateParamPrevious));
            $endDateParamPrevious = date('Y-m-d', strtotime("+ " . ((($weekNumberTo - 1) * 7) + 6) . ' days', Epidemiologic::GetTimeStampForFirstDayOfWeekOne($epiFirstDay, $yearPrevious)));
            $leafsIdsPrevious = $siteService->getLeafSiteIds($siteId, false, true, null, SesDashboardIndicatorDimDateType::CODE_WEEKLY_EPIDEMIOLOGIC, DimDateHelper::getDimDateIdFromString($startDateParamPrevious), DimDateHelper::getDimDateIdFromString($endDateParamPrevious), true);
            $nbOfHFPrevious = $this->getDashboardService()->getNumberOfExpectedWeeklyReportPeriod($leafsIdsPrevious, $startDateParamPrevious, $endDateParamPrevious);
            $nbOfParticipatingReportsPreviousYear = $this->getDashboardService()->getNumberOfParticipatingReportPeriod($leafsIdsPrevious, $startDateParamPrevious, $endDateParamPrevious, Constant::PERIOD_WEEKLY);
        }

        $diseasesList = $this->getSplitDiseasesList($diseasesAll, 14);

        $result = array();

        for ($i = 1; $i <= count($diseasesList); $i++) {
            $rows = array();
            $year = $yearTo;
            $startDateParam = $startDate;
            $endDateParam = $endDate;
            $diseases = $diseasesList[$i];

            // Current Year
            $ligne = array();
            $ligne[$this->getTranslator()->trans('REPORT.COLUMNS.WEEK', array(), 'reports')] = $this->getTranslator()->trans('REPORT.WORD.WEEK', array(), 'reports') . $weekNumberFrom . ' - ' . $this->getTranslator()->trans('REPORT.WORD.WEEK', array(), 'reports') . $weekNumberTo . ' (' . $year . ')';

            // Add line if site is not a Leaf with % of participating HF
            if ($additionalColumn) {
                $hfParticipating = "-";
                if (isset($nbOfHF) && $nbOfHF > 0) {
                    $hfParticipating = round($nbOfParticipatingReports / ($nbOfHF) * 100) . ' % (' . $nbOfParticipatingReports . '/' . ($nbOfHF) . ')';
                }

                $ligne[$this->getTranslator()->trans('REPORT.COLUMNS.HEALTH_FACILITY_PARTICIPATING', array(), 'reports')] = $hfParticipating;
            }

            /** @var SesDashboardDisease $disease */
            foreach ($diseases as $disease) {
                foreach ($disease->getDiseaseValues() as $diseaseValue) {
                    if ($diseaseValue->getPeriod() == Constant::PERIOD_WEEKLY) {
                        $nbCas = "-";
                        $value = $this->getDashboardService()->getNumberOfValidatedDiseaseValuesFromPeriod($leafsIds, $disease->getDisease(), $diseaseValue->getValue(), $startDateParam, $endDateParam);
                        if (is_numeric($value)) {
                            $nbCas += $value;
                        }
                        $ligne[$disease->getName() . ' [' . $diseaseValue->getFormatValue() . ']'] = $nbCas;
                    }
                }
            }

            $rows[] = $ligne;

            // Previous Year
            $ligne = array();
            $year = $year - 1;
            $ligne[$this->getTranslator()->trans('REPORT.COLUMNS.WEEK', array(), 'reports')] = $this->getTranslator()->trans('REPORT.WORD.WEEK', array(), 'reports') . $weekNumberFrom . ' - ' . $this->getTranslator()->trans('REPORT.WORD.WEEK', array(), 'reports') . $weekNumberTo . ' (' . $year . ')';

            $startDateParam = Epidemiologic::GetTimeStampForFirstDayOfWeekOne($epiFirstDay, $year);
            $startDateParam = date('Y-m-d', strtotime('+' . (($weekNumberFrom - 1) * 7) . ' days', $startDateParam));
            $endDateParam = date('Y-m-d', strtotime("+ " . ((($weekNumberTo - 1) * 7) + 6) . ' days', Epidemiologic::GetTimeStampForFirstDayOfWeekOne($epiFirstDay, $year)));

            // Add line if site is not a Leaf with % of participating HF
            if ($additionalColumn) {
                $hfParticipating = "-";
                if (isset($nbOfHFPrevious) && $nbOfHFPrevious > 0) {
                    $hfParticipating = round($nbOfParticipatingReportsPreviousYear / ($nbOfHFPrevious) * 100) . ' % (' . $nbOfParticipatingReportsPreviousYear . '/' . ($nbOfHFPrevious) . ')';
                }

                $ligne[$this->getTranslator()->trans('HEALTH_FACILITY_PARTICIPATING', array(), 'reports')] = $hfParticipating;
            }

            /** @var SesDashboardDisease $disease */
            foreach ($diseases as $disease) {
                foreach ($disease->getDiseaseValues() as $diseaseValue) {
                    if ($diseaseValue->getPeriod() == Constant::PERIOD_WEEKLY) {
                        $nbCas = "-";
                        $value = $this->getDashboardService()->getNumberOfValidatedDiseaseValuesFromPeriod($leafsIdsPrevious, $disease->getDisease(), $diseaseValue->getValue(), $startDateParam, $endDateParam);
                        if (is_numeric($value)) {
                            $nbCas += $value;
                        }
                        $ligne[$disease->getName() . ' [' . $diseaseValue->getFormatValue() . ']'] = $nbCas;
                    }
                }
            }

            $rows[] = $ligne;

            $result[] = array("title" => "", "rows" => $rows);
        }

        return $result;
    }

    /**
     * Retrieve alerts from leaves of children sites
     *
     * @param $startDate
     * @param $endDate
     * @param $siteId
     * @return array
     */
    private function getAlertsDashboard($startDate, $endDate, $siteId)
    {
        /** @var SiteService $siteService */
        $siteService = $this->getSiteService();

        $dimDateStartId = DimDateHelper::getDimDateIdFromString($startDate);
        $dimDateEndId = DimDateHelper::getDimDateIdFromString($endDate);

        if (!$siteService->isLeaf($siteId)) {
            $sitesIds = $siteService->getChildrenSiteIds($siteId, false, true, 1, false, null, $dimDateStartId, $dimDateEndId);
        } else {
            $sitesIds[] = $siteId;
        }

        $intl = new \IntlDateFormatter(\Locale::getDefault(), \IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT);

        $result = array();

        // Foreach Sites N-1, get Alerts
        foreach ($sitesIds as $siteId) {
            // Number of Active Leaf under this site during this period
            $siteList = $siteService->getLeafSiteIds($siteId, false, true, null, SesDashboardIndicatorDimDateType::CODE_DAILY, $dimDateStartId, $dimDateEndId, true);

            $alerts = $this->getDashboardService()->getAlerts($siteList, $startDate, $endDate);

            $rows = array();

            /** @var  SesAlert $alert */
            foreach ($alerts as $alert) {
                $ligne = array();

                $ligne[$this->getTranslator()->trans('REPORT.COLUMNS.HEALTH_FACILITY_ALERT', array(), 'reports')] = $alert->getFrontLineGroupName();
                $ligne[$this->getTranslator()->trans('REPORT.COLUMNS.RECEPTION_DATE', array(), 'reports')] = $intl->format($alert->getReceptionDate());

                foreach ($alert->getFormatMessages() as $message) {
                    $ligne[$message[0]] = $message[1];
                }

                $rows[] = $ligne;
            }

            if (count($alerts) > 0) {
                $result[] = array("title" => $siteService->find($siteId)->getName(), "rows" => $rows);
            }
        }

        return $result;
    }


    /**
     * Split Disease List into multiple lists
     *
     * @param $diseases
     * @param $maxValues
     * @return array
     */
    private function getSplitDiseasesList($diseases, $maxValues)
    {
        // Construction of List of Diseases, max $maValues columns by table
        $diseasesList = array();

        $numberOfValues = 0;
        $index = 1;
        $monArray = array();

        /** @var SesDashboardDisease $disease */
        foreach ($diseases as $disease) {
            if ($disease->getDisease() == Constant::DISEASE_ALERT) {
                continue;
            }

            $nbValues = $disease->getDiseaseValues()->count();
            if ($numberOfValues + $nbValues <= $maxValues) {
                $monArray[] = $disease;
                $numberOfValues += $nbValues;
            } else {
                $diseasesList[$index] = $monArray;
                $index++;
                $monArray = array();
                $monArray[] = $disease;
                $numberOfValues = $nbValues;
            }
        }

        $diseasesList[$index] = $monArray;

        return $diseasesList;
    }

    /**
     * Get Number of cases per disease
     * If Level District (Leaf +1) has validated the report, then data are considered as validated.
     *
     *
     * @param $startDate
     * @param $endDate
     * @param $siteId
     * @param $diseaseId
     * @param $period
     * @param $displayYear
     * @return array
     */
    private function getNumberOfCasesPerDisease($startDate, $endDate, $siteId, $diseaseId, $period, $displayYear = true)
    {
        // DashNumberOfCasesPerDiseaseByPeriod Executed in 0.86658883094788 seconds [] []

        /** @var SiteService $siteService */
        $siteService = $this->getSiteService();

        /** @var DiseaseService $diseaseService */
        $diseaseService = $this->getDiseaseService();

        $dimDateStartId = DimDateHelper::getDimDateIdFromString($startDate);
        $dimDateEndId = DimDateHelper::getDimDateIdFromString($endDate);

        $siteList = $siteService->getLeafSiteIds($siteId, false, true, null, SesDashboardIndicatorDimDateType::CODE_DAILY, $dimDateStartId, $dimDateEndId, true);

        // Time information
        $epiFirstDay = $this->GetEpiFirstDay();
        $date = strtotime($startDate);
        $monthNumber = null;
        $weekNumber = null;
        $year = Epidemiologic::GetYear($date);

        // Disease Information
        $diseases = $diseaseService->getDiseasesPeriodIds($period, $diseaseId);

        $rows = array();

        while ($date <= strtotime($endDate)) {

            $line = array();

            switch ($period) {
                case Constant::PERIOD_WEEKLY:
                    $epi = Epidemiologic::Timestamp2Epi($date, $epiFirstDay);
                    $weekNumber = $epi['Week'];
                    $year = $epi['Year'];
                    $line[$this->trans(PhpReportConstant::COLUMNS_WEEK)] = $this->trans(PhpReportConstant::WORD_WEEK)
                        . $weekNumber
                        . ($displayYear ? ' - ' . $year : '');
                    break;

                case Constant::PERIOD_MONTHLY:
                    $monthNumber = Epidemiologic::GetMonthNumber($date);
                    $monthName = Epidemiologic::GetMonthName($date);
                    $year = Epidemiologic::GetYear($date);
                    $line[$this->trans(PhpReportConstant::COLUMNS_MONTH)] = $this->getTranslator()->trans($monthName)
                        . ($displayYear ? ' - ' . $year : '');
                    break;
            }

            /** @var SesDashboardDisease $disease */
            foreach ($diseases as $disease) {
                /** @var SesDashboardDiseaseValue $diseaseValue */
                foreach ($disease->getDiseaseValues() as $diseaseValue) {
                    if ($diseaseValue->getPeriod() == $period) {
                        $nbCas = "-";
                        $result = $this->getDashboardService()->getNumberOfValidatedDiseaseValues($siteList, $disease->getDisease(), $diseaseValue->getValue(), $weekNumber, $monthNumber, $period, $year);
                        if (is_numeric($result)) {
                            $nbCas += $result;
                        }
                        $line[$disease->getName() . ' [' . $diseaseValue->getFormatValue() . ']'] = $nbCas;
                    }
                }
            }

            $rows[] = $line;

            switch ($period) {
                case Constant::PERIOD_WEEKLY:
                    $date = strtotime("+7 days", $date);
                    break;
                case Constant::PERIOD_MONTHLY:
                    $date = strtotime("+1 month", $date);
                    break;
            }
        }

        return $rows;
    }

    /**
     *  Get SMS Traffic per gateway
     *
     * @param $startDate
     * @param $endDate
     * @param $siteId
     * @param $period
     * @return array
     */
    private function getSMSTrafficPerGateway($startDate, $endDate, $siteId, $period)
    {
        /** @var SiteService $siteService */
        $siteService = $this->getSiteService();

        /** @var GatewayQueueService $gatewayQueueService */
        $gatewayQueueService = $this->getGatewayQueueService();

        $epiFirstDay = $this->GetEpiFirstDay();
        $dimDateStartId = DimDateHelper::getDimDateIdFromString($startDate);
        $dimDateEndId = DimDateHelper::getDimDateIdFromString($endDate);
        $dateStartTimestamp = strtotime($dimDateStartId, $epiFirstDay);
        $dateEndTimestamp = strtotime($dimDateEndId, $epiFirstDay);

        $siteList = $siteService->getChildrenSiteIds($siteId, false, true);

        $r = $gatewayQueueService->getWeekGatewaySMSTraffic($siteList, $dateStartTimestamp, $dateEndTimestamp, $epiFirstDay);

        $start = Epidemiologic::Timestamp2Epi($dateStartTimestamp, $epiFirstDay);
        $end = Epidemiologic::Timestamp2Epi($dateEndTimestamp, $epiFirstDay);
        $weeks = array();
        foreach (range($start['Year'], $end['Year']) as $year) {
            $from = $year === $start['Year'] ? $start['Week'] : 1;
            $to = $year === $end['Year'] ? $end['Week'] : Epidemiologic::getNumberOfWeeksInYear($year, $epiFirstDay);

            foreach (range($from, $to) as $week) {
                $weeks[] = [
                    'week' => $week,
                    'year' => $year,
                ];
            }
        }

        // initialise each week row
        $results = array_map(function ($w) {
            $week = $this->trans(PhpReportConstant::WORD_WEEK) . $w['week'] . ' - ' . $w['year'];
            return [$this->trans(PhpReportConstant::COLUMNS_WEEK) => $week];
        }, $weeks);


        foreach ($r as $row) {
            $index = array_search([
                'week' => $row['week'],
                'year' => $row['year'],
            ], $weeks);
            $inColumnName = $row['gateway'] . ' [inbound]';
            $outColumnName = $row['gateway'] . ' [outbound]';
            $results[$index][$inColumnName] = intval($row['totalInboundSMS']);
            $results[$index][$outColumnName] = intval($row['totalOutboundSMS']);
        }
        return $results;
    }
}