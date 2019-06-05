<?php
/**
 * Disease Constraint Service
 *
 * @author fc, inspired by Emmanuel's DiseaseService
 */

namespace AppBundle\Services;

use AppBundle\Entity\Constant;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Expression;

class DiseaseConstraintService
{
    private $em;
    private $diseaseConstraintRepository;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->diseaseConstraintRepository = $this->em->getRepository('AppBundle:SesDashboardDiseaseConstraint');
    }

    public function getById($id)
    {
        $diseases = $this->diseaseConstraintRepository->find($id);
        return $diseases ;
    }

    public function removeEntity($entity)
    {
        $this->em->remove($entity);
        $this->em->flush();
    }

    /**
     * Get a query object that can be used to get a list of disease constraints
     *
     * @param $diseaseId
     * @return \Doctrine\ORM\Query
     */
    public function getDiseaseConstraintListQuery($diseaseId) {
        $qb = $this->diseaseConstraintRepository->createQueryBuilder('d')
            ->where('d.FK_DiseaseId = :diseaseId')
            ->setParameter('diseaseId', $diseaseId);
        return $qb->getQuery();
    }

}