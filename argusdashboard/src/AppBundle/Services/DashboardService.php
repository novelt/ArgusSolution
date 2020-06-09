<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 4/8/2016
 * Time: 9:56 AM
 */

namespace AppBundle\Services;

use AppBundle\Entity\Constant;
use AppBundle\Entity\Security\SesDashboardUser;
use AppBundle\Entity\SesDashboardDisease;
use AppBundle\Entity\SesDashboardIndicatorDimDateType;
use AppBundle\Entity\SesDashboardSite;
use AppBundle\Repository\Gateway\GatewayQueueRepository;
use AppBundle\Repository\SesFullReportRepository;
use AppBundle\Repository\SesReportRepository;
use AppBundle\Services\IndicatorsCalculation\IndicatorDimDateService;
use AppBundle\Utils\DimDateHelper;
use AppBundle\Utils\Epidemiologic;
use Doctrine\ORM\EntityManager;
use DateTime;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Translation\Translator;

class DashboardService
{
    private $em;
    /** @var SesFullReportRepository  */
    private $fullReportRepository;

    /** @var SesReportRepository  */
    private $reportRepository;
    private $alertRepository;

    /** @var  IndicatorDimDateService */
    private $dimDateService;

    /** @var GatewayQueueRepository */
    private $gatewayRepository;

    /** @var SiteService  */
    private $siteService;

    /** @var DiseaseService  */
    private $diseaseService;

    private $epiFirstDay;

    private $pathDashboards;

    const WEEKLY_DELAY = (7*24*60); // delay in minutes

    public function __construct(EntityManager $em,
                                IndicatorDimDateService $dimDateService,
                                SiteService $siteService,
                                DiseaseService $diseaseService,
                                $epiFirstDay,
                                $pathDashboards)
    {
        $this->em = $em;
        $this->fullReportRepository = $this->em->getRepository('AppBundle:SesFullReport');
        $this->alertRepository = $this->em->getRepository('AppBundle:SesAlert');
        $this->reportRepository = $this->em->getRepository('AppBundle:SesReport');
        $this->gatewayRepository = $this->em->getRepository('AppBundle:Gateway\GatewayQueue');

        $this->dimDateService = $dimDateService;
        $this->siteService = $siteService;
        $this->diseaseService = $diseaseService;

        $this->epiFirstDay = $epiFirstDay;
        $this->pathDashboards = $pathDashboards;
    }

    /**
     * Return the number of Expected reports of this period (for each week where the sites are actives)
     * /!\  Active only if a contact exists - Based on the indicator calculation
     *
     * @param $siteId
     * @param $startDate
     * @param $endDate
     * @return int
     */
    public function getNumberOfExpectedWeeklyReportPeriod($siteId, $startDate, $endDate)
    {
        $expectedReport = 0;

        $format = 'Y-m-d';
        $startDateTime = DateTime::createFromFormat($format, $startDate);
        $endDateTime = DateTime::createFromFormat($format, $endDate);

        $firstAndLastDaysOfEpidemiologicWeeksIds = $this->dimDateService->findFirstAndLastDimDateOfEpidemiologicWeeksIdsByDateRange($startDateTime, $endDateTime);

        foreach ($firstAndLastDaysOfEpidemiologicWeeksIds as $range) {
            $expectedReport += $this->siteService->getWeeklyActiveSiteRelations($siteId, $range['firstId'], $range['lastId']);
        }

        return $expectedReport ;
    }

    /**
     * Return the number of Expected reports of this period (for each month where the sites are actives)
     * /!\  Active only if a contact exists - Based on the indicator calculation
     *
     * @param $siteId
     * @param $startDate
     * @param $endDate
     * @return int
     */
    public function getNumberOfExpectedMonthlyReportPeriod($siteId, $startDate, $endDate)
    {
        $expectedReport = 0;

        $format = 'Y-m-d';
        $starDateTime = DateTime::createFromFormat($format, $startDate);
        $endDateTime = DateTime::createFromFormat($format, $endDate);

        $firstAndLastDaysOfCalendarMonthsIds = $this->dimDateService->findFirstAndLastDimDatesOfCalendarMonthsIdsByDateRange($starDateTime, $endDateTime);

        foreach ($firstAndLastDaysOfCalendarMonthsIds as $range) {
            $expectedReport += $this->siteService->getMonthlyActiveSiteRelations($siteId, $range['firstId'], $range['lastId']);
        }

        return $expectedReport ;
    }

    /**
     * Get Number of received weekly report for a specific $siteId, a specific $weekNumber and $year
     *
     * @param $siteId
     * @param $weekNumber
     * @param $year
     * @param null $contactId
     * @param null $contactTypeId
     * @return mixed
     */
    public function getNumberOfReceivedWeeklyReport($siteId, $weekNumber, $year, $contactId=null, $contactTypeId=null)
    {
        $result = $this->fullReportRepository->getNumberOfReport($siteId, $weekNumber, null, $year, Constant::PERIOD_WEEKLY, Constant::STATUS_ALL, $contactId, $contactTypeId) ;
        return $result;
    }

    /**
     * Get Number of received monthly report for a specific $siteId, a specific $monthNumber and $year
     *
     * @param $siteId
     * @param $monthNumber
     * @param $year
     * @return mixed
     */
    public function getNumberOfReceivedMonthlyReport($siteId, $monthNumber, $year)
    {
        $result = $this->fullReportRepository->getNumberOfReport($siteId, null, $monthNumber, $year, Constant::PERIOD_MONTHLY, Constant::STATUS_ALL) ;
        return $result;
    }

    /**
     * Get Number of received report for a specific $SiteId, between $startDate and $endDate
     *
     * @param $siteId
     * @param $startDate
     * @param $endDate
     * @return mixed
     */
    public function getNumberOfReceivedWeeklyReportPeriod($siteId, $startDate, $endDate)
    {
        $result = $this->fullReportRepository->getNumberOfReportPeriod($siteId, $startDate, $endDate, Constant::PERIOD_WEEKLY, Constant::STATUS_ALL) ;
        return $result;
    }

