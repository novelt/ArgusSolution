<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 17/11/2016
 * Time: 11:40
 */

namespace AppBundle\Services\IndicatorsCalculation;

use AppBundle\Entity\SesDashboardIndicatorDimDateType;
use AppBundle\Repository\RepositoryInterface;
use AppBundle\Repository\SesDashboardIndicatorDimDateTypeRepository;
use AppBundle\Services\BaseRepositoryService;
use Symfony\Bridge\Monolog\Logger;

class IndicatorDimDateTypeService extends BaseRepositoryService
{
    /**
     * @var SesDashboardIndicatorDimDateTypeRepository
     */
    private $dashboardIndicatorDimDateTypeRepository;

    public function __construct(Logger $logger, SesDashboardIndicatorDimDateTypeRepository $dashboardIndicatorDimDateTypeRepository)
    {
        parent::__construct($logger);
        $this->dashboardIndicatorDimDateTypeRepository = $dashboardIndicatorDimDateTypeRepository;
    }

    public function generateDimDateTypesList() {
        $this->upsertDimDateType(SesDashboardIndicatorDimDateType::CODE_DAILY, "Daily", "Daily period");
        $this->upsertDimDateType(SesDashboardIndicatorDimDateType::CODE_WEEKLY, "Weekly", "Weekly period");
        $this->upsertDimDateType(SesDashboardIndicatorDimDateType::CODE_WEEKLY_EPIDEMIOLOGIC, "Weekly", "Epidemiologic week period");
        $this->upsertDimDateType(SesDashboardIndicatorDimDateType::CODE_MONTHLY, "Monthly", "Monthly period");
        $this->upsertDimDateType(SesDashboardIndicatorDimDateType::CODE_YEARLY, "Yearly", "Yearly period");
        $this->upsertDimDateType(SesDashboardIndicatorDimDateType::CODE_CUSTOM, "Custom", "Custom period");

        $this->dashboardIndicatorDimDateTypeRepository->saveChanges();
    }

    /**
     * Update or insert the given dimDateType
     * @param $dimDateTypeCode
     * @param $dimDateTypeName
     * @param $dimDateTypeDesc
     */
    private function upsertDimDateType($dimDateTypeCode, $dimDateTypeName, $dimDateTypeDesc) {
        $dimDateType = $this->dashboardIndicatorDimDateTypeRepository->findByCode($dimDateTypeCode);
        if($dimDateType == null) {
            $dimDateType = new SesDashboardIndicatorDimDateType();
            $dimDateType->setCode($dimDateTypeCode);
            $dimDateType->setName($dimDateTypeName);
            $dimDateType->setDesc($dimDateTypeDesc);

            $this->dashboardIndicatorDimDateTypeRepository->persist($dimDateType);
        }
        else {
            $dimDateType->setName($dimDateTypeName);
            $dimDateType->setDesc($dimDateTypeDesc);
        }
    }

    /**
     * @param $code
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByCode($code) {
        return $this->dashboardIndicatorDimDateTypeRepository->findByCode($code);
    }

    /**
     * @param $code
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findIdByCode($code) {
        return $this->dashboardIndicatorDimDateTypeRepository->findIdByCode($code);
    }

    /**
     * @param $codes
     * @return \AppBundle\Entity\SesDashboardIndicatorDimDateType[]
     */
    public function findByCodes($codes) {
        return $this->dashboardIndicatorDimDateTypeRepository->findByCodes($codes);
    }

    /**
     * @param $codes
     * @return \int[]
     */
    public function findIdsByCodes($codes) {
        return $this->dashboardIndicatorDimDateTypeRepository->findIdsByCodes($codes);
    }

    /**
     * @return SesDashboardIndicatorDimDateTypeRepository
     */
    public function getDashboardIndicatorDimDateTypeRepository()
    {
        return $this->dashboardIndicatorDimDateTypeRepository;
    }

    /**
     * @param SesDashboardIndicatorDimDateTypeRepository $dashboardIndicatorDimDateTypeRepository
     */
    public function setDashboardIndicatorDimDateTypeRepository($dashboardIndicatorDimDateTypeRepository)
    {
        $this->dashboardIndicatorDimDateTypeRepository = $dashboardIndicatorDimDateTypeRepository;
    }

    public function getRepository()
    {
        return $this->dashboardIndicatorDimDateTypeRepository;
    }

    public function setRepository(RepositoryInterface $repository)
    {
        $this->dashboardIndicatorDimDateTypeRepository = $repository;
    }
}