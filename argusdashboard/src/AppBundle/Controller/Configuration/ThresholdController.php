<?php
/**
 * Threshold Controller
 *
 * @author François Cardinaux
 */

namespace AppBundle\Controller\Configuration;

use AppBundle\Controller\BaseController;
use AppBundle\Entity\Import\Thresholds;
use AppBundle\Form\ThresholdInsertionType;
use AppBundle\Form\ThresholdEditionType;
use AppBundle\Form\AnyListXmlLoaderType;
use AppBundle\Entity\SesDashboardThreshold;
use AppBundle\Entity\Import\Import;
use AppBundle\Services\DiseaseService;
use AppBundle\Services\SiteService;
use AppBundle\Services\ThresholdService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;
use AppBundle\Utils\Response\XmlResponse;
use AppBundle\Utils\Response\CsvResponse;
use Symfony\Component\HttpFoundation\Response;


class ThresholdController extends BaseController
{
    const BOOKMARK_SERVICE_KEY = 'argus.configuration.thresholds';

    /**
     * Threshold List Action
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        return $this->render('configuration/threshold/list.html.twig');
    }

    /**
     * Build Datatable structure
     *
     * @return JsonResponse
     */
    public function datatableAction()
    {
        $thresoldService = $this->getThresholdService();
        $thresholds = $thresoldService->getAll();
        $thresholdsCount = count($thresholds);

        $datatableDatas = [];
        $datatableDatas["draw"] = 1;
        $datatableDatas["recordsTotal"] = $thresholdsCount;
        $datatableDatas["recoredFiltered"] = $thresholdsCount;
        $datatableDatas["data"] = [];

        /** @var SesDashboardThreshold $threshold */
        foreach ($thresholds as $threshold) {
            $datatableDatas['data'][] = [
                "dt_threshold_id" => $threshold->getId(),
                "dt_threshold_site" => ($threshold->getSite() != null ? $threshold->getSite()->getName() : ""),
                "dt_threshold_disease" => ($threshold->getDisease() != null ? $threshold->getDisease()->getName() : ""),
                "dt_threshold_value" => ($threshold->getDiseaseValue() != null ? $threshold->getDiseaseValue()->getValue() : ""),
                "dt_threshold_period" => $threshold->getPeriod(),
                "dt_threshold_week_number" => $threshold->getWeekNumber(),
                "dt_threshold_month_number" => $threshold->getMonthNumber(),
                "dt_threshold_year" => $threshold->getYear(),
                "dt_threshold_max_value" => $threshold->getMaxValue()
            ];
        }

        return new JsonResponse($datatableDatas);
    }

