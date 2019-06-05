<?php
/**
 * Site Controller
 *
 * @author François Cardinaux
 */

namespace AppBundle\Controller\Configuration;

use AppBundle\Controller\BaseController;
use AppBundle\Form\SiteEditionType;
use AppBundle\Form\SiteInsertionType;
use AppBundle\Form\AnyListXmlLoaderType;
use AppBundle\Entity\SesDashboardSite;
use AppBundle\Entity\Import\Import;
use AppBundle\Services\SiteService;
use AppBundle\Utils\DimDateHelper;
use AppBundle\Utils\Response\XmlResponse;
use AppBundle\Utils\Response\CsvResponse;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;


class SiteController extends BaseController
{
    const BOOKMARK_SERVICE_KEY = 'argus.configuration.sites';

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Get Doctrine Manager
     *
     * @return EntityManager
     */
    private function getManager()
    {
        if (is_null($this->em)) {
            return $this->getDoctrine()->getManager();
        }
        return $this->em;
    }

    /**
     * Site List Action
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        return $this->render('configuration/site/list.html.twig');
    }


    private function getSiteDataTableInformation($siteArray)
    {
        $site = $siteArray["site"];
        $relation = $this->getSiteService()->getActiveOrMostRecentRelationShipArray($siteArray['siteRelationShip']);
        $children = $siteArray["children"];

        $result[] =
         [
            "dt_site_id" => $site["id"],
            "dt_site_name" => $relation["name"],
            "dt_site_long" => $relation["longitude"],
            "dt_site_lat" => $relation["latitude"],
            "dt_site_weekly_delay" => $site["weeklyTimelinessMinutes"],
            "dt_site_monthly_delay" => $site["monthlyTimelinessMinutes"],
            "dt_site_level" => $relation["level"],
            "dt_site_path" => $site["reference"],
            "dt_site_data_source" => isset($site["reportDataSource"]) ? $site["reportDataSource"]["name"] : "",
            "dt_subsites_contacts_count" => $this->getSiteService()->getSubSitesStatus($children),
            "dt_site_alert_pref_gateway" => $site["alertPreferredGateway"],
            "dt_cascading_alert" => $site["cascadingAlert"],
            "dt_site_full_path" => $relation["path"],
            "dt_alert_recipient_count" => (isset($site['alertRecipients'])? count($site['alertRecipients']) : 0),
            "dt_site_deleted" => $this->getSiteService()->isRelationShipDailyDeleted($relation)
        ];

        if (count($children) != 0) {
            foreach ($children as $key => $child) {
                $result = array_merge($result, $this->getSiteDataTableInformation($child)) ;
            }
        }

        return $result;
    }

    /**
     * Build Datatable structure
     *
     * @return JsonResponse
     */
    public function datatableAction()
    {
        $siteService = $this->getSiteService();
        $sites = $siteService->createHierarchy(true);

        $root = reset($sites);
        $datatableDatas = [];
        $datatableDatas['data'] = $this->getSiteDataTableInformation($root);
        $datatableDatas["draw"] = 1;
        $datatableDatas["recordsTotal"] = count($datatableDatas['data']);
        $datatableDatas["recoredFiltered"] = count($datatableDatas['data']);

        return new JsonResponse($datatableDatas);
    }

    /**
     * Return the result for weekly and monthly timeliness calculation
     *
     * @param integer $timelinessValueFromEntity
     * @param integer $valueFromParams
     * @param string $operator
     * @return integer
     */
    private function returnTimelinessCalculation($timelinessValueFromEntity, $valueFromParams, $operator = "minus")
    {
        if ($operator == "plus") {
            return $timelinessValueFromEntity + $valueFromParams;
        }

        return $timelinessValueFromEntity - $valueFromParams;
    }

