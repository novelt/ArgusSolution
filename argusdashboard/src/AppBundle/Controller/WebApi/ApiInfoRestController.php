<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 24/01/2017
 * Time: 11:25
 */

namespace AppBundle\Controller\WebApi;

use AppBundle\Services\Common\InformationService;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Symfony\Component\HttpFoundation\Request;

class ApiInfoRestController extends BaseRestController
{
    /**
     * Return information about user / platform / translations
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getInformationAction(Request $request)
    {
        $user = $this->getUser();
        $token = $this->container->get('security.token_storage')->getToken();

        if ($token instanceof JWTUserToken) {
            $rawToken = $token->getCredentials();
        }

        /** @var InformationService $informationService */
        $informationService = $this->container->get('informationService');

        $userData = $informationService->getUserInformation($user);
        $platformData = $informationService->getPlatformInformation();
        $validationTranslation = $informationService->getAngularValidationDashboardTranslation($user);

        return $this->getJsonResponse( ['token' => $rawToken,
            'user_data' => $userData,
            'platform_data' => $platformData,
            'validation_translation' => $validationTranslation]);
    }
}