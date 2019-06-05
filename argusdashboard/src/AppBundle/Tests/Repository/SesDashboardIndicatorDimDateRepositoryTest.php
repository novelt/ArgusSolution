<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 16/11/2016
 * Time: 16:07
 */

namespace AppBundle\Tests\Repository;


use AppBundle\Repository\SesDashboardIndicatorDimDateRepository;
use AppBundle\Tests\BaseKernelTestCase;

class SesDashboardIndicatorDimDateRepositoryTest extends BaseKernelTestCase
{
    public function testAny() {
        /* @var $indicatorDimDateRepository SesDashboardIndicatorDimDateRepository */
        $indicatorDimDateRepository = $this->getService("SesDashboardIndicatorDimDateRepository");
        $any = $indicatorDimDateRepository->any();

        $this->assertNotNull($any);//must be true or false
    }

    public function testGenerationDimDates() {
        /* @var $indicatorDimDateRepository SesDashboardIndicatorDimDateRepository */
        $indicatorDimDateRepository = $this->getService("SesDashboardIndicatorDimDateRepository");
        $indicatorDimDateRepository->emptyTable(true);

        $indicatorDimDateRepository->generateIndicatorDimDates(new \DateTime("2015/01/01"), new \DateTime("2015/01/05"));
        $dimDates = $indicatorDimDateRepository->findAll();
        $this->assertCount(5, $dimDates);

        $indicatorDimDateRepository->emptyTable(true);
        $dimDates = $indicatorDimDateRepository->findAll();
        $this->assertCount(0, $dimDates);
    }
}