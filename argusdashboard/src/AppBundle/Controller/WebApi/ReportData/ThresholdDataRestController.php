<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 13/02/2018
 * Time: 15:19
 */

namespace AppBundle\Controller\WebApi\ReportData;

use AppBundle\Controller\BaseController;
use AppBundle\Entity\SesDashboardThreshold;
use AppBundle\Entity\WebApi\WebApiThresholdData;
use AppBundle\Services\ThresholdService;

use FOS\RestBundle\Controller\Annotations\Get;


class ThresholdDataRestController extends BaseController
{
    /** @var ThresholdService */
    private $thresholdService;

    /**
     * @return array
     * @Get("/thresholds")
     */
    public function getAllThresholdsAction()
    {
        return $this->getThresholdsDiseasePeriodYearAction();
    }

    /**
     * @Get("/thresholds/{diseaseIds}")
     *
     * @param $diseaseIds
     * @return array
     */
    public function getThresholdsDiseaseAction($diseaseIds)
    {
        return $this->getThresholdsDiseasePeriodYearAction($diseaseIds);
    }

    /**
     * @Get("/thresholds/{diseaseIds}/{periods}")
     *
     * @param $diseaseIds
     * @param $periods
     * @return array
     */
    public function getThresholdsDiseasePeriodAction($diseaseIds, $periods)
    {
        return $this->getThresholdsDiseasePeriodYearAction($diseaseIds, $periods);
    }

    /**
     * @Get("/thresholds/{diseaseIds}/{periods}/{years}")
     *
     * @param null $diseaseIds
     * @param null $periods
     * @param null $years
     * @return array
     */
    public function getThresholdsDiseasePeriodYearAction($diseaseIds = null, $periods = null, $years = null)
    {
        $this->thresholdService = $this->getThresholdService();

        // Parse all args
        $diseaseIdParam = $this->getIntegerParameter($diseaseIds);
        $periodParam = $this->getStringParameter($periods);
        $yearsParam = $this->getIntegerParameter($years);

        $thresholdsData = $this->thresholdService->getThresholdsData($diseaseIdParam, $periodParam, $yearsParam);

        return ['thresholds' => $this->mappThresholdsData($thresholdsData)];
    }

    /**
     * Map Thresholds to WebApiThresholdsData objects
     *
     * @param $thresholdsData
     * @return array
     */
    private function mappThresholdsData($thresholdsData)
    {
        $results = array();

        /** @var SesDashboardThreshold $threshold */
        foreach($thresholdsData as $threshold) {

            $wtr = new WebApiThresholdData();
            $wtr->id = $threshold->getId();
            $wtr->period = $threshold->getPeriod();
            $wtr->monthNumber = $threshold->getMonthNumber();
            $wtr->weekNumber = $threshold->getWeekNumber();
            $wtr->year = $threshold->getYear();
            $wtr->maxValue = $threshold->getMaxValue();
            $wtr->FK_DiseaseId = $threshold->getDiseaseId();
            $wtr->FK_DiseaseValueId = $threshold->getDiseaseValueId();
            $wtr->FK_SiteId = $threshold->getSiteId();

            $results[] = $wtr;
        }

        return $results ;
    }

}