<?php
/**
 * Disease Value Controller
 *
 * @author François Cardinaux
 */

namespace AppBundle\Controller\Configuration;

use AppBundle\Entity\SesDashboardDisease;
use AppBundle\Form\DiseaseValueInsertionType;
use AppBundle\Form\DiseaseValueEditionType;
use AppBundle\Entity\SesDashboardDiseaseValue;
use AppBundle\Services\DiseaseValueService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;



class DiseaseValueController extends DiseaseBaseController
{
    /**
     * Disease Value List Action
     *
     * @param {Integer} $diseaseId The disease ID
     * @param {Integer} $page The page number that must be displayed
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws NotFoundHttpException
     */
    public function listAction($diseaseId, $page)
    {
        // Get the name of the disease
        $disease = $this->_getDiseaseEntity($diseaseId);
        $diseaseName = $disease ? $disease->getName() : 'Disease';
        $diseaseValueService = $this->getDiseaseValueService();
        $query = $diseaseValueService->getDiseaseValueListQuery($diseaseId);
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $page,
            $this->getParameter('item_quantity_per_configuration_page')
        );
        $diseaseListPage = $this->getBookmarkedPage(DiseaseController::BOOKMARK_SERVICE_KEY);

        return $this->render('configuration/disease/value/list.html.twig',
            [
                'diseaseId' => $diseaseId,
                'diseaseName' => $diseaseName,
                'pagination' => $pagination,
                'diseaseListPage' => $diseaseListPage
            ]
        );
    }

    private function _getDiseaseEntity($diseaseId)
    {
        $diseaseService = $this->getDiseaseService();
        return $diseaseService->getById($diseaseId);
    }

    /**
     * Disease Insertion Action
     * @param $diseaseId
     * @param Request $request
     *
     * @return Response
     */
    public function addAction($diseaseId, Request $request)
    {
        // Get the name of the disease
        /** @var SesDashboardDisease $disease */
        $disease = $this->_getDiseaseEntity($diseaseId);
        $diseaseName = $disease ? $disease->getName() : 'Disease';
        $diseaseValueEntity = new SesDashboardDiseaseValue();
        $diseaseValueEntity->setFKDiseaseId($disease->getId());
        $diseaseValueEntity->setMandatory(true);
        $form = $this->createForm(new DiseaseValueInsertionType($this->getSupportedLocales()), $diseaseValueEntity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set the disease ID
            $diseaseService = $this->getDiseaseService();
            $diseaseEntity = $diseaseService->getById($diseaseId);
            $diseaseValueEntity->setParentDisease($diseaseEntity);
            $em = $this->getDoctrine()->getManager();
            $em->persist($diseaseValueEntity);
            $em->flush();

            return $this->redirectToRoute('configuration_disease_values', ['diseaseId' => $diseaseId]);
        }

        return $this->render('configuration/disease/value/new.html.twig',
            ['diseaseId' => $diseaseId, 'diseaseName' => $diseaseName, 'form' => $form->createView()]
        );
    }

    /**
     * Disease Edition Action
     *
     * @param $diseaseId
     * @param $diseaseValueId
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction($diseaseId, $diseaseValueId, Request $request)
    {
        // Get the name of the disease
        $disease = $this->_getDiseaseEntity($diseaseId);
        $diseaseName = $disease ? $disease->getName() : 'Disease';
        $diseaseValueService = $this->getDiseaseValueService();
        $diseaseValueEntity = $diseaseValueService->getById($diseaseValueId);
        $form = $this->createForm(new DiseaseValueEditionType($this->getSupportedLocales()), $diseaseValueEntity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($diseaseValueEntity);
            $em->flush();

            return $this->redirectToRoute('configuration_disease_values', ['diseaseId' => $diseaseId]);
        }

        return $this->render('configuration/disease/value/edit.html.twig', [
            'diseaseId' => $diseaseId,
            'diseaseValueId' => $diseaseValueId,
            'diseaseName' => $diseaseName,
            'diseaseValue' => $diseaseValueEntity->getValue(),
            'form' => $form->createView()]);
    }

    /**
     * Disease Removal Action
     *
     * @param $diseaseId
     * @param $diseaseValueId
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeAction($diseaseId, $diseaseValueId, Request $request)
    {
        $diseaseValueService = $this->getDiseaseValueService();
        $diseaseValueEntity = $diseaseValueService->getById($diseaseValueId);
        $diseaseValueService->removeEntity($diseaseValueEntity);

        return $this->redirectToRoute('configuration_disease_values', ['diseaseId' => $diseaseId]);
    }

    /**
     * Return json response with formatted and filtered sites for autocomplete element
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAllDiseaseValuesAction(Request $request)
    {
        $queryString = $request->query->get('q');
        /** @var DiseaseValueService $diseaseValuesService */
        $diseaseValuesService = $this->getDiseaseValueService();

        $diseaseValues = $diseaseValuesService->getDiseaseValuesFromQueryString($queryString);

        $jsonDiseases = [];

        /** @var SesDashboardDiseaseValue $diseaseValue */
        foreach ($diseaseValues as $diseaseValue) {
            $jsonDiseases[] = [
                "id" => $diseaseValue->getId(),
                "text" => $diseaseValue->getParentDisease()->getName() . " : " . $diseaseValue->getValue(),
            ];
        }

        return new JsonResponse(['results' => $jsonDiseases]);
    }
}