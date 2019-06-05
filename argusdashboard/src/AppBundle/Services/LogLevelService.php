<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 11/01/2017
 * Time: 15:50
 */

namespace AppBundle\Services;


use AppBundle\Repository\RepositoryInterface;
use AppBundle\Repository\SesDashboardLogLevelRepository;
use Symfony\Bridge\Monolog\Logger;

class LogLevelService extends BaseRepositoryService
{
    /**
     * @var SesDashboardLogLevelRepository
     */
    private $logLevelRepository;

    public function __construct(Logger $logger, SesDashboardLogLevelRepository $logLevelRepository)
    {
        parent::__construct($logger);
        $this->logLevelRepository = $logLevelRepository;
    }

    public function getRepository()
    {
        return $this->logLevelRepository;
    }

    public function setRepository(RepositoryInterface $repository)
    {
        $this->logLevelRepository = $repository;
    }

    /**
     * @return SesDashboardLogLevelRepository
     */
    public function getLogLevelRepository()
    {
        return $this->logLevelRepository;
    }

    /**
     * @param SesDashboardLogLevelRepository $logLevelRepository
     */
    public function setLogLevelRepository($logLevelRepository)
    {
        $this->logLevelRepository = $logLevelRepository;
    }


}