    /**
     * Get Number of received monthly report for a specific $SiteId, between $startDate and $endDate
     *
     * @param $siteId
     * @param $startDate
     * @param $endDate
     * @return mixed
     */
    public function getNumberOfReceivedMonthlyReportPeriod($siteId, $startDate, $endDate)
    {
        $result = $this->fullReportRepository->getNumberOfReportPeriod($siteId, $startDate, $endDate, Constant::PERIOD_MONTHLY, Constant::STATUS_ALL) ;
        return $result;
    }

    /**
     * Get Number of received ON TIME Weekly report for a specific $siteId, $weekNumber and $year, regarding the $delay authorized to be on time (compared to the $startDate)
     * REMARK: This method would replace the above method getNumberOfReceivedOnTimeWeeklyReport()
     * @param $siteIds
     * @param $weekNumber
     * @param $year
     * @return int
     */
    public function getNumberOfReceivedOnTimeWeeklyReports($siteIds, $weekNumber, $year, $contactId=null, $contactTypeId=null) {
        return $this->fullReportRepository->getNumberOfReceivedOnTimeWeeklyReports($siteIds, $weekNumber, $year, $contactId, $contactTypeId, self::WEEKLY_DELAY);
    }

    /**
     * Get Number of received ON TIME Monthly report for a specific $siteId, $monthNumber and $year, regarding the $delay authorized to be on time (compared to the $startDate)
     *
     * @param $siteId
     * @param $monthNumber
     * @param $year
     *
     * @return mixed
     */
    public function getNumberOfReceivedOnTimeMonthlyReports($siteId, $monthNumber, $contactId=null, $contactTypeId=null, $year)
    {
        $result = $this->fullReportRepository->getNumberOfReceivedOnTimeMonthlyReports($siteId, $monthNumber, $year, $contactId, $contactTypeId, 0) ;
        return $result;
    }

    /**
     * Get Number of received ON TIME weekly report for a specific $siteId, between $startDate and $endDate, regarding the $delay authorized to be on time
     *
     * @param $siteId
     * @param $startDate
     * @param $endDate
     *
     * @return mixed
     */
    public function getNumberOfReceivedOnTimeWeeklyReportPeriod($siteId, $startDate, $endDate)
    {
        $result = $this->getReceivedOnTimeWeeklyReportPeriod($siteId, $startDate, $endDate) ;
        return count($result);
    }

    /**
     * Get Number of received and addressed ON TIME weekly report for a specific $siteId, between $startDate and $endDate, regarding the $delay and $validationDelay authorized to be on time
     *
     * @param $siteId
     * @param $startDate
     * @param $endDate
     * @param $validationDelay
     * @return int
     */
    public function getNumberOfReceivedAndAddressedOnTimeWeeklyReportPeriod($siteId, $startDate, $endDate, $validationDelay)
    {
        $result = $this->getReceivedAndAddressedOnTimeWeeklyReportPeriod($siteId, $startDate, $endDate, $validationDelay) ;
        return count($result);
    }

    /**
     * Get Weekly Full Report received ON TIME
     *
     * @param $siteId
     * @param $startDate
     * @param $endDate
     * @return array
     */
    private function getReceivedOnTimeWeeklyReportPeriod($siteId, $startDate, $endDate)
    {
        $result = $this->fullReportRepository->getReportPeriod($siteId, $startDate, $endDate, Constant::PERIOD_WEEKLY, Constant::STATUS_ALL);

        $return = [] ;

        if ($result != null) {
            for ($i = 0; $i < count($result); $i++) {

                $receptionDate = strtotime($result[$i]['receptionDate']);

                /** @var DateTime $startDate */
                $startDate = $result[$i]['startDate'];
                $startDate = $startDate->getTimestamp();

                $weeklyTimelinessMinutes = $result[$i]['weeklyTimelinessMinutes'];
                $totalDelay = $weeklyTimelinessMinutes + self::WEEKLY_DELAY ;

                $time = strtotime("+ ". $totalDelay ." minutes", $startDate); // delay + site weeklyTimelinessMinutes param

                if ($receptionDate <=  $time){
                    $return[] = $result[$i];
                }
            }
        }

        return $return ;
    }

    /**
     * Get Weekly Full Report received ON TIME and addressed ON TIME
     *
     * @param $siteId
     * @param $startDate
     * @param $endDate
     * @param $validationDelay
     * @return array
     */
    private function getReceivedAndAddressedOnTimeWeeklyReportPeriod($siteId, $startDate, $endDate, $validationDelay)
    {
        $result = $this->getReceivedOnTimeWeeklyReportPeriod($siteId, $startDate, $endDate) ;
        $return = array() ;

        if ($result != null) {
            for ($i = 0; $i < count($result); $i++) {
                /** @var DateTime $addressedDate */
                $addressedDate = $this->getFirstAddressedDate($result[$i]['firstValidationDate'], $result[$i]['firstRejectionDate']);

                if ($addressedDate == null) {
                    continue;
                } else {
                    $addressedDate = $addressedDate->getTimestamp();
                }
                /** @var DateTime $startDate */
                $startDate = $result[$i]['startDate'];
                $startDate = $startDate->getTimestamp();

                $totalDelay = self::WEEKLY_DELAY + $validationDelay ;

                $time = strtotime("+ ". $totalDelay ." minutes", $startDate); //  delay + Validation delay

                if ($addressedDate <=  $time){
                    $return[] = $result[$i] ;
                }
            }
        }

        return $return ;
    }

    /**
     * Return min date between 2 dates. Null if the dates are null
     *
     * @param DateTime|null $date1
     * @param DateTime|null $date2
     * @return null|DateTime
     */
    private function getFirstAddressedDate($date1, $date2)
    {
        if ($date1 == null && $date2 == null) {
            return null;
        }

        if ($date1 == null) {
            return $date2;
        }

        if ($date2 == null) {
            return $date1;
        }

        return ($date1->getTimestamp() < $date2->getTimestamp() ? $date1 : $date2);
    }

