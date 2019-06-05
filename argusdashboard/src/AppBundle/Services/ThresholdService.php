<?php
/**
 * Threshold Service
 *
 * @author fc, inspired by eotin's SiteService
 */

namespace AppBundle\Services;

use AppBundle\Entity\Constant;
use AppBundle\Entity\SesDashboardDisease;
use AppBundle\Entity\SesDashboardSite;
use AppBundle\Entity\SesDashboardThreshold;
use AppBundle\Entity\SesFullReport;
use AppBundle\Entity\SesPartReport;
use AppBundle\Entity\SesReport;
use AppBundle\Entity\SesReportValues;
use AppBundle\Repository\SesDashboardThresholdRepository;
use Doctrine\ORM\EntityManager;

class ThresholdService
{
    private $em;
    /** @var SesDashboardThresholdRepository */
    private $thresholdRepository;

    /** @var SiteService */
    private $siteService;

    public function __construct(EntityManager $em, SiteService $siteService)
    {
        $this->em = $em;
        $this->thresholdRepository = $this->em->getRepository('AppBundle:SesDashboardThreshold');

        $this->siteService = $siteService;
    }

    public function getAll()
    {
        $thresholds = $this->thresholdRepository->findAll() ;
        return $thresholds;
    }

    public function removeAll()
    {
        foreach ($this->getAll() as $entity) {
            $this->em->remove($entity);
        }
        $this->em->flush();
    }

    public function getById($id)
    {
        $thresholds = $this->thresholdRepository->find($id);
        return $thresholds ;
    }

    public function removeEntity($entity)
    {
        $this->em->remove($entity);
        $this->em->flush();
    }


    /**
     * Assign Threshold to the fullReport
     *
     * @param SesFullReport $fullReport
     * @param $diseases
     */
    public function AssignThresholdToFullReport(SesFullReport $fullReport, $diseases)
    {
        // Get infos of the fullReport
        $site = $fullReport->getFrontlineGroup();
        $period = $fullReport->getPeriod();
        $weekNumber = $fullReport->getWeekNumber();
        $monthNumber = $fullReport->getMonthNumber();
        $year = $fullReport->getYear();

        $sitesIds = $this->siteService->getParentSiteIds([$site->getId()], false, null,false);
        $thresholds = $this->thresholdRepository->getAssociatedThresholds($sitesIds, $year);

        $thresholds =  $this->classifyThresholds($thresholds);

        /** @var SesDashboardDisease $disease */
        foreach($diseases as $disease) {
            /** @var SesDashboardThreshold $threshold */
            $threshold = $this->getThresholdConcerned($thresholds, $sitesIds, $disease->getId(), $period, $weekNumber, $monthNumber, $year);

            if ($threshold != null) {
                $partReports = $fullReport->getPartReports();

                /** @var SesPartReport $partReport */
                foreach($partReports as $partReport) {
                    $reports = $partReport->getReports();

                    /** @var SesReport $report */
                    foreach($reports as $report) {
                        if ($report->getDisease() == $disease->getDisease()) {
                            $reportValues = $report->getReportValues();

                            /** @var SesReportValues $reportValue */
                            foreach ($reportValues as $reportValue) {
                                $thresholdValue = $threshold->getDiseaseValue();

                                if ($thresholdValue->getValue() == $reportValue->getKey()) {
                                    $reportValue->setThresholdMaxValue($threshold->getMaxValue());
                                    break ;
                                }
                            }
                            break;
                        }
                    }
                }
            }
        }
    }

    /**
     * Create thresholds array with specific keys
     *
     * @param $thresholds
     * @return array
     */
    private function classifyThresholds($thresholds)
    {
        $resultArray = [];

        /** @var SesDashboardThreshold $threshold */
        foreach ($thresholds as $threshold) {
            $resultArray[$threshold->getSiteId()][$threshold->getDiseaseId()][$threshold->getYear()][$threshold->getPeriod()] = $threshold;

            if ($threshold->getWeekNumber() != null) {
                $resultArray[$threshold->getSiteId()][$threshold->getDiseaseId()][$threshold->getYear()][$threshold->getPeriod()][$threshold->getWeekNumber()] = $threshold;
            }

            if ($threshold->getMonthNumber() != null) {
                $resultArray[$threshold->getSiteId()][$threshold->getDiseaseId()][$threshold->getYear()][$threshold->getPeriod()][$threshold->getMonthNumber()] = $threshold;
            }
        }

        return $resultArray;
    }


    /**
     * Return the concerned threshold recursively on site hierarchy
     *
     * @param $thresholds
     * @param $siteIds
     * @param $diseaseId
     * @param $period
     * @param $weekNumber
     * @param $monthNumber
     * @param $year
     * @return null
     */
    private function getThresholdConcerned($thresholds, $siteIds, $diseaseId, $period, $weekNumber, $monthNumber, $year)
    {
        foreach ($siteIds as $siteId) {
            if (array_key_exists($siteId ,$thresholds)) {
                $thresholdSite = $thresholds[$siteId];

                if (array_key_exists($diseaseId, $thresholdSite)) {
                    $thresholdDisease = $thresholdSite[$diseaseId];

                    if (array_key_exists($year, $thresholdDisease)) {
                        $thresholdYear = $thresholdDisease[$year];

                        if (array_key_exists($period, $thresholdYear)) {
                            $thresholdPeriod = $thresholdYear[$period];

                            if (!is_array($thresholdPeriod)) {
                                return $thresholdPeriod;
                            }

                            if ($weekNumber != null && array_key_exists($weekNumber, $thresholdPeriod)) {
                                return $thresholdPeriod[$weekNumber];
                            }

                            if ($monthNumber != null && array_key_exists($monthNumber, $thresholdPeriod)) {
                                return $thresholdPeriod[$monthNumber];
                            }
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * @param $diseaseIdsParam
     * @param $periodsParam
     * @param $yearsParam
     *
     * @return mixed
     */
    public function getThresholdsData($diseaseIdsParam, $periodsParam, $yearsParam)
    {
        $thresholds = $this->thresholdRepository->getThresholdsData($diseaseIdsParam, $periodsParam, $yearsParam);
        return $thresholds;
    }

    /**
     * Get Array of Threshold rows for CSV Export
     *
     * @return array
     */
    public function getThresholdForCsvExport()
    {
        $result = array();
        $thresholds = self::getAll();

        $result[] = SesDashboardThreshold::getHeaderCsvRow();

        /** @var SesDashboardThreshold $threshold */
        foreach($thresholds as $threshold){
            $result = array_merge($result, $threshold->getCsvRow());
        }

        return $result ;
    }

    /**
     * Get a query object that can be used to get a list of thresholds
     *
     * @return \Doctrine\ORM\Query
     */
    public function getThresholdListQuery() {
        $qb = $this->thresholdRepository->createQueryBuilder('d');
        return $qb->getQuery();
    }

}