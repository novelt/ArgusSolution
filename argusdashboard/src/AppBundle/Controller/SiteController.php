<?php

namespace AppBundle\Controller;


use Symfony\Component\HttpFoundation\JsonResponse;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


/**
 * Controller used to manage TreeView content
 *
 * @Route("/site")
 *
 */
class SiteController extends BaseController
{
    /**
     * @Route("/", name="site_getJsonSite")
     */
    public function getJsonSiteAction()
    {
        // Check if user has at least ROLE_USER
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'Unable to access this page!');

        // Get Root Site.
        //$sitesArray = $this->getSiteService()->findAllHierarchy();

        $sitesArray = $this->getSiteService()->createHierarchy();

        $json = $this->getSiteService()->getTreeView(reset($sitesArray),
            $this->getUser() != null ? $this->getUser()->getDashboardPermissions() : [],
            $this->getSesDashboardPermissionHelper(),
            $this->getHomeSite());

        //return new Response('', 200,[]);
        return new JsonResponse(array($json));
    }
}
