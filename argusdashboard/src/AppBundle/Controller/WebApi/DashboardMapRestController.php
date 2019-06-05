<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 29/05/2017
 * Time: 15:18
 */

namespace AppBundle\Controller\WebApi;

use AppBundle\Services\Contact\DTO\ContactDTOService;
use Symfony\Component\HttpFoundation\JsonResponse;

class DashboardMapRestController extends BaseRestController
{
    /**
     * @return ContactDTOService
     */
    private function getContactDTOService() {
        return $this->container->get('ContactDTOService');
    }

    /**
     * Return list of contact with location
     *
     * @param $siteId
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getContactLocationsAction($siteId)
    {
        try {
            $contactDTOs = $this->getContactDTOService()->getContactLocationDTOs($this->getIntegerParameter($siteId), true);

            return ['contacts' => $contactDTOs];
        }
        catch(\Exception $e) {
            $this->getLogger()->addError(sprintf("%s", $e));
            return new JsonResponse(null, 500);
        }
    }
}