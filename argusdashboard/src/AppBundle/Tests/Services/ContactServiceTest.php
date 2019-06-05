<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 07-Jun-18
 * Time: 11:59
 */

namespace AppBundle\Tests\Services;


use AppBundle\Entity\SesDashboardContact;
use AppBundle\Entity\SesDashboardSite;
use AppBundle\Services\ContactService;
use AppBundle\Services\SiteService;
use AppBundle\Tests\BaseKernelTestCase;

class ContactServiceTest extends BaseKernelTestCase
{
    /**
     * @var ContactService
     */
    private $contactService;

    /**
     * @var SiteService
     */
    private $siteService;

    /**
     * @var SesDashboardSite
     */
    private $rootSite;

    public function setUp()
    {
        parent::setUp();

        $this->siteService = $this->getService("SiteService");
        $this->contactService = $this->getService("ContactService");
        $this->rootSite = $this->siteService->getSiteRoot();
    }

    public function testRemoveImeiFromContact() {
        $contacts = [];

        $contacts[] = $contact1 = SesDashboardContact::createNewInstance('+41786542151', 'Test contact 1', $this->rootSite, 'test1@novel-t.ch', 'A note', false, '353913081519999', null, null);
        $contacts[] = $contact2 = SesDashboardContact::createNewInstance('+41786542152', 'Test contact 2', $this->rootSite, 'test2@novel-t.ch', 'A note', false, '353913081519999', null, null);
        $contacts[] = $contact3 = SesDashboardContact::createNewInstance('+41786542153', 'Test contact 3', $this->rootSite, 'test3@novel-t.ch', 'A note', false, '353913081519999', null, null);
        $contacts[] = $contact4 = SesDashboardContact::createNewInstance('+41786542154', 'Test contact 4', $this->rootSite, 'test4@novel-t.ch', 'A note', false, '353913081519777', null, null);
        $contacts[] = $contact5 = SesDashboardContact::createNewInstance('+41786542155', 'Test contact 5', $this->rootSite, 'test4@novel-t.ch', 'A note', false, '353913081519777', null, null);
        $contacts[] = $contact6 = SesDashboardContact::createNewInstance('+41786542156', 'Test contact 6', $this->rootSite, 'test4@novel-t.ch', 'A note', false, '353913081519777', null, null);

        foreach($contacts as $contact) {
            $this->rootSite->addContact($contact);
            $this->contactService->persist($contact1);
        }
        $this->contactService->saveChanges();

        foreach($contacts as $contact) {
            $this->contactService->refresh($contact);
        }

        $this->contactService->removeImeiFromContacts(['353913081519999', '353913081519777'], [$contact1->getId(), $contact4->getId()]);

        foreach($contacts as $contact) {
            $this->contactService->refresh($contact);
        }

        $this->assertEquals('353913081519999', $contact1->getImei());
        $this->assertNull($contact2->getImei());
        $this->assertNull($contact3->getImei());
        $this->assertEquals('353913081519777', $contact4->getImei());
        $this->assertNull($contact5->getImei());
        $this->assertNull($contact6->getImei());
    }
}