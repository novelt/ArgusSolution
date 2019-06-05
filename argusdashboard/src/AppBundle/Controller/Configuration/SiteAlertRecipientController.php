<?php
/**
 * Site Alert Recipient Controller
 *
 * @author fc
 */

namespace AppBundle\Controller\Configuration;

use AppBundle\AppBundle;
use AppBundle\Controller\BaseController;
use AppBundle\Entity\SesDashboardSiteAlertRecipient;
use AppBundle\Form\SiteAlertRecipientType;
use AppBundle\Services\SiteAlertRecipientService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;

class SiteAlertRecipientController extends BaseController
{

    /**
     * List Action
     *
     * @param {Integer} $siteId
     * @param {Integer} $page The page number that must be displayed
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws NotFoundHttpException
     */
    public function listAction($siteId, $page) {

        // Find the site name out

        $siteService = $this->getSiteService();
        $siteEntity = $siteService->getById($siteId);
        $siteName = $siteEntity->getName();

        // Build the page

        $service = $this->getSiteAlertRecipientService();

        $query = $service->getSiteAlertRecipientListQuery($siteId);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $page,
            $this->getParameter('item_quantity_per_configuration_page')
        );

        $siteListPage = $this->getBookmarkedPage(SiteController::BOOKMARK_SERVICE_KEY);

        return $this->render('configuration/site/alertRecipient/list.html.twig',
            array(
                'siteId' => $siteId,
                'siteName' => $siteName,
                'pagination' => $pagination,
                'siteListPage' => $siteListPage));
    }

    /**
     * Insertion Action
     *
     * @param $siteId
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addAction($siteId, Request $request) {

        // Find the site name out

        $siteService = $this->getSiteService();
        $siteEntity = $siteService->getById($siteId);
        $siteName = $siteEntity->getName();

        // Build the form

        $siteAlertRecipientEntity = new SesDashboardSiteAlertRecipient();

        $form = $this->createForm(new SiteAlertRecipientType($this->getSupportedLocales()), $siteAlertRecipientEntity);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Set the site ID and reference

            $siteService = $this->getSiteService();
            $siteEntity = $siteService->getById($siteId);
            $siteAlertRecipientEntity->setsite($siteEntity);

            $em = $this->getDoctrine()->getManager();

            $em->persist($siteAlertRecipientEntity);

            $em->flush();

            return $this->redirectToRoute('configuration_site_alert_recipients', array('siteId' => $siteId));
        }

        return $this->render('configuration/site/alertRecipient/new.html.twig', array('siteId' => $siteId, 'siteName' => $siteName, 'form' => $form->createView()));
    }

    /**
     * Removal Action
     *
     * @param $siteId
     * @param $siteAlertRecipientId
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeAction($siteId, $siteAlertRecipientId, Request $request) {

        /** @var SiteAlertRecipientService $service */
        $service = $this->getSiteAlertRecipientService();
        $entity = $service->getById($siteAlertRecipientId);
        $service->getRepository()->remove($entity);
        $service->getRepository()->saveChanges();

        return $this->redirectToRoute('configuration_site_alert_recipients', array('siteId' => $siteId));

    }
}