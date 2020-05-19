<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 10/1/2015
 * Time: 4:29 PM
 */

namespace AppBundle\Services;

use AppBundle\Entity\SesDashboardSite;
use AppBundle\Entity\SesDashboardSiteRelationShip;
use AppBundle\Entity\SesFullReport;
use AppBundle\Entity\SesAggregatePartReport;
use AppBundle\Entity\SesPartReport;
use AppBundle\Entity\SesReport;
use AppBundle\Entity\SesReportValues;
use AppBundle\Entity\SesAlert;
use AppBundle\Entity\Constant;
use AppBundle\Repository\SesPartReportRepository;
use AppBundle\Services\Exception\LockNotAcquiredException;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Import;
use AppBundle\Entity\Import\Report\XmlExport;
use AppBundle\Entity\Import\Report\XmlReport;
use AppBundle\Entity\Import\Report\XmlReportValue;
use AppBundle\Entity\Import\Report\XmlReportValues;
use JMS\Serializer\Serializer;

use Symfony\Bridge\Monolog\Logger;


class ImportService
{
    const IMPORT_LOCK_NAME = 'REPORTS_IMPORT';

    private $em;
    private $logger ;
    private $backendService;

    /** @var SiteService */
    private $siteService;

    /** @var ReportService  */
    private $reportService;

    /** @var DiseaseService */
    private $diseaseService;

    /**
     * @var LockService
     */
    private $lockService;

    /**
     * @var Serializer
     */
    private $jmsSerializer;

    /**
     * @var string
     */
    private $pathXmlReports;

    /**
     * @var string
     */
    private $pathXsdReports;

    /**
     * @var array
     */
    private $configurationDashboard;

    /**
     * @var bool
     */
    private $autoValidation = false;

    /**
     * @var bool
     */
    private $autoAggregation = false;

    /**
     * @var float
     */
    private $lockTimeout;

    /**
     * @var int
     */
    private $sizeOfFileBatchs;

    public function __construct(EntityManager $em,
                                Logger $logger,
                                BackendService $backendService,
                                SiteService $siteService,
                                ReportService $reportService,
                                DiseaseService $diseaseService,
                                Serializer $jmsSerializer,
                                LockService $lockService,
                                $pathXmlReports,
                                $pathXsdReports,
                                $configurationDashboard,
                                $lockTimeout,
                                $sizeOfFileBatchs)
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->backendService = $backendService;
        $this->siteService = $siteService;
        $this->reportService = $reportService;
        $this->diseaseService = $diseaseService;
        $this->lockService = $lockService;
        $this->jmsSerializer = $jmsSerializer;
        $this->pathXmlReports = $pathXmlReports;
        $this->pathXsdReports = $pathXsdReports;
        $this->configurationDashboard = $configurationDashboard;
        $this->lockTimeout = $lockTimeout;
        $this->sizeOfFileBatchs = $sizeOfFileBatchs;

