<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 16/11/2016
 * Time: 16:47
 */

namespace AppBundle\Services\IndicatorsCalculation;


use AppBundle\Entity\SesDashboardIndicatorDimDate;
use AppBundle\Entity\SesDashboardIndicatorDimDateType;
use AppBundle\Repository\RepositoryInterface;
use AppBundle\Repository\SesDashboardIndicatorDimDateRepository;
use AppBundle\Services\BaseRepositoryService;
use AppBundle\Services\DbConstant;
use AppBundle\Services\IndicatorsCalculation\Exception\IndicatorDimDateGenerationExceptionException;
use AppBundle\Utils\Epidemiologic;
use Symfony\Bridge\Monolog\Logger;

class IndicatorDimDateService extends BaseRepositoryService
{
    /**
     * @var SesDashboardIndicatorDimDateRepository
     */
    private $dashboardIndicatorDimDateRepository;

    /**
     * @var IndicatorDimDateTypeService
     */
    private  $indicatorDimDateTypeService;
    /**
     * @var \DateTime
     */
    private $indicatorDimDateFrom;
    /**
     * @var \DateTime
     */
    private $indicatorDimDateTo;

    /**
     * @var int
     */
    private $epi_first_day;

    public function __construct(Logger $logger, SesDashboardIndicatorDimDateRepository $dashboardIndicatorDimDateRepository, IndicatorDimDateTypeService $indicatorDimDateTypeService, $indicator_dim_date_from, $indicator_dim_date_to, $epi_first_day)
    {
        parent::__construct($logger);
        $this->dashboardIndicatorDimDateRepository = $dashboardIndicatorDimDateRepository;
        $this->indicatorDimDateTypeService = $indicatorDimDateTypeService;
        $this->indicatorDimDateFrom = new \DateTime($indicator_dim_date_from);
        $this->indicatorDimDateTo = new \DateTime($indicator_dim_date_to);
        $this->epi_first_day = $epi_first_day;
    }

    public function generateDimDates() {
        try {
            $this->generateIndicatorDimDates();
            $this->updateDimDateEpiFieldsAndPeriods();
        }
        catch(\Exception $e) {
            throw new IndicatorDimDateGenerationExceptionException("Error during the generation of indicatorDimDates", 0 , $e);
        }
    }

    public function updateDimDateEpiFieldsAndPeriods() {
        $dimDates = $this->dashboardIndicatorDimDateRepository->findAll();

        foreach($dimDates as $dimDate) {
            $epi = Epidemiologic::Timestamp2Epi($dimDate->getFullDate()->getTimestamp(), $this->epi_first_day);
            $dimDate->setEpiWeekOfYear($epi['Week']);
            $dimDate->setEpiYear($epi['Year']);

            $dimDate->setMonthPeriodCode(sprintf("%dM%02d", $dimDate->getCalendarYear(), $dimDate->getMonthOfYear()));
            $dimDate->setWeekPeriodCode(sprintf("%dW%02d", $dimDate->getCalendarYear(), $dimDate->getWeekOfYear()));
            $dimDate->setEpiWeekPeriodCode(sprintf("%dW%02d", $dimDate->getEpiYear(), $dimDate->getEpiWeekOfYear()));
        }

        $this->dashboardIndicatorDimDateRepository->saveChanges();
    }

    /**
     * @param SesDashboardIndicatorDimDateType $dimDateType
     * @param \DateTime|null $from
     * @param \DateTime|null $to
     * @return mixed
     */
    public function getDimDatesByDateRange(SesDashboardIndicatorDimDateType $dimDateType, \DateTime $from = null, \DateTime $to = null) {
        $dimDateIds = $this->getDimDateIdsByDateRange($dimDateType, $from, $to);

        $dimDates = $this->dashboardIndicatorDimDateRepository->findById($dimDateIds);
        return $dimDates;
    }

