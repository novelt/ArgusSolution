<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 05/12/2016
 * Time: 15:21
 */

namespace AppBundle\Tests\Services;


use AppBundle\Entity\Constant;
use AppBundle\Services\DiseaseService;
use AppBundle\Tests\BaseKernelTestCase;

class DiseaseServiceTest extends BaseKernelTestCase
{
    public function testGetDiseasesPerPeriod() {
        /* @var $diseaseService DiseaseService */
        $diseaseService = $this->getService("DiseaseService");

        $diseases = $diseaseService->getDiseasesPerPeriod(Constant::PERIOD_WEEKLY, false);
        $a = $diseases;
    }
}