        // Get the configuration configuration_dashboard auto_validation & auto aggregation
        if (isset($this->configurationDashboard) && $this->configurationDashboard != null){
            $this->autoValidation = $this->configurationDashboard['auto_validation'];
            $this->autoAggregation = $this->configurationDashboard['auto_aggregation'];
        }
        else {
            $this->autoValidation = false;
            $this->autoAggregation = false;
        }
    }

    /**
     * @return bool
     */

    /**
     * @param int $wait How many seconds (and times) to retry to acquire the lock if it is already taken by another process
     * @return bool
     */
    public function acquireImportLock($wait = 0) {
        return $this->lockService->acquireLock(self::IMPORT_LOCK_NAME, $this->lockTimeout, $wait);
    }

    /**
     * @return bool
     */
    public function releaseImportLock() {
        return $this->lockService->releaseLock(self::IMPORT_LOCK_NAME);
    }

    private function getPartReportFromPhoneNumberAndAndroidId($phoneNumber, $androidId, $fullReportId)
    {
        /** @var SesPartReportRepository $repository */
        $repository = $this->em->getRepository('AppBundle:SesPartReport');
        $partReport = $repository->getPartReportFromPhoneNumberAndAndroidId($phoneNumber, $androidId, $fullReportId);

        return $partReport ;
    }

    private function getPartReportFromListWithSpecificStatus($partReports, $status)
    {
        $existingPartReport = null ;
        /** @var SesPartReport $partReport */
        foreach ($partReports as $partReport) {
            if ($partReport->getStatus() == $status) {
                $existingPartReport = $partReport;
                break ;
            }
        }

        return $existingPartReport ;
    }

    /**
     * Check is reports have been translated in all supported languages
     *
     * @param $directorySqlTemplates
     * @param $pathReports
     * @param $directoryDashboardTemplates
     * @param $pathDashboardReports
     * @return array
     */
    public function getReportsTranslated($directorySqlTemplates, $pathReports, $directoryDashboardTemplates, $pathDashboardReports)
    {
        $result = array();

        $files = glob($directorySqlTemplates."/*.twig", GLOB_BRACE);
        {
            foreach($files as $file) {
                $fileName = basename($file);
                $fileNameOutput =  basename($file, ".twig");

                $exist = file_exists($pathReports . $fileNameOutput);

                $result[] = array('Template' => $fileName,
                                    'Translated' => $exist );
            }
        }

        $files = glob($directoryDashboardTemplates."/*.{php.twig}", GLOB_BRACE);
        {
            foreach($files as $file) {
                $fileName = basename($file);
                $fileNameDashboard =  basename($file, ".twig");

                $exist = file_exists($pathDashboardReports . $fileNameDashboard);

                $result[] = array('Template' => $fileName,
                    'Translated' => $exist );
            }
        }

        return $result ;
    }

    /**
     * Scan files in the pathXmlReports folder, and import them
     */
    public function importXmlReportFiles() {
        try {
            $this->logger->info("Start execution: Checking new Xml reports");

            $fileNames = glob($this->pathXmlReports . "/*.{xml}", GLOB_BRACE);

            if (count($fileNames) == 0) {
                $this->logger->info("No Xml reports files founded");
                return;
            }

            $this->logger->info("New Xml reports files founded");

            //Sort files by modification date (To get the oldest file first)
            usort($fileNames, function ($a, $b) {
                return filemtime($a) - filemtime($b);
            });

            libxml_use_internal_errors(true);

            $nbOfFileNames = sizeof($fileNames);

            //We will not import all files once. Batch per batch. To permit to release the lock regularly, if the import is exceptionally long.
            if($nbOfFileNames > 0) {
                $fileIndex = 0;

                while($fileIndex < $nbOfFileNames) {
                    $batchFileNames = [];

                    while ($fileIndex < $nbOfFileNames && sizeof($batchFileNames) < $this->sizeOfFileBatchs) {
                        $batchFileNames[] = $fileNames[$fileIndex];
                        $fileIndex++;
                    }
                    $this->importBatchOfXmlReportFiles($batchFileNames);
                }
            }
        }
        catch(\Exception $e) {
            $this->logger->error(sprintf("Error during the import of XML reports: %s", $e));
        }
    }

    /**
     * @param array $fileNames
     * @throws \Exception
     */
    private function importBatchOfXmlReportFiles(array $fileNames) {
        if(!empty($fileNames)) {
            //Get a lock to prevent parallel imports
            if($this->lockService->acquireLock(self::IMPORT_LOCK_NAME, $this->lockTimeout)) {
                //At this step, we are thread safe
                try {
                    foreach ($fileNames as $fileName) {
                        $result = false;

                        $report = new \DOMDocument();
                        $report->load($fileName);

                        if ($report->schemaValidate($this->pathXsdReports)) {
                            $this->logger->info(sprintf("The file \"%1\$s\" is valid", basename($fileName)));

                            // Deserialize File
                            /** @var XmlExport $import */
                            $import = $this->jmsSerializer->deserialize(file_get_contents($fileName), XmlExport::class, 'xml');

                            if($import !== null) {
                                $result = $this->importXmlReportFile($import);
                            }
                            else {
                                $result = false;
                                $this->logger->error(sprintf("An error occurs when deserializing file %1\$s", $fileName));
                            }
                        }
                        else {
                            $this->logger->info(sprintf("The file \"%1\$s\" is not valid", basename($fileName)));
                            $errors = libxml_get_errors();
                            foreach ($errors as $error) {
                                $this->logger->error(sprintf("%1\$s", $error->message));
                            }
                            libxml_clear_errors();
                        }

                        $this->moveFile($fileName, $result ? $this->getSuccessDir() : $this->getErrorDir());
                    }
                }
                catch(\Exception $e) {
                    $this->logger->error(sprintf("Error during the import of XML reports: %s", $e));
                }
                finally {
                    $this->lockService->releaseLock(self::IMPORT_LOCK_NAME);
                }
            }
            else {
                throw new LockNotAcquiredException("Could not acquire a lock to process the import of XML reports.");
            }
        }
    }

    /**
     * Import Xml report
     * @param XmlExport $import
     * @return bool
     */
    public function importXmlReportFile(XmlExport $import)
    {
        if ($import->getReports() == null) {
            $this->logger->info("No reports to import");
        } else {
            $this->importReport($import->getReports()->getReports(), $this->autoValidation, $this->autoAggregation);
        }

        if ($import->getAlerts() == null) {
            $this->logger->info("No Alerts to import");
        } else {
            $this->ImportAlert($import->getAlerts()->getAlerts());
        }

        return true ;
    }

    /**
     * Import Reports
     *
     * @param $reports
     * @param $autoValidation
     * @param $autoAggregation
     */
    private function importReport($reports, $autoValidation, $autoAggregation)
    {
        //TODO Transaction to reject all the file if error
        if ($reports == null || count($reports) == 0) {
            $this->logger->info("No reports to import");
        } else {
            $this->logger->info(sprintf("%1\$s report(s) to import", count($reports)));
        }

        $count = 1;

        /** @var XmlReport $xmlReport */
        foreach($reports as $xmlReport) {
            //just to refresh the lock, in case when the import last very long. Otherwise the lock will expire and other concurrent process could break it
            $this->acquireImportLock();

            $this->logger->info(sprintf("Importing Report number %1\$s ", $count));

            $count ++ ;
            if ($xmlReport == null) {
                $this->logger->info("ImportReport: Xml report is null");
                continue ;
            }

            if ($xmlReport->getReportValues() == null) {
                $this->logger->info("ImportReport: Xml report values are null");
                continue ;
            }

            //Get the Site corresponding to the site reference
            /** @var SesDashboardSite $site */
            $site = $this->siteService->findSiteByReference($xmlReport->getSiteReference());

            if ($site == null) {
                $this->logger->info(sprintf("ImportReport: Site with reference '%1\$s' is not found", $xmlReport->getSiteReference()));
                continue ;
            }

            // Get Active Relation Ship for this period
            $siteRelationShip = $this->siteService->getActiveRelationShipPeriod($site, $xmlReport->getPeriod(), $xmlReport->getStartDate());
            if ($siteRelationShip == null) {
                if ($xmlReport->getPeriod() == Constant::PERIOD_MONTHLY) {
                    $this->logger->info(sprintf("ImportReport: Site with reference '%1\$s' has no active relation Ship for the month %2\$s year %3\$s",
                        $xmlReport->getSiteReference(),
                        $xmlReport->getMonthNumber(),
                        $xmlReport->getYear()));
                } else if ($xmlReport->getPeriod() == Constant::PERIOD_WEEKLY) {
                    $this->logger->info(sprintf("ImportReport: Site with reference '%1\$s' has no active relation Ship for the week %2\$s year %3\$s",
                        $xmlReport->getSiteReference(),
                        $xmlReport->getWeekNumber(),
                        $xmlReport->getYear()));
                }
                continue ;
            }

            $this->importReportData($site, $siteRelationShip, $xmlReport, $autoValidation, $autoAggregation);
        }
    }

    /**
     * Import Report Data (Create Full Report, Part Report, Report & values)
     *
     * @param SesDashboardSite $site
     * @param SesDashboardSiteRelationShip $siteRelationShip
     * @param XmlReport $xmlReport
     * @param $autoValidation
     * @param $autoAggregation
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function importReportData(SesDashboardSite $site, SesDashboardSiteRelationShip $siteRelationShip, XmlReport $xmlReport, $autoValidation, $autoAggregation)
    {
        // Get The full Report
        /** @var SesFullReport $fullReport */
        $fullReport = $this->reportService->getFullReportFromPeriodSiteStartDate($xmlReport->getPeriod(), $site->getId(), $xmlReport->getStartDate());
        $partReport = null ;

        // Creation of the Report
        $report = SesReport::create($xmlReport->getDisease(), $xmlReport->getReceptionDate());
        $report->setDiseaseEntity($this->diseaseService->findOneBy(array('disease' => $xmlReport->getDisease())));

        // Creation of Report Values
        /** @var XmlReportValues $xmlValues */
        $xmlValues =  $xmlReport->getReportValues();

        /** @var XmlReportValue $xmlReportValue */
        foreach($xmlValues->getReportValues() as $xmlReportValue) {
            $reportValues = SesReportValues::create($xmlReportValue->getValueReference(), $xmlReportValue->getData());
            $reportValues->setReport($report);
            $report->addReportValues($reportValues);
        }

        //FullReport doesn't exist (as Part Report)
        if ($fullReport == null) {
            //Create one fullReport
            $fullReport = SesFullReport::create($site, $siteRelationShip, $xmlReport->getSiteReference(), $xmlReport->getPeriod(), $xmlReport->getStartDate(),
                $xmlReport->getWeekNumber(), $xmlReport->getMonthNumber(), $xmlReport->getYear(), false);
            // Creation of a Part Report.
            $partReport = SesPartReport::create($xmlReport->getContactName(), $xmlReport->getPhoneNumber(), false, $xmlReport->getReportId());
            $partReport->setFullReport($fullReport) ;
            // Adding the part Report in the FullReportList
            $fullReport->addPartReport($partReport);

            // Attach the report to the partReport
            $report->setPartReport($partReport);
            //Adding the report in the Part Report list
            $partReport->addReport($report);

            $fullReport->setStatus(Constant::STATUS_PENDING);
            $partReport->setStatus(Constant::STATUS_PENDING);

            // persist
            $this->em->persist($fullReport);
        } else { // if $fullReport already exist

            if ($xmlReport->getReportId() != null) {
                // Get the Part Report with phone number, reportId and fullReportId
                $partReport = $this->getPartReportFromPhoneNumberAndAndroidId(
                    $xmlReport->getPhoneNumber(),
                    $xmlReport->getReportId(),
                    $fullReport->getId()
                );
            }

            if ($partReport == null) { // we don't have a partReport with this id for the selected period

                // Check the status to know if we need to create a new Part Report
                $partReports = $fullReport->getSortPartReports(function (SesPartReport $a, SesPartReport $b) {
                    return ($a->getId() < $b->getId());
                });

                // Check if we have a pending part Report
                $existPendingPartReport = null ;
                /** @var SesPartReport $partReport */
                foreach ($partReports as $partReport) {
                    if ($partReport != null && $partReport->getStatus() == Constant::STATUS_PENDING) {

                        // If Report Id is not set, do not take a partReport with the AndroidReportId set
                        if ($xmlReport->getReportId() == null && $partReport->getAndroidReportId() != null) {
                            continue ;
                        }

                        $existPendingPartReport = $partReport;
                        break;
                    }
                }

                if ($autoValidation && count($partReports) > 0) {// if autovalidation, we take the first existing part report and replace values
                    $partReport = $partReports[count($partReports) - 1];
                }  else if ( $xmlReport->getReportId() != null || // If Report Id is set, create a new PartReport
                    count($partReports) == 0 ||
                    (count($partReports) > 0 && $existPendingPartReport == null)) {

                    // Creation of a part Report
                    $partReport = SesPartReport::create($xmlReport->getContactName(), $xmlReport->getPhoneNumber(), false, $xmlReport->getReportId());
                    $partReport->setFullReport($fullReport);
                    // Adding the part Report in the FullReportList
                    $fullReport->addPartReport($partReport);

                    $partReport->setStatus(Constant::STATUS_PENDING);

                    if ((count($partReports) > 0) && ($fullReport->getStatus() != Constant::STATUS_PENDING)) {
                        $fullReport->setStatus(Constant::STATUS_CONFLICTING);
                    }
                } else {
                    $partReport = $existPendingPartReport;
                }
            }

            // Search same report from this partReport
            /** @var SesReport $existingReport */
            foreach($partReport->getReports() as $existingReport) {
                if ($existingReport->getDisease() == $xmlReport->getDisease()){
                    // We found an existing report for this disease
                    $existingReport->Archive() ;
                }
            }

            // Attach the report to the partReport
            $report->setPartReport($partReport);
            //Adding the report in the Part Report list
            $partReport->addReport($report);
        }

        if ($autoValidation) {
            $fullReport->setStatus(Constant::STATUS_VALIDATED);
            $partReport->setStatus(Constant::STATUS_VALIDATED);
        }

        $this->em->flush();

        // If we have more than 1 pending report, update status of old pending reports to Superseded
        // After flush then we get the Id
        $this->supersedePendingReports($fullReport);
        $this->em->flush();

        if ($autoAggregation) {
            $this->backendService->generateAutoAggregation($siteRelationShip->getParentSite(), $fullReport, $partReport);
        }

        $this->em->clear();
    }

    /**
     * Supersede old pending reports
     *
     * @param SesFullReport $fullReport
     */
    private function supersedePendingReports(SesFullReport $fullReport)
    {
        $moreThanOnePendingReport = false ;

        if ($fullReport->getPartReports() != null) {
            $partReports = $fullReport->getSortPartReports(function (SesPartReport $a, SesPartReport $b) {
                if ($a->getAndroidReportId() != null && $b->getAndroidReportId() != null) {
                    return ($a->getAndroidReportId() < $b->getAndroidReportId());
                }

                return ($a->getId() < $b->getId());
            });

            /** @var SesPartReport $partReport */
            foreach($partReports as $partReport) {
                if ($partReport->isPending()) {
                    if ($moreThanOnePendingReport) {
                        $partReport->setStatus(Constant::STATUS_SUPERSEDED);
                    }
                    $moreThanOnePendingReport = true ;
                }
            }
        }
    }

    /**
     * Import Alerts
     *
     * @param $alerts
     */
    private function ImportAlert($alerts)
    {
        /** @var Import\Report\XmlAlert $xmlAlert */
        foreach($alerts as $xmlAlert) {
            //just to refresh the lock, in case when the import last very long. Otherwise the lock will expire and other concurrent process could break it
            $this->acquireImportLock();

            if ($xmlAlert == null) {
                $this->logger->info("ImportAlert: Xml alert is null");
                continue ;
            }

            //Get the Site corresponding to the site reference
            $site = $this->siteService->findSiteByReference($xmlAlert->getSiteReference());

            if ($site == null) {
                $this->logger->info(sprintf("ImportAlert: Site with reference '%1\$s' is not found", $xmlAlert->getSiteReference()));
                continue ;
            }

            // Get Active Relation Ship for this period
            $siteRelationShips = $this->siteService->getActiveRelationShip($site, null, null );
            if ($siteRelationShips == null || count($siteRelationShips) == 0) {
                $this->logger->info(sprintf("ImportAlert: Site with reference '%1\$s' has no current active relation Ship", $xmlAlert->getSiteReference()));
                continue ;
            }

            $siteRelationShip = $siteRelationShips[count($siteRelationShips) -1];

            $alert = SesAlert::create($site, $siteRelationShip, $xmlAlert->getSiteReference(), $xmlAlert->getContactName(), $xmlAlert->getPhoneNumber(), $xmlAlert->getReceptionDate(), $xmlAlert->getMessage());

            $this->em->persist($alert);
            $this->em->flush();
        }
    }

    /**
     * Validate a Part Report
     *
     * @param $fullReportId
     * @param $partReportId
     * @param $userName
     * @return null|object
     */
    public function validatePartReport($fullReportId, $partReportId, $userName)
    {
        // Get the FullReport
        /** @var SesFullReport $sesFullReport */
        $sesFullReport = $this->reportService->getFullReport($fullReportId);

        // Get the PartReport
        /** @var SesPartReport $sesPartReport */
        $sesPartReport = $this->reportService->getPartReport($partReportId);

        if (null === $sesFullReport || null == $sesPartReport) { // FullReport or Part Report doesn't exist
            $this->logger->info(sprintf("validatePartReport: FullReport with id [%1\$s] or PartReport with id [%2\$s] is not found", $fullReportId,$partReportId));
            return null;
        }

        $sesPartReport->setStatus(Constant::STATUS_VALIDATED);
        $sesFullReport->setStatus(Constant::STATUS_VALIDATED);

        $brotherPartReports = $sesFullReport->getPartReports() ;
        $listPartReportToRemove = array();

        /** @var SesPartReport $brotherPartReport */
        foreach ($brotherPartReports as $brotherPartReport) {
            if ($brotherPartReport->getId() != $partReportId) {
                $brotherPartReport->setStatus(Constant::STATUS_REJECTED);
                $listPartReportToRemove[] = $brotherPartReport ;
            }
        }

        //Check if Parent exists for the current RelationShip
        /** @var SesDashboardSiteRelationShip $relationShip */
        $relationShip = $sesFullReport->getSiteRelationShip();
        $parentSite = $relationShip->getParentSite();

        if ($parentSite == null) {
            $this->logger->info(sprintf("validatePartReport: Parent Site of Relation Ship [%1\$s] in null", $relationShip->getId()));
            return null;
        }

        //Check if parent has already a FullReport
        $existParentFullReport = $this->reportService->getFullReportFromPeriodSiteStartDate($sesFullReport->getPeriod(), $parentSite->getId(), $sesFullReport->getStartDate());

        if ($existParentFullReport == null) {
            // Get the Active Relation Ship for
            $parentSiteRelationShip = $this->siteService->getActiveRelationShipPeriod($parentSite, $sesFullReport->getPeriod(), $sesFullReport->getStartDate());

            // We have to create this fullReport
            $existParentFullReport = SesFullReport::create($parentSite, $parentSiteRelationShip,"", $sesFullReport->getPeriod(), $sesFullReport->getStartDate(), $sesFullReport->getWeekNumber(),
                $sesFullReport->getMonthNumber(), $sesFullReport->getYear(), true );

            $existParentFullReport->setStatus(Constant::STATUS_PENDING);
            $this->em->persist($existParentFullReport);
        } else {
            switch($existParentFullReport->getStatus()) {
                case Constant::STATUS_VALIDATED:
                case Constant::STATUS_REJECTED:
                    $existParentFullReport->setStatus(Constant::STATUS_CONFLICTING);
                    break;
            }
        }

        // Check if FullReport has already Part Reports
        $existingPartReports = $existParentFullReport->getPartReports();
        // Check if there is a pending Part Report
        $existingPartReportPending = null ;

        /** @var SesPartReport $existingPartReport */
        foreach ($existingPartReports as $existingPartReport) {
            if ($existingPartReport->getStatus() == Constant::STATUS_PENDING){
                $existingPartReportPending = $existingPartReport;
                break ;
            }
        }

        if ($existingPartReportPending == null) {
            // We need to create it
            // If it exists a part report validated, clone it
            $existingPartReportToClone = $this->getPartReportFromListWithSpecificStatus($existingPartReports, Constant::STATUS_VALIDATED);

            if ($existingPartReportToClone == null) {
                $existingPartReportPending = SesPartReport::create('','', true);
                $existingPartReportPending->setStatus(Constant::STATUS_PENDING);
            } else {
                // Clone partReport
                $existingPartReportPending = clone $existingPartReportToClone;
                // Keep only partReport validated
                $aggregatePartReports =  $existingPartReportPending->getAggregatePartReports();
                /** @var SesAggregatePartReport $aggregatePartReport */
                foreach($aggregatePartReports as $aggregatePartReport) {
                    $partReportToKeep = $aggregatePartReport->getPartReport();
                    if ($partReportToKeep == null || $partReportToKeep->getStatus() != Constant::STATUS_VALIDATED) {
                        $existingPartReportPending->removeAggregatePartReport($aggregatePartReport);
                    }
                }
            }

            $existingPartReportPending->setStatus(Constant::STATUS_PENDING);
            $existParentFullReport->addPartReport($existingPartReportPending);
            $this->em->persist($existingPartReportPending);
        } else {
            // We remove other part Report now refused in this aggregate
            $aggregatePartReports = $existingPartReportPending->getAggregatePartReports();
            for($i = 0 ; $i < count($listPartReportToRemove) ; $i++) {
                /** @var SesAggregatePartReport $aggregatePartReport */
                foreach($aggregatePartReports as $aggregatePartReport) {
                    $partReportIdToRemove = $aggregatePartReport->getPartReport()->getId();
                    if ($partReportIdToRemove == $listPartReportToRemove[$i]->getId()) {
                        $this->em->remove($aggregatePartReport);
                        $existingPartReportPending->removeAggregatePartReport($aggregatePartReport);
                    }
                }
            }
        }

        // Check if Table Aggregate Part Report doesn't contains this entry
        $aggregatePartReportAlreadyExists = false ;

        /** @var SesAggregatePartReport $aggregatePartReport */
        foreach ($existingPartReportPending->getAggregatePartReports() as $aggregatePartReport) {
            if ($aggregatePartReport->getPartReport()->getId() == $partReportId){
                $aggregatePartReportAlreadyExists = true ;
                break;
            }
        }

        if (! $aggregatePartReportAlreadyExists) {
            $aggregate = new SesAggregatePartReport();
            $aggregate->setPartReportOwner($existingPartReportPending);
            $aggregate->setPartReport($sesPartReport);
            $existingPartReportPending->addAggregatePartReport($aggregate);
            $this->em->persist($aggregate);
        }

        // Aggregate All the data for the pending part Report
        // Now Calculate the aggregation
        $existingPartReportPending->resetAllValues();
        foreach($existingPartReportPending->getAggregatePartReports() as $linkedPartReport) {
            $partReport = $linkedPartReport->getPartReport();
            if ($partReport != null) {
                $this->backendService->aggregateData($existingPartReportPending, $partReport);
            }
        }

        $this->em->flush();

        return $sesFullReport;
    }

    /**
     * Reject a Part Report
     *
     * @param $fullReportId
     * @param $partReportId
     * @param $userName
     * @return SesFullReport|null
     */
    public function rejectPartReport($fullReportId, $partReportId, $userName)
    {
        // Get the FullReport
        /** @var SesFullReport $sesFullReport */
        $sesFullReport = $this->reportService->getFullReport($fullReportId);

        // Get the PartReport
        /** @var SesPartReport $sesPartReport */
        $sesPartReport = $this->reportService->getPartReport($partReportId);

        if (null === $sesFullReport || null == $sesPartReport) { // FullReport or PartReport doesn't exist
            $this->logger->info(sprintf("validatePartReport: FullReport with id [%1\$s] or PartReport with id [%2\$s] is not found", $fullReportId,$partReportId));
            return null;
        }

        $oldStatus = $sesPartReport->getStatus();

        // STEP 1 : Status Rejected for this part Report
        $sesPartReport->setStatus(Constant::STATUS_REJECTED);
        $sesFullReport->setStatus(Constant::STATUS_REJECTED);

        foreach ($sesFullReport->getPartReports() as $brotherReport) {
            if ($brotherReport->isValidated() && $brotherReport->getId() != $partReportId) {// We keep this Part Report Validated
                $sesFullReport->setStatus(Constant::STATUS_VALIDATED);
            }
        }

        //Check if Parent exists for the current RelationShip
        /** @var SesDashboardSiteRelationShip $relationShip */
        $relationShip = $sesFullReport->getSiteRelationShip();
        $parentSite = $relationShip->getParentSite();

        if ($parentSite == null) {
            $this->logger->info(sprintf("validatePartReport: Parent Site of Relation Ship [%1\$s] in null", $relationShip->getId()));
            return null;
        }

        //Check if parent has already a FullReport
        /** @var SesFullReport $existParentFullReport */
        $existParentFullReport = $this->reportService->getFullReportFromPeriodSiteStartDate($sesFullReport->getPeriod(), $parentSite->getId(), $sesFullReport->getStartDate());

        // STEP 2 : Case when partReport was already validated --> Aggregation was made at upper level
        if ($oldStatus == Constant::STATUS_VALIDATED) {
            if ($existParentFullReport != null) {

                // Check if FullReport has already Part Reports
                $existingPartReports =  $existParentFullReport->getPartReports();
                // Check if there is a pending Part Report
                $existingPartReportPending = null ;
                /** @var SesPartReport $existingPartReport */
                foreach ($existingPartReports as $existingPartReport) {
                    if ($existingPartReport->getStatus() == Constant::STATUS_PENDING) {
                        $existingPartReportPending = $existingPartReport;
                        break ;
                    }
                }

                if ($existingPartReportPending == null) {
                    // We need to create it
                    // If it exists a part report validated, clone it
                    $existingPartReportToClone = $this->getPartReportFromListWithSpecificStatus($existingPartReports, Constant::STATUS_VALIDATED);

                    if ($existingPartReportToClone == null) {
                        $existingPartReportPending = SesPartReport::create('','', true);
                        $existingPartReportPending->setStatus(Constant::STATUS_PENDING);
                    } else {
                        // Clone partReport
                        $existingPartReportPending = clone $existingPartReportToClone;
                    }

                    $existingPartReportPending->setStatus(Constant::STATUS_PENDING);
                    $existParentFullReport->addPartReport($existingPartReportPending);
                    $this->em->persist($existingPartReportPending);
                }

                // Check if Table Aggregate Part Report does contains this entry
                $aggregatePartReports = $existingPartReportPending->getAggregatePartReports() ;

                /** @var SesAggregatePartReport $aggregatePartReport */
                foreach ($aggregatePartReports as $aggregatePartReport) {
                    if ($aggregatePartReport->getPartReport()->getId() == $partReportId) {
                        $this->em->remove($aggregatePartReport);
                        $existingPartReportPending->removeAggregatePartReport($aggregatePartReport);
                        break;
                    }
                }

                switch($existParentFullReport->getStatus()) {
                    case Constant::STATUS_VALIDATED:
                    case Constant::STATUS_REJECTED:
                        $existParentFullReport->setStatus(Constant::STATUS_CONFLICTING);
                        break;
                }

                // Aggregate All the data for the pending part Report
                // Now Calculate the aggregation
                $existingPartReportPending->resetAllValues();
                /** @var SesAggregatePartReport $linkedPartReport */
                foreach($existingPartReportPending->getAggregatePartReports() as $linkedPartReport) {
                    $partReport = $linkedPartReport->getPartReport();
                    if ($partReport != null) {
                        $this->backendService->aggregateData($existingPartReportPending, $partReport);
                    }
                }
            }
        }

        // STEP 3 : When rejecting a report, All children report will be notified
        // Unvalidate children
       if ($sesPartReport->isAggregate()) {
           $aggregatePartReports = $sesPartReport->getAggregatePartReports() ;

           //TODO : Keep the version which was validated and create new one cloned and PENDING
           /** @var SesAggregatePartReport $aggregate */
           foreach ($aggregatePartReports as $aggregate) {
                $partReport = $aggregate->getPartReport() ;
                if ($partReport->getStatus() != Constant::STATUS_REJECTED) {
                    $partReport->setStatus(Constant::STATUS_PENDING);
                }

                $partReport->getFullReport()->setStatus(Constant::STATUS_REJECTED_FROM_ABOVE);
            }
        }

        $this->em->flush();

        return $sesFullReport;
    }

    /**
     * @param $file
     * @param $newDirectory
     */
    private function moveFile($file, $newDirectory)
    {
        try
        {
            rename($file, $newDirectory . basename($file));
        }
        catch (\Exception $ex)
        {
            $this->logger->error(sprintf("An error occurs when moving file \"%1\$s\" : %2\$s", $file, $ex->getMessage()));
        }
    }

    /**
     * @return string
     */
    private function getSuccessDir()
    {
        $path = getcwd(). '/app/work/reports/Success/' ;

        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        return $path;
    }

    /**
     * @return string
     */
    private function getErrorDir()
    {
        $path = getcwd(). '/app/work/reports/Error/' ;

        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        return $path ;
    }

    /**
     * @return LockService
     */
    public function getLockService()
    {
        return $this->lockService;
    }

    /**
     * @param LockService $lockService
     */
    public function setLockService($lockService)
    {
        $this->lockService = $lockService;
    }

    /**
     * @return mixed
     */
    public function getJmsSerializer()
    {
        return $this->jmsSerializer;
    }

    /**
     * @param mixed $jmsSerializer
     */
    public function setJmsSerializer($jmsSerializer)
    {
        $this->jmsSerializer = $jmsSerializer;
    }

    /**
     * @return string
     */
    public function getPathXmlReports()
    {
        return $this->pathXmlReports;
    }

    /**
     * @param string $pathXmlReports
     */
    public function setPathXmlReports($pathXmlReports)
    {
        $this->pathXmlReports = $pathXmlReports;
    }

    /**
     * @return string
     */
    public function getPathXsdReports()
    {
        return $this->pathXsdReports;
    }

    /**
     * @param string $pathXsdReports
     */
    public function setPathXsdReports($pathXsdReports)
    {
        $this->pathXsdReports = $pathXsdReports;
    }

    /**
     * @return array
     */
    public function getConfigurationDashboard()
    {
        return $this->configurationDashboard;
    }

    /**
     * @param array $configurationDashboard
     */
    public function setConfigurationDashboard($configurationDashboard)
    {
        $this->configurationDashboard = $configurationDashboard;
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param Logger $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return SiteService
     */
    public function getSiteService()
    {
        return $this->siteService;
    }

    /**
     * @param SiteService $siteService
     */
    public function setSiteService($siteService)
    {
        $this->siteService = $siteService;
    }

    /**
     * @return ReportService
     */
    public function getReportService()
    {
        return $this->reportService;
    }

    /**
     * @param ReportService $reportService
     */
    public function setReportService($reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * @return bool
     */
    public function isAutoValidation()
    {
        return $this->autoValidation;
    }

    /**
     * @param bool $autoValidation
     */
    public function setAutoValidation($autoValidation)
    {
        $this->autoValidation = $autoValidation;
    }

    /**
     * @return bool
     */
    public function isAutoAggregation()
    {
        return $this->autoAggregation;
    }

    /**
     * @param bool $autoAggregation
     */
    public function setAutoAggregation($autoAggregation)
    {
        $this->autoAggregation = $autoAggregation;
    }

    /**
     * @return float
     */
    public function getLockTimeout()
    {
        return $this->lockTimeout;
    }

    /**
     * @param float $lockTimeout
     */
    public function setLockTimeout($lockTimeout)
    {
        $this->lockTimeout = $lockTimeout;
    }

    /**
     * @return int
     */
    public function getSizeOfFileBatchs()
    {
        return $this->sizeOfFileBatchs;
    }

    /**
     * @param int $sizeOfFileBatchs
     */
    public function setSizeOfFileBatchs($sizeOfFileBatchs)
    {
        $this->sizeOfFileBatchs = $sizeOfFileBatchs;
    }
}