    /**
     * Get timeliness value for week or month depends of parent's one
     *
     * @param SesDashboardSite $site
     * @param array $defaultValues
     * @param string $query
     * @return integer
     */
    private function getTimelinessValuesWithParent(SesDashboardSite $site, array $defaultValues, $query = "week")
    {
        if ($site == null || $site->getParent() == null) {
            return 0;
        }
      
        /** @var SesDashboardSite $parentSite */
        $parentSite = $site->getParent();

        switch ($query) {
            case "week":
                $diffWeeklyMinutes = $this
                    ->returnTimelinessCalculation(
                        $parentSite->getWeeklyTimelinessMinutes(),
                        $defaultValues['sites']['weekly_timeliness_minutes']
                    );

                if ($diffWeeklyMinutes <= 0) {
                    $diffWeeklyMinutes = $defaultValues['sites']['weekly_timeliness_minutes'];
                }

                return $diffWeeklyMinutes;
            case "month":
                $diffMonthlyMinutes = $this
                    ->returnTimelinessCalculation(
                        $parentSite->getMonthlyTimelinessMinutes(),
                        $defaultValues['sites']['monthly_timeliness_minutes']
                    );

                if ($diffMonthlyMinutes <= 0) {
                    $diffMonthlyMinutes = $defaultValues['sites']['monthly_timeliness_minutes'];
                }

                return $diffMonthlyMinutes;
        }
    }


