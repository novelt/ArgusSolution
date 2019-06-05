<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 17/11/2016
 * Time: 15:10
 */

namespace AppBundle\Tests\Services;

use AppBundle\Entity\SesDashboardIndicatorDimDateType;
use AppBundle\Entity\SesDashboardSite;
use AppBundle\Services\LocaleService;
use AppBundle\Services\SiteService;
use AppBundle\Tests\BaseKernelTestCase;

class SiteServiceTest extends BaseKernelTestCase
{
    /**
     * @var SiteService
     */
    private $siteService;

    /**
     * @var LocaleService
     */
    private $localeService;

    /**
     * @var bool
     */
    private $rootSiteCreated;

    /**
     * @var SesDashboardSite
     */
    private $rootSite;

    /**
     * @var str
     */
    private $originalLocaleRootSite;

    /**
     * @var SesDashboardSite
     */
    private $siteLevel1;

    /**
     * @var SesDashboardSite
     */
    private $siteLevel2;

    public function setUp()
    {
        parent::setUp();

        $this->initializeDatabase();

        $this->siteService = $this->getService("SiteService");
        $this->localeService = $this->getService("LocaleService");

        $this->rootSite = $this->siteService->getSiteRoot();
        if($this->rootSite === null) {
            $this->rootSite = $this->createNewSite("sitesRoot", "sitesRoot", 0, null);
            $this->siteService->persist($this->rootSite);
            $this->siteService->saveChanges();

            $this->rootSiteCreated = true;
        }
        else {
            $this->originalLocaleRootSite = $this->rootSite->getLocale();
            $this->rootSite->setLocale(null);
        }

        $this->siteService->saveChanges();

        //creation of sites
        $this->siteLevel1 = $this->createNewSite("site1_unitTest", "site1_unitTest", 1, $this->rootSite);
        $this->siteService->persist($this->siteLevel1);
        $this->siteService->saveChanges();

        $this->siteLevel2 = $this->createNewSite("site2_unitTest", "site2_unitTest", 2, $this->siteLevel1);
        $this->siteService->persist($this->siteLevel2);
        $this->siteService->saveChanges();
    }

    public function testGetSiteLocale() {
        $this->assertEquals($this->siteService->getSiteLocale($this->rootSite), $this->localeService->getDefaultLocale());
        $this->assertEquals($this->siteService->getSiteLocale($this->siteLevel1), $this->localeService->getDefaultLocale());
        $this->assertEquals($this->siteService->getSiteLocale($this->siteLevel2), $this->localeService->getDefaultLocale());

        $this->rootSite->setLocale("jp");
        $this->siteService->saveChanges();

        $this->assertEquals($this->siteService->getSiteLocale($this->rootSite), $this->rootSite->getLocale());
        $this->assertEquals($this->siteService->getSiteLocale($this->siteLevel1), $this->rootSite->getLocale());
        $this->assertEquals($this->siteService->getSiteLocale($this->siteLevel2), $this->rootSite->getLocale());

        $this->siteLevel1->setLocale("es");
        $this->siteService->saveChanges();

        $this->assertEquals($this->siteService->getSiteLocale($this->rootSite), $this->rootSite->getLocale());
        $this->assertEquals($this->siteService->getSiteLocale($this->siteLevel1), $this->siteLevel1->getLocale());
        $this->assertEquals($this->siteService->getSiteLocale($this->siteLevel2), $this->siteLevel1->getLocale());

        $this->siteLevel2->setLocale("xd");
        $this->siteService->saveChanges();

        $this->assertEquals($this->siteService->getSiteLocale($this->rootSite), $this->rootSite->getLocale());
        $this->assertEquals($this->siteService->getSiteLocale($this->siteLevel1), $this->siteLevel1->getLocale());
        $this->assertEquals($this->siteService->getSiteLocale($this->siteLevel2), $this->siteLevel2->getLocale());

        $this->siteLevel1->setLocale(null);
        $this->siteService->saveChanges();

        $this->assertEquals($this->siteService->getSiteLocale($this->rootSite), $this->rootSite->getLocale());
        $this->assertEquals($this->siteService->getSiteLocale($this->siteLevel1), $this->rootSite->getLocale());
        $this->assertEquals($this->siteService->getSiteLocale($this->siteLevel2), $this->siteLevel2->getLocale());

        $this->rootSite->setLocale(null);
        $this->siteService->saveChanges();

        $this->assertEquals($this->siteService->getSiteLocale($this->rootSite), $this->localeService->getDefaultLocale());
        $this->assertEquals($this->siteService->getSiteLocale($this->siteLevel1), $this->localeService->getDefaultLocale());
        $this->assertEquals($this->siteService->getSiteLocale($this->siteLevel2), $this->siteLevel2->getLocale());
    }

