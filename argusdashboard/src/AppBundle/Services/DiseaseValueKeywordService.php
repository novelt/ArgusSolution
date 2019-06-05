<?php
/**
 * Disease Value Keyword Service
 *
 * @author fc, inspired by Emmanuel's DiseaseService
 */

namespace AppBundle\Services;

use AppBundle\Entity\Constant;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Expression;

class DiseaseValueKeywordService
{
    private $em;
    private $diseaseValueKeywordRepository;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->diseaseValueKeywordRepository = $this->em->getRepository('AppBundle:SesDashboardDiseaseValueKeyword');
    }

    public function getById($id)
    {
        $diseases = $this->diseaseValueKeywordRepository->find($id);
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
     * @param $valueId
     * @return \Doctrine\ORM\Query
     */
    public function getDiseaseValueKeywordListQuery($valueId) {
        $qb = $this->diseaseValueKeywordRepository->createQueryBuilder('k')
            ->where('k.FK_ValueId = :valueId')
            ->setParameter('valueId', $valueId);
        return $qb->getQuery();
    }

}