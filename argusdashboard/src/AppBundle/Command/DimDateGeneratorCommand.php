<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 09/12/2016
 * Time: 11:39
 */

namespace AppBundle\Command;

use AppBundle\Services\IndicatorsCalculation\IndicatorDataCalculationService;
use AppBundle\Utils\Parser;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DimDateGeneratorCommand
 *
 *  Use this command if epi_first_day value is <> 1. It will repopulate epi week columns in the indicatordimdate table
 *
 * @package AppBundle\Command
 */
class DimDateGeneratorCommand extends BaseCommand
{
    const COMMAND_NAME = 'dimdate:generate';

    const APPLICATION_PARAM_DATE_FROM = 'indicator_dim_date_from';
    const APPLICATION_PARAM_EPI_FIRST_DAY = 'epi_first_day';

    private $defaultDateFrom;
    private $epiFirstDay;

    /**
     * @var IndicatorDataCalculationService
     */
    private $indicatorDataCalculationService;


    public function __construct(Logger $logger, Parser $parser, IndicatorDataCalculationService $indicatorDataCalculationService)
    {
        parent::__construct($logger, $parser);
        $this->indicatorDataCalculationService = $indicatorDataCalculationService;
    }

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->defaultDateFrom = $this->getApplicationParameter(self::APPLICATION_PARAM_DATE_FROM);
        $this->epiFirstDay = $this->getApplicationParameter(self::APPLICATION_PARAM_EPI_FIRST_DAY);

        if($this->epiFirstDay === null) {
            throw new \Exception(sprintf("The application parameter '%s' is missing", self::APPLICATION_PARAM_EPI_FIRST_DAY));
        }
    }

    protected function configure()
    {
        $this
            ->setName(sprintf('%s', self::COMMAND_NAME))
            ->setDescription('Initialize DimeDate data');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->logger->debug("Initialize DimDates");
            $this->indicatorDataCalculationService->initializeData();
        }

        catch(\Exception $e) {
            $this->logger->error(sprintf("Error during the process of the indicators calculation. Exception: %s", (string)$e));
        }
    }

    /**
     * @return mixed
     */
    public function getDefaultDateFrom()
    {
        return $this->defaultDateFrom;
    }

    /**
     * @param mixed $defaultDateFrom
     */
    public function setDefaultDateFrom($defaultDateFrom)
    {
        $this->defaultDateFrom = $defaultDateFrom;
    }

    /**
     * @return mixed
     */
    public function getEpiFirstDay()
    {
        return $this->epiFirstDay;
    }

    /**
     * @param mixed $epiFirstDay
     */
    public function setEpiFirstDay($epiFirstDay)
    {
        $this->epiFirstDay = $epiFirstDay;
    }

    /**
     * @return IndicatorDataCalculationService
     */
    public function getIndicatorDataCalculationService()
    {
        return $this->indicatorDataCalculationService;
    }

    /**
     * @param IndicatorDataCalculationService $indicatorDataCalculationService
     */
    public function setIndicatorDataCalculationService($indicatorDataCalculationService)
    {
        $this->indicatorDataCalculationService = $indicatorDataCalculationService;
    }
}