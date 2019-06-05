<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 23/02/2017
 * Time: 10:57
 */

namespace AppBundle\Tests\Services\Timezone;


use AppBundle\Services\Timezone\TimezoneService;
use AppBundle\Tests\BaseKernelTestCase;

class TimezoneServiceTest extends BaseKernelTestCase
{
    public function testGetTimezones() {
        /* @var $timezoneService TimezoneService*/
        $timezoneService = $this->getService('TimezoneService');

        $timezones = $timezoneService->getTimezones();
        $this->assertNotEmpty($timezones);

        $defaultTimezone = $timezoneService->getDefaultTimezone();
        $this->assertNotNull($defaultTimezone);

        foreach($timezoneService->getTimezones() as $timezone) {
            $tz = $timezoneService->getTimezoneById($timezone->getId());
            $dtz = $timezoneService->getDateTimezoneById($timezone->getId());

            $this->assertNotNull($tz);
            if($tz !== null) {
                $this->assertEquals($timezone->getId(), $tz->getId());
            }
            $this->assertNotNull($dtz);
            if($dtz !== null) {
                $this->assertEquals(new \DateTimeZone($timezone->getId()), $dtz);
            }
        }

        $fakeTimezone = 'Mars/Olympus';
        $this->assertNull($timezoneService->getTimezoneById($fakeTimezone));
        $this->assertNull($timezoneService->getDateTimezoneById($fakeTimezone));

        $this->assertNull($timezoneService->getTimezoneById(null));
        $this->assertNull($timezoneService->getDateTimezoneById(null));
    }
}