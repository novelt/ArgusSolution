<?php

/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 29/05/2017
 * Time: 16:16
 */

namespace AppBundle\Services\Contact\DTO;

use AppBundle\Entity\SesDashboardContact;
use AppBundle\Entity\SesDashboardContactType;
use AppBundle\Entity\SesDashboardIndicatorDimDateType;
use AppBundle\Services\BaseService;
use AppBundle\Services\ContactService;
use AppBundle\Services\ContactTypeService;
use AppBundle\Services\SiteService;
use Symfony\Bridge\Monolog\Logger;

class ContactDTOService extends BaseService
{
    /**
     * @var ContactService
     */
    private $contactService;

    /**
     * @var ContactTypeService
     */
    private $contactTypeService;

    /**
     * @var SiteService
     */
    private $siteService;

    public function __construct(Logger $logger, ContactService $contactService, ContactTypeService $contactTypeService, SiteService $siteService)
    {
        parent::__construct($logger);
        $this->contactService = $contactService;
        $this->contactTypeService = $contactTypeService;
        $this->siteService = $siteService;
    }

    /**
     * @param null $siteId
     * @param null $sendReports
     * @return array
     */
    public function getContactLocationDTOs($siteId=null, $sendReports=null) {
        // Get Children Sites Ids
        $siteIds = $this->siteService->getChildrenSiteIds([$siteId], false, false, null, null, SesDashboardIndicatorDimDateType::CODE_DAILY, null, null);

        //Get contact Type Ids
        $contactTypes = $this->contactTypeService->getContactTypes($sendReports);
        $contactTypesIds = [];
        /** @var SesDashboardContactType $contactType */
        foreach ($contactTypes as $contactType) {
            $contactTypesIds[] = $contactType->getId();
        }

        $contacts = $this->contactService->findContact(null, $contactTypesIds, $siteIds, null, null);
        $contactsDTO = [];

        /** @var SesDashboardContact $contact */
        foreach($contacts as $contact) {
            $contactDTO = new ContactDTO();
            $contactDTO->setId($contact->getId());
            $contactDTO->setName($contact->getName());
            $contactDTO->setPhoneNumber($contact->getPhoneNumber());
            $contactDTO->setContactTypeId($contact->getContactTypeId());
            $contactDTO->setEmail($contact->getEmail());
            $contactDTO->setNotes($contact->getNote());
            $contactDTO->setImei1($contact->getImei());
            $contactDTO->setImei2($contact->getImei2());
            $contactDTO->setSiteId($contact->getSiteId());

            $latitude = $contact->getSite() != null ? $contact->getSite()->getLatitude() : null;
            $longitude = $contact->getSite() != null ? $contact->getSite()->getLongitude() : null;

            $contactDTO->setLatitude(empty($latitude) ? null : $latitude);
            $contactDTO->setLongitude(empty($longitude) ? null : $longitude);

            $contactsDTO[] = $contactDTO;
        }

        return $contactsDTO;
    }
}