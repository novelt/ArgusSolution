<?php

namespace AppBundle\Services\Common;

use AppBundle\Entity\Security\SesDashboardUser;
use AppBundle\Services\BaseService;
use AppBundle\Utils\SesDashboardPermissionHelper;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 24/01/2017
 * Time: 11:34
 */
class InformationService extends BaseService
{
    private $defaultLocale;
    private $epiFirstDay;
    private $version;
    private $translator;

    public function __construct(Logger $logger, $locale, $epiFirstDay, $version, TranslatorInterface $translator)
    {
        parent::__construct($logger);

        $this->defaultLocale = $locale;
        $this->epiFirstDay = $epiFirstDay;
        $this->version = $version ;
        $this->translator = $translator;
    }

    /**
     * Return some user information
     *
     * @param SesDashboardUser $user
     * @return array
     */
    public function getUserInformation(SesDashboardUser $user)
    {
        // Add user data to the payload
        $userName = $user->getUsername();
        $firstName = $user->getFirstName();
        $lastName = $user->getLastName();
        $locale = ($user->getLocale() == null ? $this->defaultLocale : $user->getLocale());
        $isAdmin = $user->isAdmin();
        $homeSiteId = ($user->getSite() == null ? "" : $user->getSite()->getId());
        $homeSiteName = ($user->getSite() == null ? "" : $user->getSite()->getName());

        $result = [
            'userName' => $userName,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'locale' => $locale,
            'isAdmin' => $isAdmin,
            'homeSiteId' => $homeSiteId,
            'homeSiteName' => $homeSiteName,
        ];

        return $result ;
    }

    /**
     * return Platform information
     *
     * @return array
     */
    public function getPlatformInformation()
    {
        $result = ['epiFirstDay' => $this->epiFirstDay,
            'version' => $this->version];

        return $result;
    }

    /**
     * Return Angular translated file information
     *
     * @param SesDashboardUser $user
     * @return null
     */
    public function getAngularValidationDashboardTranslation(SesDashboardUser $user)
    {
        $locale = ($user->getLocale() == null ? $this->defaultLocale : $user->getLocale());
        $result = $this->getAngularTranslations($locale, 'angular_validation_dashboard');

        return $result;
    }

    /**
     * Return all translations from translation file fileName
     *
     * @param $locale
     * @param $fileName
     * @return null
     */
    private function getAngularTranslations($locale, $fileName)
    {
        $catalogue = $this->translator->getCatalogue($locale);
        $messages = $catalogue->all();
        return array_key_exists($fileName, $messages) ? $messages[$fileName] : null;
    }

    /**
     * @return mixed
     */
    public function getDefaultLocale()
    {
        return $this->defaultLocale;
    }

    /**
     * @param mixed $defaultLocale
     */
    public function setDefaultLocale($defaultLocale)
    {
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * @return mixed
     */
    public function getEpiFirstDay()
    {
        return $this->epiFirstDay;
    }

    /**
     * @param mixed $epiFirstDay
     */
    public function setEpiFirstDay($epiFirstDay)
    {
        $this->epiFirstDay = $epiFirstDay;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param mixed $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return TranslatorInterface
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * @param TranslatorInterface $translator
     */
    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }
}