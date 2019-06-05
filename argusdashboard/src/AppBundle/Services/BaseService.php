<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 16/11/2016
 * Time: 17:02
 */

namespace AppBundle\Services;


use AppBundle\Entity\Security\SesDashboardUser;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class BaseService
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var TokenStorage
     */
    protected $tokenStorage;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param Logger $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * Return the authenticated user
     * @return SesDashboardUser|null
     */
    protected function getAuthenticatedUser(TokenStorage $tokenStorage = null) {
        $storage = ($tokenStorage !== null ? $tokenStorage : $this->tokenStorage);

        if($storage === null) {
            return null;
        }

        if (null === $token = $storage->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return null;
        }

        return $user;
    }

    /**
     * @return TokenStorage
     */
    public function getTokenStorage()
    {
        return $this->tokenStorage;
    }

    /**
     * @param TokenStorage $tokenStorage
     */
    public function setTokenStorage($tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }
}