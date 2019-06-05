<?php
/**
 * Site Alert Recipient Service
 *
 * @author fc, inspired by eotin's SiteService
 */

namespace AppBundle\Services;

use AppBundle\Repository\RepositoryInterface;
use AppBundle\Repository\SesDashboardSiteAlertRecipientRepository;
use Symfony\Bridge\Monolog\Logger;

class SiteAlertRecipientService extends BaseRepositoryService
{
    /**
     * @var SesDashboardSiteAlertRecipientRepository
     */
    private $siteAlertRecipientRepository;

    public function __construct(Logger $logger, SesDashboardSiteAlertRecipientRepository $siteAlertRecipientRepository)
    {
        parent::__construct($logger);
        $this->siteAlertRecipientRepository = $siteAlertRecipientRepository;
    }

    public function getAll()
    {
        $siteAlertRecipients = $this->siteAlertRecipientRepository->findAll() ;
        return $siteAlertRecipients;
    }

    public function removeAll() {
        foreach ($this->getAll() as $entity) {
            $this->remove($entity);
        }
        $this->saveChanges();
    }

    public function getById($id)
    {
        $recipients = $this->siteAlertRecipientRepository->find($id);
        return $recipients ;
    }

    /**
     * Get a query object that can be used to get a list of sites
     *
     * @return \Doctrine\ORM\Query
     */
    public function getSiteAlertRecipientListQuery($siteId) {
        $qb = $this->siteAlertRecipientRepository->createQueryBuilder('sar')
            ->where('sar.FK_SiteId = :siteId')
            ->setParameter('siteId', $siteId);
        return $qb->getQuery();
    }

    /**
     * @return RepositoryInterface|SesDashboardSiteAlertRecipientRepository
     */
    public function getRepository()
    {
        return $this->siteAlertRecipientRepository;
    }

    public function setRepository(RepositoryInterface $repository)
    {
        $this->siteAlertRecipientRepository = $repository;
    }

    /**
     * @return SesDashboardSiteAlertRecipientRepository
     */
    public function getSiteAlertRecipientRepository()
    {
        return $this->siteAlertRecipientRepository;
    }

    /**
     * @param SesDashboardSiteAlertRecipientRepository $siteAlertRecipientRepository
     */
    public function setSiteAlertRecipientRepository($siteAlertRecipientRepository)
    {
        $this->siteAlertRecipientRepository = $siteAlertRecipientRepository;
    }
}