    /**
     * Get Number of received ON TIME monthly report for a specific $siteId, between $startDate and $endDate, regarding the $delay authorized to be on time
     *
     * @param $siteId
     * @param $startDate
     * @param $endDate
     *
     * @return mixed
     */
    public function getNumberOfReceivedOnTimeMonthlyReportPeriod($siteId, $startDate, $endDate)
    {
        $result = $this->getReceivedOnTimeMonthlyReportPeriod($siteId, $startDate, $endDate);
        return count($result);
    }

    /**
     * GET Number of received and Addressed ON TIME monthly report for a specific $siteId, between $startDate and $endDate, regarding the $delay and $validationDelay authorized to be on time
     *
     * @param $siteId
     * @param $startDate
     * @param $endDate
     * @param $validationDelay
     * @return int
     */
    public function getNumberOfReceivedAndAddressedOnTimeMonthlyReportPeriod($siteId, $startDate, $endDate, $validationDelay)
    {
        $result = $this->getReceivedAndAddressedOnTimeMonthlyReportPeriod($siteId, $startDate, $endDate, $validationDelay) ;
        return count($result);
    }

    /**
     * GET Monthly Full Report received
     *
     * @param $siteId
     * @param $startDate
     * @param $endDate
     * @return array
     */
    private function getReceivedOnTimeMonthlyReportPeriod($siteId, $startDate, $endDate)
    {
        $result = $this->fullReportRepository->getReportPeriod($siteId, $startDate, $endDate, Constant::PERIOD_MONTHLY, Constant::STATUS_ALL);

        $return = array() ;

        if ($result != null) {
            for ($i = 0; $i < count($result); $i++) {
                $receptionDate = strtotime($result[$i]['receptionDate']);

                /** @var DateTime $startDate */
                $startDate = strtotime($result[$i]['startDate']);
                $startDate = $startDate->getTimestamp();

                $monthlyTimelinessMinutes = $result[$i]['monthlyTimelinessMinutes'];

                $time = strtotime("first day of next month", $startDate); // first day of next month
                $time = strtotime("+". $monthlyTimelinessMinutes." minutes", $time); //  site monthlyTimelinessMinutes param

                if ($receptionDate <=  $time){
                    $return[] = $result[$i];
                }
            }
        }

        return $return ;
    }

    /**
     * GET Monthly received and addressed Full Report
     *
     * @param $siteId
     * @param $startDate
     * @param $endDate
     * @param $validationDelay
     * @return array
     */
    private function getReceivedAndAddressedOnTimeMonthlyReportPeriod($siteId, $startDate, $endDate, $validationDelay)
    {
        $result = $this->getReceivedOnTimeMonthlyReportPeriod($siteId, $startDate, $endDate) ;
        $return = array() ;

        if ($result != null) {
            for ($i = 0; $i < count($result); $i++) {
                /** @var DateTime $addressedDate */
                $addressedDate = $this->getFirstAddressedDate($result[$i]['firstValidationDate'], $result[$i]['firstRejectionDate']);

                if ($addressedDate == null) {
                    continue;
                } else {
                    $addressedDate = $addressedDate->getTimestamp();
                }

                /** @var DateTime $startDate */
                $startDate = $result[$i]['startDate'];
                $startDate = $startDate->getTimestamp();

                $time = strtotime("first day of next month", $startDate); //  delay
                $time = strtotime("+". $validationDelay ." minutes", $time); //  Validation delay

                if ($addressedDate <=  $time){
                    $return[] = $result[$i] ;
                }
            }
        }

        return $return ;
    }

    /**
     * GET Number of Validated Disease values
     *
     * @param $siteId
     * @param $disease
     * @param $diseaseValue
     * @param $weekNumber
     * @param $monthNumber
     * @param $period
     * @param $year
     * @return mixed
     */
    public function getNumberOfValidatedDiseaseValues($siteId, $disease, $diseaseValue, $weekNumber, $monthNumber, $period, $year, $contactId=null, $contactTypeId=null){
        $result = $this->fullReportRepository->getNumberOfValidatedDiseaseValues($siteId, $disease, $diseaseValue, $weekNumber, $monthNumber, $period, $year, $contactId, $contactTypeId) ;
        return $result;
    }

    /**
     * @param $siteIds
     * @param $disease
     * @param $diseaseValue
     * @param $weekNumber
     * @param $monthNumber
     * @param $period
     * @param $year
     * @param $contactId
     * @return int|null
     */
    public function getNumberOfValidatedDiseaseZeroValues($siteIds, $weekNumber, $monthNumber, $period, $year, $contactId, $contactTypeId=null) {
        $result = $this->fullReportRepository->getNumberOfValidatedDiseaseZeroValues($siteIds, $weekNumber, $monthNumber, $period, $year, $contactId, $contactTypeId);
        return $result;
    }

    /**
     * GET Number of Validated Disease values during a period
     *
     * @param $siteId
     * @param $disease
     * @param $diseaseValue
     * @param $startDate
     * @param $endDate
     * @return mixed
     */
    public function getNumberOfValidatedDiseaseValuesFromPeriod($siteId, $disease, $diseaseValue, $startDate, $endDate){
        $result = $this->fullReportRepository->getNumberOfValidatedDiseaseValuesFromPeriod($siteId, $disease, $diseaseValue, $startDate, $endDate) ;
        return $result;
    }

    /**
     * GET Alerts
     *
     * @param $siteIds
     * @param $startDate
     * @param $endDate
     * @return mixed
     */
    public function getAlerts($siteIds, $startDate, $endDate){
        $result = $this->alertRepository->getAlerts($siteIds, $startDate, $endDate) ;
        return $result;
    }

