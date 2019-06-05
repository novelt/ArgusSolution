<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 20-Jul-18
 * Time: 19:57
 */

namespace AppBundle\Services;


use AppBundle\Entity\SesDashboardLock;
use AppBundle\Repository\RepositoryInterface;
use AppBundle\Repository\SesDashboardLockRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bridge\Monolog\Logger;

class LockService extends BaseRepositoryService
{
    /**
     * @var SesDashboardLockRepository
     */
    private $lockRepository;

    /**
     * @var array
     */
    private $locks;

    public function __construct(Logger $logger, SesDashboardLockRepository $lockRepository)
    {
        parent::__construct($logger);
        $this->lockRepository = $lockRepository;
        $this->locks = [];
    }

    /**
     * This is the function to lock.
     * @param string $name The lock will be done on the name
     * @param float $timeout How many time maintain the lock. After this time, it will be possible to "break" it
     * @param int $wait How many seconds (and times) to retry to acquire the lock if it is already taken by another process
     * @return bool True if lock acquired. Else false.
     */
    public function acquireLock($name, $timeout = 30.0, $wait = 0) {
        // Insure that the timeout is at least 1 ms.
        $timeout = max($timeout, 0.001);
        $expire = microtime(TRUE) + $timeout;

        if(isset($this->locks[$name])) {
            $lockValue = $this->locks[$name];
            $success = false;

            $this->logger->debug(sprintf("Refreshing the lock '%s' (value [%d]) with new expire [%g]", $name, $lockValue, $expire));

            try {
                $lock = $this->findOneLock($name, $lockValue, null);
                if ($lock !== null) {
                    $lock->setExpire($expire);
                    $this->saveChanges($lock);
                    $success = true;
                    $this->logger->debug(sprintf("Refreshed the lock '%s' (value [%d]) with new expire [%g]", $name, $lockValue, $expire));
                }
            }
            catch(\Exception $e) {
                $this->logger->error(sprintf("Error when refreshing the lock '%s' (value [%d]): %s", $name, $lockValue, $e));
            }

            if(!$success) {
                // The lock was broken.
                unset($this->locks[$name]);
            }

            return $success;
        }
        else {
            // Optimistically try to acquire the lock, then retry once if it fails.
            // The first time through the loop cannot be a retry.
            $retry = false;

            // We always want to do this code at least once.
            do {
                try {
                    $lock = new SesDashboardLock();
                    $lock->setName($name);
                    $lock->setValue($this->generateLockValue());
                    $lock->setExpire($expire);

                    $this->persist($lock);
                    $this->saveChanges($lock);

                    //We track all acquired locks
                    $this->locks[$name] = $lock->getValue();

                    // We never need to try again.
                    $retry = false;
                }
                catch (\Exception $e) {
                    if($e instanceof UniqueConstraintViolationException || $e->getPrevious() !== null && $e->getPrevious() instanceof UniqueConstraintViolationException) {
                        $this->lockRepository->resetConnection();
                        // Suppress the error. If this is our first pass through the loop,
                        // then $retry is FALSE. In this case, the insert must have failed
                        // meaning some other request acquired the lock but did not release it.
                        // We decide whether to retry by checking lock_may_be_available()
                        // Since this will break the lock in case it is expired.
                        try {
                            if ($retry) $retry = false;
                            else {
                                $retry = $this->lockMayBeAvailable($name);

                                //added feature: it is possible to retry n times
                                while (!$retry && $wait > 0) {
                                    $this->logger->debug(sprintf("Waiting 1 second to try to acquire the lock [%s]", $name));
                                    sleep(1);
                                    $wait--;
                                    $retry = $this->lockMayBeAvailable($name);
                                }
                            }
                        } catch (\Exception $e) {
                            $this->logger->error(sprintf("Error during lockMayBeAvailable for the lock [%s]: %s", $name, $e));
                            $retry = false;
                        }
                    }
                    else {
                        $this->logger->error(sprintf("An exception different of UniqueConstraintViolationException has been catched: %s", $e));
                    }
                }

                // We only retry in case the first attempt failed, but we then broke
                // an expired lock.
            } while ($retry);
        }

        return isset($this->locks[$name]);
    }

