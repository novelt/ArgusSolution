<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 7/22/2015
 * Time: 11:29 AM
 */

namespace AppBundle\Repository;

use AppBundle\Entity\Constant;
use Doctrine\ORM\EntityRepository;

class SesPartReportRepository extends EntityRepository
{
    public function getPartReportFromFullReportId($fullReportId)
    {
        $qb = $this->createQueryBuilder('pr')
            ->join('pr.reports', 'r')
            ->addSelect('r');

        $qb
            ->where('pr.FK_FullReportId = :fullReportId')
            ->setParameter('fullReportId', $fullReportId)
        ;

        return $qb
            ->getQuery()
            ->getResult()
            ;
    }

    public function getValidatedPartReportFromFullReportId($fullReportId)
    {
        $qb = $this->createQueryBuilder('pr')
            ->join('pr.reports', 'r')
            ->addSelect('r');

        $qb
            ->where('pr.FK_FullReportId = :fullReportId')
            ->setParameter('fullReportId', $fullReportId)
        ;

        $qb
            ->andWhere('pr.status = :status')
            ->setParameter('status', Constant::STATUS_VALIDATED)
        ;

        $qb
            ->andWhere('r.isArchived = :archived')
            ->setParameter('archived', false)
        ;

        $qb
            ->andWhere('r.isDeleted = :deleted')
            ->setParameter('deleted', false)
        ;

        $qb
            ->addOrderBy('r.disease','ASC')
        ;

        return $qb
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    /**
     * Return Part Report from phoneNumber Android Report Id and full Report Id
     *
     * @param $phoneNumber
     * @param $androidId
     * @param $fullReportId
     *
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getPartReportFromPhoneNumberAndAndroidId($phoneNumber, $androidId, $fullReportId)
    {
        $qb = $this->createQueryBuilder('pr')
            ->join('pr.reports', 'r')
            ->addSelect('r');

        $qb
            ->where('pr.contactPhoneNumber = :phoneNumber')
            ->setParameter('phoneNumber', $phoneNumber)
        ;

        $qb
            ->andWhere('pr.androidReportId = :androidId')
            ->setParameter('androidId', $androidId)
        ;

        $qb
            ->andWhere('pr.FK_FullReportId = :fullReportId')
            ->setParameter('fullReportId', $fullReportId)
        ;

        $qb
            ->addOrderBy('r.disease','ASC')
        ;

        return $qb
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    /**
     * @param $reportIds
     * @param $statuses
     *
     * @return array
     */
    public function getPartReportData($reportIds, $statuses)
    {
        $qb = $this->createQueryBuilder('pr')
            ->innerJoin('pr.reports', 'r')
            ->innerJoin('r.reportValues', 'rv')
            ->select('pr, r, rv');

        if ($reportIds != null) {
            $qb
                ->andWhere('pr.FK_FullReportId IN (:reportIds)')
                ->setParameter('reportIds', $reportIds)
            ;
        }

        if ($statuses != null) {
            $qb
                ->andWhere('pr.status IN (:statuses)')
                ->setParameter('statuses', $statuses)
            ;
        }

        $result = $qb
            ->getQuery()
            ->getResult();

        return $result ;
    }
}