    /**
     * Get number of Validated Weekly Report for a specific $siteId, a specific $weekNumber and $year
     *
     * @param $siteIds
     * @param $weekNumber
     * @param $year
     * @return mixed
     */
    public function getNumberOfValidatedWeeklyReport($siteIds, $weekNumber, $year){
        $result = $this->fullReportRepository->getNumberOfReport($siteIds, $weekNumber, null, $year, Constant::PERIOD_WEEKLY, Constant::STATUS_VALIDATED) ;
        return $result;
    }

    /**
     * Get number of Validated Monthly Report for a specific $siteId, a specific $monthNumber and $year
     *
     * @param $siteIds
     * @param $monthNumber
     * @param $year
     * @return mixed
     */
    public function getNumberOfValidatedMonthlyReport($siteIds, $monthNumber, $year){
        $result = $this->fullReportRepository->getNumberOfReport($siteIds, null, $monthNumber, $year, Constant::PERIOD_MONTHLY, Constant::STATUS_VALIDATED) ;
        return $result;
    }

    /**
     * Return number of validated Weekly reports for a specific $siteId, between $startDate and $endDate
     *
     * @param $siteIds
     * @param $startDate
     * @param $endDate
     * @return mixed
     */
    public function getNumberOfValidatedWeeklyReportPeriod($siteIds, $startDate, $endDate){
        $result = $this->fullReportRepository->getNumberOfReportPeriod($siteIds, $startDate, $endDate, Constant::PERIOD_WEEKLY, Constant::STATUS_VALIDATED) ;
        return $result;
    }

    /**
     * Return number of validated Monthly reports for a specific $siteId, between $startDate and $endDate
     *
     * @param $siteIds
     * @param $startDate
     * @param $endDate
     * @return mixed
     */
    public function getNumberOfValidatedMonthlyReportPeriod($siteIds, $startDate, $endDate){
        $result = $this->fullReportRepository->getNumberOfReportPeriod($siteIds, $startDate, $endDate, Constant::PERIOD_MONTHLY, Constant::STATUS_VALIDATED) ;
        return $result;
    }

    /**
     * Return number of rejected Weekly reports for a specific $siteId, a specific $weekNumber and $year
     *
     * @param $siteIds
     * @param $weekNumber
     * @param $year
     * @return mixed
     */
    public function getNumberOfRejectedWeeklyReport($siteIds, $weekNumber, $year) {
        $result = $this->fullReportRepository->getNumberOfReport($siteIds, $weekNumber, null, $year, Constant::PERIOD_WEEKLY, Constant::STATUS_REJECTED) ;
        return $result;
    }

    /**
     * Return number of rejected Monthly reports for a specific $siteId, a specific $monthNumber and $year
     *
     * @param $siteIds
     * @param $monthNumber
     * @param $year
     * @return mixed
     */
    public function getNumberOfRejectedMonthlyReport($siteIds, $monthNumber, $year) {
        $result = $this->fullReportRepository->getNumberOfReport($siteIds, null, $monthNumber, $year, Constant::PERIOD_MONTHLY, Constant::STATUS_REJECTED) ;
        return $result;
    }

    /**
     * Return number of Rejected Weekly reports for a specific $siteId, between $startDate and $endDate
     *
     * @param $siteIds
     * @param $startDate
     * @param $endDate
     * @return mixed
     */
    public function getNumberOfRejectedWeeklyReportPeriod($siteIds, $startDate, $endDate) {
        $result = $this->fullReportRepository->getNumberOfReportPeriod($siteIds, $startDate, $endDate, Constant::PERIOD_WEEKLY, Constant::STATUS_REJECTED) ;
        return $result;
    }

    /**
     * Return number of Rejected Monthly reports for a specific $siteId, between $startDate and $endDate
     *
     * @param $siteIds
     * @param $startDate
     * @param $endDate
     * @return mixed
     */
    public function getNumberOfRejectedMonthlyReportPeriod($siteIds, $startDate, $endDate) {
        $result = $this->fullReportRepository->getNumberOfReportPeriod($siteIds, $startDate, $endDate, Constant::PERIOD_MONTHLY, Constant::STATUS_REJECTED) ;
        return $result;
    }

    /**
     * Return number of Pending Weekly reports for a specific $siteId, $weekNumber and $year
     *
     * @param $siteIds
     * @param $weekNumber
     * @param $year
     * @return mixed
     */
    public function getNumberOfPendingWeeklyReport($siteIds, $weekNumber, $year){
        $result = $this->fullReportRepository->getNumberOfReport($siteIds, $weekNumber, null, $year, Constant::PERIOD_WEEKLY, Constant::STATUS_PENDING) ;
        return $result;
    }

    /**
     * Return number of Pending Weekly reports for a specific $siteId, between $startDate and $endDate
     *
     * @param $siteIds
     * @param $startDate
     * @param $endDate
     * @return mixed
     */
    public function getNumberOfPendingWeeklyReportPeriod($siteIds, $startDate, $endDate) {
        $result = $this->fullReportRepository->getNumberOfReportPeriod($siteIds, $startDate, $endDate, Constant::PERIOD_WEEKLY, Constant::STATUS_PENDING) ;
        return $result;
    }

    /**
     * Return number of Pending Monthly reports for a specific $siteId, $monthNumber and $year
     *
     * @param $siteIds
     * @param $monthNumber
     * @param $year
     * @return mixed
     */
    public function getNumberOfPendingMonthlyReport($siteIds, $monthNumber, $year){
        $result = $this->fullReportRepository->getNumberOfReport($siteIds, null, $monthNumber, $year, Constant::PERIOD_MONTHLY, Constant::STATUS_PENDING) ;
        return $result;
    }

    /**
     * Return number of Pending Monthly reports for a specific $siteId, between $startDate and $endDate
     *
     * @param $siteIds
     * @param $startDate
     * @param $endDate
     * @return mixed
     */
    public function getNumberOfPendingMonthlyReportPeriod($siteIds, $startDate, $endDate){
        $result = $this->fullReportRepository->getNumberOfReportPeriod($siteIds, $startDate, $endDate, Constant::PERIOD_MONTHLY, Constant::STATUS_PENDING) ;
        return $result;
    }