    /**
     * Threshold Insertion Action
     * @param Request $request
     * @return Response
     */
    public function addAction(Request $request)
    {
        $thresholdEntity = new SesDashboardThreshold();
        $form = $this->createForm(new ThresholdInsertionType($this->getSupportedLocales()), $thresholdEntity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $thresholdEntity->setDisease($thresholdEntity->getDiseaseValue()->getParentDisease());
            $em = $this->getDoctrine()->getManager();
            $em->persist($thresholdEntity);
            $em->flush();

            return $this->redirectToRoute('configuration_thresholds');
        }

        return $this->render(
            'configuration/threshold/new.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Threshold Edition Action
     *
     * @param $thresholdId
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction($thresholdId, Request $request)
    {
        $thresholdService = $this->getThresholdService();
        /** @var SesDashboardThreshold $thresholdEntity */
        $thresholdEntity = $thresholdService->getById($thresholdId);

        $form = $this->createForm(new ThresholdEditionType($this->getSupportedLocales()), $thresholdEntity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $thresholdEntity->setDisease($thresholdEntity->getDiseaseValue()->getParentDisease());
            $em = $this->getDoctrine()->getManager();
            $em->persist($thresholdEntity);
            $em->flush();

            return $this->redirectToRoute('configuration_thresholds');
        }

        return $this->render(
            'configuration/threshold/edit.html.twig',
            ['thresholdId' => $thresholdId, 'form' => $form->createView()]
        );
    }

    /**
     * Threshold Removal Action
     *
     * @param $thresholdId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeAction($thresholdId)
    {
        $service = $this->getThresholdService();
        $entity = $service->getById($thresholdId);
        $service->removeEntity($entity);

        return $this->redirectToRoute('configuration_thresholds');
    }

    /**
     * Load a list of thresholds, with values and constraints
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function loadListFromXMLAction(Request $request)
    {
        $form = $this->createForm(AnyListXmlLoaderType::class, null, ['file_field_label' => 'Thresholds (XML file)']);
        $form->handleRequest($request);

        if ($form->isValid()) {
            if ( $form->get('cancel')->isClicked() ) {
                return $this->redirectToRoute('configuration_thresholds');
            }

            $errors = [];

            try {
                $newData = $form->getData();
                $fileNameAndPath = implode(
                    '/',
                    [$newData['file']->getPath(),
                        $newData['file']->getBaseName()]
                );
                $xml = file_get_contents($fileNameAndPath);
                $thresholds = null;

                if ($xml) {
                    $serializer = $this->getJmsSerializer();
                    /** @var Import $response */
                    $response = $serializer->deserialize($xml, Import::class, 'xml');
                    /** @var Thresholds $thresholds */
                    $thresholds = $response->getThresholds();
                }

                if ($thresholds) {
                    // Check Thresholds
                    foreach($thresholds->getDashboardThresholds() as $threshold) {
                        $this->postDeserialize($threshold, $errors);
                    }

                    if (count($errors) == 0) {
                        // First, empty the current threshold list
                        /** @var ThresholdService $thresholdService */
                        $thresholdService = $this->getThresholdService();
                        $thresholdService->removeAll();

                        $em = $this->getDoctrine()->getManager();

                        /** @var SesDashboardThreshold $threshold */
                        foreach($thresholds->getDashboardThresholds() as $threshold) {
                            $em->persist($threshold);
                        }
                        $em->flush();
                    }
                }
            } catch (\Exception $exception) {
                $form->addError(
                    new FormError($exception->getCode().': '.$exception->getmessage())
                );
            }

            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $form->addError($error);
                }
            } else {
                return $this->redirectToRoute('configuration_thresholds');
            }
        }

        return $this->render(
            'configuration/threshold/load_from_xml.html.twig',
            ['form' => $form->createView()]
        );
    }


    /**
     * @param SesDashboardThreshold $threshold
     * @param $errors
     */
    private function postDeserialize($threshold, &$errors)
    {
        /** @var SiteService $siteService */
        $siteService = $this->getSiteService();
        $site = $siteService->findOneBy(array('reference' => $threshold->getSiteReference()));
        if ($site == null) {
            $errors[] = new FormError(sprintf("Not site found with reference [%s]", $threshold->getSiteReference()));
            return ;
        }

        $threshold->setSite($site);

        /** @var DiseaseService $diseaseService */
        $diseaseService = $this->getDiseaseService();
        $disease = $diseaseService->findOneBy(array('disease' => $threshold->getDiseaseReference()));
        if ($disease == null) {
            $errors[] = new FormError(sprintf("Not disease found with reference [%s]", $threshold->getDiseaseReference()));
            return ;
        }

        $threshold->setDisease($disease);

        $diseaseValue = $diseaseService->findDiseaseValue($threshold->getDisease()->getDisease(), $threshold->getValueReference());
        if ($diseaseValue == null) {
            $errors[] = new FormError(sprintf("Not diseaseValue found with reference [%s]", $threshold->getValueReference()));
            return ;
        }

        $threshold->setDiseaseValue($diseaseValue);
    }

    /**
     * Export Threshold Xml configuration file
     *
     * @return \Symfony\Component\HttpFoundation\Response;
     */
    public function saveListToXMLAction()
    {
        $serializer = $this->getJmsSerializer();
        /** @var ThresholdService $thresholdService */
        $thresholdService = $this->getThresholdService();
        $thresholds = $thresholdService->getAll();

        $import = new Import();
        $import->setThresholdEntities($thresholds);
        $xml = $serializer->serialize($import, 'xml');

        $response = new XmlResponse($xml, 200);
        $response->setFilename(self::getThresholdFileName());

        return $response;
    }

    /**
     * Export Threshold Csv file
     *
     * @return CsvResponse
     */
    public function saveListToCSVAction()
    {
        /** @var ThresholdService $thresholdService */
        $thresholdService = $this->getThresholdService();
        $data = $thresholdService->getThresholdForCsvExport();

        $response = new CsvResponse($data, 200, SesDashboardThreshold::getHeaderCsvRow());
        $response->setFilename("thresholds.csv");

        return $response ;
    }
}