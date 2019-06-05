<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 11/26/2015
 * Time: 4:35 PM
 */

namespace AppBundle\Services;

use AppBundle\Entity\Constant;
use AppBundle\Entity\SesDashboardDisease;
use AppBundle\Entity\SesDashboardDiseaseValue;
use AppBundle\Repository\RepositoryInterface;
use AppBundle\Repository\SesDashboardDiseaseRepository;
use AppBundle\Repository\SesDashboardDiseaseValueRepository;
use Doctrine\ORM\EntityManager;

class DiseaseService extends BaseRepositoryService
{
    private $em;

    /** @var SesDashboardDiseaseRepository  */
    private $diseaseRepository;

    /** @var SesDashboardDiseaseValueRepository  */
    private $diseaseValueRepository;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->diseaseRepository = $this->em->getRepository('AppBundle:SesDashboardDisease');
        $this->diseaseValueRepository = $this->em->getRepository('AppBundle:SesDashboardDiseaseValue');
    }

    public function getAll()
    {
        $diseases = $this->diseaseRepository->findAll() ;
        return $diseases;
    }

    public function removeAll() {
        foreach ($this->getAll() as $entity) {
            $this->em->remove($entity);
        }
        $this->em->flush();
    }

    public function getById($id)
    {
        $diseases = $this->diseaseRepository->find($id);
        return $diseases ;
    }

    public function findOneBy($params)
    {
        $site = $this->diseaseRepository->findOneBy($params);
        return $site;
    }

    public function removeEntity($entity)
    {
        $this->em->remove($entity);
        $this->em->flush();
    }

    /**
     * @param $period
     * @param bool $includeAlerts
     * @param null $reportDataSourceId
     * @param null $reportDataSourceCode
     * @return SesDashboardDisease[]
     */
    public function getDiseases($period, $includeAlerts = true, $reportDataSourceId = null, $reportDataSourceCode = null)
    {
        $diseases = $this->diseaseRepository->findDiseases($period, $includeAlerts, $reportDataSourceId, $reportDataSourceCode);
        return $diseases;
    }

    public function getDiseasesPeriodIds($period, $diseasesIds)
    {
        $diseases = $this->diseaseRepository->findDiseasesPeriodIds($period, $diseasesIds);
        return $diseases;
    }

    /**
     * @param $diseaseReference
     * @param $valueReference
     * @return SesDashboardDiseaseValue
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findDiseaseValue($diseaseReference, $valueReference)
    {
        $diseaseValue = $this->diseaseValueRepository->findDiseaseValue($diseaseReference, $valueReference);
        return $diseaseValue;
    }

    /**
     * @param $diseaseCode
     * @return SesDashboardDisease
     */
    public function findDiseaseByCode($diseaseCode) {
        return $this->diseaseRepository->findDiseaseByCode($diseaseCode);
    }

    /**
     * Return all diseases having values for $period
     *
     * @param $period
     * @param bool $includeAlerts
     * @return SesDashboardDisease[]
     */
    public function getDiseasesPerPeriod($period, $includeAlerts = true)
    {
        $diseases = $this->diseaseRepository->getDiseasesPerPeriod($period, $includeAlerts);
        return $diseases;
    }

    /**
     * Get Array of Disease rows for CSV Export
     *
     * @return array
     */
    public function getDiseaseForCsvExport()
    {
        $result = array();
        $diseases = self::getAll();

        $result[] = SesDashboardDisease::getHeaderCsvRow();

        foreach($diseases as $disease){
            $result = array_merge($result, $disease->getCsvRow());
        }

        return $result ;
    }

    /**
     * Get a query object that can be used to get a list of diseases
     *
     * @return \Doctrine\ORM\Query
     */
    public function getDiseaseListQuery() {
        $qb = $this->diseaseRepository->createQueryBuilder('d');
        return $qb->getQuery();
    }

    public function getRepository()
    {
        return $this->diseaseRepository;
    }

    public function setRepository(RepositoryInterface $repository)
    {
        $this->diseaseRepository = $repository;
    }
}