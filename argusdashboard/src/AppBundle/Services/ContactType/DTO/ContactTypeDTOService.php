<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 14/02/2017
 * Time: 15:58
 */

namespace AppBundle\Services\ContactType\DTO;


use AppBundle\Services\BaseService;
use AppBundle\Services\ContactTypeService;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Translation\TranslatorInterface;

class ContactTypeDTOService extends BaseService
{
    const I18N_DOMAIN = "business";

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var ContactTypeService
     */
    private $contactTypeService;

    public function __construct(Logger $logger, TranslatorInterface $translator, ContactTypeService $contactTypeService)
    {
        parent::__construct($logger);
        $this->contactTypeService = $contactTypeService;
        $this->translator = $translator;
    }

    /**
     * @param $sendReports
     * @return array
     */
    public function getContactTypeDTOs($sendReports=null) {
        $contactTypes = $this->contactTypeService->getContactTypes($sendReports);

        $contactTypesDTO = [];

        foreach($contactTypes as $contactType) {
            $contactTypeDTO = new ContactTypeDTO();
            $contactTypesDTO[] = $contactTypeDTO;
            $contactTypeDTO->setId($contactType->getId());
            $contactTypeDTO->setName($this->translator->trans('contactType.long.'.$contactType->getName(), array(), self::I18N_DOMAIN));
            $contactTypeDTO->setShortName($this->translator->trans('contactType.short.'.$contactType->getName(), array(), self::I18N_DOMAIN));
            $contactTypeDTO->setReference($contactType->getReference());
        }

        return $contactTypesDTO;
    }

    /**
     * @return ContactTypeService
     */
    public function getContactTypeService()
    {
        return $this->contactTypeService;
    }

    /**
     * @param ContactTypeService $contactTypeService
     */
    public function setContactTypeService($contactTypeService)
    {
        $this->contactTypeService = $contactTypeService;
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