    /**
     * Get last Report received ever
     *
     * @param $siteId
     * @return mixed
     */
    public function getLastReportReceivedEver($siteId)
    {
        $result = $this->reportRepository->getLastReportReceivedEver($siteId);
        return $result ;
    }

    /**
     * @param $contactPhoneNumber
     * @return mixed
     */
    public function getLastMessageSentEver($contactPhoneNumber)
    {
        $result = $this->gatewayRepository->getLastMessageSentEver($contactPhoneNumber);
        return $result ;
    }

    /**
     * @param $contactPhoneNumber
     * @param $from
     * @param $to
     * @return mixed
     */
    public function getNumberOfMessageSent($contactPhoneNumber, $from, $to)
    {
        $result = $this->gatewayRepository->getNumberOfMessageSent($contactPhoneNumber, $from, $to);
        return $result ;
    }

    /**
     * Return number of Weekly Addressed reports for a specific $siteId, between $startDate and $endDate
     *
     * @param $siteIds
     * @param $startDate
     * @param $endDate
     * @return mixed
     */
    public function getNumberOfAddressedWeeklyReportPeriod($siteIds, $startDate, $endDate)
    {
        $result = $this->fullReportRepository->getNumberOfAddressedReportPeriod($siteIds, $startDate, $endDate, Constant::PERIOD_WEEKLY, Constant::STATUS_ALL) ;
        return $result;
    }

    /**
     * Return number of Monthly Addressed reports for a specific $siteId, between $startDate and $endDate
     *
     * @param $siteIds
     * @param $startDate
     * @param $endDate
     * @return mixed
     */
    public function getNumberOfAddressedMonthlyReportPeriod($siteIds, $startDate, $endDate)
    {
        $result = $this->fullReportRepository->getNumberOfAddressedReportPeriod($siteIds, $startDate, $endDate, Constant::PERIOD_MONTHLY, Constant::STATUS_ALL) ;
        return $result;
    }

    /**
     * Return Number of Created Weekly Report ON TIME
     *
     * @param $siteIds
     * @param $startDate
     * @param $endDate
     * @return int
     */
    public function getNumberOfCreatedWeeklyReportOnTimePeriod($siteIds, $startDate, $endDate)
    {
        $result = $this->getCreatedWeeklyReportOnTimePeriod($siteIds, $startDate, $endDate);
        return count($result);
    }

    /**
     * Return Number of Created and Addressed Weekly Report ON TIME
     *
     * @param $siteIds
     * @param $startDate
     * @param $endDate
     * @param $validationDelay
     * @return int
     */
    public function getNumberOfCreatedAndAddressedWeeklyReportOnTimePeriod($siteIds, $startDate, $endDate, $validationDelay)
    {
        $result = $this->getCreatedAndAddressedWeeklyReportOnTimePeriod($siteIds, $startDate, $endDate, $validationDelay);
        return count($result);
    }

    /**
     * Return Created Weekly Full Report ON TIME
     *
     * @param $siteIds
     * @param $startDate
     * @param $endDate
     * @return array
     */
    private function getCreatedWeeklyReportOnTimePeriod($siteIds, $startDate, $endDate)
    {
        $result = $this->fullReportRepository->getReportPeriod($siteIds, $startDate, $endDate, Constant::PERIOD_WEEKLY, Constant::STATUS_ALL) ;

        $return = array() ;

        if ($result != null) {
            for ($i = 0; $i < count($result); $i++) {
                /** @var DateTime $createdDate */
                $createdDate = $result[$i]['createdDate'] ;
                if ($createdDate == null){
                    continue;
                }
                else {
                    $createdDate = $createdDate->getTimeStamp();
                }

                /** @var DateTime $startDate */
                $startDate = $result[$i]['startDate'];
                $startDate = $startDate->getTimestamp();

                $weeklyTimelinessMinutes = $result[$i]['weeklyTimelinessMinutes'];
                $totalDelay = $weeklyTimelinessMinutes + self::WEEKLY_DELAY ;

                $time = strtotime("+ ". $totalDelay ." minutes", $startDate); // delay + site weeklyTimelinessMinutes param

                if ($createdDate <=  $time){
                    $return[] = $result[$i] ;
                }
            }
        }

        return $return ;
    }

    /**
     * Return Created and Addressed Weekly Full Report ON TIME
     *
     * @param $siteIds
     * @param $startDate
     * @param $endDate
     * @param $validationDelay
     * @return array
     */
    private function getCreatedAndAddressedWeeklyReportOnTimePeriod($siteIds, $startDate, $endDate, $validationDelay)
    {
        $result = $this->getCreatedWeeklyReportOnTimePeriod($siteIds, $startDate, $endDate) ;
        $return = array() ;

        if ($result != null) {
            for ($i = 0; $i < count($result); $i++) {
                /** @var DateTime $addressedDate */
                $addressedDate = $this->getFirstAddressedDate($result[$i]['firstValidationDate'], $result[$i]['firstRejectionDate']);

                if ($addressedDate == null) {
                    continue;
                }
                else{
                    $addressedDate = $addressedDate->getTimestamp();
                }

                /** @var DateTime $startDate */
                $startDate = $result[$i]['startDate'];
                $startDate = $startDate->getTimestamp();

                $totalDelay = self::WEEKLY_DELAY + $validationDelay ;

                $time = strtotime("+ ". $totalDelay ." minutes", $startDate); //  delay + Validation delay

                if ($addressedDate <=  $time){
                    $return[] = $result[$i] ;
                }
            }
        }

        return $return ;
    }

