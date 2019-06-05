<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 16/11/2016
 * Time: 15:29
 */

namespace AppBundle\Repository;


use AppBundle\Entity\SesDashboardIndicatorDimDate;
use AppBundle\Services\DbConstant;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\Expr\Join;

class SesDashboardIndicatorDimDateRepository extends BaseRepository
{
    /**
     * @param \DateTime $from
     * @param \DateTime $to
     * @return bool
     */
    public function generateIndicatorDimDates(\DateTime $from, \DateTime $to) {
        $storedProc = $this->_em->getConnection()->prepare("CALL usp_sesdashboard_indicatorDimDates_populate('".$from->format("Y/m/d")."', '".$to->format("Y/m/d")."')");
        return $storedProc->execute();
    }

    /**
     * @return int[]
     */
    public function findAllUntilToday() {
        return $this->findAllDimDateIdsByDateRange(null, new \DateTime("now"));
    }

    /**
     * @param null $dayOfMonth
     * @param null $monthOfYear
     * @param null $calendarYear
     * @return SesDashboardIndicatorDimDate|null
     */
    public function findOneDimDateByDayMonthAndYear($dayOfMonth=null, $monthOfYear=null, $calendarYear=null) {
        $qb = $this->findDimDateQueryBuilder($dayOfMonth, $monthOfYear, $calendarYear);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findDimDateQueryBuilder($dayOfMonth=null, $monthOfYear=null, $calendarYear=null) {
        $qb = $this->createQueryBuilder('d');

        if($dayOfMonth !== null) {
            if($dayOfMonth === DbConstant::NULL) {
                $qb->andWhere('d.dayOfMonth IS NULL');
            }
            else if($dayOfMonth === DbConstant::NOT_NULL) {
                $qb->andWhere('d.dayOfMonth IS NOT NULL');
            }
            else if(is_array($dayOfMonth)) {
                $qb->andWhere('d.dayOfMonth IN (:dayOfMonth)');
                $qb->setParameter('dayOfMonth', $dayOfMonth);
            }
            else {
                $qb->andWhere('d.dayOfMonth = :dayOfMonth');
                $qb->setParameter('dayOfMonth', $dayOfMonth);
            }
        }

        if($monthOfYear !== null) {
            if($monthOfYear === DbConstant::NULL) {
                $qb->andWhere('d.monthOfYear IS NULL');
            }
            else if($monthOfYear === DbConstant::NOT_NULL) {
                $qb->andWhere('d.monthOfYear IS NOT NULL');
            }
            else if(is_array($monthOfYear)) {
                $qb->andWhere('d.monthOfYear IN (:monthOfYear)');
                $qb->setParameter('monthOfYear', $monthOfYear);
            }
            else {
                $qb->andWhere('d.monthOfYear = :monthOfYear');
                $qb->setParameter('monthOfYear', $monthOfYear);
            }
        }

        if($calendarYear !== null) {
            if($calendarYear === DbConstant::NULL) {
                $qb->andWhere('d.calendarYear IS NULL');
            }
            else if($calendarYear === DbConstant::NOT_NULL) {
                $qb->andWhere('d.calendarYear IS NOT NULL');
            }
            else if(is_array($calendarYear)) {
                $qb->andWhere('d.calendarYear IN (:calendarYear)');
                $qb->setParameter('calendarYear', $calendarYear);
            }
            else {
                $qb->andWhere('d.calendarYear = :calendarYear');
                $qb->setParameter('calendarYear', $calendarYear);
            }
        }

        return $qb;
    }

    /**
     * @param \DateTime|null $from
     * @param \DateTime|null $to
     * @return int[]
     */
    public function findFirstDimDatesOfCalendarWeeksIdsByDateRange(\DateTime $from = null, \DateTime $to = null) {
        return $this->findFirstDimDatesByDateRange('weekOfYear', $from, $to);
    }

    /**
     * @param \DateTime|null $from
     * @param \DateTime|null $to
     * @return int[]
     */
    public function findFirstDimDatesOfEpidemiologicWeeksIdsByDateRange(\DateTime $from = null, \DateTime $to = null) {
        return $this->findFirstDimDatesByDateRange('epiWeekOfYear', $from, $to, 'epiYear');
    }

    /**
     * @param \DateTime|null $from
     * @param \DateTime|null $to
     * @return int[]
     */
    public function findFirstDimDatesOfCalendarMonthsIdsByDateRange(\DateTime $from = null, \DateTime $to = null) {
        return $this->findFirstDimDatesByDateRange('monthOfYear', $from, $to);
    }

    /**
     * @param $dateType
     * @param \DateTime|null $from
     * @param \DateTime|null $to
     * @param string $yearField
     * @return \int[]
     */
    private function findFirstDimDatesByDateRange($dateType, \DateTime $from = null, \DateTime $to = null, $yearField = 'calendarYear') {
        $qb = $this->findFirstDimDatesByDateRangeQueryBuilder($dateType, $from, $to, $yearField);

        $dimDateIds = array_map('current', $qb->getQuery()->getScalarResult());

        return $dimDateIds;
    }

    /**
     * @param $dateColumn
     * @param \DateTime|null $from
     * @param \DateTime|null $to
     * @param string $yearField
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function findFirstDimDatesByDateRangeQueryBuilder($dateColumn, \DateTime $from = null, \DateTime $to = null, $yearField = 'calendarYear') {
        $qb = $this->createQueryBuilder('d1')
            ->select('min(d2.id)')
            ->innerJoin(SesDashboardIndicatorDimDate::class, 'd2', Join::WITH,
                        sprintf('d1.%s = d2.%s AND d1.%s = d2.%s', $dateColumn, $dateColumn, $yearField, $yearField));

            //->andWhere('d1.id >= :startLimit')
           // ->setParameter('startLimit', 19700101); //Hard coded selection: nothing before 2017/01/01

        if($from !== null) {
            $qb->andWhere('d1.fullDate >= :from');
            $qb->setParameter('from', $from);
        }
        if($to !== null) {
            $qb->andWhere('d1.fullDate <= :to');
            $qb->setParameter('to', $to);
        }

        $qb->groupBy(sprintf('d2.%s', $dateColumn), sprintf('d2.%s', $yearField));
        $qb->orderBy('d2.id');

        return $qb;
    }

    /**
     * @param \DateTime|null $from
     * @param \DateTime|null $to
     * @return array
     */
    public function findFirstAndLastDimDatesOfCalendarWeeksIdsByDateRange(\DateTime $from = null, \DateTime $to = null) {
        return $this->findFirstAndLastDimDatesByDateRange('weekOfYear', $from, $to);
    }

    /**
     * @param \DateTime|null $from
     * @param \DateTime|null $to
     * @return array
     */
    public function findFirstAndLastDimDatesOfEpidemiologicWeeksIdsByDateRange(\DateTime $from = null, \DateTime $to = null) {
        return $this->findFirstAndLastDimDatesByDateRange('epiWeekOfYear', $from, $to, 'epiYear');
    }

    /**
     * @param \DateTime|null $from
     * @param \DateTime|null $to
     * @return array
     */
    public function findFirstAndLastDimDatesOfCalendarMonthsIdsByDateRange(\DateTime $from = null, \DateTime $to = null) {
        return $this->findFirstAndLastDimDatesByDateRange('monthOfYear', $from, $to);
    }

    /**
     * @param $dateColumn
     * @param \DateTime|null $from
     * @param \DateTime|null $to
     * @param string $yearField
     * @return array
     */
    private function findFirstAndLastDimDatesByDateRange($dateColumn, \DateTime $from = null, \DateTime $to = null, $yearField = 'calendarYear') {
        $qb = $this->findFirstAndLastDimDatesByDateRangeQueryBuilder($dateColumn, $from, $to, $yearField);

        return $qb->getQuery()->getScalarResult();
    }

    private function findFirstAndLastDimDatesByDateRangeQueryBuilder($dateColumn, \DateTime $from = null, \DateTime $to = null, $yearField = 'calendarYear') {
        $qb = $this->findFirstDimDatesByDateRangeQueryBuilder($dateColumn, $from, $to, $yearField);

        $qb->select('min(d2.id) as firstId');
        $qb->addSelect('max(d2.id) as lastId');

        return $qb;
    }

    /**
     * @param \DateTime|null $from
     * @param \DateTime|null $to
     * @return int[]
     */
    public function findAllDimDateIdsByDateRange(\DateTime $from = null, \DateTime $to = null) {
        $qb = $this->createQueryBuilder('d');
        $qb->select('d.id');

        if($from !== null) {
            $qb->andWhere('d.fullDate >= :from');
            $qb->setParameter('from', $from);
        }
        if($to !== null) {
            $qb->andWhere('d.fullDate <= :to');
            $qb->setParameter('to', $to);
        }

        $dimDateIds = array_map('current', $qb->getQuery()->getScalarResult());

        return $dimDateIds;
    }

    /**
     * @return SesDashboardIndicatorDimDate[]
     */
    public function findAll() {
        return parent::findAll();
    }

    /**
     * @param mixed $id
     * @param null $lockMode
     * @param null $lockVersion
     * @return null|SesDashboardIndicatorDimDate
     */
    public function find($id, $lockMode = null, $lockVersion = null)
    {
        return parent::find($id, $lockMode, $lockVersion); // TODO: Change the autogenerated stub
    }

    /**
     * @param $periodCode
     * @return array
     */
    public function findFirstAndLastDimDateIdsOfCalendarWeeksByPeriodCode($periodCode) {
        return $this->findFirstAndLastDimDateIdsByPeriodCode('weekPeriodCode', $periodCode);
    }

    /**
     * @param $periodCode
     * @return array
     */
    public function findFirstAndLastDimDateIdsOfEpidemiologicWeeksIdsByPeriodCode($periodCode) {
        return $this->findFirstAndLastDimDateIdsByPeriodCode('epiWeekPeriodCode', $periodCode);
    }

    /**
     * @param $periodCode
     * @return array
     */
    public function findFirstAndLastDimDateIdsOfCalendarMonthsIdsByPeriodCode($periodCode) {
        return $this->findFirstAndLastDimDateIdsByPeriodCode('monthPeriodCode', $periodCode);
    }

    /**
     * @param $periodCodeColumn
     * @param $periodCode
     * @return array
     */
    private function findFirstAndLastDimDateIdsByPeriodCode($periodCodeColumn, $periodCode) {
        $qb = $this->findFirstAndLastDimDateIdsByPeriodCodeQueryBuilder($periodCodeColumn, $periodCode);

        return $qb->getQuery()->getScalarResult();
    }

    /**
     * @param $periodCodeColumn
     * @param $periodCode
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function findFirstAndLastDimDateIdsByPeriodCodeQueryBuilder($periodCodeColumn, $periodCode) {
        $qb = $this->findFirstDimDateIdsByPeriodCodeQueryBuilder($periodCodeColumn, $periodCode);

        $qb->select(sprintf('d.%s as period', $periodCodeColumn));
        $qb->addSelect('min(d.id) as firstId');
        $qb->addSelect('max(d.id) as lastId');

        return $qb;
    }

    /**
     * @param $periodCodeColumn
     * @param $periodCode
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function findFirstDimDateIdsByPeriodCodeQueryBuilder($periodCodeColumn, $periodCode) {
        $qb = $this->createQueryBuilder('d')
            ->select('min(d.id)');

        $this->addWhere($qb, 'd', $periodCodeColumn, $periodCode);

        $qb->groupBy(sprintf('d.%s', $periodCodeColumn));
        $qb->orderBy('d.id');

        return $qb;
    }
}