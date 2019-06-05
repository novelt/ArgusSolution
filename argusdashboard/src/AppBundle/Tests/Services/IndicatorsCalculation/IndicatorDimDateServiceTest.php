<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 16/11/2016
 * Time: 17:32
 */

namespace AppBundle\Tests\Services\IndicatorsCalculation;


use AppBundle\Entity\SesDashboardIndicatorDimDate;
use AppBundle\Repository\SesDashboardIndicatorDimDateRepository;
use AppBundle\Services\IndicatorsCalculation\IndicatorDimDateService;
use AppBundle\Tests\BaseKernelTestCase;
use AppBundle\Utils\DimDateHelper;

class IndicatorDimDateServiceTest extends BaseKernelTestCase
{
    public function testGenerateIndicatorDimDatesAndCountAndFirstDaysOfWeeksMonday()
    {
        /* @var $indicatorDimDateService IndicatorDimDateService */
        $indicatorDimDateService = $this->getService("IndicatorDimDateService");

        $indicatorDimDateService->setEpiFirstDay(1);
        $indicatorDimDateService->generateIndicatorDimDates(new \DateTime("2020/05/01"), new \DateTime("2020/12/31"));
        $indicatorDimDateService->updateDimDateEpiFieldsAndPeriods();

        $dimDatesIds = $indicatorDimDateService->findAllDimDatesIdsByDateRange(new \DateTime("2020/05/01"), new \DateTime("2020/12/31"));
        $this->assertCount(245, $dimDatesIds); //day days

        //The dates are important here: 2020-12-08 is a tuesday, 2020-12-16 is a wednesday
        $firstDaysEpiWeeks = $indicatorDimDateService->findFirstDimDatesOfEpidemiologicWeeksIdsByDateRange(new \DateTime('2020-12-08'), new \DateTime('2020-12-16'));
        $this->assertCount(2, $firstDaysEpiWeeks); //2 days

        if(sizeof($firstDaysEpiWeeks) == 2) {
            $this->assertEquals(20201207, $firstDaysEpiWeeks[0]);//monday
            $this->assertEquals(20201214, $firstDaysEpiWeeks[1]);//monday
        }

        foreach($dimDatesIds as $dimDateId) {
            $dimDate = $indicatorDimDateService->find($dimDateId);
            if($dimDate !== null) {
                $indicatorDimDateService->remove($dimDate);
            }
        }
        $indicatorDimDateService->saveChanges();
    }

    public function testGenerateIndicatorDimDatesAndCountAndFirstAndLastDaysOfWeeksMonday()
    {
        /* @var $indicatorDimDateService IndicatorDimDateService */
        $indicatorDimDateService = $this->getService("IndicatorDimDateService");

        $indicatorDimDateService->setEpiFirstDay(1);
        $indicatorDimDateService->generateIndicatorDimDates(new \DateTime("2020/05/01"), new \DateTime("2020/12/31"));
        $indicatorDimDateService->updateDimDateEpiFieldsAndPeriods();

        $dimDatesIds = $indicatorDimDateService->findAllDimDatesIdsByDateRange(new \DateTime("2020/05/01"), new \DateTime("2020/12/31"));
        $this->assertCount(245, $dimDatesIds); //day days

        $firstAndLastDaysEpiWeeksIds = $indicatorDimDateService->findFirstAndLastDimDateOfEpidemiologicWeeksIdsByDateRange(new \DateTime('2020-05-05'), new \DateTime('2020-05-30'));

        $this->assertCount(4, $firstAndLastDaysEpiWeeksIds);
        $this->assertEquals(20200504, $firstAndLastDaysEpiWeeksIds[0]['firstId']);
        $this->assertEquals(20200510, $firstAndLastDaysEpiWeeksIds[0]['lastId']);
        $this->assertEquals(20200511, $firstAndLastDaysEpiWeeksIds[1]['firstId']);
        $this->assertEquals(20200517, $firstAndLastDaysEpiWeeksIds[1]['lastId']);
        $this->assertEquals(20200518, $firstAndLastDaysEpiWeeksIds[2]['firstId']);
        $this->assertEquals(20200524, $firstAndLastDaysEpiWeeksIds[2]['lastId']);
        $this->assertEquals(20200525, $firstAndLastDaysEpiWeeksIds[3]['firstId']);
        $this->assertEquals(20200531, $firstAndLastDaysEpiWeeksIds[3]['lastId']);

        $firstAndLastDaysEpiWeeks = $indicatorDimDateService->findFirstAndLastDimDatesOfEpidemiologicWeeksByDateRange(new \DateTime('2020-05-05'), new \DateTime('2020-05-30'));

        $this->assertCount(4, $firstAndLastDaysEpiWeeksIds);
        foreach($firstAndLastDaysEpiWeeks as $firstAndLastDaysEpiWeek) {
            $this->assertCount(2, $firstAndLastDaysEpiWeek);

            $this->assertNotNull($firstAndLastDaysEpiWeek[0]);
            $this->assertNotNull($firstAndLastDaysEpiWeek[1]);

            $this->assertInstanceOf(SesDashboardIndicatorDimDate::class, $firstAndLastDaysEpiWeek[0]);
            $this->assertInstanceOf(SesDashboardIndicatorDimDate::class, $firstAndLastDaysEpiWeek[1]);
        }

        $this->assertEquals(20200504, $firstAndLastDaysEpiWeeks[0][0]->getId());
        $this->assertEquals(20200510, $firstAndLastDaysEpiWeeks[0][1]->getId());
        $this->assertEquals(20200511, $firstAndLastDaysEpiWeeks[1][0]->getId());
        $this->assertEquals(20200517, $firstAndLastDaysEpiWeeks[1][1]->getId());
        $this->assertEquals(20200518, $firstAndLastDaysEpiWeeks[2][0]->getId());
        $this->assertEquals(20200524, $firstAndLastDaysEpiWeeks[2][1]->getId());
        $this->assertEquals(20200525, $firstAndLastDaysEpiWeeks[3][0]->getId());
        $this->assertEquals(20200531, $firstAndLastDaysEpiWeeks[3][1]->getId());

        foreach($dimDatesIds as $dimDateId) {
            $dimDate = $indicatorDimDateService->find($dimDateId);
            if($dimDate !== null) {
                $indicatorDimDateService->remove($dimDate);
            }
        }
        $indicatorDimDateService->saveChanges();
    }

