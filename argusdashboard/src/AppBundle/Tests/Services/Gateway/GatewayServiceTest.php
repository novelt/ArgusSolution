<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 24/02/2017
 * Time: 10:18
 */

namespace AppBundle\Tests\Services;

use AppBundle\Entity\Gateway\GatewayDevice;
use AppBundle\Entity\SesDashboardContact;
use AppBundle\Entity\SesDashboardSite;
use AppBundle\Services\ContactService;
use AppBundle\Services\Gateway\GatewayDeviceService;
use AppBundle\Services\SiteService;
use AppBundle\Tests\BaseKernelTestCase;


class GatewayQueueServiceTest extends BaseKernelTestCase
{
    /**
     * @var SiteService
     */
    private $siteService;

    /**
     * @var ContactService
     */
    private $contactService;

    /**
     * @var GatewayDeviceService
     */
    private $gatewayDeviceService;

    /**
     * @var SesDashboardSite
     */
    private $rootSite;

    /**
     * @var SesDashboardSite
     */
    private $siteLevel1;

    /**
     * @var SesDashboardSite
     */
    private $siteLevel2;

    /**
     * @var SesDashboardContact
     */
    private $contact;

    /** @var GatewayDevice */
    private $gatewayDeviceSite;

    /** @var GatewayDevice */
    private $gatewayDeviceContact;


    public function setUp()
    {
        parent::setUp();

        $this->siteService = $this->getService("SiteService");
        $this->contactService = $this->getService("ContactService");
        $this->gatewayDeviceService = $this->getService("GatewayDeviceService");

        $this->rootSite = $this->siteService->getSiteRoot();
        if($this->rootSite === null) {
            $this->rootSite = $this->siteService->createSiteInstance("sitesRoot", "sitesRoot", null);
            $this->siteService->persist($this->rootSite);
            $this->siteService->saveChanges();
        }

        //creation of sites
        $this->siteLevel1 = $this->siteService->createSiteInstance("site1_unitTest", "site1_unitTest", $this->rootSite);
        $this->siteService->persist($this->siteLevel1);
        $this->siteService->saveChanges();

        $this->siteLevel2 = $this->siteService->createSiteInstance("site2_unitTest", "site2_unitTest", $this->siteLevel1);
        $this->siteService->persist($this->siteLevel2);
        $this->siteService->saveChanges();

        $this->contact = SesDashboardContact::createNewInstance("+123456789", "contact", $this->siteLevel2);
        $this->contactService->persist($this->contact);
        $this->contactService->saveChanges();
    }

    public function testAlertPreferredGateway()
    {
        $this->alertPreferredGatewayNull();
        $this->alertPreferredGatewayContactNotActiveAnymore();
        $this->alertPreferredGatewaySiteNotActiveAnymore();
        $this->alertPreferredGatewaySite();
        $this->alertPreferredGatewayContact();
    }

    private function alertPreferredGatewayNull()
    {
        $alertPreferredGateway = $this->gatewayDeviceService->getAlertPreferredGateway($this->contact);
        $this->assertNull($alertPreferredGateway);
    }

    private function alertPreferredGatewayContactNotActiveAnymore()
    {
        $this->contact->setAlertPreferredGateway("ContactAlertPreferredGateway");
        $this->contactService->saveChanges($this->contact);

        $alertPreferredGateway = $this->gatewayDeviceService->getAlertPreferredGateway($this->contact);
        $this->assertNull($alertPreferredGateway);
    }

    private function alertPreferredGatewaySiteNotActiveAnymore()
    {
        $this->siteLevel2->setAlertPreferredGateway("Site2AlertPreferredGateway");
        $this->siteService->saveChanges();

        $alertPreferredGateway = $this->gatewayDeviceService->getAlertPreferredGateway($this->contact);
        $this->assertNull($alertPreferredGateway);
    }

    private function alertPreferredGatewaySite()
    {
        $this->gatewayDeviceSite = GatewayDevice::createNewInstance("Site2AlertPreferredGateway");
        $this->gatewayDeviceService->persist($this->gatewayDeviceSite);
        $this->gatewayDeviceService->saveChanges();

        $alertPreferredGateway = $this->gatewayDeviceService->getAlertPreferredGateway($this->contact);
        $this->assertEquals("Site2AlertPreferredGateway", $alertPreferredGateway);
    }

    private function alertPreferredGatewayContact()
    {
        $this->gatewayDeviceContact = GatewayDevice::createNewInstance("ContactAlertPreferredGateway");
        $this->gatewayDeviceService->persist($this->gatewayDeviceContact);
        $this->gatewayDeviceService->saveChanges();

        $alertPreferredGateway = $this->gatewayDeviceService->getAlertPreferredGateway($this->contact);
        $this->assertEquals("ContactAlertPreferredGateway", $alertPreferredGateway);
    }


    public function tearDown()
    {
        //remove contacts
        $this->contactService->remove($this->contact->getId());
        $this->contactService->saveChanges();

        //remove gatewayDevice
        $this->gatewayDeviceService->remove($this->gatewayDeviceSite);
        $this->gatewayDeviceService->remove($this->gatewayDeviceContact);
        $this->gatewayDeviceService->saveChanges();

        //remove sites
        $this->siteService->remove($this->siteLevel2);
        $this->siteService->remove($this->siteLevel1);
        $this->siteService->remove($this->rootSite);
        $this->siteService->saveChanges();

        parent::tearDown();
    }
}