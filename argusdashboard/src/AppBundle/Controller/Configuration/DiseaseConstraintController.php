<?php
/**
 * Disease Constraint Controller
 *
 * @author François Cardinaux
 */

namespace AppBundle\Controller\Configuration;

use AppBundle\AppBundle;
use AppBundle\Controller\BaseController;
use AppBundle\Form\DiseaseConstraintType;
use AppBundle\Form\AnyListXmlLoaderType;
use AppBundle\Form\AnyListXmlSavingType;
use AppBundle\Entity\SesDashboardDiseaseConstraint;
use AppBundle\Entity\Import\Import;
use Symfony\Component\HttpFoundation\Request;
// use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Form\FormError;



class DiseaseConstraintController extends DiseaseBaseController
{

    /**
     * Disease Constraint List Action
     *
     * @param {Integer} $diseaseId The disease ID
     * @param {Integer} $page The page number that must be displayed
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws NotFoundHttpException
     */
    public function listAction($diseaseId, $page) {

        // Get the name of the disease

        $disease = $this->getDiseaseEntity($diseaseId);
        $diseaseName = $disease ? $disease->getName() : 'Disease';

        $diseaseConstraintService = $this->getDiseaseConstraintService();

        $query = $diseaseConstraintService->getDiseaseConstraintListQuery($diseaseId);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $page,
            $this->getParameter('item_quantity_per_configuration_page')
        );

        $diseaseListPage = $this->getBookmarkedPage(DiseaseController::BOOKMARK_SERVICE_KEY);

        return $this->render('configuration/disease/constraint/list.html.twig',
            array(
                'diseaseId' => $diseaseId,
                'diseaseName' => $diseaseName,
                'pagination' => $pagination,
                'diseaseListPage' => $diseaseListPage));
    }

    /**
     * Disease Insertion Action
     *
     * @param $diseaseId
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addAction($diseaseId, Request $request) {

        // Get the name of the disease

        $disease = $this->getDiseaseEntity($diseaseId);
        $diseaseName = $disease ? $disease->getName() : 'Disease';

        $diseaseConstraintEntity = new SesDashboardDiseaseConstraint();

        $form = $this->createForm(new DiseaseConstraintType($this->getSupportedLocales()), $diseaseConstraintEntity);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Set the disease ID

            $diseaseService = $this->getDiseaseService();
            $diseaseEntity = $diseaseService->getById($diseaseId);
            $diseaseConstraintEntity->setParentDisease($diseaseEntity);
            $em = $this->getDoctrine()->getManager();

            $em->persist($diseaseConstraintEntity);

            $em->flush();

            return $this->redirectToRoute('configuration_disease_constraints', array('diseaseId' => $diseaseId));
        }

        return $this->render('configuration/disease/constraint/new.html.twig', array('diseaseId' => $diseaseId, 'diseaseName' => $diseaseName, 'form' => $form->createView()));
    }

    /**
     * Disease Edition Action
     *
     * @param $diseaseId
     * @param $diseaseConstraintId
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction($diseaseId, $diseaseConstraintId, Request $request) {

        // Get the name of the disease

        $disease = $this->getDiseaseEntity($diseaseId);
        $diseaseName = $disease ? $disease->getName() : 'Disease';

        $diseaseConstraintService = $this->getDiseaseConstraintService();

        $diseaseConstraintEntity = $diseaseConstraintService->getById($diseaseConstraintId);

        $form = $this->createForm(new DiseaseConstraintType($this->getSupportedLocales()), $diseaseConstraintEntity);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $em->persist($diseaseConstraintEntity);

            $em->flush();

            return $this->redirectToRoute('configuration_disease_constraints', array('diseaseId' => $diseaseId));
        }

        return $this->render('configuration/disease/constraint/edit.html.twig', array(
            'diseaseId' => $diseaseId,
            'diseaseConstraintId' => $diseaseConstraintId,
            'diseaseName' => $diseaseName,
            'form' => $form->createView()));
    }

    /**
     * Disease Removal Action
     *
     * @param $diseaseId
     * @param $diseaseConstraintId
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeAction($diseaseId, $diseaseConstraintId, Request $request) {

        $diseaseConstraintService = $this->getDiseaseConstraintService();

        $diseaseConstraintEntity = $diseaseConstraintService->getById($diseaseConstraintId);

        $diseaseConstraintService->removeEntity($diseaseConstraintEntity);

        return $this->redirectToRoute('configuration_disease_constraints', array('diseaseId' => $diseaseId));

    }
}