<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 11/9/2016
 * Time: 2:47 PM
 */

namespace AppBundle\Controller\Configuration;


use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class LanguageController extends BaseController
{
    /**
     * Change user locale settings and redirect to last page
     *
     * @param Request $request
     * @param $locale
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function changeLanguageAction(Request $request, $locale)
    {
         $currentLocale = $request->getLocale() ;

        if($locale != null) {
            $user = $this->getUser();
            if ($user != null) {
                $user->setLocale($locale);
                $this->getUserManager()->updateUser($user);
            }

            $this->get('session')->set('_locale', $locale);
            $request->setLocale($locale);
        }

        $referer = $request->headers->get('referer');
        $lastPath = substr($referer, strpos($referer, $request->getBaseUrl()));
        $lastPath = str_replace($request->getBaseUrl(), '', $lastPath);

        // get last route
        $matcher = $this->get('router')->getMatcher();

        // get path with new locale
        $newPath = str_replace('/'.$currentLocale.'/','/'. $locale .'/' , $lastPath);

        $parameters = $matcher->match($newPath);

        // set new locale
        $parameters['_locale'] = $locale;

        // default parameters has to be unsetted!
        $route = $parameters['_route'];
        unset($parameters['_route']);
        unset($parameters['_controller']);

        return $this->redirectToRoute($route, $parameters);
    }
}