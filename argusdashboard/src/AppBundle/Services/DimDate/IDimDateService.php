<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 23/03/2018
 * Time: 15:53
 */

namespace AppBundle\Services\DimDate;


use AppBundle\Entity\SesDashboardIndicatorDimDate;

interface IDimDateService
{
    /**
     * @param $dimDateFromId
     * @return SesDashboardIndicatorDimDate|null
     */
    function getDimDateFrom($dimDateFromId);

    /**
     * @param $dimDateFromId
     * @return SesDashboardIndicatorDimDate|null
     */
    function getWeekDimDateFrom($dimDateFromId);

    /**
     * @param $dimDateFromId
     * @return SesDashboardIndicatorDimDate|null
     */
    function getMonthDimDateFrom($dimDateFromId);

    /**
     * @param $dimDateToId
     * @return SesDashboardIndicatorDimDate|null
     */
    function getDimDateTo($dimDateToId);

    /**
     * @param $dimDateToId
     * @return SesDashboardIndicatorDimDate|null
     */
    function getWeekDimDateTo($dimDateToId);

    /**
     * @param $dimDateToId
     * @return SesDashboardIndicatorDimDate|null
     */
    function getMonthDimDateTo($dimDateToId);

}