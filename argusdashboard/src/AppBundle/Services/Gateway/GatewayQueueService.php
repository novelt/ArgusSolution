<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 17/06/2016
 * Time: 14:45
 */

namespace AppBundle\Services\Gateway;

use AppBundle\Repository\Gateway\GatewayQueueRepository;
use AppBundle\Repository\RepositoryInterface;
use AppBundle\Services\BaseRepositoryService;

use AppBundle\Utils\Epidemiologic;
use Symfony\Bridge\Monolog\Logger;

/**
 * Class GatewayQueueService
 * @package AppBundle\Services\Gateway
 */
class GatewayQueueService extends BaseRepositoryService
{
    /** @var GatewayQueueRepository */
    private $gatewayQueueRepository;

    /**
     * GatewayQueueService constructor.
     * @param Logger $logger
     * @param GatewayQueueRepository $gatewayQueueRepository
     */
    public function __construct(Logger $logger, GatewayQueueRepository $gatewayQueueRepository)
    {
        parent::__construct($logger);

        $this->gatewayQueueRepository = $gatewayQueueRepository;
    }

    /**
     * Return weekly SMS traffic
     *
     * @param $siteIds
     * @param $startTimestamp
     * @param $endTimestamp
     * @param $epiFirstDay
     * @return array
     */
    public function getWeekGatewaySMSTraffic($siteIds, $startTimestamp, $endTimestamp, $epiFirstDay)
    {
        $start = Epidemiologic::Timestamp2Epi($startTimestamp, $epiFirstDay);
        $end = Epidemiologic::Timestamp2Epi($endTimestamp, $epiFirstDay);

        return $this->gatewayQueueRepository->getWeekGatewaySMSTraffic($siteIds, $start, $end);
    }

    public function getRepository()
    {
        return $this->gatewayQueueRepository;
    }

    public function setRepository(RepositoryInterface $repository)
    {
        $this->gatewayQueueRepository = $repository;
    }
}