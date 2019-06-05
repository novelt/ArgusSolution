<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 23/02/2017
 * Time: 10:57
 */

namespace AppBundle\Tests\Services\Timezone;

use AppBundle\Services\DimDate\AbstractDimDateService;
use AppBundle\Services\DimDate\CurrentRangeDimDateService;
use AppBundle\Services\DimDate\PreviousRangeDimDateService;
use AppBundle\Tests\BaseKernelTestCase;
use AppBundle\Utils\DimDateHelper;

/**
 * Assumptions : First day of week must be monday epiFirstDay == 1
 *
 * Class RangeDimDateServiceTest
 * @package AppBundle\Tests\Services\Timezone
 */
class RangeDimDateServiceTest extends BaseKernelTestCase
{
    /* @var $dimDateService AbstractDimDateService*/
    private $dimDateService ;

    public function setUp()
    {
        parent::setUp();
        $this->initializeDatabase();

        $this->dimDateService = $this->getService('SiteDimDateService');
    }

    public function testDimDateFrom()
    {
        $date = '2019-02-04';
        $dimDateFrom = $this->dimDateService->getDimDateFrom(DimDateHelper::getDimDateIdFromString($date));

        if ($this->dimDateService instanceof PreviousRangeDimDateService) {
            // Must be the same
            $this->assertEquals($dimDateFrom->getId(), DimDateHelper::getDimDateIdFromString($date));
        } else if ($this->dimDateService instanceof CurrentRangeDimDateService) {
            // Must be the same
            $this->assertEquals($dimDateFrom->getId(), DimDateHelper::getDimDateIdFromString($date));
        }

        $date = '2019-02-05';
        $dimDateFrom = $this->dimDateService->getDimDateFrom(DimDateHelper::getDimDateIdFromString($date));

        if ($this->dimDateService instanceof PreviousRangeDimDateService) {
            // Must be the same
            $this->assertEquals($dimDateFrom->getId(), DimDateHelper::getDimDateIdFromString($date));
        } else if ($this->dimDateService instanceof CurrentRangeDimDateService) {
            // Must be the same
            $this->assertEquals($dimDateFrom->getId(), DimDateHelper::getDimDateIdFromString($date));
        }

        $date = '2019-02-10';
        $dimDateFrom = $this->dimDateService->getDimDateFrom(DimDateHelper::getDimDateIdFromString($date));

        if ($this->dimDateService instanceof PreviousRangeDimDateService) {
            // Must be the same
            $this->assertEquals($dimDateFrom->getId(), DimDateHelper::getDimDateIdFromString($date));
        } else if ($this->dimDateService instanceof CurrentRangeDimDateService) {
            // Must be the same
            $this->assertEquals($dimDateFrom->getId(), DimDateHelper::getDimDateIdFromString($date));
        }
    }

    public function testDimDateTo()
    {
        $date = '2019-02-04';
        $dimDateFrom = $this->dimDateService->getDimDateTo(DimDateHelper::getDimDateIdFromString($date));

        if ($this->dimDateService instanceof PreviousRangeDimDateService) {
            // Must be the same
            $this->assertEquals($dimDateFrom->getId(), DimDateHelper::getDimDateIdFromString($date));
        } else if ($this->dimDateService instanceof CurrentRangeDimDateService) {
            // Must be the same
            $this->assertEquals($dimDateFrom->getId(), DimDateHelper::getDimDateIdFromString($date));
        }

        $date = '2019-02-05';
        $dimDateFrom = $this->dimDateService->getDimDateTo(DimDateHelper::getDimDateIdFromString($date));

        if ($this->dimDateService instanceof PreviousRangeDimDateService) {
            // Must be the same
            $this->assertEquals($dimDateFrom->getId(), DimDateHelper::getDimDateIdFromString($date));
        } else if ($this->dimDateService instanceof CurrentRangeDimDateService) {
            // Must be the same
            $this->assertEquals($dimDateFrom->getId(), DimDateHelper::getDimDateIdFromString($date));
        }

        $date = '2019-02-10';
        $dimDateFrom = $this->dimDateService->getDimDateTo(DimDateHelper::getDimDateIdFromString($date));

        if ($this->dimDateService instanceof PreviousRangeDimDateService) {
            // Must be the same
            $this->assertEquals($dimDateFrom->getId(), DimDateHelper::getDimDateIdFromString($date));
        } else if ($this->dimDateService instanceof CurrentRangeDimDateService) {
            // Must be the same
            $this->assertEquals($dimDateFrom->getId(), DimDateHelper::getDimDateIdFromString($date));
        }
    }

