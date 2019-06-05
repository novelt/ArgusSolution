<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 11/01/2017
 * Time: 15:48
 */

namespace AppBundle\Services;


use AppBundle\Repository\RepositoryInterface;
use AppBundle\Repository\SesDashboardLogRepository;
use Symfony\Bridge\Monolog\Logger;

class LogService extends BaseRepositoryService
{
    /**
     * @var SesDashboardLogRepository
     */
    private $logRepository;

    public function __construct(Logger $logger, SesDashboardLogRepository $logRepository)
    {
        parent::__construct($logger);
        $this->logRepository = $logRepository;
    }

    public function writeLogs($calculationSessionId) {
        $logs = $this->getLogs($calculationSessionId);

        foreach($logs as $log) {
            $message = sprintf("Database logs: Id:[%s], date:[%s], source:[%s], message:[%s]", $log->getId(), $log->getCreationDate()->format("Y-m-d H:i:s"), $log->getSource(), $log->getMessage());
            $this->logger->log($log->getLogLevel()->getCode(), $message);
        }
    }

    /**
     * @param $calculationSessionId
     * @return \AppBundle\Entity\SesDashboardLog[]
     */
    public function getLogs($calculationSessionId) {
        return $this->logRepository->getLogs($calculationSessionId);
    }

    public function getRepository()
    {
        return $this->logRepository;
    }

    public function setRepository(RepositoryInterface $repository)
    {
        $this->logRepository = $repository;
    }

    /**
     * @return SesDashboardLogRepository
     */
    public function getLogRepository()
    {
        return $this->logRepository;
    }

    /**
     * @param SesDashboardLogRepository $logRepository
     */
    public function setLogRepository($logRepository)
    {
        $this->logRepository = $logRepository;
    }
}