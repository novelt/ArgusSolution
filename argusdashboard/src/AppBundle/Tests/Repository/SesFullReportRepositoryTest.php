<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 01/12/2016
 * Time: 11:34
 */

namespace AppBundle\Tests\Repository;


use AppBundle\Entity\Constant;
use AppBundle\Repository\SesFullReportRepository;
use AppBundle\Services\DashboardService;
use AppBundle\Tests\BaseKernelTestCase;

class SesFullReportRepositoryTest extends BaseKernelTestCase
{
    public function testGetNumberOfReceivedOnTimeReports() {
        /* @var $fullReportRepository SesFullReportRepository*/
        $fullReportRepository = $this->getService('SesFullReportRepository');

        $weeks = array();
        for($i = 1; $i <= 40; $i++) {
            $weeks[] = $i;
        }

        $res = $fullReportRepository->getNumberOfReceivedOnTimeWeeklyReports(915, $weeks, 2016, null, null, DashboardService::WEEKLY_DELAY);
        $a = $res;
    }
}