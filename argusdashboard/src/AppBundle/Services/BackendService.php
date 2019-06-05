<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 01/12/2016
 * Time: 15:22
 */

namespace AppBundle\Services;

use AppBundle\Entity\Constant;
use AppBundle\Entity\SesAggregatePartReport;
use AppBundle\Entity\SesDashboardSite;
use AppBundle\Entity\SesFullReport;
use AppBundle\Entity\SesPartReport;
use AppBundle\Entity\SesReport;
use AppBundle\Entity\SesReportValues;
use AppBundle\Repository\SesDashboardSiteRepository;
use AppBundle\Repository\SesFullReportRepository;
use AppBundle\Repository\SesPartReportRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Monolog\Logger;

class BackendService
{
    private $em;
    private $logger ;
    private $aggregatePartReportRepository;
    /** @var SesFullReportRepository  */
    private $fullReportRepository;
    /** @var SesPartReportRepository  */
    private $partReportRepository;

    /** @var SesDashboardSiteRepository  */
    private $siteRepository;

    /** @var  SiteService */
    private $siteService;

    public function __construct(EntityManager $em, Logger $logger, SiteService $siteService)
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->aggregatePartReportRepository = $this->em->getRepository('AppBundle:SesAggregatePartReport');
        $this->fullReportRepository = $this->em->getRepository('AppBundle:SesFullReport');
        $this->partReportRepository = $this->em->getRepository('AppBundle:SesPartReport');
        $this->siteRepository = $this->em->getRepository('AppBundle:SesDashboardSite');