    public function testDimDateWeekFrom()
    {
        $date = '2019-02-04';
        $dimDateFrom = $this->dimDateService->getWeekDimDateFrom(DimDateHelper::getDimDateIdFromString($date));

        if ($this->dimDateService instanceof PreviousRangeDimDateService) {
            // Must be previous monday
            $this->assertEquals($dimDateFrom->getId(), DimDateHelper::getDimDateIdFromString('2019-01-28'));
        } else if ($this->dimDateService instanceof CurrentRangeDimDateService) {
            // Must be the same
            $this->assertEquals($dimDateFrom->getId(), DimDateHelper::getDimDateIdFromString($date));
        }

        $date = '2019-02-05';
        $dimDateFrom = $this->dimDateService->getWeekDimDateFrom(DimDateHelper::getDimDateIdFromString($date));

        if ($this->dimDateService instanceof PreviousRangeDimDateService) {
            // Must be first day of previous week
            $this->assertEquals($dimDateFrom->getId(), DimDateHelper::getDimDateIdFromString('2019-01-28'));
        } else if ($this->dimDateService instanceof CurrentRangeDimDateService) {
            // Must be first day of current week
            $this->assertEquals($dimDateFrom->getId(), DimDateHelper::getDimDateIdFromString('2019-02-04'));
        }

        $date = '2019-02-10';
        $dimDateFrom = $this->dimDateService->getWeekDimDateFrom(DimDateHelper::getDimDateIdFromString($date));

        if ($this->dimDateService instanceof PreviousRangeDimDateService) {
            // Must be first day of previous week
            $this->assertEquals($dimDateFrom->getId(), DimDateHelper::getDimDateIdFromString('2019-01-28'));
        } else if ($this->dimDateService instanceof CurrentRangeDimDateService) {
            // Must be first day of current week
            $this->assertEquals($dimDateFrom->getId(), DimDateHelper::getDimDateIdFromString('2019-02-04'));
        }
    }

    public function testDimDateWeekTo()
    {
        $date = '2019-02-04';
        $dimDateFrom = $this->dimDateService->getWeekDimDateTo(DimDateHelper::getDimDateIdFromString($date));

        if ($this->dimDateService instanceof PreviousRangeDimDateService) {
            // Must be first day of previous week
            $this->assertEquals($dimDateFrom->getId(), DimDateHelper::getDimDateIdFromString('2019-01-28'));
        } else if ($this->dimDateService instanceof CurrentRangeDimDateService) {
            // Must be the same
            $this->assertEquals($dimDateFrom->getId(), DimDateHelper::getDimDateIdFromString($date));
        }

        $date = '2019-02-05';
        $dimDateFrom = $this->dimDateService->getWeekDimDateTo(DimDateHelper::getDimDateIdFromString($date));

        if ($this->dimDateService instanceof PreviousRangeDimDateService) {
            // Must be first day of previous week
            $this->assertEquals($dimDateFrom->getId(), DimDateHelper::getDimDateIdFromString('2019-01-28'));
        } else if ($this->dimDateService instanceof CurrentRangeDimDateService) {
            // Must be first day of current week
            $this->assertEquals($dimDateFrom->getId(), DimDateHelper::getDimDateIdFromString('2019-02-04'));
        }

        $date = '2019-02-10';
        $dimDateFrom = $this->dimDateService->getWeekDimDateTo(DimDateHelper::getDimDateIdFromString($date));

        if ($this->dimDateService instanceof PreviousRangeDimDateService) {
            // Must be first day of previous week
            $this->assertEquals($dimDateFrom->getId(), DimDateHelper::getDimDateIdFromString('2019-01-28'));
        } else if ($this->dimDateService instanceof CurrentRangeDimDateService) {
            // Must be first day of current week
            $this->assertEquals($dimDateFrom->getId(), DimDateHelper::getDimDateIdFromString('2019-02-04'));
        }
    }

