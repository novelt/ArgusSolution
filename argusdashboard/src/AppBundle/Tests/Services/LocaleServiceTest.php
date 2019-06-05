<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 24/02/2017
 * Time: 10:18
 */

namespace AppBundle\Tests\Services;


use AppBundle\Services\LocaleService;
use AppBundle\Tests\BaseKernelTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Translation\TranslatorInterface;


class LocaleServiceTest extends BaseKernelTestCase
{
    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var string
     */
    private $tmpDir;

    public function setUp()
    {
        parent::setUp();
        $this->rootDir = $this->getParameter('kernel.root_dir');
        $this->tmpDir = $this->rootDir.'/'.'tmp_'.mt_rand();

        $fs = new Filesystem();
        if(!$fs->exists($this->tmpDir)) {
            $fs->mkdir($this->tmpDir);
        }

        $fs->dumpFile($this->tmpDir.'/'.'test.en.yml', 'test: test');
        $fs->dumpFile($this->tmpDir.'/'.'test.fr.yml', 'test: test');
        $fs->dumpFile($this->tmpDir.'/'.'test.ab.yml.test.en.yml', 'test: test');
        $fs->dumpFile($this->tmpDir.'/'.'test.ru.yml', 'test: test');
        $fs->dumpFile($this->tmpDir.'/'.'test.abc.yml', 'test: test');
    }

    public function testGetAvailableLocales() {
        /* @var $localeService LocaleService */
        $localeService = $this->getService('LocaleService');

        $localeService->setTranslationFoldersPaths([$this->tmpDir]);

        $locales = $localeService->getAvailableLocales();

        $this->assertTrue(sizeof($locales) == 3);
        $this->assertTrue(in_array('en', $locales));
        $this->assertTrue(in_array('fr', $locales));
        $this->assertTrue(in_array('ru', $locales));
    }

    public function testDefaultLocaleIsNotNull() {
        /* @var $localeService LocaleService */
        $localeService = $this->getService('LocaleService');

        $this->assertNotNull($localeService->getDefaultLocale());
    }

    public function tearDown()
    {
        parent::tearDown();

        $fs = new Filesystem();
        if($fs->exists($this->tmpDir)) {
            $fs->remove($this->tmpDir);
        }
    }
}