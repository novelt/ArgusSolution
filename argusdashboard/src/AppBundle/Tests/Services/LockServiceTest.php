<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 23-Jul-18
 * Time: 11:24
 */

namespace AppBundle\Tests\Services;


use AppBundle\Services\LockService;
use AppBundle\Tests\BaseKernelTestCase;

class LockServiceTest extends BaseKernelTestCase
{
    /**
     * @var LockService
     */
    private $lockService;

    protected function setUp()
    {
        parent::setUp();

        $this->lockService = $this->getService('LockService');
    }

    public function testLockAndRefreshLock() {
        $lockAcquired = $this->lockService->acquireLock('TEST');
        $this->assertTrue($lockAcquired);

        //Refresh the lock
        $lockAcquired = $this->lockService->acquireLock('TEST', 60.0);
        $this->assertTrue($lockAcquired);
    }

    public function testLockAgain() {
        //Must be run just after testLockAndRefreshLock(). For each test functions, we get new instances of services.
        $lockAcquired = $this->lockService->acquireLock('TEST');
        $this->assertFalse($lockAcquired);
    }

    public function testLockAndUnLockAndReLock() {
        $lockAcquired = $this->lockService->acquireLock('TEST2', 10);
        $this->assertTrue($lockAcquired);

        $lock = $this->lockService->findOneLock('TEST2');
        $this->assertNotNull($lock);

        $released = $this->lockService->releaseLock('TEST2');
        $this->assertTrue($released);

        $lock = $this->lockService->findOneLock('TEST2');
        $this->assertNull($lock);
    }

    public function testReleaseAllLocks() {
        $lockAcquired = $this->lockService->acquireLock('TEST10', 10);
        $this->assertTrue($lockAcquired);

        $lockAcquired = $this->lockService->acquireLock('TEST20', 10);
        $this->assertTrue($lockAcquired);

        $lockAcquired = $this->lockService->acquireLock('TEST30', 10);
        $this->assertTrue($lockAcquired);

        $lockAcquired = $this->lockService->acquireLock('TEST40', 10);
        $this->assertTrue($lockAcquired);

        $lockAcquired = $this->lockService->acquireLock('TEST50', 10);
        $this->assertTrue($lockAcquired);

        $allReleased = $this->lockService->releaseAllLocks();
        $this->assertTrue($allReleased);
    }
}