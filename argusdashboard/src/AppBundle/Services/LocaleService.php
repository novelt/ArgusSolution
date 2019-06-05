<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 24/02/2017
 * Time: 10:07
 */

namespace AppBundle\Services;
use Symfony\Bridge\Monolog\Logger;

class LocaleService extends BaseService
{
    /**
     * @var string[]
     */
    private $translationFoldersPaths;

    /**
     * @var string
     */
    private $defaultLocale;

    public function __construct(Logger $logger, $translationFoldersPaths, $defaultLocale)
    {
        parent::__construct($logger);
        $this->translationFoldersPaths = $translationFoldersPaths;
        $this->defaultLocale = $defaultLocale;
    }

    public function getAvailableLocales() {
        $locales = [];

        $regex = '/\.[a-zA-Z]{2}\.yml/'; //for example: ".en.yml"

        foreach($this->translationFoldersPaths as $folderPath) {
            //find ever files with the yml extension
            $files = glob($folderPath . "\\*.{yml}", GLOB_BRACE);

            foreach($files as $file) {
                $matches = [];
                if(preg_match_all($regex, $file, $matches)) {
                    //in case we have many matches, we take the last one
                    $lastMatch = array_values(array_slice($matches[0], -1))[0];

                    //the locale is the 2nd and 3rd character
                    $locales[] = substr($lastMatch, 1, 2);
                }
            }
        }

        $availableLocales = array_unique($locales);

        return $availableLocales;
    }

    /**
     * @return \string[]
     */
    public function getTranslationFoldersPaths()
    {
        return $this->translationFoldersPaths;
    }

    /**
     * @param \string[] $translationFoldersPaths
     */
    public function setTranslationFoldersPaths($translationFoldersPaths)
    {
        $this->translationFoldersPaths = $translationFoldersPaths;
    }

    /**
     * @return string
     */
    public function getDefaultLocale()
    {
        return $this->defaultLocale;
    }

    /**
     * @param string $defaultLocale
     */
    public function setDefaultLocale($defaultLocale)
    {
        $this->defaultLocale = $defaultLocale;
    }
}