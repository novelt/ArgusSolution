<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 17/11/2016
 * Time: 17:12
 */

namespace AppBundle\Controller\Configuration;

use AppBundle\Controller\BaseController;
use AppBundle\Entity\SesDashboardContactType;
use AppBundle\Form\AnyListXmlLoaderType;
use AppBundle\Form\ContactTypeEditionType;
use AppBundle\Form\ContactTypeInsertionType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Utils\Response\XmlResponse;
use AppBundle\Entity\Import\Import;

class ContactTypeController extends BaseController
{
    const BOOKMARK_SERVICE_KEY = 'argus.configuration.contact.types';

    /**
     * Contact Type List Action
     *
     * @param {Integer} $page The page number that must be displayed
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws NotFoundHttpException
     */
    public function listAction($page)
    {
        $this->bookmarkPage(self::BOOKMARK_SERVICE_KEY, $page);

        $contactTypeService = $this->getContactTypeService();
        $query = $contactTypeService->getContactTypeListQuery();

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $page,
            $this->getParameter('item_quantity_per_configuration_page')
        );

        return $this->render('configuration/contact_type/list.html.twig', array('pagination' => $pagination));
    }

    /**
     * Contact Type Insertion Action
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addAction(Request $request)
    {
        $contactTypeEntity = new SesDashboardContactType();
        $contactTypeEntity->setSendsReports(true);
        $contactTypeEntity->setUseInIndicatorsCalculation(true);
        $form = $this->createForm(new ContactTypeInsertionType($this->getSupportedLocales()), $contactTypeEntity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($contactTypeEntity);
            $em->flush();

            return $this->redirectToRoute('configuration_contact_types');
        }

        return $this->render('configuration/contact_type/new.html.twig', array('form' => $form->createView()));
    }

    /**
     * Contact Type Edition Action
     *
     * @param $contactTypeId
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction($contactTypeId, Request $request)
    {
        $contactTypeService = $this->getContactTypeService();
        $contactTypeEntity = $contactTypeService->getById($contactTypeId);
        $form = $this->createForm(new ContactTypeEditionType($this->getSupportedLocales()), $contactTypeEntity);

        $form->handleRequest($request);

        $returnPage = $this->getBookmarkedPage(self::BOOKMARK_SERVICE_KEY);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($contactTypeEntity);
            $em->flush();

            return $this->redirectToRoute('configuration_contact_types', array('page' => $returnPage));
        }

        return $this->render('configuration/contact_type/edit.html.twig', array('contactTypeId' => $contactTypeId, 'form' => $form->createView(), 'page' => $returnPage));
    }

    /**
     * Remove contact Type from DB
     *
     * @param $contactTypeId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeAction($contactTypeId)
    {
        $contactTypeService = $this->getContactTypeService();
        $contactTypeService->remove($contactTypeId);
        $returnPage = $this->getBookmarkedPage(self::BOOKMARK_SERVICE_KEY);
        return $this->redirectToRoute('configuration_contact_types', array('page' => $returnPage));
    }

    /**
     * Load contact Type from Xml file
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function loadListFromXMLAction(Request $request)
    {
        $form = $this->createForm(AnyListXmlLoaderType::class, null, array('file_field_label' => 'Contact Types (XML file)'));
        $form->handleRequest($request);

        if ($form->isValid()) {
            if ( $form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('configuration_contact_types');
            }

            try {
                $newData = $form->getData();
                $fileNameAndPath = implode('/', array($newData['file']->getPath(), $newData['file']->getBaseName()));
                $xml = file_get_contents($fileNameAndPath);
                $contactTypes = null;

                if ($xml) {
                    $serializer = $this->getJmsSerializer();
                    $response = $serializer->deserialize($xml, 'AppBundle\\Entity\\Import\\Import', 'xml');
                    $contactTypes = $response->getContactTypes();
                }

                if ($contactTypes) {
                    $contactTypeService = $this->getContactTypeService();
                    $dashboardContactTypes = $contactTypes->getDashboardContactTypes();

                    // Persist or update contact regarding the reference
                    foreach ($dashboardContactTypes as $dashboardContactType) {
                        $existingContactType = $contactTypeService->findOneBy(array('reference' => $dashboardContactType->getReference()));
                        if ($existingContactType) {
                            $existingContactType->setName($dashboardContactType->getName());
                            $existingContactType->setDesc($dashboardContactType->getDesc());
                            $existingContactType->setSendsReports($dashboardContactType->isSendsReports());
                            $existingContactType->setUseInIndicatorsCalculation($dashboardContactType->isUseInIndicatorsCalculation());
                        } else {
                            $contactTypeService->saveContactType($dashboardContactType);
                        }
                    }

                    $contactTypeService->saveChanges();

                    return $this->redirectToRoute('configuration_contact_types');
                }

            } catch (\Exception $exception) {
                $form->addError(new FormError($exception->getCode().': '.$exception->getmessage()));
            }
        }

        return $this->render('configuration/contact_type/load_from_xml.html.twig', array('form' => $form->createView()));
    }

    /**
     * Export Contact Type Xml configuration file
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response;
     */
    public function saveListToXMLAction(Request $request) {
        $serializer = $this->getJmsSerializer();
        $contactTypeService = $this->getContactTypeService();
        $contactTypes = $contactTypeService->getAll();

        $import = new Import();
        $import->setContactTypeEntities($contactTypes);
        $xml = $serializer->serialize($import, 'xml');

        $response = new XmlResponse($xml, 200);
        $response->setFilename("contactTypes.xml");

        return $response;
    }

}