    /**
     * Return Number of created Monthly Report ON TIME
     *
     * @param $siteIds
     * @param $startDate
     * @param $endDate
     * @return int
     */
    public function getNumberOfCreatedMonthlyReportOnTimePeriod($siteIds, $startDate, $endDate)
    {
        $result = $this->getCreatedMonthlyReportOnTimePeriod($siteIds, $startDate, $endDate);
        return count($result);
    }

    /**
     * Return Number of created and addressed Monthly Report ON TIME
     *
     * @param $siteIds
     * @param $startDate
     * @param $endDate
     * @param $validationDelay
     * @return int
     */
    public function getNumberOfCreatedAndAddressedMonthlyReportOnTimePeriod($siteIds, $startDate, $endDate, $validationDelay)
    {
        $result = $this->getCreatedAndAddressedMonthlyReportOnTimePeriod($siteIds, $startDate, $endDate, $validationDelay);
        return count($result);
    }

    /**
     * Return created monthly Full Report ON TIME
     *
     * @param $siteIds
     * @param $startDate
     * @param $endDate
     * @return array
     */
    private function getCreatedMonthlyReportOnTimePeriod($siteIds, $startDate, $endDate)
    {
        $result = $this->fullReportRepository->getReportPeriod($siteIds, $startDate, $endDate, Constant::PERIOD_MONTHLY, Constant::STATUS_ALL) ;

        $return = array() ;

        if ($result != null) {
            for ($i = 0; $i < count($result); $i++) {
                /** @var DateTime $createdDate */
                $createdDate = $result[$i]['createdDate'] ;

                if ($createdDate == null){
                    continue;
                }
                else {
                    $createdDate = $createdDate->getTimestamp();
                }

                /** @var DateTime $startDate */
                $startDate = $result[$i]['startDate'];
                $startDate = $startDate->getTimestamp();

                $monthlyTimelinessMinutes = $result[$i]['monthlyTimelinessMinutes'];

                $time = strtotime("first day of next month", $startDate); // first day of next month
                $time = strtotime("+". $monthlyTimelinessMinutes ." minutes", $time); // + site monthlyTimelinessMinutes param

                if ($createdDate <=  $time){
                    $return[] = $result[$i] ;
                }
            }
        }

        return $return ;
    }

    /**
     * Return created and addressed monthly Full Report ON TIME
     *
     * @param $siteIds
     * @param $startDate
     * @param $endDate
     * @param $validationDelay
     * @return array
     */
    private function getCreatedAndAddressedMonthlyReportOnTimePeriod($siteIds, $startDate, $endDate, $validationDelay)
    {
        $result = $this->getCreatedMonthlyReportOnTimePeriod($siteIds, $startDate, $endDate) ;
        $return = array() ;

        if ($result != null) {
            for ($i = 0; $i < count($result); $i++) {
                /** @var DateTime $addressedDate */
                $addressedDate = $this->getFirstAddressedDate($result[$i]['firstValidationDate'], $result[$i]['firstRejectionDate']);

                if ($addressedDate == null) {
                    continue;
                }
                else{
                    $addressedDate = $addressedDate->getTimestamp();
                }

                /** @var DateTime $startDate */
                $startDate = $result[$i]['startDate'];
                $startDate = $startDate->getTimestamp();

                $time = strtotime("first day of next month", $startDate); // first day of next month
                $time = strtotime("+". $validationDelay ." minutes", $time); //  + Validation delay

                if ($addressedDate <=  $time){
                    $return[] = $result[$i] ;
                }
            }
        }

        return $return ;
    }

    /**
     * Get Number of Participating Report during a period
     *
     * @param $siteIds
     * @param $startDate
     * @param $endDate
     * @param $period
     * @return int
     */
    public function getNumberOfParticipatingReportPeriod($siteIds, $startDate, $endDate, $period)
    {
        return $this->fullReportRepository->getNumberOfReportPeriod($siteIds, $startDate, $endDate, $period, Constant::STATUS_VALIDATED);
    }

    /**
     * @param SesDashboardUser $user
     * @param SesDashboardSite|null $site
     * @param $period
     * @param $weekNumber
     * @param $monthNumber
     * @param $year
     * @param $autoValidationEnabled
     * @param $translator
     * @return JsonResponse
     */
    public function createDashboardJsonFile($user, $site, $period, $weekNumber, $monthNumber, $year, $autoValidationEnabled, $translator)
    {
        $json = $this
            ->createDashboardJson(
                $site,
                $period,
                $weekNumber,
                $monthNumber,
                $year,
                $this->epiFirstDay,
                $this->diseaseService
                    ->getDiseasesPerPeriod($period),
                $translator,
                $autoValidationEnabled
            );

        $pathDashboard = $this->pathDashboards . DIRECTORY_SEPARATOR;
        $pathReport = "DashboardWeekly_".$site->getId().
            "_W".$weekNumber."_".$year."_".
            $user->getId();
        file_put_contents(
            $pathDashboard . $pathReport . ".json",
            json_encode($json)
        );

        return new JsonResponse(
            [
                'pathReport' => $pathReport,
                'reportTitle' => $json['title'],
                'reportDetails' => $json['titleDetails'],
                'siteName' => $site->getName(),
                'period' => $translator->trans('Week')." ".
                    $weekNumber." - ".$year
            ], JsonResponse::HTTP_OK);
    }

