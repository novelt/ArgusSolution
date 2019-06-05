<?php
/**
 * Contact Service
 *
 * @author François Cardinaux
 */

namespace AppBundle\Services;

use AppBundle\Repository\RepositoryInterface;
use AppBundle\Repository\SesDashboardContactTypeRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Monolog\Logger;

class ContactTypeService extends BaseRepositoryService
{
    private $dashboardContactTypeRepository;

    public function __construct(Logger $logger, SesDashboardContactTypeRepository $dashboardContactTypeRepository)
    {
        $this->logger = $logger;
        $this->dashboardContactTypeRepository = $dashboardContactTypeRepository;
    }

    /**
     * Get Contact Type by ID
     *
     * @param $contactTypeId
     * @return null|object
     */
    public function getById($contactTypeId)
    {
        $contactType = $this->dashboardContactTypeRepository->find($contactTypeId);
        return $contactType ;
    }

    /**
     * Find one Contact Type
     *
     * @param $params
     * @return mixed
     */
    public function findOneBy(array $params)
    {
        $site = $this->dashboardContactTypeRepository->findOneBy($params);
        return $site;
    }

    /**
     * @param $reference
     * @return \AppBundle\Entity\SesDashboardContactType[]
     */
    public function findByReference($reference) {
        return $this->dashboardContactTypeRepository->findByReference($reference);
    }

    /**
     * Get All Contact Types
     *
     * @return array
     */
    public function getAll()
    {
        return $this->dashboardContactTypeRepository->findAll();
    }

    /**
     * @param $sendsReports bool
     * @return \AppBundle\Entity\SesDashboardContactType[]
     */
    public function getContactTypes($sendsReports=null, $useInIndicatorsCalculation = null) {
        return $this->dashboardContactTypeRepository->getContactTypes($sendsReports, $useInIndicatorsCalculation);
    }

    /**
     * Persist contact Type
     *
     * @param $contactType
     */
    public function saveContactType($contactType)
    {
        $this->dashboardContactTypeRepository->persist($contactType);
    }

    /**
     * Remove Contact Type by ID
     *
     * @param $contactTypeId
     */
    public function remove($contactTypeId)
    {
        $entity = $this->getById($contactTypeId);
        if ($entity != null) {
            $this->dashboardContactTypeRepository->remove($entity);
            $this->dashboardContactTypeRepository->saveChanges();
        }
    }

    /**
     * Get a query object that can be used to get a list of contact types
     *
     * @return \Doctrine\ORM\Query
     */
    public function getContactTypeListQuery()
    {
        $qb = $this->dashboardContactTypeRepository->createQueryBuilder('ct');
        $qb->orderBy('ct.id');
        return $qb->getQuery();
    }

    public function getRepository()
    {
        return $this->dashboardContactTypeRepository;
    }

    public function setRepository(RepositoryInterface $repository)
    {
        $this->dashboardContactTypeRepository = $repository;
    }
}