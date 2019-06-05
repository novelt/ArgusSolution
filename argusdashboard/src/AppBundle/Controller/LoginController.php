<?php
/**
 * User: mvirtanen
 * Date: 2/23/2016
 */
namespace AppBundle\Controller;

use Lexik\Bundle\JWTAuthenticationBundle\Exception\ExpiredTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\InvalidTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Guard\JWTTokenAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller used to manage import configuration
 *
 * @Route("/")
 *
 */
class LoginController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'))
        {
            // User is logged-in redirect to reports
            return $this->redirect($this->generateUrl('report_index'));
        }
        else
        {
            // User needs to log in.
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
    }

    /**
     * Create JWT token for the current user
     *
     * @Route("/jwtToken", name="jwtToken")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function tokenAction(Request $request)
    {
        /** @var \Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager $jwtManager */
        $jwtManager = $this->container->get('lexik_jwt_authentication.jwt_manager');
        $token = $jwtManager->create($this->getUser());

        return new Response($token);
    }

    /**
     * Specific route for sso
     *
     * @Route("/sso", name="sso")
     *
     * @param Request $request
     * @return Response
     */
    public function ssoAction (Request $request)
    {
        /** @var JWTTokenAuthenticator $jwtTokenAuthenticator */
        $jwtTokenAuthenticator = $this->container->get('lexik_jwt_authentication.jwt_token_authenticator');
        $preAuthenticationJWTUserToken = null ;

        try {
            $preAuthenticationJWTUserToken = $jwtTokenAuthenticator->getCredentials($request);
        } catch (ExpiredTokenException $ex) {
            // Token expired
        } catch (InvalidTokenException $ex) {
            // Token invalid
        }

        if ($preAuthenticationJWTUserToken == null) { // Redirect to login page
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        } else {
            // Temporary cookie to authenticate user coming from Angular Dashboard
            $cookie = new Cookie('BEARER', $preAuthenticationJWTUserToken->getCredentials(), time()+10);

            /** @var RedirectResponse $redirectResponse */
            $redirectResponse = $this->redirect($this->generateUrl('report_index'));
            $redirectResponse->headers->setCookie($cookie);
            $redirectResponse->sendHeaders()->send();
        }
    }
}