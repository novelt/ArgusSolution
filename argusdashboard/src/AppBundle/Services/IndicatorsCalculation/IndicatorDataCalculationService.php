<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 17/11/2016
 * Time: 17:25
 */

namespace AppBundle\Services\IndicatorsCalculation;

use AppBundle\Services\BaseService;
use AppBundle\Services\LogService;
use Symfony\Bridge\Monolog\Logger;

class IndicatorDataCalculationService extends BaseService
{
    /**
     * @var IndicatorDimDateTypeService
     */
    private $indicatorDimDateTypeService;

    /**
     * @var IndicatorDimDateService
     */
    private $indicatorDimDateService;

    /**
     * @var LogService
     */
    private $logService;

    public function __construct(Logger $logger, IndicatorDimDateTypeService $indicatorDimDateTypeService, IndicatorDimDateService $indicatorDimDateService, LogService $logService)
    {
        parent::__construct($logger);

        $this->indicatorDimDateTypeService = $indicatorDimDateTypeService;
        $this->indicatorDimDateService = $indicatorDimDateService;
        $this->logService = $logService;
    }

    public function initializeData() {
        // ---- Generate dimDateTypes ----
        $this->indicatorDimDateTypeService->generateDimDateTypesList();
        //--------------------------------

        // ---- Generate the indicator dimDates ----
        $this->indicatorDimDateService->generateDimDates();
        // -----------------------------------------
    }

    /**
     * @return IndicatorDimDateService
     */
    public function getIndicatorDimDateService()
    {
        return $this->indicatorDimDateService;
    }

    /**
     * @param IndicatorDimDateService $indicatorDimDateService
     */
    public function setIndicatorDimDateService($indicatorDimDateService)
    {
        $this->indicatorDimDateService = $indicatorDimDateService;
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
}