    public function testGenerateIndicatorDimDatesAndCountAndFirstDaysOfWeeksTuesday()
    {
        /* @var $indicatorDimDateService IndicatorDimDateService */
        $indicatorDimDateService = $this->getService("IndicatorDimDateService");

        $indicatorDimDateService->setEpiFirstDay(2); //tuesday
        $indicatorDimDateService->generateIndicatorDimDates(new \DateTime("2020/05/01"), new \DateTime("2020/12/31"));
        $indicatorDimDateService->updateDimDateEpiFieldsAndPeriods();

        $dimDatesIds = $indicatorDimDateService->findAllDimDatesIdsByDateRange(new \DateTime("2020/05/01"), new \DateTime("2020/12/31"));
        $this->assertCount(245, $dimDatesIds); //day days

        //The dates are important here: 2020-12-09 is a wednesday, 2020-12-17 is a thursday
        $firstDaysEpiWeeks = $indicatorDimDateService->findFirstDimDatesOfEpidemiologicWeeksIdsByDateRange(new \DateTime('2020-12-09'), new \DateTime('2020-12-17'));
        $this->assertCount(2, $firstDaysEpiWeeks); //2 days

        if(sizeof($firstDaysEpiWeeks) == 2) {
            $this->assertEquals(20201208, $firstDaysEpiWeeks[0]);//tuesday
            $this->assertEquals(20201215, $firstDaysEpiWeeks[1]);//tuesday
        }

        foreach($dimDatesIds as $dimDateId) {
            $dimDate = $indicatorDimDateService->find($dimDateId);
            if($dimDate !== null) {
                $indicatorDimDateService->remove($dimDate);
            }
        }
        $indicatorDimDateService->saveChanges();
    }

    public function testGetDimDateIdFromDateTime()
    {

        $date = new \DateTime();
        $date->setDate(2017, 2, 8);

        $dimDateId = DimDateHelper::getDimDateIdFromDateTime($date);
        $this->assertEquals(20170208, $dimDateId);

        $date = null ;
        $dimDateId = DimDateHelper::getDimDateIdFromDateTime($date);
        $this->assertNull($dimDateId);
    }

    public function testFindFirstDaysOfEpidemiologicWeeksIds()
    {
        /* @var $indicatorDimDateService IndicatorDimDateService */
        $indicatorDimDateService = $this->getService("IndicatorDimDateService");

        $date = new \DateTime();
        $date->setDate(2010, 01, 21);

        $dimDateIds = $indicatorDimDateService->findFirstDimDatesOfEpidemiologicWeeksIdsByDateRange($date, $date);

        $this->assertNotNull($dimDateIds);
        $this->assertNotEquals(0, sizeof($dimDateIds));
    }

    public function testUpdateDimDateEpiFieldsAndPeriods() {
        $this->initializeDatabase();

        /* @var $indicatorDimDateService IndicatorDimDateService */
        $indicatorDimDateService = $this->getService("IndicatorDimDateService");

        $indicatorDimDateService->updateDimDateEpiFieldsAndPeriods();
    }

    public function testFindFirstAndLastDimDateIdsOfCalendarWeeksIdsByPeriodCode() {
        /* @var $indicatorDimDateService IndicatorDimDateService */
        $indicatorDimDateService = $this->getService("IndicatorDimDateService");

        $dimDateIds = $indicatorDimDateService->findFirstAndLastDimDateIdsOfCalendarWeeksByPeriodCode(["2018W27", "2018W28"]);

        $this->assertCount(2, $dimDateIds);

        $this->assertEquals('2018W27', $dimDateIds[0]['period']);
        $this->assertEquals(20180702, $dimDateIds[0]['firstId']);
        $this->assertEquals(20180708, $dimDateIds[0]['lastId']);
        $this->assertEquals('2018W28', $dimDateIds[1]['period']);
        $this->assertEquals(20180709, $dimDateIds[1]['firstId']);
        $this->assertEquals(20180715, $dimDateIds[1]['lastId']);
    }
}