        $this->siteService = $siteService;
    }

    /**
     * @deprecated Not working with the new Site hierarchy with Relation Ships
     *
     * @param SesDashboardSite $site
     * @param SesDashboardSite $parentSite
     */
    public function moveSite(SesDashboardSite $site, SesDashboardSite $parentSite)
    {
        // Step 1 Remove all AggregatePart Report in the old aggregation for this site
        $this->removeReportsFromAggregation($site);

        // Step 2 Move site to the new Parent
        $this->moveSiteToNewParent($site, $parentSite);

        // Step 3 Calculate new Aggregation in the new tree
        $this->addReportsToAggregation($site);
    }

    /**
     * Calculate new Aggregation for all PartReports from sites
     *
     * @param SesDashboardSite $site
     */
    public function addReportsToAggregation(SesDashboardSite $site)
    {
        $fullReports = $site->getFullReports();

        /** @var SesFullReport $fullReport */
        foreach($fullReports as $fullReport) {
            $partReports = $fullReport->getPartReports();

            /** @var SesPartReport $partReport */
            foreach($partReports as $partReport) {
                // Add an aggregate Part Report
                $this->generateAutoAggregation($site->getParent(), $fullReport, $partReport);
            }

            unset($partReports);
        }

        $this->em->flush();

        unset($fullReports);
    }

    /**
     * Move Site to new Parent
     *
     * @param SesDashboardSite $site
     * @param SesDashboardSite $parentSite
     */
    private function moveSiteToNewParent(SesDashboardSite $site, SesDashboardSite $parentSite)
    {
        // Move Site to new Parent Site
        $site->setParent($parentSite);
        $parentSite->addChild($site);

        if (!$site->isLeaf()) {
            $children = $site->getChildren();
            /** @var SesDashboardSite $child */
            foreach($children as $child) {
                $this->moveSiteToNewParent($child, $site) ;
            }
        }

        $this->em->flush();
    }

    /**
     * Remove all Part reports assigned to the site to the aggregations
     *
     * @param SesDashboardSite $site
     */
    private function removeReportsFromAggregation(SesDashboardSite $site)
    {
        // Get All fullReports from this site
        $fullReports = $site->getFullReports();

        //Search all Aggregate Part Reports containing part Reports from these fullReports

        /** @var SesFullReport $fullReport */
        foreach($fullReports as $fullReport) {
            $partReports = $fullReport->getPartReports();

            /** @var SesPartReport $partReport */
            foreach($partReports as $partReport) {
                // Get the AggregatePartReport
                $aggregatePartReports = $this->aggregatePartReportRepository->findBy(['FK_PartReportId' => $partReport->getId()]);

                // Remove partReport in this aggregation
                /** @var SesAggregatePartReport $aggregatePartReport */
                foreach($aggregatePartReports as $aggregatePartReport) {
                    $partReportOwner = $aggregatePartReport->getPartReportOwner();
                    $partReportOwner->removeAggregatePartReport($aggregatePartReport);
                    $this->em->remove($aggregatePartReport);

                    // Re calculate aggregation for this part Report
                    $this->calculateAggregation($partReportOwner);
                }
            }
        }
        $this->em->flush();
    }

    /**
     * Calculate Aggregation Part Report with AggregatePartReports
     *
     * @param SesPartReport $partReport
     */
    private function calculateAggregation(SesPartReport $partReport) {
        if (! $partReport->isAggregate()) {
            return ;
        }

        $partReport->resetAllValues();
        $aggregatePartReports = $partReport->getAggregatePartReports() ;

        /** @var SesAggregatePartReport $aggregatePartReport */
        foreach($aggregatePartReports as $aggregatePartReport) {
            $report = $aggregatePartReport->getPartReport();
            $this->aggregateData($partReport, $report);
        }

        $aggregatePartReportsUsed = $this->aggregatePartReportRepository->findBy(['FK_PartReportId' => $partReport->getId()]);

        /** @var SesAggregatePartReport $aggregatePartReport */
        foreach($aggregatePartReportsUsed as $aggregatePartReport) {
            $partReportOwner = $aggregatePartReport->getPartReportOwner();
            $this->calculateAggregation($partReportOwner);
        }
    }

    /**
     * Aggregate Data into the $newPartReport
     *
     * @param SesPartReport $newPartReport
     * @param SesPartReport $otherPartReport
     */
    public function aggregateData(SesPartReport $newPartReport, SesPartReport $otherPartReport)
    {
        if ($otherPartReport == null) {
            return ;
        }

        $otherReports = $otherPartReport->getReports() ;

        /** @var SesReport $otherReport */
        foreach($otherReports as $otherReport) {
            if ($otherReport->isArchived() || $otherReport->isDeleted()) {
                continue;
            }

            $existReport = null;
            $reports = $newPartReport->getReports();

            /** @var SesReport $report */
            foreach($reports as $report) {
                if($otherReport->getDisease() == $report->getDisease()) {
                    $existReport = $report;
                    break ;
                }

                unset($report);
            }

            unset($reports);

            if ($existReport == null) {
                $existReport = SesReport::create($otherReport->getDisease(), new \DateTime());
                $existReport->setDiseaseEntity($otherReport->getDiseaseEntity());
                $newPartReport->addReport($existReport);
            }

            $otherReportValues = $otherReport->getReportValues();

            /** @var SesReportValues $otherReportValue */
            foreach($otherReportValues as $otherReportValue) {
                $existReportValue = null;

                $existReportValues = $existReport->getReportValues();

                /** @var SesReportValues $reportValue */
                foreach ($existReportValues as $reportValue) {
                    if($otherReportValue->getKey() == $reportValue->getKey()) {
                        $existReportValue = $reportValue ;
                        unset($reportValue);
                        break ;
                    }
                }

                if ($existReportValue == null) {
                    $existReportValue = SesReportValues::create($otherReportValue->getKey(), $otherReportValue->getValue());
                    $existReport->addReportValues($existReportValue);
                } else {
                    $existReportValue->setValue($existReportValue->getValue() + $otherReportValue->getValue());
                }

                unset($existReportValue);
                unset($otherReportValue);
            }
            unset($otherReportValues);
        }
        unset($otherReports);
    }

    /**
     * Auto Aggregate Data recursively until the siteRoot
     *
     * @param SesDashboardSite $site
     * @param SesFullReport $newFullReport
     * @param SesPartReport $newPartReport
     */
    public function generateAutoAggregation(SesDashboardSite $site, SesFullReport $newFullReport, SesPartReport $newPartReport)
    {
        if ($site == null) {
            $this->logger->info("GenerateAutoAggregation: Site null");
            return ;
        }

        $relationShip = $this->siteService->getActiveRelationShipPeriod($site, $newFullReport->getPeriod(), $newFullReport->getStartDate());
        if ($relationShip == null) {
            if ($newFullReport->getPeriod() == Constant::PERIOD_MONTHLY) {
                $this->logger->info(sprintf("generateAutoAggregation: Site with id '%1\$s' has no active relation Ship for the month %2\$s year %3\$s",
                    $site->getId(),
                    $newFullReport->getMonthNumber(),
                    $newFullReport->getYear()));
            } else if ($newFullReport->getPeriod() == Constant::PERIOD_WEEKLY) {
                $this->logger->info(sprintf("generateAutoAggregation: Site with id '%1\$s' has no active relation Ship for the week %2\$s year %3\$s",
                    $site->getId(),
                    $newFullReport->getWeekNumber(),
                    $newFullReport->getYear()));
            }
            return ;
        }


        // Get the Aggregate full Report for this site, period and startdate
        $fullReport = $this->fullReportRepository->getFullReportFromPeriodSiteStartDate(
                $newFullReport->getPeriod(),
                $site->getId(),
                $newFullReport->getStartDate());

        if ($fullReport == null) {
            // We have to create this fullReport
            $fullReport = SesFullReport::create($site,
                $relationShip,
                "",
                $newFullReport->getPeriod(),
                $newFullReport->getStartDate(),
                $newFullReport->getWeekNumber(),
                $newFullReport->getMonthNumber(),
                $newFullReport->getYear(),
                true);

            $this->em->persist($fullReport);
        }

        if (count($fullReport->getPartReports()) == 0) {
            // We have to create the part Report
            $partReport =  SesPartReport::create(null,null, true);
            $partReport->setFullReport($fullReport) ;
            // Adding the part Report in the FullReportList
            $fullReport->addPartReport($partReport);

        } else {
            $partReports = $fullReport->getSortPartReports(function($a, $b){
                return ($a->getId() < $b->getId());
            });

            $partReport = $partReports[count($partReports) -1];
            $partReport->resetAllValues() ;
        }

        $partReport->setStatus(Constant::STATUS_VALIDATED);
        $fullReport->setStatus(Constant::STATUS_VALIDATED);

        $this->em->flush();

        // Get All the Report doing the aggregate
        $aggregatePartReports = $partReport->getAggregatePartReports();

        // Add our partReport if it is not yet in the aggregate
        $found = false ;
        if ($aggregatePartReports != null) {
            /** @var SesAggregatePartReport $aggregatePartReport */
            foreach ($aggregatePartReports as $aggregatePartReport) {
                $other = $aggregatePartReport->getPartReport();

                if ($other != null && ($other->getId() == $newPartReport->getId())) {
                    $found = true;
                    break;
                }
            }
        }

        if (!$found) {
            $aggregatePartReport = SesAggregatePartReport::create($partReport, $newPartReport);
            $partReport->addAggregatePartReport($aggregatePartReport);
        }

        // Aggregate All the data for the pending part Report
        // Now Calculate the aggregation
        $partReport->resetAllValues();

        /** @var SesAggregatePartReport $linkedPartReport */
        foreach($partReport->getAggregatePartReports() as $linkedPartReport) {
            $partReportToAggregate = $linkedPartReport->getPartReport();
            if ($partReportToAggregate != null) {
                $this->aggregateData($partReport, $partReportToAggregate);
            }
        }

        $this->em->flush();

        // Then create the Auto Aggregation for the parent recursively
        if ($relationShip->getParentSite() != null) {
            $this->generateAutoAggregation($relationShip->getParentSite(), $fullReport, $partReport);
        }

        $this->em->clear();
    }

    /**
     * Recalculate aggregation if aggregation doesn't exist : /!\ AVADAR Specific
     *
     * @param SesDashboardSite $site
     * @param $startDate
     * @param $endDate
     */
    public function reCalculateAggregation(SesDashboardSite $site, $startDate, $endDate)
    {
        // Step 2 : Check if sites has already reports : If yes, return;
        $fullReports = $this->fullReportRepository->getNumberOfReportPeriod($site->getId(), $startDate, $endDate, Constant::PERIOD_WEEKLY, Constant::STATUS_ALL);

        if (isset($fullReports) &&  intval($fullReports) > 0) {
            return ;
        }

        // Step 3 :
        // get all Ids of childs sites
        $children = $this->siteRepository->findBy(['FK_ParentId' => $site->getId()]);

        if ($children == null || count($children) == 0) {
            return ;
        }

        $childrenIds = [];
        /** @var SesDashboardSite $child */
        foreach ($children as $child) {
            $childrenIds[] = $child->getId();
        }

        $fullReportsAggregated = $this->fullReportRepository->getAggregateValuesReportPeriod($childrenIds, $startDate, $endDate, Constant::PERIOD_WEEKLY, Constant::STATUS_ALL);

        if ($fullReportsAggregated == null || count($fullReportsAggregated) == 0) {
            return ;
        }

        $yearReportValues = [];

        for ($i= 0 ; $i < count($fullReportsAggregated); $i++) {
            $newFullReportAggregated =  $fullReportsAggregated[$i];

            $weekNumber = $newFullReportAggregated['weekNumber'];
            $year =  $newFullReportAggregated['year'];
            $key = $newFullReportAggregated['key'];
            $value = $newFullReportAggregated['value'];
            $partReportIds = $newFullReportAggregated['partReportIds'];
            $startDate = $newFullReportAggregated['startDate'];
            $createdDate = $newFullReportAggregated['createdDate'];

            if (!array_key_exists($year, $yearReportValues)) {
                $yearReportValues[$year] = [];
            }

            if (!array_key_exists($weekNumber, $yearReportValues[$year])) {
                $yearReportValues[$year][$weekNumber] = [];
            }

            if (!array_key_exists('reportValues', $yearReportValues[$year][$weekNumber])) {
                $yearReportValues[$year][$weekNumber]['reportValues'] = [];
            }

            if (!array_key_exists($key, $yearReportValues[$year][$weekNumber]['reportValues'])) {
                $yearReportValues[$year][$weekNumber]['reportValues'][$key] = $value;
            }

            $yearReportValues[$year][$weekNumber]['partReportIds'] = $partReportIds;
            $yearReportValues[$year][$weekNumber]['startDate'] = $startDate;
            $yearReportValues[$year][$weekNumber]['createdDate'] = $createdDate;

        }


        foreach ($yearReportValues as $year => $value) {
            foreach ($value as $week => $values) {
                $keyValues = $values['reportValues'];
                $aggregatePartReportIds = $values['partReportIds'];
                $startDate = $values['startDate'];
                $createdDate = $values['createdDate'];
                $aggregatePartReportIdsTable = explode(',',$aggregatePartReportIds );

                $partReports = [];
                foreach ($aggregatePartReportIdsTable as $partReportId) {
                    $partReports[] = $this->partReportRepository->find($partReportId);
                }

                $fullReport = $this->createAggregateFullReport($site,
                    Constant::PERIOD_WEEKLY,
                    $startDate,
                    $week,
                    null,
                    $year,
                    \DateTime::createFromFormat("Y-m-d H:i:s" ,$createdDate),
                    $keyValues,
                    $partReports);

                $this->em->persist($fullReport);
            }
        }

        $this->em->flush();
    }

    /**
     * Create AVADAR Specific FullReports
     *
     * @param SesDashboardSite $site
     * @param $period
     * @param $startDate
     * @param $weekNumber
     * @param $monthNumber
     * @param $year
     * @param $creationDate
     * @param $keyValues
     * @param $partReports
     *
     * @return SesFullReport
     */
    private function createAggregateFullReport(SesDashboardSite $site, $period, $startDate, $weekNumber, $monthNumber, $year, $creationDate, $keyValues, $partReports )
    {

        // TODO Fix , Add RelationShip
        $fullReport =  SesFullReport::create($site,
            $site->getReference(),
            $period,
            $startDate,
            $weekNumber,
            $monthNumber,
            $year,
            true);

        $fullReport->setStatus(Constant::STATUS_VALIDATED);
        $fullReport->setCreatedDate($creationDate);

        $partReport = SesPartReport::create('', '', true);
        $partReport->setStatus(Constant::STATUS_VALIDATED);
        $partReport->setCreatedDate($creationDate);

        foreach ($partReports as $fkPartReport) {
            $partReport->addAggregatePartReport(SesAggregatePartReport::create($partReport, $fkPartReport));
        }

        $report = SesReport::create('afp', $creationDate);

        foreach ($keyValues as $key=>$value) {
            $report->addReportValues(SesReportValues::create($key, $value));
        }

        $partReport->addReport($report);
        $fullReport->addPartReport($partReport);

        return $fullReport;
    }
}