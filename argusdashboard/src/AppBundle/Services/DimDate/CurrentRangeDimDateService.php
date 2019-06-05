<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 23/03/2018
 * Time: 15:52
 */

namespace AppBundle\Services\DimDate;

use AppBundle\Services\IndicatorsCalculation\IndicatorDimDateService;
use Symfony\Bridge\Monolog\Logger;

class CurrentRangeDimDateService extends AbstractDimDateService
{
    public function __construct(Logger $logger, IndicatorDimDateService $dimDateService)
    {
        parent::__construct($logger, $dimDateService);
    }

    /**
     * Return First day of current Week
     *
     * @param $dimDateFromId
     * @return \AppBundle\Entity\SesDashboardIndicatorDimDate|null
     */
    function getWeekDimDateFrom($dimDateFromId)
    {
        $dimDate = $this->indicatorDimDateService->find($dimDateFromId);
        $id = $this->indicatorDimDateService->findFirstDimDatesOfEpidemiologicWeeksIdsByDateRange($dimDate->getFullDate(), $dimDate->getFullDate());

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
     * Return first day of current Week
     *
     * @param $dimDateToId
     * @return \AppBundle\Entity\SesDashboardIndicatorDimDate|null
     */
    function getWeekDimDateTo($dimDateToId)
    {
        $dimDate = $this->indicatorDimDateService->find($dimDateToId);
        $id = $this->indicatorDimDateService->findFirstDimDatesOfEpidemiologicWeeksIdsByDateRange($dimDate->getFullDate(), $dimDate->getFullDate());

        if (isset($id) && isset($id[0])) {
            $dimDateTo = $this->indicatorDimDateService->find($id[0]);
            return $dimDateTo;
        }

        return $dimDate;
    }

    /**
     * Return first day of current Month
     *
     * @param $dimDateToId
     * @return \AppBundle\Entity\SesDashboardIndicatorDimDate|null
     */
    function getMonthDimDateTo($dimDateToId)
    {
        $dimDate = $this->indicatorDimDateService->find($dimDateToId);
        $id = $this->indicatorDimDateService->findFirstDimDatesOfCalendarMonthsIdsByDateRange($dimDate->getFullDate(), $dimDate->getFullDate());

        if (isset($id) && isset($id[0])) {
            $dimDateTo = $this->indicatorDimDateService->find($id[0]);
            return $dimDateTo;
        }

        return $dimDate;
    }
}