    public function testGetSiteTimeZone() {
        // tests
        $this->assertNull($this->siteService->getSiteTimeZone($this->rootSite));
        $this->assertNull($this->siteService->getSiteTimeZone($this->siteLevel1));
        $this->assertNull($this->siteService->getSiteTimeZone($this->siteLevel2));

        $this->rootSite->setTimezone("tzRoot");
        $this->siteService->saveChanges();

        $this->assertEquals($this->siteService->getSiteTimeZone($this->rootSite), $this->rootSite->getTimezone());
        $this->assertEquals($this->siteService->getSiteTimeZone($this->siteLevel1), $this->rootSite->getTimezone());
        $this->assertEquals($this->siteService->getSiteTimeZone($this->siteLevel2), $this->rootSite->getTimezone());

        $this->siteLevel1->setTimezone("tzSite1");
        $this->siteService->saveChanges();

        $this->assertEquals($this->siteService->getSiteTimeZone($this->rootSite), $this->rootSite->getTimezone());
        $this->assertEquals($this->siteService->getSiteTimeZone($this->siteLevel1), $this->siteLevel1->getTimezone());
        $this->assertEquals($this->siteService->getSiteTimeZone($this->siteLevel2), $this->siteLevel1->getTimezone());

        $this->siteLevel2->setTimezone("tzSite2");
        $this->siteService->saveChanges();

        $this->assertEquals($this->siteService->getSiteTimeZone($this->rootSite), $this->rootSite->getTimezone());
        $this->assertEquals($this->siteService->getSiteTimeZone($this->siteLevel1), $this->siteLevel1->getTimezone());
        $this->assertEquals($this->siteService->getSiteTimeZone($this->siteLevel2), $this->siteLevel2->getTimezone());

        $this->siteLevel1->setTimezone(null);
        $this->siteService->saveChanges();

        $this->assertEquals($this->siteService->getSiteTimeZone($this->rootSite), $this->rootSite->getTimezone());
        $this->assertEquals($this->siteService->getSiteTimeZone($this->siteLevel1), $this->rootSite->getTimezone());
        $this->assertEquals($this->siteService->getSiteTimeZone($this->siteLevel2), $this->siteLevel2->getTimezone());

        $this->rootSite->setTimezone(null);
        $this->siteService->saveChanges();

        $this->assertNull($this->siteService->getSiteTimeZone($this->rootSite));
        $this->assertNull($this->siteService->getSiteTimeZone($this->siteLevel1));
        $this->assertEquals($this->siteService->getSiteTimeZone($this->siteLevel2), $this->siteLevel2->getTimezone());

        $this->siteService->saveChanges();
    }

    public function testGetChildrenSiteIds() {
        /* @var $siteService SiteService */
        $siteService = $this->getService('SiteService');

        $rootSite = $siteService->getSiteRoot();

        if($rootSite !== null) {
            $childrenSiteIds = $this->siteService->getChildrenSiteIds($rootSite->getId(), false, true, null, true, null, null, null);
            $sites = $siteService->findAll();

            //it's a basic test, but it's a test: by giving the root site, we must receive the whole sites in the result
            $this->assertEquals(sizeof($sites), sizeof($childrenSiteIds));
        }
    }

    public function testGetParentSiteIds() {
        $parentSiteIds = $this->siteService->getParentSiteIds($this->siteLevel2->getId(), true, null, true, null, null, null);
        $sites = $this->siteService->findAll();

        //it's a basic test, but it's a test: by giving a leaf site, we must receive the whole parents sites (--> all sites in the db) in the result
        $this->assertEquals(sizeof($sites), sizeof($parentSiteIds));
    }

    public function testGetParentAndChildrenSiteIds() {
        $parentAndChildrenSiteIds = $this->siteService->getChildrenAndParentSiteIds($this->siteLevel1->getId(), true, true, null, null, null);
        $sites = $this->siteService->findAll();

        //it's a basic test, but it's a test: by giving a leaf site, we must receive the whole parents & children sites (--> all sites in the db) in the result
        $this->assertEquals(sizeof($sites), sizeof($parentAndChildrenSiteIds));
    }

    /**
     * @param $ref
     * @param $name
     * @param $level
     * @param $parent
     * @return SesDashboardSite
     */
    private function createNewSite($ref, $name, $level, SesDashboardSite $parent=null) {
        //TODO use the siteService to create a new site when it will host the creation logic
        $site = new SesDashboardSite();
        $site->setReference($ref);
        $site->setWeeklyReminderOverrunMinutes(0);
        $site->setMonthlyReminderOverrunMinutes(0);
        $site->setWeeklyTimelinessMinutes(0);
        $site->setMonthlyTimelinessMinutes(0);
        $site->setLevel($level);

        $parentSiteId = null;

        if($parent !== null) {
            $parentSiteId = $parent->getId();
        }

        $this->siteService->createNewSite($site, $name, $parentSiteId, null, null, null, null);

        return $site;
    }

    /**
     * @return SiteService
     */
    public function getSiteService()
    {
        return $this->siteService;
    }

    /**
     * @param SiteService $siteService
     */
    public function setSiteService($siteService)
    {
        $this->siteService = $siteService;
    }
}