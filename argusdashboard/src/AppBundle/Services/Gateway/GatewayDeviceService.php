<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 17/06/2016
 * Time: 14:45
 */

namespace AppBundle\Services\Gateway;

use AppBundle\Entity\Gateway\GatewayDevice;
use AppBundle\Entity\SesDashboardContact;
use AppBundle\Repository\Gateway\GatewayDeviceRepository;
use AppBundle\Repository\RepositoryInterface;
use AppBundle\Services\BaseRepositoryService;
use AppBundle\Services\ContactService;
use AppBundle\Services\SiteService;

use Symfony\Bridge\Monolog\Logger;

/**
 * Class GatewayDeviceService
 * @package AppBundle\Services\Gateway
 */
class GatewayDeviceService extends BaseRepositoryService
{
    /** @var GatewayDeviceRepository */
    private $gatewayDeviceRepository;

    /** @var SiteService */
    private $siteService;

    /** @var ContactService */
    private $contactService;

    /**
     * GatewayDeviceService constructor.
     * @param Logger $logger
     * @param GatewayDeviceRepository $gatewayDeviceRepository
     * @param SiteService $siteService
     * @param ContactService $contactService
     */
    public function __construct(Logger $logger, GatewayDeviceRepository $gatewayDeviceRepository, SiteService $siteService, ContactService $contactService)
    {
        parent::__construct($logger);

        $this->gatewayDeviceRepository = $gatewayDeviceRepository;
        $this->siteService = $siteService;
        $this->contactService = $contactService;
    }

    /**
     * Return All Gateway Devices
     *
     * @return array
     */
    public function getAllGatewayDevices()
    {
        return $this->gatewayDeviceRepository->findAll();
    }


    /**
     * Return the Alert Preferred Gateway
     *
     * @param SesDashboardContact $contact
     * @return mixed|null
     */
    public function getAlertPreferredGateway(SesDashboardContact $contact)
    {
        if ($contact == null) {
            return null ;
        }

        // Check if contact has an alert preferred gateway configured
        $contactAlertPreferredGatewayNumber = $contact->getAlertPreferredGateway();

        if ($contactAlertPreferredGatewayNumber != null) {
            // Check if this preferredGateway is still in use
            /** @var GatewayDevice $gatewayDevice */
            $gatewayDevice = $this->gatewayDeviceRepository->find($contactAlertPreferredGatewayNumber);

            if ($gatewayDevice != null) {
                return $gatewayDevice->getGatewayId();
            }
        }

        // Check if a site (recursively up) has an alert preferred gateway configured
        $contactSite = $contact->getSite();
        $contactAlertPreferredGatewayNumber = $this->siteService->getSiteAlertPreferredGateway($contactSite);

        if ($contactAlertPreferredGatewayNumber != null) {
            // Check if this preferredGateway is still in use
            /** @var GatewayDevice $gatewayDevice */
            $gatewayDevice = $this->gatewayDeviceRepository->find($contactAlertPreferredGatewayNumber);

            if ($gatewayDevice != null) {
                return $gatewayDevice->getGatewayId();
            }
        }

        return null;
    }

    public function findGateway($gatewayNumber)
    {
        return $this->gatewayDeviceRepository->find($gatewayNumber);
    }

    public function getRepository()
    {
        return $this->gatewayDeviceRepository;
    }

    public function setRepository(RepositoryInterface $repository)
    {
        $this->gatewayDeviceRepository = $repository;
    }
}