    /**
     * @param SesDashboardIndicatorDimDateType $dimDateType
     * @param null $from
     * @param null $to
     * @return SesDashboardIndicatorDimDate[]|array|\int[]|null|string
     */
    public function getDimDateIdsByDateRange(SesDashboardIndicatorDimDateType $dimDateType, $from = null, $to = null) {
        $dimDateIds = null;

        if($from === DbConstant::NULL || $to === DbConstant::NULL) {
            return DbConstant::NULL;
        }
        else if($from === DbConstant::NOT_NULL  || $to === DbConstant::NOT_NULL) {
            return DbConstant::NOT_NULL;
        }

        switch($dimDateType->getCode()) {
            case SesDashboardIndicatorDimDateType::CODE_DAILY:
                return $this->dashboardIndicatorDimDateRepository->findAllDimDateIdsByDateRange($from, $to);
            case SesDashboardIndicatorDimDateType::CODE_WEEKLY:
                return $this->dashboardIndicatorDimDateRepository->findFirstDimDatesOfCalendarWeeksIdsByDateRange($from, $to);
            case SesDashboardIndicatorDimDateType::CODE_WEEKLY_EPIDEMIOLOGIC:
                return $this->dashboardIndicatorDimDateRepository->findFirstDimDatesOfEpidemiologicWeeksIdsByDateRange($from, $to);
            case SesDashboardIndicatorDimDateType::CODE_MONTHLY:
                return $this->dashboardIndicatorDimDateRepository->findFirstDimDatesOfCalendarMonthsIdsByDateRange($from, $to);
            case SesDashboardIndicatorDimDateType::CODE_YEARLY:
                return array();//TODO
        }

        return $dimDateIds;
    }

    /**
     * Generates dim dates from the specified indicatorDimDateFrom until today
     * @return bool
     */
    public function generateIndicatorDimDates(\DateTime $dateFrom = null, \DateTime $dateTo = null) {
        return $this->dashboardIndicatorDimDateRepository->generateIndicatorDimDates($dateFrom === null ? $this->indicatorDimDateFrom : $dateFrom, $dateTo === null ? $this->indicatorDimDateTo : $dateTo);
    }

    /**
     * @return int[]
     */
    public function findAllUntilToday() {
        return $this->dashboardIndicatorDimDateRepository->findAllUntilToday();
    }

    /**
     * @param null $dayOfMonth
     * @param null $monthOfYear
     * @param null $calendarYear
     * @return SesDashboardIndicatorDimDate|null
     */
    public function findOneDimDateByDayMonthAndYearByDateRange($dayOfMonth=null, $monthOfYear=null, $calendarYear=null) {
        return $this->dashboardIndicatorDimDateRepository->findOneDimDateByDayMonthAndYear($dayOfMonth, $monthOfYear, $calendarYear);
    }

    /**
     * @param \DateTime|null $from
     * @param \DateTime|null $to
     * @return int[]
     */
    public function findFirstDimDatesOfCalendarWeeksIdsByDateRange(\DateTime $from = null, \DateTime $to = null) {
        return $this->dashboardIndicatorDimDateRepository->findFirstDimDatesOfCalendarWeeksIdsByDateRange($from, $to);
    }

    /**
     * @param \DateTime|null $from
     * @param \DateTime|null $to
     * @return int[]
     */
    public function findFirstDimDatesOfEpidemiologicWeeksIdsByDateRange(\DateTime $from = null, \DateTime $to = null) {
        $fromWithoutTime = clone $from;
        $fromWithoutTime->setTime(0, 0, 0);

        $toWithoutTime = clone $to;
        $toWithoutTime->setTime(0, 0, 0);

        return $this->dashboardIndicatorDimDateRepository->findFirstDimDatesOfEpidemiologicWeeksIdsByDateRange($fromWithoutTime, $toWithoutTime);
    }

    /**
     * @param \DateTime|null $from
     * @param \DateTime|null $to
     * @return int[]
     */
    public function findFirstDimDatesOfCalendarMonthsIdsByDateRange(\DateTime $from = null, \DateTime $to = null) {
        return $this->dashboardIndicatorDimDateRepository->findFirstDimDatesOfCalendarMonthsIdsByDateRange($from, $to);
    }

    /**
     * @param SesDashboardIndicatorDimDateType $dimDateType
     * @param \DateTime|null $from
     * @param \DateTime|null $to
     * @return array|int[]
     */
    public function findFirstAndLastDimDatesIdsByDateRange(SesDashboardIndicatorDimDateType $dimDateType, \DateTime $from = null, \DateTime $to = null) {
        switch($dimDateType->getCode()) {
            case SesDashboardIndicatorDimDateType::CODE_DAILY:
                return $this->dashboardIndicatorDimDateRepository->findAllDimDateIdsByDateRange($from, $to);
            case SesDashboardIndicatorDimDateType::CODE_WEEKLY:
                return $this->dashboardIndicatorDimDateRepository->findFirstAndLastDimDatesOfCalendarWeeksIdsByDateRange($from, $to);
            case SesDashboardIndicatorDimDateType::CODE_WEEKLY_EPIDEMIOLOGIC:
                return $this->dashboardIndicatorDimDateRepository->findFirstAndLastDimDatesOfEpidemiologicWeeksIdsByDateRange($from, $to);
            case SesDashboardIndicatorDimDateType::CODE_MONTHLY:
                return $this->dashboardIndicatorDimDateRepository->findFirstAndLastDimDatesOfCalendarMonthsIdsByDateRange($from, $to);
            case SesDashboardIndicatorDimDateType::CODE_YEARLY:
                return array();//TODO
        }
    }

