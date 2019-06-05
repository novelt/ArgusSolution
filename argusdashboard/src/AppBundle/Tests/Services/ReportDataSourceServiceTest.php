<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 03-Jul-18
 * Time: 14:28
 */

namespace AppBundle\Tests\Services;


use AppBundle\Services\ReportDataSourceService;
use AppBundle\Tests\BaseKernelTestCase;

class ReportDataSourceServiceTest extends BaseKernelTestCase
{
    /**
     * @var ReportDataSourceService
     */
    private $reportDataSourceService;

    protected function setUp()
    {
        parent::setUp();
        $this->reportDataSourceService = $this->getService('ReportDataSourceService');
    }

    public function testFindAll() {
        $all = $this->reportDataSourceService->findAll();
    }
}