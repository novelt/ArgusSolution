<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 4/14/2016
 * Time: 3:01 PM
 */

namespace AppBundle\Repository;

use AppBundle\Entity\SesDashboardDiseaseValue;
use Doctrine\ORM\EntityRepository;

class SesDashboardDiseaseValueRepository extends EntityRepository
{
    /**
     * @param $diseaseReference
     * @param $valueReference
     * @return SesDashboardDiseaseValue
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findDiseaseValue($diseaseReference, $valueReference)
    {
        $qb = $this->createQueryBuilder('dv')
            ->join('dv.parentDisease', 'd');

        $qb
            ->where('d.disease = :disease')
            ->setParameter('disease', $diseaseReference)
        ;

        $qb
            ->andWhere('dv.value = :value')
            ->setParameter('value', $valueReference)
        ;

        return $qb
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    /**
     * Return all diseases Values by querying on the disease name
     *
     * @param $queryString
     * @return array
     */
    public function getDiseaseValuesFromQueryString($queryString)
    {
        $qb = $this->createQueryBuilder("dv")
            ->join('dv.parentDisease', 'd')
            ->select('dv')
            ->addSelect('d')
            ->where("d.name LIKE :query_string")
            ->setParameter(":query_string", "%" . $queryString . "%")
            ->getQuery();

        return $qb->getResult();
    }
}