    /**
     * @param SesDashboardIndicatorDimDateType $dimDateType
     * @param \DateTime|null $from
     * @param \DateTime|null $to
     * @return array[SesDashboardIndicatorDimDate[]]
     */
    public function findFirstAndLastDimDatesByDateRange(SesDashboardIndicatorDimDateType $dimDateType, \DateTime $from = null, \DateTime $to = null) {
        switch($dimDateType->getCode()) {
            case SesDashboardIndicatorDimDateType::CODE_DAILY:
                return $this->findAllDimDatesByDateRange($from, $to);
            case SesDashboardIndicatorDimDateType::CODE_WEEKLY:
                return $this->findFirstAndLastDimDatesOfCalendarWeeksByDateRange($from, $to);
            case SesDashboardIndicatorDimDateType::CODE_WEEKLY_EPIDEMIOLOGIC:
                return $this->findFirstAndLastDimDatesOfEpidemiologicWeeksByDateRange($from, $to);
            case SesDashboardIndicatorDimDateType::CODE_MONTHLY:
                return $this->findFirstAndLastDimDatesOfCalendarMonthsByDateRange($from, $to);
            case SesDashboardIndicatorDimDateType::CODE_YEARLY:
                return array();//TODO
            default:
                return null;
        }
    }

    /**
     * @param \DateTime|null $from
     * @param \DateTime|null $to
     * @return int[]
     */
    public function findFirstAndLastDimDatesOfCalendarWeeksIdsByDateRange(\DateTime $from = null, \DateTime $to = null) {
        return $this->dashboardIndicatorDimDateRepository->findFirstAndLastDimDatesOfCalendarWeeksIdsByDateRange($from, $to);
    }

    /**
     * @param \DateTime|null $from
     * @param \DateTime|null $to
     * @return int[]
     */
    public function findFirstAndLastDimDateOfEpidemiologicWeeksIdsByDateRange(\DateTime $from = null, \DateTime $to = null) {
        return $this->dashboardIndicatorDimDateRepository->findFirstAndLastDimDatesOfEpidemiologicWeeksIdsByDateRange($from, $to);
    }

    /**
     * @param \DateTime|null $from
     * @param \DateTime|null $to
     * @return array
     */
    public function findAllDimDatesByDateRange(\DateTime $from = null, \DateTime $to = null) {
        $firstAndLastIds = $this->findAllDimDatesIdsByDateRange($from, $to);
        return $this->getFirstAndLastDimDates($firstAndLastIds);
    }

    /**
     * @param \DateTime|null $from
     * @param \DateTime|null $to
     * @return array
     */
    public function findFirstAndLastDimDatesOfCalendarWeeksByDateRange(\DateTime $from = null, \DateTime $to = null) {
        $firstAndLastIds = $this->findFirstAndLastDimDatesOfCalendarWeeksIdsByDateRange($from, $to);
        return $this->getFirstAndLastDimDates($firstAndLastIds);
    }

    /**
     * @param \DateTime|null $from
     * @param \DateTime|null $to
     * @return array
     */
    public function findFirstAndLastDimDatesOfEpidemiologicWeeksByDateRange(\DateTime $from = null, \DateTime $to = null) {
        $firstAndLastIds = $this->findFirstAndLastDimDateOfEpidemiologicWeeksIdsByDateRange($from, $to);
        return $this->getFirstAndLastDimDates($firstAndLastIds);
    }

    /**
     * @param \DateTime|null $from
     * @param \DateTime|null $to
     * @return array
     */
    public function findFirstAndLastDimDatesOfCalendarMonthsByDateRange(\DateTime $from = null, \DateTime $to = null) {
        $firstAndLastIds = $this->findFirstAndLastDimDatesOfCalendarMonthsIdsByDateRange($from, $to);
        return $this->getFirstAndLastDimDates($firstAndLastIds);
    }

