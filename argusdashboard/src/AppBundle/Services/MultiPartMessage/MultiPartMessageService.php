<?php

namespace AppBundle\Services\MultiPartMessage;

use AppBundle\Entity\MultiPartMessage\MultiPartMessage;
use AppBundle\Repository\MultiPartMessage\MultiPartMessageRepository;
use AppBundle\Repository\RepositoryInterface;
use AppBundle\Services\BaseRepositoryService;
use Symfony\Bridge\Monolog\Logger;

/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 05/12/2016
 * Time: 14:36
 */
class MultiPartMessageService extends BaseRepositoryService
{
    /**
     * @var MultiPartMessageRepository
     */
    private $multipartMessageRepository;

    public function __construct(Logger $logger, MultiPartMessageRepository $multipartMessageRepository)
    {
        parent::__construct($logger);
        $this->multipartMessageRepository = $multipartMessageRepository;
    }

    /**
     *
     * Save MultiPartMessage
     * @param $message
     * @param $gatewayNumber
     * @param $fromNumber
     * @param $odkId
     * @param $class
     */
    public function saveMultiPartMessage($message, $gatewayNumber, $fromNumber, $odkId, $class)
    {
        $multiPartMessage = new MultiPartMessage();
        $multiPartMessage->setCreationDate(new \DateTime());
        $multiPartMessage->setMessage($message);
        $multiPartMessage->setGatewayNumber($gatewayNumber);
        $multiPartMessage->setFromNumber($fromNumber);
        $multiPartMessage->setOdkId($odkId);
        $multiPartMessage->setClass($class);

        $this->persist($multiPartMessage);
        $this->saveChanges($multiPartMessage);
    }

    /**
     * @return array
     */
    public function getNonProcessedMultiPartMessages()
    {
        return $this->multipartMessageRepository->getNonProcessedMultiPartMessages();
    }

    /**
     * @param $gatewayNumber
     * @param $fromNumber
     * @param $date
     * @param int $interval
     *
     * @return array
     */
    public function getMultiPartMessages($gatewayNumber, $fromNumber, $date, $interval = 15)
    {
        return $this->multipartMessageRepository->getMultiPartMessagesInRange($gatewayNumber, $fromNumber, $date, $interval);
    }

    /**
     * @param null $fromNumber
     * @param null $gatewayNumber
     * @param null $message
     * @return MultiPartMessage[]
     */
    public function findMultiPartMessage($fromNumber=null, $gatewayNumber = null, $message = null) {
        return $this->multipartMessageRepository->findMultiPartMessage($fromNumber, $gatewayNumber, $message);
    }

    /**
     * @param null $fromNumber
     * @param null $gatewayNumber
     * @param null $message
     * @return MultiPartMessage
     */
    public function findOneMultiPartMessage($fromNumber=null, $gatewayNumber = null, $message = null) {
        return $this->multipartMessageRepository->findOneMultiPartMessage($fromNumber, $gatewayNumber, $message);
    }

    public function getRepository()
    {
        return $this->multipartMessageRepository;
    }

    public function setRepository(RepositoryInterface $repository)
    {
        $this->multipartMessageRepository = $repository;
    }


    /**
     * @return MultiPartMessageRepository
     */
    public function getMultipartMessageRepository()
    {
        return $this->multipartMessageRepository;
    }

    /**
     * @param MultiPartMessageRepository $multipartMessageRepository
     */
    public function setMultipartMessageRepository($multipartMessageRepository)
    {
        $this->multipartMessageRepository = $multipartMessageRepository;
    }
}