    /**
     * Create dynamic json file for the Epidemiologic weekly summary Dashboard
     *
     * @param SesDashboardSite $site
     * @param $period
     * @param $weekNumber
     * @param $monthNumber
     * @param $year
     * @param $epi
     * @param $diseases
     * @param Translator $translator
     * @return array
     */
    public function createDashboardJson($site, $period, $weekNumber, $monthNumber, $year, $epi, $diseases, $translator, $autoValidationEnabled = false){

        $firstDayOfWeekTS = Epidemiologic::GetFirstDayOfWeek($weekNumber, $year, $epi);
        $lastDayOfWeekTS = Epidemiologic::GetLastDayOfWeek($weekNumber, $year, $epi);
        $firstDayOfWeekOneOfYearTS = Epidemiologic::GetTimeStampForFirstDayOfWeekOne($epi,$year);
        $lastWeeks = 12 ; // Number of weeks we need

        $pathDashboardReport = "DashboardReports/";

        $firstDayOfWeekOneTS = strtotime("- " .($lastWeeks-1). " weeks", $firstDayOfWeekTS);
        $epiFirstDayOfWeekOne = Epidemiologic::Timestamp2Epi($firstDayOfWeekOneTS, $epi);
        $weekNumberFirstDayOfWeekOne = $epiFirstDayOfWeekOne['Week'];
        $yearFirstDayOfWeekOne = $epiFirstDayOfWeekOne['Year'];

        $dimDateStartId = DimDateHelper::getDimDateIdFromString(date("Y-m-d", $firstDayOfWeekOneTS));
        $dimDateEndId = DimDateHelper::getDimDateIdFromString(date("Y-m-d", $lastDayOfWeekTS));

        $sitesLeaf = $this->siteService->getLeafSiteIds($site->getId(), false, true, null, SesDashboardIndicatorDimDateType::CODE_WEEKLY_EPIDEMIOLOGIC, $dimDateStartId, $dimDateEndId, true);
        $nbOfHF = $this->getNumberOfExpectedWeeklyReportPeriod($sitesLeaf, date("Y-m-d", $firstDayOfWeekOneTS), date("Y-m-d", $lastDayOfWeekTS));

        $nbOfParticipatingReports = $this->getNumberOfParticipatingReportPeriod($sitesLeaf, date("Y-m-d", $firstDayOfWeekOneTS), date("Y-m-d", $lastDayOfWeekTS), Constant::PERIOD_WEEKLY);
        $participatingHF = ($nbOfHF != 0 ? (round($nbOfParticipatingReports / ($nbOfHF) * 100)) : 0) . ' % ('.$nbOfParticipatingReports. '/'. ($nbOfHF).')';

        $reports = array() ;

        $report = array() ;
        $report["report"] = $pathDashboardReport."DashboardSendCompletenessTimelinessBySite.php";
        $report["title"] = $translator->trans('REPORT.TITLE.PERFORMANCE_INDICATOR.TRANSMISSION', array(), 'reports') ." - ". $translator->trans('REPORT.CHART.WEEK', array(), 'reports')." ".$weekNumber." - ". $year;
        $report["macros"] = array() ;
        $report["macros"]["Range"] = array();
        $report["macros"]["Range"]["start"] = date("Y-m-d", $firstDayOfWeekTS);
        $report["macros"]["Range"]["end"] = date("Y-m-d", $lastDayOfWeekTS) ;
        $report["macros"]["Site"] = $site->getId() ;
        $report["macros"]["Diseases"] = "undefined";
        $report["macros"]["Period"] = $period ;
        $report["macros"]["Locale"] = $translator->getLocale() ;
        $report["format"] = "html";
        $report["newRow"] = false;
        $report["class"] = "col-xs-6";
        $reports[] = $report ;

        $report = array() ;
        $report["report"] = $pathDashboardReport."DashboardSendCompletenessTimelinessByPeriod.php";
        $report["title"] =  sprintf("%1\$s : %2\$s - %3\$s %4\$d (%5\$d) %6\$s %3\$s %7\$d (%8\$d)",
            $translator->trans('Sending reports'),
            $site->getName(),
            $translator->trans('REPORT.CHART.WEEK', array(), 'reports'),
            $weekNumberFirstDayOfWeekOne,
            $yearFirstDayOfWeekOne,
            $translator->trans('REPORT.TITLE.TO_A', array(), 'reports'),
            $weekNumber,
            $year);
        $report["macros"] = array() ;
        $report["macros"]["Range"] = array();
        $report["macros"]["Range"]["start"] = date("Y-m-d", $firstDayOfWeekOneTS);
        $report["macros"]["Range"]["end"] = date("Y-m-d", $lastDayOfWeekTS);
        $report["macros"]["Site"] = $site->getId() ;
        $report["macros"]["Diseases"] = "undefined";
        $report["macros"]["Period"] = $period ;
        $report["macros"]["Locale"] = $translator->getLocale() ;
        $report["format"] = "chart";
        $report["newRow"] = false;
        $report["class"] = "col-xs-6";
        $reports[] = $report ;

        // New section "Completeness & Timeliness for Report validation" if site is not leaf()
        // Test if Auto validation enabled, do not display this report
        if (!$autoValidationEnabled && ! $site->isLeaf()) {
            $report = array() ;
            $report["report"] = $pathDashboardReport."DashboardValidateCompletenessTimelinessBySite.php";
            $report["title"] = $translator->trans('REPORT.TITLE.PERFORMANCE_INDICATOR.VALIDATION', array(), 'reports') ." - ". $translator->trans('REPORT.CHART.WEEK', array(), 'reports')." ".$weekNumber." - ". $year;
            $report["macros"] = array() ;
            $report["macros"]["Range"] = array();
            $report["macros"]["Range"]["start"] = date("Y-m-d", $firstDayOfWeekTS);
            $report["macros"]["Range"]["end"] = date("Y-m-d", $lastDayOfWeekTS);
            $report["macros"]["Site"] = $site->getId() ;
            $report["macros"]["Diseases"] = "ALL";
            $report["macros"]["Period"] = $period ;
            $report["macros"]["Locale"] = $translator->getLocale() ;
            $report["format"] = "html";
            $report["newRow"] = true;
            $report["class"] = "col-xs-6";
            $reports[] = $report ;

            $report = array() ;
            $report["report"] = $pathDashboardReport."DashboardValidateCompletenessTimelinessByPeriod.php";
            $report["title"] =  sprintf("%1\$s : %2\$s - %3\$s %4\$d (%5\$d) %6\$s %3\$s %7\$d (%8\$d)",
                $translator->trans('Validating reports'),
                $site->getName(),
                $translator->trans('REPORT.CHART.WEEK', array(), 'reports'),
                $weekNumberFirstDayOfWeekOne,
                $yearFirstDayOfWeekOne,
                $translator->trans('REPORT.TITLE.TO_A', array(), 'reports'),
                $weekNumber,
                $year);
            $report["macros"] = array() ;
            $report["macros"]["Range"] = array();
            $report["macros"]["Range"]["start"] = date("Y-m-d", $firstDayOfWeekOneTS);
            $report["macros"]["Range"]["end"] = date("Y-m-d", $lastDayOfWeekTS);
            $report["macros"]["Site"] = $site->getId() ;
            $report["macros"]["Diseases"] = "ALL";
            $report["macros"]["Period"] = $period ;
            $report["macros"]["Locale"] = $translator->getLocale() ;
            $report["format"] = "chart";
            $report["newRow"] = false;
            $report["class"] = "col-xs-6";
            $reports[] = $report ;
        }

        // DashboardDiseasesWeeklyBySite
        $report = array() ;
        $report["report"] = $pathDashboardReport."DashboardDiseasesWeeklyBySite.php";
        $report["title"] = $translator->trans('REPORT.TITLE.EVENT_NUMBER', array(), 'reports')." ". $translator->trans('REPORT.CHART.WEEK', array(), 'reports')." ".$weekNumber;
        $report["macros"] = array() ;
        $report["macros"]["Range"] = array();
        $report["macros"]["Range"]["start"] = date("Y-m-d", $firstDayOfWeekTS);
        $report["macros"]["Range"]["end"] = date("Y-m-d", $lastDayOfWeekTS);
        $report["macros"]["Site"] = $site->getId() ;
        $report["macros"]["Diseases"] = "ALL";
        $report["macros"]["Period"] = $period ;
        $report["macros"]["Locale"] = $translator->getLocale() ;
        $report["format"] = "html";
        $report["newRow"] = true;
        $reports[] = $report ;

        // DashboardAlerts
        $report = array() ;
        $report["report"] = $pathDashboardReport."DashboardAlerts.php";
        $report["title"] = $translator->trans('REPORT.TITLE.RECEIVED_ALERT', array(), 'reports')." ". $translator->trans('REPORT.CHART.WEEK', array(), 'reports')." ".$weekNumber;
        $report["macros"] = array() ;
        $report["macros"]["Range"] = array();
        $report["macros"]["Range"]["start"] = date("Y-m-d", $firstDayOfWeekTS);
        $report["macros"]["Range"]["end"] = date("Y-m-d", $lastDayOfWeekTS);
        $report["macros"]["Site"] = $site->getId() ;
        $report["macros"]["Diseases"] = "undefined";
        $report["macros"]["Period"] = $period ;
        $report["macros"]["Locale"] = $translator->getLocale() ;
        $report["format"] = "html";
        $report["newRow"] = true;
        $reports[] = $report ;

        // DashboardDiseasesWeeklyAggregation
        $report = array() ;
        $report["report"] = $pathDashboardReport."DashboardDiseasesWeeklyAggregation.php";
        $report["title"] = $translator->trans('REPORT.TITLE.EVENT_ACCUMULATION', array(), 'reports')." ".$site->getName();
        $report["macros"] = array() ;
        $report["macros"]["Range"] = array();
        $report["macros"]["Range"]["start"] = date("Y-m-d", $firstDayOfWeekOneOfYearTS);
        $report["macros"]["Range"]["end"] = date("Y-m-d", $lastDayOfWeekTS);
        $report["macros"]["Site"] = $site->getId() ;
        $report["macros"]["Diseases"] = "ALL";
        $report["macros"]["Period"] = $period ;
        $report["macros"]["Locale"] = $translator->getLocale() ;
        $report["format"] = "html";
        $report["newRow"] = true;
        $reports[] = $report ;

        // DashboardDiseasesWeeklyByWeek for All diseases
        $modulo = 1 ;
        /** @var SesDashboardDisease $disease */
        foreach($diseases as $disease){
            if ($disease->getDisease() == Constant::DISEASE_ALERT){continue;}
            $report = array() ;
            //$report["report"] = $pathDashboardReport."DashboardDiseasesWeeklyByWeek.php";
            $report["report"] = $pathDashboardReport."DashboardNumberOfCasesPerDiseaseByPeriod.php";
            $report["title"] = $site->getName()." - ".$disease->getName();
            $report["descriptionWarning"] = $participatingHF .' '.  $translator->trans('REPORT.DESCRIPTION.HEALTH_FACILITY_PARTICIPATING', array(), 'reports');
            $report["macros"] = array() ;
            $report["macros"]["Range"] = array();
            $report["macros"]["Range"]["start"] = date("Y-m-d", $firstDayOfWeekOneTS);
            $report["macros"]["Range"]["end"] = date("Y-m-d", $lastDayOfWeekTS);
            $report["macros"]["Site"] = $site->getId() ;
            $report["macros"]["Diseases"] = $disease->getId();
            $report["macros"]["Period"] = $period ;
            $report["macros"]["Locale"] = $translator->getLocale() ;
            $report["format"] = "chart";
            $report["newRow"] = (($modulo % 2 != 0) ? true :  false );
            $report["class"] = "col-xs-6";
            $reports[] = $report ;

            $modulo ++ ;
        }

        $intl = new \IntlDateFormatter(\Locale::getDefault(), \IntlDateFormatter::SHORT, \IntlDateFormatter::NONE);
        $result = array( "title" => $translator->trans('Weekly epidemiologic situation summary'),
                         "titleDetails" => $site->getName()." - ".$translator->trans('Week')." ".$weekNumber. " (".$translator->trans('From')." ".$intl->format(new DateTime("@$firstDayOfWeekTS"))." ".$translator->trans('To')." ".$intl->format(new DateTime("@$lastDayOfWeekTS")).")" ,
                            //"description" => "Ma description",
                         "reports" => $reports);

        return $result;
    }
}
