<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 7/22/2015
 * Time: 11:29 AM
 */

namespace AppBundle\Repository;

use AppBundle\Services\DbConstant;
use Doctrine\ORM\AbstractQuery;
use AppBundle\Entity\Constant;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

class SesFullReportRepository extends BaseRepository
{
    public function getFullReportFromPeriodSiteStartDate($period, $siteId, $startDate)
    {
        $qb = $this->createQueryBuilder('fr');

        $qb
            ->where('fr.period = :period')
            ->setParameter('period', $period)
        ;

        $qb
            ->andWhere('fr.FK_SiteId = :siteId')
            ->setParameter('siteId', $siteId)
        ;

        $qb
            ->andWhere('fr.startDate = :startDate')
            ->setParameter('startDate', $startDate)
        ;

        return $qb
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    public function getFullReport($fullReportId, $isDeleted, $isArchived)
    {
        $qb = $this->createQueryBuilder('fr')
            ->leftJoin('fr.partReports', 'pr')
            ->addSelect('pr')
            ->leftJoin('pr.reports', 'r', 'WITH', 'r.isArchived = :isarchived AND r.isDeleted = :isdeleted')
            ->setParameter('isarchived', $isArchived)->setParameter('isdeleted', $isDeleted)
            ->addSelect('r')
            ->leftJoin('r.reportValues', 'rv')
            ->addSelect('rv');

        $qb
            ->where('fr.id = :fullReportId')
            ->setParameter('fullReportId', $fullReportId)
        ;

        // Order by pr.id to display last part report before others
        // Then order by Report disease
        // Then order by reportValue Key
        $qb
            ->addOrderBy('pr.id','DESC')
            ->addOrderBy('r.disease', 'ASC')
            ->addOrderBy('rv.key', 'ASC')
        ;

        return $qb
        ->getQuery()
        ->getOneOrNullResult()
            ;
    }

    /**
     * Return Full Report during a period
     *
     * @param $siteId
     * @param $startDate
     * @param $endDate
     * @param $period
     * @param $status
     * @return array
     */
    public function getReportPeriod($siteId, $startDate, $endDate, $period, $status)
    {
        $qb = $this->createQueryBuilder('fr')
            ->innerJoin('fr.frontLineGroup', 's')
            ->innerJoin('fr.partReports', 'pr')
            ->innerJoin('pr.reports', 'r')
            ->groupBy('fr.id')
            //->select('fr, s, pr, r');
        ->select ('fr.id,
                    MIN(r.receptionDate) as receptionDate,
                    fr.startDate as startDate,
                    fr.createdDate as createdDate,
                    fr.firstValidationDate as firstValidationDate,
                    fr.firstRejectionDate as firstRejectionDate,
                    s.weeklyTimelinessMinutes as weeklyTimelinessMinutes,
                    s.monthlyTimelinessMinutes as monthlyTimelinessMinutes');

        $qb
            ->where('fr.startDate BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
        ;

        $qb
            ->andWhere('fr.FK_SiteId IN (:fkSiteId)')
            ->setParameter('fkSiteId', $siteId)
        ;

        $qb
            ->andWhere('fr.period = :period')
            ->setParameter('period', $period)
        ;

        if ($status != Constant::STATUS_ALL){
            $qb
                ->andWhere('fr.status = :status')
                ->setParameter('status', $status)
            ;
        }

        $result = $qb
            ->getQuery()
            ->getArrayResult();

        return $result ;
    }

    /**
     * Sued to calculate and create Aggregate Reports
     *
     * @param $siteId
     * @param $startDate
     * @param $endDate
     * @param $period
     * @param $status
     *
     * @return array
     */
    public function getAggregateValuesReportPeriod($siteId, $startDate, $endDate, $period, $status)
    {
        $qb = $this->createQueryBuilder('fr')
            ->innerJoin('fr.frontLineGroup', 's')
            ->innerJoin('fr.partReports', 'pr')
            ->innerJoin('pr.reports', 'r')
            ->innerJoin('r.reportValues', 'rv')
            ->select('fr.id, min(fr.createdDate) as createdDate, fr.weekNumber, fr.year, fr.startDate, GROUP_CONCAT(pr.id) as partReportIds, rv.key, sum(rv.value) as value');

        $qb
            ->where('fr.startDate BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
        ;

        $qb
            ->andWhere('fr.FK_SiteId IN (:fkSiteId)')
            ->setParameter('fkSiteId', $siteId)
        ;

        $qb
            ->andWhere('fr.period = :period')
            ->setParameter('period', $period)
        ;

        if ($status != Constant::STATUS_ALL){
            $qb
                ->andWhere('fr.status = :status')
                ->setParameter('status', $status)
            ;
        }

        $qb
            ->addGroupBy('fr.weekNumber, fr.year, fr.startDate')
            ->addGroupBy('rv.key');

        $qb
            ->addOrderBy('fr.year', 'ASC')
            ->addOrderBy ('fr.weekNumber', 'ASC');

        $result = $qb
            ->getQuery()
            ->getResult();

        return $result ;
    }

    /**
     * Return number of FullReport during a period
     *
     * @param $siteId
     * @param $startDate
     * @param $endDate
     * @param $period
     * @param $status
     * @return mixed
     */
    public function getNumberOfReportPeriod($siteId, $startDate, $endDate, $period, $status)
    {
        $qb = $this->createQueryBuilder('fr')
            ->select('COUNT(fr.id)');

        $qb
            ->where('fr.startDate BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
        ;

        $qb
            ->andWhere('fr.FK_SiteId IN (:fkSiteId)')
            ->setParameter('fkSiteId', $siteId)
        ;

        $qb
            ->andWhere('fr.period = :period')
            ->setParameter('period', $period)
        ;

        if ($status != Constant::STATUS_ALL){
            $qb
                ->andWhere('fr.status = :status')
                ->setParameter('status', $status)
            ;
        }

        return $qb
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    /**
     * Return number of FullReport for a specific week or month
     *
     * @param $siteId
     * @param $weekNumber
     * @param $monthNumber
     * @param $year
     * @param $period
     * @param $status
     * @return mixed
     */
    public function getNumberOfReport($siteId, $weekNumber, $monthNumber, $year, $period, $status, $contactId=null, $contactTypeId=null)
    {
        return $this->getNumberOfReportQueryBuilder($siteId, $weekNumber, $monthNumber, $year, $period, $status, $contactId, $contactTypeId)
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    /**
     * @param $siteId
     * @param $weekNumber
     * @param $monthNumber
     * @param $year
     * @param $period
     * @param $status
     * @param null $contactId
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getNumberOfReportQueryBuilder($siteId, $weekNumber, $monthNumber, $year, $period, $status, $contactId=null, $contactTypeId=null)
    {
        $qb = $this->createQueryBuilder('fr')
            ->select('COUNT(fr.id)');

        $qb
            ->where('fr.FK_SiteId IN (:fkSiteId)')
            ->setParameter('fkSiteId', $siteId)
        ;

        $qb
            ->andWhere('fr.year = :year')
            ->setParameter('year', $year)
        ;

        if ($status != Constant::STATUS_ALL){
            $qb
                ->andWhere('fr.status = :status')
                ->setParameter('status', $status)
            ;
        }

        $qb
            ->andWhere('fr.period = :period')
            ->setParameter('period', $period)
        ;

        if ($period == Constant::PERIOD_WEEKLY){
            $qb
                ->andWhere('fr.weekNumber = :weekNumber')
                ->setParameter('weekNumber', $weekNumber)
            ;
        }
        else if ($period == Constant::PERIOD_MONTHLY){
            $qb
                ->andWhere('fr.monthNumber = :monthNumber')
                ->setParameter('monthNumber', $monthNumber)
            ;
        }

        if($contactId !== null || $contactTypeId !== null) {
            $qb->innerJoin('fr.frontLineGroup', 'site')
                ->innerJoin('site.contacts', 'contact');

            if ($contactId !== null) {
                if($contactId == DbConstant::NOT_NULL) {
                    $qb->andWhere('contact.id IS NOT NULL');
                }
                else if($contactId == DbConstant::NULL) {
                    $qb->andWhere('contact.id IS NULL');
                }
                else if (is_array($contactId)) {
                    $qb->andWhere('contact.id IN (:contactId)');
                    $qb->setParameter('contactId', $contactId);
                } else {
                    $qb->andWhere('contact.id = :contactId');
                    $qb->setParameter('contactId', $contactId);
                }
            }

            if ($contactTypeId !== null) {
                if($contactTypeId == DbConstant::NOT_NULL) {
                    $qb->andWhere('contact.contactTypeId IS NOT NULL');
                }
                else if($contactTypeId == DbConstant::NULL) {
                    $qb->andWhere('contact.contactTypeId IS NULL');
                }
                else
                    if (is_array($contactTypeId)) {
                    $qb->andWhere('contact.contactTypeId IN (:contactTypeId)');
                    $qb->setParameter('contactTypeId', $contactTypeId);
                } else {
                    $qb->andWhere('contact.contactTypeId = :contactTypeId');
                    $qb->setParameter('contactTypeId', $contactTypeId);
                }
            }
        }

        return $qb;
    }

    /**
     * Returns the number of received on time weekly reports in function of the given parameters
     * @param $siteId
     * @param $weekNumber
     * @param $year
     * @param $delay
     * @return int
     */
    public function getNumberOfReceivedOnTimeWeeklyReports($siteId, $weekNumber, $year, $contactId, $contactTypeId=null, $delay)
    {
        $qb = $this->getReceivedOnTimeReportsIdsQueryBuilder(Constant::PERIOD_WEEKLY, $siteId, $year, $contactId, $contactTypeId, $delay);

        if(is_array($weekNumber)) {
            $qb->andWhere('fr.weekNumber IN (:weekNumber)');
            $qb->setParameter('weekNumber', $weekNumber);
        }
        else {
            $qb->andWhere('fr.weekNumber = :weekNumber');
            $qb->setParameter('weekNumber', $weekNumber);
        }

        //could not make a sum with a sub request like: select count(*) from (select ....)
        //It seems that doctrine does not handle sub queries.
        //So we count the number of rows in php
        $result = array_map('current', $qb->getQuery()->getScalarResult());
        return sizeof($result);
    }

    /**
     * Returns the number of received on time monthly reports in function of the given parameters
     * @param $siteId
     * @param $monthNumber
     * @param $year
     * @param $delay
     * @return int
     */
    public function getNumberOfReceivedOnTimeMonthlyReports($siteId, $monthNumber, $year, $contactId=null, $contactTypeId=null, $delay)
    {
        $qb = $this->getReceivedOnTimeReportsIdsQueryBuilder(Constant::PERIOD_MONTHLY, $siteId, $year, $contactId, $contactTypeId, $delay);

        if(is_array($monthNumber)) {
            $qb->andWhere('fr.monthNumber IN (:monthNumber)');
            $qb->setParameter('monthNumber', $monthNumber);
        }
        else {
            $qb->andWhere('fr.monthNumber = :monthNumber');
            $qb->setParameter('monthNumber', $monthNumber);
        }

        //could not make a sum with a sub request like: select count(*) from (select ....)
        //It seems that doctrine does not handle sub queries.
        //So we count the number of rows in php
        $result = array_map('current', $qb->getQuery()->getScalarResult());
        return sizeof($result);
    }

    /**
     * Returns a pre-built query builder to get the id of received on time reports in function of the given parameters
     * @param $period
     * @param $siteId
     * @param $weekNumber
     * @param $year
     * @param $delay
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getReceivedOnTimeReportsIdsQueryBuilder($period, $siteId, $year, $contactId=null, $contactTypeId=null, $delay)
    {
        $qb = $this->createQueryBuilder('fr')
            ->select('fr.id')
            ->innerJoin('fr.partReports', 'pr')
            ->innerJoin('pr.reports', 'r')
            ->innerJoin('fr.frontLineGroup', 's');

        $qb->where('fr.period = :period')
            ->setParameter('period', $period);

        if(is_array($siteId)) {
            $qb->andWhere('fr.FK_SiteId IN (:fkSiteId)');
            $qb->setParameter('fkSiteId', $siteId);
        }
        else {
            $qb->andWhere('fr.FK_SiteId = :fkSiteId');
            $qb->setParameter('fkSiteId', $siteId);
        }

        if(is_array($year)) {
            $qb->andWhere('fr.year IN (:year)');
            $qb->setParameter('year', $year);
        }
        else {
            $qb->andWhere('fr.year = :year');
            $qb->setParameter('year', $year);
        }

        if($contactId !== null || $contactTypeId !== null) {
            $qb->innerJoin('s.contacts', 'contact');

            if($contactId !== null) {
                if (is_array($contactId)) {
                    $qb->andWhere('contact.id IN (:contactId)');
                    $qb->setParameter('contactId', $contactId);
                }
                else if ($contactId === DbConstant::NULL) {
                    $qb->andWhere('contact.id IS NULL');
                }
                else if ($contactId === DbConstant::NOT_NULL) {
                    $qb->andWhere('contact.id IS NOT NULL');
                }
                else {
                    $qb->andWhere('contact.id = :contactId');
                    $qb->setParameter('contactId', $contactId);
                }
            }
            if($contactTypeId !== null){
                if (is_array($contactTypeId)) {
                    $qb->andWhere('contact.contactTypeId IN (:contactTypeId)');
                    $qb->setParameter('contactTypeId', $contactTypeId);
                }
                else if ($contactTypeId === DbConstant::NULL) {
                    $qb->andWhere('contact.contactTypeId IS NULL');
                }
                else if ($contactTypeId === DbConstant::NOT_NULL) {
                    $qb->andWhere('contact.contactTypeId IS NOT NULL');
                }
                else {
                    $qb->andWhere('contact.contactTypeId = :contactTypeId');
                    $qb->setParameter('contactTypeId', $contactTypeId);
                }
            }
        }

        $qb->andWhere("r.receptionDate <= DATE_ADD(fr.startDate, (s.weeklyTimelinessMinutes * 60 + :delay), 'SECOND')") //doctrine does not support minutes...
            ->setParameter('delay', ($delay*60));

        $qb->groupBy('fr.id');

        return $qb;
    }

    /**
     * Return Number of validated Disease Values
     * @param $siteIds
     * @param $disease
     * @param $diseaseValue
     * @param $weekNumber
     * @param $monthNumber
     * @param $period
     * @param $year
     * @param $contactId
     * @return mixed|string
     *
     */
    public function getNumberOfValidatedDiseaseValues($siteIds, $disease, $diseaseValue, $weekNumber, $monthNumber, $period, $year, $contactId, $contactTypeId)
    {
        $qb = $this->getValidatedDiseaseValuesQueryBuilder($siteIds, $disease, $diseaseValue, $weekNumber, $monthNumber, $period, $year, $contactId, $contactTypeId)
            ->select('SUM(rv.value) as value');

        $result = $qb
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR)
        ;

        if($result !== null) {
            return $result;
        }

        //TODO: do we really need to return '-' ?
        return '-' ;
    }

    /**
     * Return Number of validated Disease Values containg the value '0' --> zero report submissions
     * Remark: the given parameters must not be arrays
     *
     * @param $siteId int
     * @param $disease string
     * @param $diseaseValue string
     * @param $weekNumber int
     * @param $monthNumber int
     * @param $period string
     * @param $year int
     * @param $contactId int
     * @return int
     */
    public function getNumberOfValidatedDiseaseZeroValues($siteId, $weekNumber, $monthNumber, $period, $year, $contactId, $contactTypeId)
    {
        $qb = $this->getValidatedDiseaseValuesQueryBuilder($siteId, null, null, $weekNumber, $monthNumber, $period, $year, $contactId, $contactTypeId);

        $qb->select('SUM(rv.value) as total');
        $qb->groupBy('fr.FK_SiteId, fr.year');

        if ($period == Constant::PERIOD_WEEKLY){
            $qb->addGroupBy('fr.weekNumber');
        }
        else if ($period == Constant::PERIOD_MONTHLY){
            $qb->addGroupBy('fr.monthNumber');
        }

        if($contactId !== null) {
            $qb->addGroupBy('contact.id');
        }

        $qb->having('total = 0');

        $result = $qb->getQuery()->getScalarResult();

        return sizeof($result);
    }

    /**
     * @param $siteIds
     * @param $disease
     * @param $diseaseValue
     * @param $weekNumber
     * @param $monthNumber
     * @param $period
     * @param $year
     * @param $contactId
     * @param $contactTypeId
     * @return QueryBuilder
     */
    public function getValidatedDiseaseValuesQueryBuilder($siteIds, $disease, $diseaseValue, $weekNumber, $monthNumber, $period, $year, $contactId, $contactTypeId)
    {
        $qb = $this->createQueryBuilder('fr')
            ->leftJoin('fr.partReports', 'pr', 'WITH', 'pr.status = :status')
            ->setParameter('status', Constant::STATUS_VALIDATED)
            ->innerJoin('pr.reports', 'r', 'WITH', 'r.isArchived = :isarchived AND r.isDeleted = :isdeleted')
            ->setParameter('isarchived', false)->setParameter('isdeleted', false)
            ->innerJoin('r.reportValues', 'rv');

        $qb
            ->where('fr.period = :period')
            ->setParameter('period', $period)
        ;

        if ($period == Constant::PERIOD_WEEKLY && $weekNumber !== null){
            if($weekNumber === DbConstant::NOT_NULL) {
                $qb->andWhere('fr.weekNumber IS NOT NULL');
            }
            else if($weekNumber === DbConstant::NULL) {
                $qb->andWhere('fr.weekNumber IS NULL');
            }
            else if(is_array($weekNumber)) {
                $qb->andWhere('fr.weekNumber IN (:weekNumber)');
                $qb->setParameter('weekNumber', $weekNumber);
            }
            else {
                $qb->andWhere('fr.weekNumber = :weekNumber');
                $qb->setParameter('weekNumber', $weekNumber);
            }
        }
        else if ($period == Constant::PERIOD_MONTHLY && $monthNumber !== null){
            if($monthNumber === DbConstant::NOT_NULL) {
                $qb->andWhere('fr.monthNumber IS NOT NULL');
            }
            else if($monthNumber === DbConstant::NULL) {
                $qb->andWhere('fr.monthNumber IS NULL');
            }
            else if(is_array($monthNumber)) {
                $qb->andWhere('fr.monthNumber IN (:monthNumber)');
                $qb->setParameter('monthNumber', $monthNumber);
            }
            else {
                $qb->andWhere('fr.monthNumber = :monthNumber');
                $qb->setParameter('monthNumber', $monthNumber);
            }
        }

        if($year !== null) {
            if($year === DbConstant::NOT_NULL) {
                $qb->andWhere('fr.year IS NOT NULL');
            }
            else if($year === DbConstant::NULL) {
                $qb->andWhere('fr.year IS NULL');
            }
            else if(is_array($year)) {
                $qb->andWhere('fr.year IN (:year)');
                $qb->setParameter('year', $year);
            }
            else {
                $qb->andWhere('fr.year = :year');
                $qb->setParameter('year', $year);
            }
        }

        if($siteIds !== null) {
            if($siteIds === DbConstant::NOT_NULL) {
                $qb->andWhere('fr.FK_SiteId IS NOT NULL');
            }
            else if($siteIds === DbConstant::NULL) {
                $qb->andWhere('fr.FK_SiteId IS NULL');
            }
            else if(is_array($siteIds)) {
                $qb->andWhere('fr.FK_SiteId IN (:fkSiteId)');
                $qb->setParameter('fkSiteId', $siteIds);
            }
            else {
                $qb->andWhere('fr.FK_SiteId = :fkSiteId');
                $qb->setParameter('fkSiteId', $siteIds);
            }
        }

        if($disease !== null) {
            if($disease === DbConstant::NOT_NULL) {
                $qb->andWhere('r.disease IS NOT NULL');
            }
            else if($disease === DbConstant::NULL) {
                $qb->andWhere('r.disease IS NULL');
            }
            else if(is_array($disease)) {
                $qb->andWhere('r.disease IN (:disease)');
                $qb->setParameter('disease', $disease);
            }
            else {
                $qb->andWhere('r.disease = :disease');
                $qb->setParameter('disease', $disease);
            }
        }

        if($diseaseValue !== null) {
            if($diseaseValue === DbConstant::NOT_NULL) {
                $qb->andWhere('rv.key IS NOT NULL');
            }
            else if($diseaseValue === DbConstant::NULL) {
                $qb->andWhere('rv.key IS NULL');
            }
            else if(is_array($diseaseValue)) {
                $qb->andWhere('rv.key IN (:diseaseValue)');
                $qb->setParameter('diseaseValue', $diseaseValue);
            }
            else {
                $qb->andWhere('rv.key = :diseaseValue');
                $qb->setParameter('diseaseValue', $diseaseValue);
            }
        }

        if($contactId !== null || $contactTypeId !== null) {
            $qb->innerJoin('fr.frontLineGroup', 'site')
                ->innerJoin('site.contacts', 'contact');

            if($contactId !== null) {
                if($contactId === DbConstant::NOT_NULL) {
                    $qb->andWhere('contact.id IS NOT NULL');
                }
                else if($contactId === DbConstant::NULL) {
                    $qb->andWhere('contact.id IS NULL');
                }
                else if (is_array($contactId)) {
                    $qb->andWhere('contact.id IN (:contactId)');
                    $qb->setParameter('contactId', $contactId);
                }
                else {
                    $qb->andWhere('contact.id = :contactId');
                    $qb->setParameter('contactId', $contactId);
                }
            }
            if($contactTypeId !== null) {
                if($contactTypeId === DbConstant::NOT_NULL) {
                    $qb->andWhere('contact.contactTypeId IS NOT NULL');
                }
                else if($contactTypeId === DbConstant::NULL) {
                    $qb->andWhere('contact.contactTypeId IS NULL');
                }
                else if (is_array($contactTypeId)) {
                    $qb->andWhere('contact.contactTypeId IN (:contactTypeId)');
                    $qb->setParameter('contactTypeId', $contactTypeId);
                }
                else {
                    $qb->andWhere('contact.contactTypeId = :contactTypeId');
                    $qb->setParameter('contactTypeId', $contactTypeId);
                }
            }
        }

        return $qb;
    }

    /**
     * Return Number of validated Disease Values during a period
     *
     * @param $siteIds
     * @param $disease
     * @param $diseaseValue
     * @param $startDate
     * @param $endDate
     * @return string
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getNumberOfValidatedDiseaseValuesFromPeriod($siteIds, $disease, $diseaseValue, $startDate, $endDate)
    {
        $qb = $this->createQueryBuilder('fr')
            ->leftJoin('fr.partReports', 'pr', 'WITH', 'pr.status = :status')
            ->setParameter('status', Constant::STATUS_VALIDATED)
            ->innerJoin('pr.reports', 'r', 'WITH', 'r.isArchived = :isarchived AND r.isDeleted = :isdeleted')
            ->setParameter('isarchived', false)->setParameter('isdeleted', false)
            ->innerJoin('r.reportValues', 'rv')
            ->select('SUM(rv.value) as value');

        $qb
            ->where('fr.startDate BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
        ;

        $qb
            ->andWhere('fr.FK_SiteId IN (:fkSiteId)')
            ->setParameter('fkSiteId', $siteIds)
        ;

        $qb
            ->andWhere('r.disease = :disease')
            ->setParameter('disease', $disease)
        ;

        $qb
            ->andWhere('rv.key = :diseaseValue')
            ->setParameter('diseaseValue', $diseaseValue)
        ;

        $result = $qb
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if ($result != null && $result['value'] != null) {
            return $result['value'];
        }

        return '-' ;
    }

    /**
     * Return number of Addressed FullReport during a period
     *
     * @param $siteId
     * @param $startDate
     * @param $endDate
     * @param $period
     * @param $status
     * @return mixed
     */
    public function getNumberOfAddressedReportPeriod($siteId, $startDate, $endDate, $period, $status)
    {
        $qb = $this->createQueryBuilder('fr')
            ->select('COUNT(fr.id)');

        $qb
            ->where('fr.startDate BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
        ;

        $qb
            ->andWhere('fr.FK_SiteId IN (:fkSiteId)')
            ->setParameter('fkSiteId', $siteId)
        ;

        $qb
            ->andWhere('fr.period = :period')
            ->setParameter('period', $period)
        ;

        if ($status != Constant::STATUS_ALL){
            $qb
                ->andWhere('fr.status = :status')
                ->setParameter('status', $status)
            ;
        }

        $qb
            ->andWhere('fr.firstValidationDate IS NOT NULL OR fr.firstRejectionDate IS NOT NULL');

        return $qb
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }


    /**
     * Create the query used by the export Data functionality
     * No join authorized as the query is used to iterate on results
     *
     * @param $display
     * @param $startDate
     * @param $endDate
     * @param $period
     * @return \Doctrine\ORM\Query
     */
    public function getExportQuery($display, $startDate, $endDate, $period)
    {
        $qb = $this->createQueryBuilder('fr');

        // Status Filters
        self::whereStatus($qb, $display);

        // Start & End Date Filters
        self::whereTime($qb, $startDate, $endDate);

        // Period Status
        self::wherePeriod($qb, $period);

        return $qb->getQuery();
    }

    /**
     * Order by date desc
     *
     * @param $siteIds
     * @param $statuses
     * @param $startDate
     * @param $endDate
     * @param $periods
     *
     * @return array
     */
    public function getFullReportData($siteIds, $statuses, $startDate, $endDate, $periods)
    {
        $qb = $this->createQueryBuilder('fr')
            ->innerJoin('fr.frontLineGroup', 's')
            ->innerJoin('fr.siteRelationShip', 'srs', Join::WITH, 'srs.level != 0') // We don't want to take report at the first level
            ->select('fr, s');

        if ($siteIds != null) {
            $qb
                ->andWhere('fr.FK_SiteId IN (:fkSiteIds)')
                ->setParameter('fkSiteIds', $siteIds)
            ;
        }

        if ($statuses != null) {
            $qb
                ->andWhere('fr.status IN (:statuses)')
                ->setParameter('statuses', $statuses)
            ;
        }

        if ($startDate != null && $endDate != null) {
            $qb
                ->andWhere('fr.startDate BETWEEN :start AND :end')
                ->setParameter('start', $startDate)
                ->setParameter('end', $endDate)
            ;
        }

        if ($periods != null) {
            $qb
                ->andWhere('fr.period IN (:periods)')
                ->setParameter('periods', $periods);
        }

        $qb->orderBy('fr.startDate', 'DESC');

        $result = $qb
            ->getQuery()
            ->getResult();

        return $result ;
    }
}
