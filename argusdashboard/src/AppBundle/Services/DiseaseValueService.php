<?php
/**
 * Disease Value Service
 *
 * @author fc, inspired by Emmanuel's DiseaseService
 */

namespace AppBundle\Services;

use AppBundle\Entity\Constant;
use AppBundle\Repository\SesDashboardDiseaseValueRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Expression;

class DiseaseValueService
{
    private $em;
    /** @var SesDashboardDiseaseValueRepository */
    private $diseaseValueRepository;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->diseaseValueRepository = $this->em->getRepository('AppBundle:SesDashboardDiseaseValue');
    }

    public function getById($id)
    {
        $diseases = $this->diseaseValueRepository->find($id);
        return $diseases ;
    }

    public function removeEntity($entity)
    {
        $this->em->remove($entity);
        $this->em->flush();
    }

    /**
     * Get a query object that can be used to get a list of disease values
     *
     * @param $diseaseId
     * @return \Doctrine\ORM\Query
     */
    public function getDiseaseValueListQuery($diseaseId) {
        $qb = $this->diseaseValueRepository->createQueryBuilder('d')
            ->where('d.FK_DiseaseId = :diseaseId')
            ->setParameter('diseaseId', $diseaseId);
        return $qb->getQuery();
    }

    /**
     * Return all diseases Values by querying on the disease name
     *
     * @param $queryString
     * @return array
     */
    public function getDiseaseValuesFromQueryString($queryString)
    {
        return $this->diseaseValueRepository->getDiseaseValuesFromQueryString($queryString);
    }

}