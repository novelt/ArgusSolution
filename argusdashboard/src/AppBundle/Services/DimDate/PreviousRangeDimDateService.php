<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 23/03/2018
 * Time: 15:52
 */

namespace AppBundle\Services\DimDate;

use AppBundle\Services\IndicatorsCalculation\IndicatorDimDateService;
use AppBundle\Utils\DimDateHelper;
use Symfony\Bridge\Monolog\Logger;

/**
 *
 * Class PreviousRangeDimDateService
 * @package AppBundle\Services\DimDate
 */
class PreviousRangeDimDateService extends AbstractDimDateService
{
    public function __construct(Logger $logger, IndicatorDimDateService $dimDateService)
    {
        parent::__construct($logger, $dimDateService);
    }

    /**
     * Return first day of previous week
     *
     * @param $dimDateFromId
     * @return \AppBundle\Entity\SesDashboardIndicatorDimDate|null
     */
    function getWeekDimDateFrom($dimDateFromId)
    {
        $dimDate = $this->indicatorDimDateService->find($dimDateFromId);
        /** Subtract 7 days */
        $time = strtotime('- 7 days', $dimDate->getFullDate()->getTimestamp());
        $firstDayOfPreviousWeek = date('Y-m-d', $time);

        $firstDayOfPreviousWeekDimDateId =  DimDateHelper::getDimDateIdFromString($firstDayOfPreviousWeek);
        $firstDayOfPreviousWeekDimDate = $this->indicatorDimDateService->find($firstDayOfPreviousWeekDimDateId);

        $id = $this->indicatorDimDateService->findFirstDimDatesOfEpidemiologicWeeksIdsByDateRange($firstDayOfPreviousWeekDimDate->getFullDate(), $firstDayOfPreviousWeekDimDate->getFullDate());

        if (isset($id) && isset($id[0])) {
            $dimDateFrom = $this->indicatorDimDateService->find($id[0]);
            return $dimDateFrom;
        }

        return $dimDate;
    }

    /**
     * Return First day of current Month
     *
     * @param $dimDateFromId
     * @return \AppBundle\Entity\SesDashboardIndicatorDimDate|null
     */
    function getMonthDimDateFrom($dimDateFromId)
    {
        $dimDate = $this->indicatorDimDateService->find($dimDateFromId);
        $id = $this->indicatorDimDateService->findFirstDimDatesOfCalendarMonthsIdsByDateRange($dimDate->getFullDate(), $dimDate->getFullDate());

        if (isset($id) && isset($id[0])) {
            $dimDateFrom = $this->indicatorDimDateService->find($id[0]);
            return $dimDateFrom;
        }

        return $dimDate;
    }

    /**
     * Return first day of previous week
     *
     * @param $dimDateToId
     * @return \AppBundle\Entity\SesDashboardIndicatorDimDate|null
     */
    function getWeekDimDateTo($dimDateToId)
    {
        $dimDate = $this->indicatorDimDateService->find($dimDateToId);
        /** Subtract 7 days $id */
        $time = strtotime('- 7 days', $dimDate->getFullDate()->getTimestamp());
        $firstDayOfPreviousWeek = date('Y-m-d', $time);

        $firstDayOfPreviousWeekDimDateId =  DimDateHelper::getDimDateIdFromString($firstDayOfPreviousWeek);
        $firstDayOfPreviousWeekDimDate = $this->indicatorDimDateService->find($firstDayOfPreviousWeekDimDateId);

        $id = $this->indicatorDimDateService->findFirstDimDatesOfEpidemiologicWeeksIdsByDateRange($firstDayOfPreviousWeekDimDate->getFullDate(), $firstDayOfPreviousWeekDimDate->getFullDate());

        if (isset($id) && isset($id[0])) {
            $dimDateTo = $this->indicatorDimDateService->find($id[0]);
            return $dimDateTo;
        }

        return $dimDate;
    }

    /**
     * Return first day of current month
     *
     * @param $dimDateToId
     * @return \AppBundle\Entity\SesDashboardIndicatorDimDate|null
     */
    function getMonthDimDateTo($dimDateToId)
    {
        $dimDate = $this->indicatorDimDateService->find($dimDateToId);
        $id = $this->indicatorDimDateService->findFirstDimDatesOfCalendarMonthsIdsByDateRange($dimDate->getFullDate(), $dimDate->getFullDate());

        if (isset($id) && isset($id[0])) {
            $dimDateFrom = $this->indicatorDimDateService->find($id[0]);
            return $dimDateFrom;
        }

        return $dimDate;
    }
}