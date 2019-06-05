<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 10/02/2017
 * Time: 14:29
 */

namespace AppBundle\Controller\WebApi;

use AppBundle\Services\ContactType\DTO\ContactTypeDTOService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class ContactTypesRestController extends BaseRestController
{
    /**
     * @return ContactTypeDTOService
     */
    private function getContactTypeDTOService() {
        return $this->container->get('ContactTypeDTOService');
    }

    /**
     * @param null $sendsReports
     * @return JsonResponse
     */
    public function getContacttypesAction($sendsReports) {
        $contactTypeDTOs = $this->getContactTypeDTOService()->getContactTypeDTOs($this->getBooleanParameter($sendsReports));

        return $this->getJsonResponse(['contactTypes' => $contactTypeDTOs]);
    }
}