<?php

/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 7/22/2015
 * Time: 9:55 AM
 */

namespace AppBundle\Services;

use AppBundle\Repository\SesAlertRepository;
use AppBundle\Repository\SesDashboardSiteRelationShipRepository;
use AppBundle\Repository\SesFullReportRepository;
use AppBundle\Repository\SesPartReportRepository;
use AppBundle\Services\IndicatorsCalculation\IndicatorDimDateService;

use Doctrine\ORM\EntityManager;

class ReportService
{
    private $em;

    /** @var  IndicatorDimDateService */
    private $dimDateService;

    public function __construct(EntityManager $em, IndicatorDimDateService $dimDateService)
    {
        $this->em = $em;
        $this->dimDateService = $dimDateService;
    }

    function getFullReport($fullReportId)
    {
        /** @var SesFullReportRepository $repositoryFullReport */
        $repositoryFullReport = $this->em->getRepository('AppBundle:SesFullReport');
        $sesFullReport = $repositoryFullReport->getFullReport($fullReportId, false, false);
        return $sesFullReport;
    }

    public function getFullReportFromPeriodSiteStartDate($period, $siteId, $startDate)
    {
        /** @var SesFullReportRepository $repositoryFullReport */
        $repositoryFullReport = $this->em->getRepository('AppBundle:SesFullReport');
        $fullReport = $repositoryFullReport->getFullReportFromPeriodSiteStartDate($period, $siteId, $startDate);

        return $fullReport ;
    }

    public function getAllFullReport()
    {
        /** @var SesFullReportRepository $repositoryFullReport */
        $repositoryFullReport = $this->em->getRepository('AppBundle:SesFullReport');
        $sesFullReport = $repositoryFullReport->findAll();
        return $sesFullReport;
    }

    function getPartReport($partReportId)
    {
        /** @var SesPartReportRepository $repositoryPartReport */
        $repositoryPartReport = $this->em->getRepository('AppBundle:SesPartReport');
        $sesPartReport = $repositoryPartReport->find($partReportId);
        return $sesPartReport;
    }

    public function getNewAlerts($sites, $limit)
    {
        /** @var SesAlertRepository $repository */
        $repository = $this->em->getRepository('AppBundle:SesAlert');
        $alerts =  $repository->getNewAlerts($sites, $limit);

        return $alerts ;
    }

    public function getOldAlerts($sites, $limit)
    {
        /** @var SesAlertRepository $repository */
        $repository = $this->em->getRepository('AppBundle:SesAlert');
        $alerts =  $repository->getOldAlerts($sites, $limit);

        return $alerts ;
    }

    public function getSitesRelationWithReports($siteRelationShipIds, $display, $startDate, $endDate, $period)
    {
        /** @var SesDashboardSiteRelationShipRepository $repository */
        $repository = $this->em->getRepository('AppBundle:SesDashboardSiteRelationShip');
        $siteRelations = $repository->findSitesRelationWithReports($siteRelationShipIds, $display, $startDate, $endDate, $period);

        return $siteRelations;
    }

    public function readAlert($alertId)
    {
        $repository = $this->em->getRepository('AppBundle:SesAlert');
        $alert = $repository->find($alertId);

        if (null !== $alert) {
            $alert->setRead(true);
            $this->em->flush();

            return true;
        }

        return false;
    }

    /**
    public function getTemplateFullReport($period, $includeAlerts = true)
    {
        $repository = $this->em->getRepository('AppBundle:SesDashboardDisease');
        $ses_diseases = $repository->findDiseases($period, $includeAlerts = true);

        $fullReport = new SesFullReport();
        $fullReport->setPeriod($period);

        $partReport = new SesPartReport();
        $partReport->setReceptionDate(new \DateTime());

       foreach($ses_diseases as $sesDisease)
       {
           $report = new SesReport();
           $report->setDisease($sesDisease->getDisease());

           foreach($sesDisease->getDiseaseValues() as $sesDiseaseValue)
           {
               $values = new SesReportValues();
               $values->setKey($sesDiseaseValue->getValue());

               $report->addReportValues($values);
           }

           $partReport->addReport($report);
       }

        $fullReport->addPartReport($partReport);

        return $fullReport;
    }
     **/

    /**
     public function createFullReport($fullReport, $period, $path, $startDate)
    {
        $firstDay = null ;

        $time = strtotime($startDate->format('Y-m-d'));

        // get First Day of Week / Month
        if ($period == Constant::PERIOD_WEEKLY)
        {
            $firstDay = date('Y-m-d',strtotime("last Monday", $time));
        }
        else if ($period == Constant::PERIOD_MONTHLY)
        {
            $firstDay = date('Y-m-d',strtotime("first day of this month", $time));
        }

        $firstDay = \DateTime::createFromFormat('Y-m-d', $firstDay);
        $firstDay->setTime(0,0,0); //Set time to 0 to try to retrieve same weekly report in Database

        $repository = $this->em->getRepository('AppBundle:SesFullReport');
        $sesFullReport = $repository->getFullReportFromPeriodSiteStartDate($period,$path, $firstDay);

        if (null == $sesFullReport)
        {
            // persist our fullReport from Form
            $fullReport->setStartDate($firstDay);
            $this->em->persist($fullReport);
        }
        else // We add this Full report our PartReport
        {
            $partReport = $fullReport->getPartReports()[0];
            $sesFullReport->addPartReport($partReport);
        }

        $this->em->flush() ;
    }
     **/

    /**
     * @param $siteIds
     * @param $statuses
     * @param $startDate
     * @param $endDate
     * @param $period
     * @return mixed
     */
    public function getFullReportData($siteIds, $statuses, $startDate, $endDate, $period)
    {
        /** @var SesFullReportRepository $repository */
        $repository = $this->em->getRepository('AppBundle:SesFullReport');
        $fullReports = $repository->getFullReportData($siteIds, $statuses, $startDate, $endDate, $period);
        return $fullReports;
    }

    /**
     * @param $reportIds
     * @param $statuses
     * @return array
     */
    public function getPartReportData($reportIds, $statuses)
    {
        /** @var SesPartReportRepository $repository */
        $repository = $this->em->getRepository('AppBundle:SesPartReport');
        $fullReports = $repository->getPartReportData($reportIds, $statuses);
        return $fullReports;
    }
}