    public function getFirstAndLastDimDates($firstAndLastIds = []) {
        //store all the ids in a table, to request all of them once
        $dimDateIds = [];
        foreach($firstAndLastIds as $firstAndLastId) {
            $dimDateIds[] = $firstAndLastId['firstId'];
            $dimDateIds[] = $firstAndLastId['lastId'];
        }

        $dimDates = $this->findById($dimDateIds);

        $firstAndLastDimDates = [];

        $i = 0;
        foreach($firstAndLastIds as $firstAndLastId) {
            if(array_key_exists('period', $firstAndLastId)) {
                $key = $firstAndLastId['period'];
            }
            else {
                $key = $i;
            }

            $firstAndLastDimDates[$key][0] = $this->getDimDate($dimDates, $firstAndLastId['firstId']);
            $firstAndLastDimDates[$key][1] = $this->getDimDate($dimDates, $firstAndLastId['lastId']);
            $i++;
        }

        return $firstAndLastDimDates;
    }

    /**
     * Returns an SesDashboardIndicatorDimDate, by searching it in the given array $dimDates, with the given id
     * @param SesDashboardIndicatorDimDate[] $dimDates
     * @param int $id
     * @return SesDashboardIndicatorDimDate|null
     */
    private function getDimDate(array $dimDates, $id) {
        $dimDate = null;

        if($dimDates!== null && !empty($dimDates) && $id !== null) {
            $i = 0;
            $found = false;

            while (!$found && $i < sizeof($dimDates)) {
                if ($dimDates[$i] !== null && $dimDates[$i]->getId() == $id) {
                    $dimDate = $dimDates[$i];
                    $found = true;
                } else {
                    $i++;
                }
            }
        }

        return $dimDate;
    }

    /**
     * @param \DateTime|null $from
     * @param \DateTime|null $to
     * @return int[]
     */
    public function findFirstAndLastDimDatesOfCalendarMonthsIdsByDateRange(\DateTime $from = null, \DateTime $to = null) {
        return $this->dashboardIndicatorDimDateRepository->findFirstAndLastDimDatesOfCalendarMonthsIdsByDateRange($from, $to);
    }

    /**
     * @param \DateTime|null $from
     * @param \DateTime|null $to
     * @return int[]
     */
    public function findAllDimDatesIdsByDateRange(\DateTime $from = null, \DateTime $to = null) {
        return $this->dashboardIndicatorDimDateRepository->findAllDimDateIdsByDateRange($from, $to);
    }

    /**
     * @param SesDashboardIndicatorDimDateType $dimDateType
     * @param $periodCode
     * @return array[SesDashboardIndicatorDimDate[]]|null
     */
    public function findFirstAndLastDimDateIdsByPeriodCode(SesDashboardIndicatorDimDateType $dimDateType, $periodCode) {
        switch($dimDateType->getCode()) {
            case SesDashboardIndicatorDimDateType::CODE_WEEKLY:
                return $this->findFirstAndLastDimDateIdsOfCalendarWeeksByPeriodCode($periodCode);
            case SesDashboardIndicatorDimDateType::CODE_WEEKLY_EPIDEMIOLOGIC:
                return $this->findFirstAndLastDimDateIdsOfEpidemiologicWeeksByPeriodCode($periodCode);
            case SesDashboardIndicatorDimDateType::CODE_MONTHLY:
                return $this->findFirstAndLastDimDateIdsOfCalendarMonthsByPeriodCode($periodCode);
            case SesDashboardIndicatorDimDateType::CODE_YEARLY:
                return array();//TODO
            default:
                return null;
        }
    }

    /**
     * @param SesDashboardIndicatorDimDateType $dimDateType
     * @param $periodCode
     * @return array[SesDashboardIndicatorDimDate[]]
     */
    public function findFirstAndLastDimDatesByPeriodCode(SesDashboardIndicatorDimDateType $dimDateType, $periodCode) {
        switch($dimDateType->getCode()) {
            case SesDashboardIndicatorDimDateType::CODE_WEEKLY:
                return $this->findFirstAndLastDimDatesOfCalendarWeeksByPeriodCode($periodCode);
            case SesDashboardIndicatorDimDateType::CODE_WEEKLY_EPIDEMIOLOGIC:
                return $this->findFirstAndLastDimDatesOfEpidemiologicWeeksByPeriodCode($periodCode);
            case SesDashboardIndicatorDimDateType::CODE_MONTHLY:
                return $this->findFirstAndLastDimDatesOfCalendarMonthsByPeriodCode($periodCode);
            case SesDashboardIndicatorDimDateType::CODE_YEARLY:
                return array();//TODO
            default:
                return null;
        }
    }

    /**
     * @param $periodCode
     * @return array
     */
    public function findFirstAndLastDimDatesOfCalendarWeeksByPeriodCode($periodCode) {
        $firstAndLastIds = $this->findFirstAndLastDimDateIdsOfCalendarWeeksByPeriodCode($periodCode);
        return $this->getFirstAndLastDimDates($firstAndLastIds);
    }