    private function generateLockValue() {
        return uniqid(mt_rand(), true);
    }

    /**
     * @param $name
     * @return bool
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function lockMayBeAvailable($name) {
        $lock = $this->findOneLock($name, null);

        if($lock === null) {
            return true;
        }

        $expire = $lock->getExpire();
        $now = microtime(TRUE);

        if ($now > $expire) {

            // We check two conditions to prevent a race condition where another
            // request acquired the lock and set a new expire time. We add a small
            // number to $expire to avoid errors with float to string conversion.
            try {
                return $this->removeExpiredLock($name, $lock->getValue(), $expire + 0.0001);
            }
            catch (\Exception $e) {
                $this->logger->error(sprintf("Error when removing a lock with name [%s], value [%s] and expire >= [%g]: %s", $name, $lock->getValue(), $expire + 0.0001, $e));
                return false;
            }
        }

        return false;
    }

    /**
     * Release all locks acquired during this PHP process
     * @return bool
     */
    public function releaseAllLocks() {
        $success = true;

        foreach($this->locks as $lockName => $lockValue) {
            $released = $this->releaseLock($lockName);

            if(!$released) $success = false;
        }

        return $success;
    }

    /**
     * @param $name
     * @return bool
     */
    public function releaseLock($name) {
        try {
            $this->logger->debug(sprintf("Releasing the lock [%s]", $name));

            if (isset($this->locks[$name])) {
                $lock = $this->findOneLock($name, $this->locks[$name]);

                if ($lock !== null) {
                    $this->remove($lock);
                    $this->saveChanges($lock);

                    unset($this->locks[$name]);

                    $this->logger->debug(sprintf("Lock released [%s]", $name));

                    return true;
                }
                $this->logger->warning(sprintf("The lock [%s] could not be found in the database to be released.", $name));
            }
            else {
                $this->logger->warning(sprintf("The lock [%s] could not be found to be released.", $name));
            }
        }
        catch(\Exception $e) {
            $this->logger->error(sprintf("Error when releasing the lock [%s] (value [%g]): %s", $lock->getName(), $lock->getValue(), $e));
        }

        return false;
    }

    /**
     * @param null $name
     * @param null $value
     * @param null $expire
     * @param null $hydrationMode
     * @return mixed|SesDashboardLock|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneLock($name = null, $value = null, $expire = null, $hydrationMode = null) {
        return $this->lockRepository->findOneLock($name, $value, $expire, $hydrationMode);
    }

    /**
     * @param null $name
     * @param null $value
     * @param null $expire
     * @param null $hydrationMode
     * @return array
     */
    public function findLocks($name = null, $value = null, $expire = null, $hydrationMode = null) {
        return $this->lockRepository->findLocks($name, $value, $expire, $hydrationMode);
    }

    /**
     * @param null $name
     * @param null $value
     * @param null $expire
     * @return bool
     */
    private function removeExpiredLock($name=null, $value=null, $expire=null) {
        return ($this->lockRepository->removeExpiredLock($name, $value, $expire) > 0);
    }

    public function getRepository()
    {
        return $this->lockRepository;
    }

    public function setRepository(RepositoryInterface $repository)
    {
        $this->lockRepository = $repository;
    }

    /**
     * @return SesDashboardLockRepository
     */
    public function getLockRepository()
    {
        return $this->lockRepository;
    }

    /**
     * @param SesDashboardLockRepository $lockRepository
     */
    public function setLockRepository($lockRepository)
    {
        $this->lockRepository = $lockRepository;
    }

    /**
     * @return array
     */
    public function getLocks()
    {
        return $this->locks;
    }

    /**
     * @param array $locks
     */
    public function setLocks($locks)
    {
        $this->locks = $locks;
    }

    /**
     * @return Registry
     */
    public function getDoctrine()
    {
        return $this->doctrine;
    }

    /**
     * @param Registry $doctrine
     */
    public function setDoctrine($doctrine)
    {
        $this->doctrine = $doctrine;
    }
}