    public function testDimDateMonthFrom()
    {
        $date = '2019-02-04';
        $dimDateFrom = $this->dimDateService->getMonthDimDateFrom(DimDateHelper::getDimDateIdFromString($date));

        if ($this->dimDateService instanceof PreviousRangeDimDateService) {
            // Must be first day of month
            $this->assertEquals($dimDateFrom->getId(), DimDateHelper::getDimDateIdFromString('2019-02-01'));
        } else if ($this->dimDateService instanceof CurrentRangeDimDateService) {
            // Must be first day of month
            $this->assertEquals($dimDateFrom->getId(), DimDateHelper::getDimDateIdFromString('2019-02-01'));
        }

        $date = '2019-02-05';
        $dimDateFrom = $this->dimDateService->getMonthDimDateFrom(DimDateHelper::getDimDateIdFromString($date));

        if ($this->dimDateService instanceof PreviousRangeDimDateService) {
            // Must be first day of month
            $this->assertEquals($dimDateFrom->getId(), DimDateHelper::getDimDateIdFromString('2019-02-01'));
        } else if ($this->dimDateService instanceof CurrentRangeDimDateService) {
            // Must be first day of month
            $this->assertEquals($dimDateFrom->getId(), DimDateHelper::getDimDateIdFromString('2019-02-01'));
        }

        $date = '2019-02-10';
        $dimDateFrom = $this->dimDateService->getMonthDimDateFrom(DimDateHelper::getDimDateIdFromString($date));

        if ($this->dimDateService instanceof PreviousRangeDimDateService) {
            // Must be first day of month
            $this->assertEquals($dimDateFrom->getId(), DimDateHelper::getDimDateIdFromString('2019-02-01'));
        } else if ($this->dimDateService instanceof CurrentRangeDimDateService) {
            // Must be first day of month
            $this->assertEquals($dimDateFrom->getId(), DimDateHelper::getDimDateIdFromString('2019-02-01'));
        }
    }

    public function testDimDateMonthTo()
    {
        $date = '2019-02-04';
        $dimDateFrom = $this->dimDateService->getMonthDimDateTo(DimDateHelper::getDimDateIdFromString($date));

        if ($this->dimDateService instanceof PreviousRangeDimDateService) {
            // Must be first day of month
            $this->assertEquals($dimDateFrom->getId(), DimDateHelper::getDimDateIdFromString('2019-02-01'));
        } else if ($this->dimDateService instanceof CurrentRangeDimDateService) {
            // Must be first day of month
            $this->assertEquals($dimDateFrom->getId(), DimDateHelper::getDimDateIdFromString('2019-02-01'));
        }

        $date = '2019-02-05';
        $dimDateFrom = $this->dimDateService->getMonthDimDateTo(DimDateHelper::getDimDateIdFromString($date));

        if ($this->dimDateService instanceof PreviousRangeDimDateService) {
            // Must be first day of month
            $this->assertEquals($dimDateFrom->getId(), DimDateHelper::getDimDateIdFromString('2019-02-01'));
        } else if ($this->dimDateService instanceof CurrentRangeDimDateService) {
            // Must be first day of month
            $this->assertEquals($dimDateFrom->getId(), DimDateHelper::getDimDateIdFromString('2019-02-01'));
        }

        $date = '2019-02-10';
        $dimDateFrom = $this->dimDateService->getMonthDimDateTo(DimDateHelper::getDimDateIdFromString($date));

        if ($this->dimDateService instanceof PreviousRangeDimDateService) {
            // Must be first day of month
            $this->assertEquals($dimDateFrom->getId(), DimDateHelper::getDimDateIdFromString('2019-02-01'));
        } else if ($this->dimDateService instanceof CurrentRangeDimDateService) {
            // Must be first day of month
            $this->assertEquals($dimDateFrom->getId(), DimDateHelper::getDimDateIdFromString('2019-02-01'));
        }
    }
}