<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 31/03/2017
 * Time: 11:16
 */

namespace AppBundle\EventListener\FOSUserBundle;

use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Listener responsible to change the redirection at the end of the password updating
 */
class PasswordUpdatingListener implements EventSubscriberInterface
{
    private $router;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::CHANGE_PASSWORD_SUCCESS => 'onPasswordUpdatingSuccess',
        );
    }

    public function onPasswordUpdatingSuccess(FormEvent $event)
    {
        // Log Out user
        $url = $this->router->generate('fos_user_security_logout');

        $event->setResponse(new RedirectResponse($url));
    }
}