    /**
     * Site Insertion Action
     * @param Request $request
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\DBALException
     */
    public function addAction(Request $request)
    {
        $siteEntity = new SesDashboardSite();

        // Set default values (http://stackoverflow.com/a/15795185)
        $defaultValues = $this->getParameter('configuration_defaults');
        $siteEntity->setWeeklyReminderOverrunMinutes(
            $defaultValues['sites']['weekly_reminder_overrun_minutes']
        );
        $siteEntity->setMonthlyReminderOverrunMinutes(
            $defaultValues['sites']['monthly_reminder_overrun_minutes']
        );
      
        $siteEntity->setWeeklyTimelinessMinutes(0);
        $siteEntity->setMonthlyTimelinessMinutes(0);

        $form = $this->createForm(new SiteInsertionType($this->getSupportedLocales()), $siteEntity);
        $em = $this->getManager();

        if ($request->query->get('siteId') !== null) {
            $siteId = $request->query->get('siteId');
            $site = $em->getRepository("AppBundle:SesDashboardSite")->find($siteId);

            if ($site && $site instanceof SesDashboardSite) {
                $weeklyTimelinessValue = $this
                    ->returnTimelinessCalculation(
                        $site->getWeeklyTimelinessMinutes(),
                        $defaultValues['sites']['weekly_timeliness_minutes']
                    );
                $monthlyTimelinessValue = $this
                    ->returnTimelinessCalculation(
                        $site->getMonthlyTimelinessMinutes(),
                        $defaultValues['sites']['monthly_timeliness_minutes']
                    );

                $form->get('parent')->setData($site);
                $form->get('weeklyTimelinessMinutes')->setData($weeklyTimelinessValue);
                $form->get('monthlyTimelinessMinutes')->setData($monthlyTimelinessValue);
            }
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var SiteService $siteService */
            $siteService = $this->getSiteService();

            // Retrieve UnMapped fields
            $siteName = $form->get('name')->getData();
            $parentSiteId = $form->get('parent')->getData();
            $longitude = $form->get('longitude')->getData();
            $latitude = $form->get('latitude')->getData();
            $reportDataSourceId = $this->getParser()->parseInteger($form->get('reportDataSourceId')->getData());

            // ---- Check that no site in the same branch is already configured with the same report data source ----
            $dto = null;
            if($reportDataSourceId !== null) {
                $overwriteReportDataSourceId = $this->getParser()->parseBoolean($form->get('overwriteReportDataSourceId')->getData());

                $dto = $this->getSiteService()->getSitesSameBranchDataSourceConfigConflictDTO(true, $parentSiteId->getId(), $reportDataSourceId);

                //this is situation should never happen, unless the html page is... "tuned". This is an additional check on server side, as this type of configuration can have a big impact on data
                if($dto->isConflict() && !$overwriteReportDataSourceId) {
                    $form->get('reportDataSourceId')->addError(new FormError($dto->getMessage()));
                }
            }
            // ------------------------------------------------------------------------------------------------------

            if($form->isValid()) {
                $siteService->createNewSite($siteEntity, $siteName, $parentSiteId, $longitude, $latitude, $reportDataSourceId, $dto);

                return $this->redirectToRoute('configuration_sites');
            }
        }

        return $this->render(
            'configuration/site/new.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Return json response with formatted and filtered sites for autocomplete element
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAllSitesAction(Request $request)
    {
        $queryString = $request->query->get('q');
        $siteService = $this->getSiteService();

        $search = empty($queryString) ? null : $queryString;
        $sitesRelations = $siteService->getAllSiteRelationShipsArray($search);

        $jsonSites = [];

        foreach ($sitesRelations as $relation) {
            if (!$this->getSiteService()->isRelationShipWeeklyDeleted($relation)) {
                $jsonSites[] = [
                    "id" => $relation["FK_SiteId"],
                    "text" => $relation["path"] . " : " . $relation["name"]
                ];
            }
        }

        return new JsonResponse(['results' => $jsonSites]);
    }

    /**
     * Site Edition Action
     * @param $siteId
     * @param Request $request
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\DBALException
     */
    public function editAction($siteId, Request $request)
    {
        $siteService = $this->getSiteService();
        /** @var SesDashboardSite $site */
        $site = $siteService->getById($siteId);
        $form = $this->createForm(new SiteEditionType($this->getSupportedLocales()), $site);

        $form->get('name')->setData($site->getActiveOrMostRecentSiteRelationShip()->getName());
        $form->get('longitude')->setData($site->getActiveOrMostRecentSiteRelationShip()->getLongitude());
        $form->get('latitude')->setData($site->getActiveOrMostRecentSiteRelationShip()->getLatitude());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Retrieve UnMapped fields
            $siteName = $form->get('name')->getData();
            $longitude = $form->get('longitude')->getData();
            $latitude = $form->get('latitude')->getData();
            $reportDataSourceId = $this->getParser()->parseInteger($form->get('reportDataSourceId')->getData());

            // ---- Check that no site in the same branch is already configured with the same report data source ----
            $dto = null;
            if($reportDataSourceId !== null) {
                $overwriteReportDataSourceId = $this->getParser()->parseBoolean($form->get('overwriteReportDataSourceId')->getData());
                $dto = $this->getSiteService()->getSitesSameBranchDataSourceConfigConflictDTO(false, $siteId, $reportDataSourceId);

                //this is situation should never happen, unless the html page is... "tuned". This is an additional check on server side, as this type of configuration can have a big impact on data
                if($dto->isConflict() && !$overwriteReportDataSourceId) {
                    $form->get('reportDataSourceId')->addError(new FormError($dto->getMessage()));
                }
            }
            // ------------------------------------------------------------------------------------------------------

            if($form->isValid()) {
                $siteService->editSite($site, $siteName, $longitude, $latitude, $reportDataSourceId, $dto);
                return $this->redirectToRoute('configuration_sites');
            }
        }

        return $this->render(
            'configuration/site/edit.html.twig',
            [
                'siteId' => $siteId,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @param SesDashboardSite $site
     * @ParamConverter("site", class=SesDashboardSite::class, options={"id" = "siteId"})
     * @return JsonResponse
     */
    public function enableOrDisableAction(SesDashboardSite $site)
    {
        $siteService = $this->getSiteService();
        $siteService->enableOrDisableSite($site);

        return new JsonResponse(
            ["returnUrl" => $this->generateUrl('configuration_sites')],
            JsonResponse::HTTP_OK
        );
    }

    /**
     * Get timeliness values depends of parent's ones
     *
     * @ParamConverter("site", class=SesDashboardSite::class, options={"id" = "siteId"})
     * @param SesDashboardSite $site
     * @return JsonResponse
     */
    public function getTimelinessValuesWithParentAction(SesDashboardSite $site)
    {
        $defaultValues = $this->getParameter('configuration_defaults');

        $weeklyMinutes = $this->getTimelinessValuesWithParent($site, $defaultValues);
        $monthlyMinutes = $this->getTimelinessValuesWithParent($site, $defaultValues, "month");

        return new JsonResponse(
            [
                "week" => $weeklyMinutes,
                "month" => $monthlyMinutes
            ],
            JsonResponse::HTTP_OK
        );
    }

    /**
     * Return parent and children sites having the given reportDataSource
     *
     * @param $isNewSite boolean
     * @param $siteId int
     * @param $reportDataSourceId int
     * @return JsonResponse
     */
    public function getSitesSameBranchDataSourceConfigConflictAction($isNewSite, $siteId, $reportDataSourceId) {
        try {
            $isNewSiteBool = $this->getParser()->parseBoolean($isNewSite);

            $dto = $this->getSiteService()->getSitesSameBranchDataSourceConfigConflictDTO($isNewSiteBool, $siteId, $reportDataSourceId);

            $result = [
                "conflict" => $dto->isConflict(),
                "msg" => $dto->getMessage()
            ];

            return new JsonResponse($result, JsonResponse::HTTP_OK);
        }
        catch(\Exception $e) {
            return new JsonResponse(null, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Load a list of sites from an XML file
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function loadListFromXMLAction(Request $request)
    {
        $form = $this->createForm(
            AnyListXmlLoaderType::class,
            null,
            ['file_field_label' => 'Sites (' . $this->get('translator')->trans('XML file') . ')']
        );
        $form->handleRequest($request);

        if ($form->isValid()) {
            if ( $form->get('cancel')->isClicked() ) {
                return $this->redirectToRoute('configuration_sites');
            }

            try {
                $newData = $form->getData();
                $fileNameAndPath = implode(
                    '/',
                    [
                        $newData['file']->getPath(),
                        $newData['file']->getBaseName()
                    ]
                );
                $xml = file_get_contents($fileNameAndPath);
                $sites = null;

                if ($xml) {
                    $serializer = $this->getJmsSerializer();
                    $response = $serializer->deserialize($xml, Import::class, 'xml');
                    $sites = $response->getSites();
                } else {
                    $errors[] = new FormError("Xml file not recognized");
                }

                if ($sites) {
                    /** @var SiteService $siteService */
                    $siteService = $this->getSiteService();
                    $errors = $siteService->importSites($sites, $this->getWeeklyTimelinessMinutes(), $this->getMonthlyTimelinessMinutes());
                } else {
                    $errors[] = new FormError("No Sites found in this file");
                }

            } catch (\Exception $exception) {
                $errors[] = new FormError($exception->getCode().': '.$exception->getmessage());
            }

            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $form->addError($error);
                }
            } else {
               return $this->redirectToRoute('configuration_sites');
            }
        }

        return $this
            ->render(
                'configuration/site/load_from_xml.html.twig',
                ['form' => $form->createView()]
            );
    }

    /**
     * Export Site Xml configuration file
     *
     * @return XmlResponse
     */
    public function saveListToXMLAction()
    {
        $serializer = $this->getJmsSerializer();
        $siteService = $this->getSiteService();
        $sites = $siteService->findAllWithRelations(null, null, DimDateHelper::getDimDateTodayId());

        // Remove the root
        /**
         * @var int $key
         * @var SesDashboardSite $site
         */
        foreach ($sites as $key => $site) {
            if (SesDashboardSite::ROOT_REFERENCE === $site->getReference()) {
                unset($sites[$key]);
                break;
            }
        }

        $import = new Import();
        $import->setSiteEntities($sites);
        $xml = $serializer->serialize($import, 'xml');

        $response = new XmlResponse($xml, 200);
        $response->setFilename(self::getSiteFileName());

        return $response;
    }

    /**
     * Export Site Csv file
     *
     * @return CsvResponse
     */
    public function saveListToCSVAction()
    {
        $siteService = $this->getSiteService();
        $data = $siteService->getSiteForCsvExport();

        $response = new CsvResponse($data, 200, SesDashboardSite::getHeaderCsvRow());
        $response->setFilename("sites.csv");

        return $response ;
    }
}

