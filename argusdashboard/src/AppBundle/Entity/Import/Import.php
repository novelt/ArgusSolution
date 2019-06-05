<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 10/1/2015
 * Time: 6:46 PM
 */

namespace AppBundle\Entity\Import;

use JMS\Serializer\Annotation as JMS;
use AppBundle\Entity\Import\Contacts;

/** @JMS\XmlRoot("import") */
class Import
{
    /**
     * @JMS\Type("AppBundle\Entity\Import\Sites")
     */
    private $sites;

    /**
     * @JMS\Type("AppBundle\Entity\Import\Contacts")
     */
    private $contacts;

    /**
     * @JMS\Type("AppBundle\Entity\Import\ContactTypes")
     */
    private $contactTypes;

    /**
     * @JMS\Type("AppBundle\Entity\Import\Diseases")
     */
    private $diseases;

    /**
     * @JMS\Type("AppBundle\Entity\Import\Thresholds")
     */
    private $thresholds;

    public function getSites()
    {
        return $this->sites;
    }

    public function getContacts()
    {
        return $this->contacts;
    }

    public function getContactTypes()
    {
        return $this->contactTypes;
    }

    public function getDiseases()
    {
        return $this->diseases;
    }

    public function getThresholds()
    {
        return $this->thresholds;
    }

    public function setContactEntities($contactEntities)
    {
        $this->contacts = new Contacts();
        $this->contacts->setDashboardContacts($contactEntities);
    }

    public function setSiteEntities($siteEntities)
    {
        $this->sites = new Sites();
        $this->sites->setDashboardSites($siteEntities);
    }

    public function setDiseaseEntities($diseasesEntities)
    {
        $this->diseases = new Diseases();
        $this->diseases->setDashboardDiseases($diseasesEntities);
    }

    public function setThresholdEntities($thresholdsEntities)
    {
        $this->thresholds = new Thresholds();
        $this->thresholds->setDashboardThresholds($thresholdsEntities);
    }

    public function setContactTypeEntities($contactTypeEntities)
    {
        $this->contactTypes = new ContactTypes();
        $this->contactTypes->setDashboardContactTypes($contactTypeEntities);
    }

    public function cleanEntities(){
        $this->contacts = null;
        $this->sites = null;
        $this->diseases = null;
        $this->thresholds = null;
        $this->contactTypes = null ;
    }
}
