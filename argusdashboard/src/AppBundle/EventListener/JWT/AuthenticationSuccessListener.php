<?php

namespace AppBundle\EventListener\JWT;

use AppBundle\Entity\Security\SesDashboardUser;
use AppBundle\Services\Common\InformationService;
use FOS\UserBundle\Model\UserInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;

/**
 * https://github.com/lexik/LexikJWTAuthenticationBundle/blob/master/Resources/doc/2-data-customization.md#eventsjwt_decoded---validating-data-in-the-jwt-payload
 *
 * Add public data to the token response
 *
 * Created by PhpStorm.
 * User: eotin
 * Date: 04/01/2017
 * Time: 14:10
 */
class AuthenticationSuccessListener
{
    /** @var  InformationService */
    private $informationService;

    public function __construct($informationService)
    {
        $this->informationService = $informationService;
    }

    /**
     * @param AuthenticationSuccessEvent $event
     */
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        $user = $event->getUser();

        if (!$user instanceof UserInterface) {
            return;
        }

        if (!$user instanceof SesDashboardUser) {
            return ;
        }

        $data['user_data'] = $this->informationService->getUserInformation($user);
        $data['platform_data'] = $this->informationService->getPlatformInformation();
        $data['validation_translation'] = $this->informationService->getAngularValidationDashboardTranslation($user);

        $event->setData($data);
    }
}