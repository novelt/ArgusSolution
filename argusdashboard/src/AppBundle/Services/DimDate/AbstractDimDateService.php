<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 26/03/2018
 * Time: 11:34
 */

namespace AppBundle\Services\DimDate;

use AppBundle\Services\IndicatorsCalculation\IndicatorDimDateService;
use Symfony\Bridge\Monolog\Logger;

abstract class AbstractDimDateService implements IDimDateService
{
    /** @var IndicatorDimDateService */
    protected $indicatorDimDateService;

    public function __construct(Logger $logger, IndicatorDimDateService $dimDateService)
    {
        $this->indicatorDimDateService = $dimDateService;
    }

    /**
     * Return DimDate with $dimDateId
     *
     * @param $dimDateId
     * @return \AppBundle\Entity\SesDashboardIndicatorDimDate|null
     */
    function getDimDateFrom($dimDateId)
    {
        $dimDate = $this->indicatorDimDateService->find($dimDateId);
        return $dimDate;
    }

    /**
     * Return Dimdate with $dimDateId
     *
     * @param $dimDateId
     * @return \AppBundle\Entity\SesDashboardIndicatorDimDate|null
     */
    function getDimDateTo($dimDateId)
    {
        $dimDate = $this->indicatorDimDateService->find($dimDateId);
        return $dimDate;
    }
}