    /**
     * @param $periodCode
     * @return array
     */
    public function findFirstAndLastDimDateIdsOfCalendarWeeksByPeriodCode($periodCode) {
        return $this->dashboardIndicatorDimDateRepository->findFirstAndLastDimDateIdsOfCalendarWeeksByPeriodCode($periodCode);
    }

    /**
     * @param $periodCode
     * @return array
     */
    public function findFirstAndLastDimDatesOfEpidemiologicWeeksByPeriodCode($periodCode) {
        $firstAndLastIds = $this->findFirstAndLastDimDateIdsOfEpidemiologicWeeksByPeriodCode($periodCode);
        return $this->getFirstAndLastDimDates($firstAndLastIds);
    }

    /**
     * @param $periodCode
     * @return array
     */
    public function findFirstAndLastDimDateIdsOfEpidemiologicWeeksByPeriodCode($periodCode) {
        return $this->dashboardIndicatorDimDateRepository->findFirstAndLastDimDateIdsOfCalendarWeeksByPeriodCode($periodCode);
    }

    /**
     * @param $periodCode
     * @return array
     */
    public function findFirstAndLastDimDatesOfCalendarMonthsByPeriodCode($periodCode) {
        $firstAndLastIds = $this->findFirstAndLastDimDateIdsOfCalendarMonthsByPeriodCode($periodCode);
        return $this->getFirstAndLastDimDates($firstAndLastIds);
    }

    /**
     * @param $periodCode
     * @return array
     */
    public function findFirstAndLastDimDateIdsOfCalendarMonthsByPeriodCode($periodCode) {
        return $this->dashboardIndicatorDimDateRepository->findFirstAndLastDimDateIdsOfCalendarWeeksByPeriodCode($periodCode);
    }

    /**
     * @return \AppBundle\Entity\SesDashboardIndicatorDimDate[]
     */
    public function findAll() {
        return $this->dashboardIndicatorDimDateRepository->findAll();
    }

    /**
     * @param $id
     * @return SesDashboardIndicatorDimDate|null
     */
    public function find($id, $lockMode = null, $lockVersion = null)
    {
        return parent::find($id, $lockMode, $lockVersion);
    }

    /**
     * @return mixed
     */
    public function emptyTable($autocommit) {
        return $this->dashboardIndicatorDimDateRepository->emptyTable($autocommit);
    }

    /**
     * @return SesDashboardIndicatorDimDateRepository
     */
    public function getDashboardIndicatorDimDateRepository()
    {
        return $this->dashboardIndicatorDimDateRepository;
    }

    /**
     * @param SesDashboardIndicatorDimDateRepository $dashboardIndicatorDimDateRepository
     */
    public function setDashboardIndicatorDimDateRepository($dashboardIndicatorDimDateRepository)
    {
        $this->dashboardIndicatorDimDateRepository = $dashboardIndicatorDimDateRepository;
    }

    /**
     * @return IndicatorDimDateTypeService
     */
    public function getIndicatorDimDateTypeService()
    {
        return $this->indicatorDimDateTypeService;
    }

    /**
     * @param IndicatorDimDateTypeService $indicatorDimDateTypeService
     */
    public function setIndicatorDimDateTypeService($indicatorDimDateTypeService)
    {
        $this->indicatorDimDateTypeService = $indicatorDimDateTypeService;
    }

    /**
     * @return \DateTime
     */
    public function getIndicatorDimDateFrom()
    {
        return $this->indicatorDimDateFrom;
    }

    /**
     * @param \DateTime $indicatorDimDateFrom
     */
    public function setIndicatorDimDateFrom($indicatorDimDateFrom)
    {
        $this->indicatorDimDateFrom = $indicatorDimDateFrom;
    }

    /**
     * @return \DateTime
     */
    public function getIndicatorDimDateTo()
    {
        return $this->indicatorDimDateTo;
    }

    /**
     * @param \DateTime $indicatorDimDateTo
     */
    public function setIndicatorDimDateTo($indicatorDimDateTo)
    {
        $this->indicatorDimDateTo = $indicatorDimDateTo;
    }

    /**
     * @return int
     */
    public function getEpiFirstDay()
    {
        return $this->epi_first_day;
    }

    /**
     * @param int $epi_first_day
     */
    public function setEpiFirstDay($epi_first_day)
    {
        $this->epi_first_day = $epi_first_day;
    }

    public function getRepository()
    {
        return $this->dashboardIndicatorDimDateRepository;
    }

    public function setRepository(RepositoryInterface $repository)
    {
        $this->dashboardIndicatorDimDateRepository = $repository;
    }


}