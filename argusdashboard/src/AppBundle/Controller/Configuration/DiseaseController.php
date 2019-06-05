<?php
/**
 * Disease Controller
 *
 * @author François Cardinaux
 */

namespace AppBundle\Controller\Configuration;

use AppBundle\Controller\BaseController;
use AppBundle\Form\DiseaseInsertionType;
use AppBundle\Form\DiseaseEditionType;
use AppBundle\Form\AnyListXmlLoaderType;
use AppBundle\Entity\SesDashboardDisease;
use AppBundle\Entity\Import\Import;
use AppBundle\Services\DiseaseService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;
use AppBundle\Utils\Response\XmlResponse;
use AppBundle\Utils\Response\CsvResponse;
use Symfony\Component\HttpFoundation\Response;


class DiseaseController extends BaseController
{
    const BOOKMARK_SERVICE_KEY = 'argus.configuration.diseases';

    /**
     * Disease List Action
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        return $this->render('configuration/disease/list.html.twig');
    }

    /**
     * Build Datatable structure
     *
     * @return JsonResponse
     */
    public function datatableAction()
    {
        /** @var DiseaseService $diseaseService */
        $diseaseService = $this->getDiseaseService();
        $diseases = $diseaseService->getAll();
        $diseasesCount = count($diseases);

        $datatableDatas = [];
        $datatableDatas["draw"] = 1;
        $datatableDatas["recordsTotal"] = $diseasesCount;
        $datatableDatas["recoredFiltered"] = $diseasesCount;
        $datatableDatas["data"] = [];

        /** @var SesDashboardDisease $disease */
        foreach ($diseases as $disease) {
            $datatableDatas['data'][] = [
                "dt_disease_id" => $disease->getId(),
                "dt_disease_ref" => $disease->getDisease(),
                "dt_disease_name" => $disease->getName(),
                "dt_disease_keyWord" => $disease->getKeyword(),
                "dt_disease_position" => $disease->getPosition()
            ];
        }

        return new JsonResponse($datatableDatas);
    }

    /**
     * Disease Insertion Action
     * @param Request $request
     * @return Response
     */
    public function addAction(Request $request)
    {
        $diseaseEntity = new SesDashboardDisease();
        $form = $this->createForm(new DiseaseInsertionType($this->getSupportedLocales()), $diseaseEntity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($diseaseEntity);
            $em->flush();

            return $this->redirectToRoute('configuration_diseases');
        }

        return $this->render('configuration/disease/new.html.twig', array('form' => $form->createView()));
    }

    /**
     * Disease Edition Action
     *
     * @param $diseaseId
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction($diseaseId, Request $request)
    {
        $diseaseService = $this->getDiseaseService();
        /** @var SesDashboardDisease $diseaseEntity */
        $diseaseEntity = $diseaseService->getById($diseaseId);
        $form = $this->createForm(new DiseaseEditionType($this->getSupportedLocales()), $diseaseEntity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Why it is working without this in the creation method and not in the edition method ??
            if (! is_numeric($diseaseEntity->getReportDataSourceId())) {
                $diseaseEntity->setReportDataSourceId(null);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($diseaseEntity);
            $em->flush();

            return $this->redirectToRoute('configuration_diseases');
        }

        return $this->render(
            'configuration/disease/edit.html.twig',
            ['diseaseId' => $diseaseId, 'form' => $form->createView()]
        );
    }

    /**
     * Disease Removal Action
     *
     * @param $diseaseId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeAction($diseaseId)
    {
        $service = $this->getDiseaseService();
        $entity = $service->getById($diseaseId);
        $service->removeEntity($entity);

        return $this->redirectToRoute('configuration_diseases');
    }

    /**
     * Load a list of diseases, with values and constraints
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function loadListFromXMLAction(Request $request)
    {
        $form = $this->createForm(AnyListXmlLoaderType::class, null, array('file_field_label' => 'Diseases (XML file)'));
        $form->handleRequest($request);

        if ($form->isValid()) {
            if ( $form->get('cancel')->isClicked() ) {
                return $this->redirectToRoute('configuration_diseases');
            }

            try {
                $newData = $form->getData();
                $fileNameAndPath = implode('/', array($newData['file']->getPath(), $newData['file']->getBaseName()));
                $xml = file_get_contents($fileNameAndPath);
                $diseases = null;

                if ($xml) {
                    $serializer = $this->getJmsSerializer();
                    $response = $serializer->deserialize($xml, Import::class, 'xml');
                    $diseases = $response->getDiseases();
                }

                if ($diseases) {
                    // First, empty the current disease list
                    $diseaseService = $this->getDiseaseService();
                    $diseaseService->removeAll();
                    // Then, persists all new diseases
                    $em = $this->getDoctrine()->getManager();
                    foreach($diseases->getDashboardDiseases() as $disease) {
                        $diseaseValues = $disease->getDiseaseValues();
                        if (!is_null($diseaseValues)) {
                            $ormDiseaseValues = $diseaseValues->getDashboardDiseasesValues();
                            $disease->setDiseaseValues($ormDiseaseValues);

                            foreach($disease->getDiseaseValues() as $diseaseValue) {
                                $diseaseValue->setParentDisease($disease);
                            }
                        }

                        $diseaseConstraints = $disease->getDiseaseConstraints();
                        if (!is_null($diseaseConstraints)) {
                            $ormDiseaseConstraints = $diseaseConstraints->getDashboardDiseasesConstraints();
                            $disease->setDiseaseConstraints($ormDiseaseConstraints);

                            foreach($disease->getDiseaseConstraints() as $diseaseConstraint) {
                                $diseaseConstraint->setParentDisease($disease);
                            }
                        }

                        $em->persist($disease);
                    }
                    $em->flush();

                    return $this->redirectToRoute('configuration_diseases');
                }

            } catch (\Exception $exception) {
                $form->addError(new FormError($exception->getCode().': '.$exception->getmessage()));
            }
        }

        return $this->render('configuration/disease/load_from_xml.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Export Disease Xml configuration file
     *
     * @return \Symfony\Component\HttpFoundation\Response;
     */
    public function saveListToXMLAction()
    {
        $serializer = $this->getJmsSerializer();
        $diseaseService = $this->getDiseaseService();
        $diseases = $diseaseService->getAll();

        $import = new Import();
        $import->setDiseaseEntities($diseases);
        $xml = $serializer->serialize($import, 'xml');

        $response = new XmlResponse($xml, 200);
        $response->setFilename(self::getDiseaseFileName());

        return $response;
    }

    /**
     * Export list to CSV
     *
     * @return CsvResponse
     */
    public function saveListToCSVAction()
    {
        $diseaseService = $this->getDiseaseService();
        $data = $diseaseService->getDiseaseForCsvExport();

        $response = new CsvResponse($data, 200, SesDashboardDisease::getHeaderCsvRow());
        $response->setFilename("diseases.csv");

        return $response ;
    }
}
