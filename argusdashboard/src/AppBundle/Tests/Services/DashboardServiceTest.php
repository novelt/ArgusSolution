<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 13/12/2016
 * Time: 14:11
 */

namespace AppBundle\Tests\Services;


use AppBundle\Entity\Constant;
use AppBundle\Services\DashboardService;
use AppBundle\Tests\BaseKernelTestCase;

class DashboardServiceTest extends BaseKernelTestCase
{
    public function testGetNumberOfValidatedDiseaseZeroValues() {
        /* @var $dashboardService DashboardService */
        $dashboardService = $this->getService('DashboardService');

        $res = $dashboardService->getNumberOfValidatedDiseaseZeroValues(797, null, null, Constant::PERIOD_WEEKLY, null, null);
        $a = $res;
    }
}