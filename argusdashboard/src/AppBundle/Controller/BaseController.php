<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 3/17/2016
 * Time: 12:07 PM
 */
namespace AppBundle\Controller;

use AppBundle\Entity\Constant;
use AppBundle\Entity\Security\SesDashboardUser;
use AppBundle\Entity\SesDashboardSite;
use AppBundle\Entity\SesFullReport;
use AppBundle\Entity\SesPartReport;
use AppBundle\Services\ContactTypeService;
use AppBundle\Services\DbConstant;
use AppBundle\Services\IndicatorsCalculation\IndicatorDimDateService;
use AppBundle\Services\Odk\OdkService;
use AppBundle\Services\DashboardService;
use AppBundle\Services\Report\ReportUploadService;
use AppBundle\Services\ReportService;
use AppBundle\Services\SiteService;
use AppBundle\Utils\Parser;
use AppBundle\Utils\SesDashboardPermissionHelper;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

class BaseController extends Controller
{
    const PARAM_NULL = "undefined";
    const PARAM_NONE = "none";
    const PARAM_ALL = "all";
    const PARAM_ARRAY_SEPARATOR = ',';
    const PARAM_DATA_TYPE_STRING = 'STRING';
    const PARAM_DATA_TYPE_INTEGER = 'INTEGER';
    const PARAM_DATA_TYPE_DATE = 'DATE';
    const PARAM_DATA_TYPE_BOOLEAN = 'BOOLEAN';

    private $sesDashboardPermissionHelper;

    public function getSesDashboardPermissionHelper()
    {
        if ($this->sesDashboardPermissionHelper == null) {
            $this->sesDashboardPermissionHelper = new SesDashboardPermissionHelper($this->getUser());
        }

        return $this->sesDashboardPermissionHelper;
    }

    /*** Bookmark service wrapper  ***/

    /**
     * Bookmark the page
     *
     * @param $listKey
     * @param $page
     */
    function bookmarkPage($listKey, $page)
    {
        $bookmarkService = $this->container->get('PaginatorBookmarkService');
        $bookmarkService->setPage($listKey, $page);
    }

    /**
     * Get the bookmarked page
     *
     * @param $listKey
     * @return integer
     */
    function getBookmarkedPage($listKey)
    {
        $bookmarkService = $this->container->get('PaginatorBookmarkService');
        return $bookmarkService->getPage($listKey);
    }


    /*** Access to services  ***/

    /**
     * Get the parser to parse received parameters
     *
     * @return Parser
     */
    function getParser() {
        return $this->container->get('parser');
    }

    /**
     * Get Site Service
     *
     * @return SiteService
     */
    function getSiteService()
    {
        return $this->container->get('siteService');
    }

    /**
     * Get Site Service
     *
     * @return object
     */
    function getSiteAlertRecipientService()
    {
        return $this->container->get('siteAlertRecipientService');
    }

    /**
     * Get Contact Service
     *
     * @return object
     */
    function getContactService()
    {
        return $this->container->get('contactService');
    }

    /**
     * Get Contact Type Service
     *
     * @return ContactTypeService
     */
    function getContactTypeService()
    {
        return $this->container->get('contactTypeService');
    }

    /**
     * Get Report Service
     *
     * @return ReportService
     */
    function getReportService()
    {
        return $this->container->get('reportService');
    }

    /**
     * Get Disease Service
     *
     * @return object
     */
    function getDiseaseService()
    {
        return $this->container->get('diseaseService');
    }

    /**
     * Get Disease Value Service
     *
     * @return object
     */
    function getDiseaseValueService()
    {
        return $this->container->get('diseaseValueService');
    }

    /**
     * Get Disease Value Keyword Service
     *
     * @return object
     */
    function getDiseaseValueKeywordService()
    {
        return $this->container->get('diseaseValueKeywordService');
    }

    /**
     * Get Disease Value Service
     *
     * @return object
     */
    function getDiseaseConstraintService()
    {
        return $this->container->get('diseaseConstraintService');
    }

    /**
     * Get Threshold Service
     *
     * @return object
     */
    function getThresholdService()
    {
        return $this->container->get('thresholdService');
    }

    /**
     * Get Security Service
     *
     * @return object
     */
    function getSecurityService()
    {
        return $this->container->get('securityService');
    }

    /**
     * Get Jms Serializer
     *
     * @return object
     */
    function getJmsSerializer()
    {
        return $this->container->get('jms_serializer');
    }

    /**
     * Get Dashboard Service
     *
     * @return DashboardService
     */
    function getDashboardService()
    {
        return $this->container->get('dashboardService');
    }

    /**
     * @return IndicatorDimDateService
     */
    function getDimDateService()
    {
        return $this->container->get('IndicatorDimDateService');
    }

    /**
     * @return \AppBundle\Services\Gateway\GatewayDeviceService|object
     */
    function getGatewayDeviceService()
    {
        return $this->container->get('GatewayDeviceService');
    }

    /**
     * @return \AppBundle\Services\Gateway\GatewayQueueService|object
     */
    function getGatewayQueueService()
    {
        return $this->container->get('GatewayQueueService');
    }

    /**
     * @return \AppBundle\Services\ImportService|object
     */
    function getImportService()
    {
        return $this->container->get('ImportService');
    }



    /*** Access to translator  ***/
    /**
     * Get Translator
     *
     * @return TranslatorInterface
     */
    public function getTranslator()
    {
        return $this->container->get('translator');
    }

    /**
     * Get Logger
     *
     * @return Logger
     */
    public function getLogger()
    {
        return $this->get('logger');
    }

    /*** Access to manager  ***/

    /**
     * Get Fos User Manager
     *
     * @return object
     */
    function getUserManager()
    {
        return $this->get('fos_user.user_manager');
    }

    /***********************************/
    // Logger Method //

    function LogInfoAction($action, $infos)
    {
        $userInfos = "No informations about User";
        $actionInfos = "No informations about Action";
        $otherInfos = "No informations about Message";

        if ($this->getUser() != null) {
            $userInfos = sprintf("UserId: %1\$s, UserName: %2\$s", $this->getUser()->getId(), $this->getUser()->getUserName());
        }

        if ($action != null && $action != "") {
            $actionInfos = $action ;
        }

        if ($infos != null && $infos != "") {
            $otherInfos = $infos ;
        }

        $this->getLogger()->info("/*** Audit Trail Informations ***/");
        $this->getLogger()->info(sprintf("USER INFO - %1\$s", $userInfos));
        $this->getLogger()->info(sprintf("ACTION INFO - %1\$s", $actionInfos));
        $this->getLogger()->info(sprintf("MESSAGE INFO - %1\$s", $otherInfos));
        $this->getLogger()->info("/********************************/");

    }

    /**********************************/

    /**
     * Return Home SesDashboardSite regarding User definition
     *
     * @return null|SesDashboardSite
     */
    function getHomeSite()
    {
        $homeSite = $this->getSiteService()->getHomeSite($this->getUser());

        if ($homeSite == null) {
            $this->createAccessDeniedException();
        }

       return $homeSite;
    }

    /*** Access To parameters ***/

    /**
     * Get Argus supported locales
     *
     * @return mixed
     */
    public function getSupportedLocales()
    {
        return $this->container->getParameter('locales_supported');
    }

    /**
     * Get rScript configuration
     *
     * @return mixed
     */
    public function getRScripts()
    {
        if ($this->container->hasParameter('rscripts')) {
            return $this->container->getParameter('rscripts');
        }

        return null ;
    }

    /**
     * Get AnalysesRScripts folder
     *
     * @return mixed|null
     */
    public function getAnalysesRScripts()
    {
        if ($this->container->hasParameter('analysesRscripts')) {
            return $this->container->getParameter('analysesRscripts');
        }

        return null ;
    }


    /**
     * Get Epi first Day param
     *
     * @return mixed
     */
    function GetEpiFirstDay()
    {
        return $this->container->getParameter('epi_first_day');
    }

    function getWeeklyTimelinessMinutes()
    {
        return $this->getParameter('configuration_defaults')['sites']['weekly_timeliness_minutes'];
    }

    function getMonthlyTimelinessMinutes()
    {
        return $this->getParameter('configuration_defaults')['sites']['monthly_timeliness_minutes'];
    }

    function isAutoValidationEnabled()
    {
        $autoValidation = false ;
        $configurationDashboard = $this->getParameter('configuration_dashboard');
        if (isset($configurationDashboard) && $configurationDashboard != null) {
            $autoValidation = $configurationDashboard['auto_validation'];
        }

        return $autoValidation;
    }

    function getNbMaxNewAlert()
    {
        return $this->getParameter('configuration_alerts')['max_new_alerts'];
    }

    function getNbMaxOldAlert()
    {
        return $this->getParameter('configuration_alerts')['max_old_alerts'];
    }

    /**
     * Get Xml Site file name
     *
     * @return mixed
     */
    function getSiteFileName()
    {
        return $this->getParameter('configuration_file_names')['sites'];
    }

    /**
     * Get Xml Contact file name
     *
     * @return mixed
     */
    function getContactFileName()
    {
        return $this->getParameter('configuration_file_names')['contacts'];
    }

    /**
     * Get Xml Disease file name
     *
     * @return mixed
     */
    function getDiseaseFileName()
    {
        return $this->getParameter('configuration_file_names')['diseases'];
    }

    /**
     * Get Xml Threshold file name
     *
     * @return mixed
     */
    function getThresholdFileName()
    {
        return $this->getParameter('configuration_file_names')['thresholds'];
    }

    /**
     * Get Argus Report parameter
     *
     * @return bool|mixed
     */
    function getArgusReports()
    {
        if ($this->container->hasParameter('argus_reports')) {
            return $this->getParameter('argus_reports');
        }

        return false ;
    }

    /**
     * Get Argus Report Global parameter
     *
     * @return bool
     */
    function getArgusReportGlobal()
    {
        $argusReport = $this->getArgusReports();

        if (false === $argusReport) {
            return false;
        }

        if (isset($argusReport['global'])) {
            return $argusReport['global'];
        }

        return false;
    }

    /**
     * Get Percent decimal number for reports
     *
     * @return int
     */
    function getArgusReportPercentDecimalNumber()
    {
        $percent_decimal = 2 ;

        $argusReportGlobal = $this->getArgusReportGlobal();

        if (false === $argusReportGlobal) {
            return $percent_decimal;
        }

        if (isset($argusReportGlobal['percent_decimal']) && is_numeric($argusReportGlobal['percent_decimal'])) {
            return intval($argusReportGlobal['percent_decimal']);
        }

        return $percent_decimal;
    }

    /**
     * Return argus_reports contact_list_report parameter, otherwise false
     *
     * @return mixed
     */
    function getArgusReportContactList()
    {
        $argusReport = $this->getArgusReports();

        if (false == $argusReport) {
            return false ;
        }

        if (isset($argusReport['contact_list_report'])) {
            return $argusReport['contact_list_report'] ;
        }

        return false ;
    }

    /**
     * Return Column configuration for Contact List Report
     *
     * @return mixed
     */
    function getArgusReportContactListColumnConfig()
    {
        $argusReportContactList = $this->getArgusReportContactList();

        if (false === $argusReportContactList) {
            return false ;
        }

        if (isset($argusReportContactList['column_config'])) {
            return $argusReportContactList['column_config'];
        }

        return null ;
    }

    /**
     * @param $value
     * @param $convertAsArray
     * @return array|mixed
     */
    protected function getStringParameter($value, $convertAsArray = true) {
        return $this->getParameterValue($value, self::PARAM_DATA_TYPE_STRING, $convertAsArray);
    }

    /**
     * @param $value
     * @param $convertAsArray
     * @return array|mixed
     */
    protected function getIntegerParameter($value, $convertAsArray = true) {
        return $this->getParameterValue($value, self::PARAM_DATA_TYPE_INTEGER, $convertAsArray);
    }

    /**
     * @param $value
     * @param $convertAsArray
     * @return array|mixed
     */
    protected function getDateParameter($value, $convertAsArray = true) {
        return $this->getParameterValue($value, self::PARAM_DATA_TYPE_DATE, $convertAsArray);
    }

    /**
     * @param $value
     * @param $convertAsArray
     * @return array|mixed
     */
    protected function getBooleanParameter($value, $convertAsArray = true) {
        return $this->getParameterValue($value, self::PARAM_DATA_TYPE_BOOLEAN, $convertAsArray);
    }

    /**
     * @param $value
     * @param null $dataType
     * @param $convertAsArray <p>if the value received contains some PARAM_ARRAY_SEPARATOR</p>
     * @return array|mixed
     */
    protected function getParameterValue($value, $dataType = null, $convertAsArray = true)
    {
        if($value === null) {
            return null;
        }

        $values = [];

        if ($convertAsArray && strpos($value, self::PARAM_ARRAY_SEPARATOR) !== false) {
            $values = explode(self::PARAM_ARRAY_SEPARATOR, $value);
        }
        else {
            $values[] = $value;
        }

        $i = 0;
        foreach($values as $value) {
            if($value === self::PARAM_NULL && $dataType != self::PARAM_DATA_TYPE_STRING) {
                $values[$i] = null;
            }
            else if($value === self::PARAM_NONE && $dataType != self::PARAM_DATA_TYPE_STRING) {
                $values[$i] = DbConstant::NULL;
            }
            else if($value === self::PARAM_ALL && $dataType != self::PARAM_DATA_TYPE_STRING) {
                $values[$i] = DbConstant::NOT_NULL;
            }
            else if($dataType == self::PARAM_DATA_TYPE_STRING) {
                if($value == '' || trim($value) == '') {
                    $values[$i] = null;
                }
                else {
                    $values[$i] = $value;
                }
            }
            else if($dataType == self::PARAM_DATA_TYPE_INTEGER) {
                $values[$i] = $this->getParser()->parseInteger($value);
            }
            else if($dataType == self::PARAM_DATA_TYPE_DATE) {
                $values[$i] = $this->getParser()->parseDate($value);
            }
            else if($dataType == self::PARAM_DATA_TYPE_BOOLEAN) {
                $values[$i] = $this->getParser()->parseBoolean($value);
            }
            else {
                $values[$i] = $value;
            }

            $i++;
        }

        if(sizeof($values) == 1) {
            return $values[0];
        }

        return $values;
    }

    /**
     * Return DataTable translation in Json hierarchical format from yml
     *
     * @return array
     */
    public function getDatatablesTranslations()
    {
        /** @var Request $request */
        $request    = $this->get('request');
        /** @var Translator $translator */
        $translator = $this->get('translator');
        $locale     = $request->getLocale();

        $catalogue = $translator->getCatalogue($locale);
        $messages = $catalogue->all();
        $dataTables = $messages['datatables'];

        $result = [];

        foreach($dataTables as $key=>$value) {
            $temp = &$result;

            $split = explode('.',$key);
            if (sizeof($split) == 1) {
                $result[$key] = $value;
            } else {
                foreach($split as $s) {
                    if (!isset($temp[$s])) {
                        $temp[$s] = [];
                    }
                    $temp = & $temp[$s];
                }
                $temp = $value ;
            }
        }

        return $result;
    }

    /**
     * @param SesFullReport $fullReport
     * @return array
     */
    protected function getJsonDataStatus($fullReport)
    {
        $translator = $this->get('translator');

        return array('fullReportId' => $fullReport->getId(),
            'css' => $fullReport->getCss(),
            'status' => $translator->trans($fullReport->getStatus()),
            'partReports' => $this->getJsonDataStatusPartReport($fullReport)
        );
    }

    /**
     * @param SesFullReport $fullReport
     * @return array
     */
    protected function getJsonDataStatusPartReport($fullReport)
    {
        $translator = $this->get('translator');
        $result = [];

        /** @var SesPartReport $partReport */
        foreach($fullReport->getPartReports() as $partReport)
        {
            if ($fullReport->isAggregate())
            {
                $result[] = array(
                    'partReportId' => 0,
                    'css' => $partReport->getCss(),
                    'status' => $translator->trans($partReport->getStatus()),
                );
            }

            $result[] = [
                'partReportId' => $partReport->getId(),
                'css' => $partReport->getCss(),
                'status' => $translator->trans($partReport->getStatus()),
            ];
        }

        return $result;
    }

    protected function getJsonDataResponse($result, $message)
    {
        $translator = $this->get('translator');

        if ($result != null) {
            $response =  new JsonResponse(
                [
                    'data' => $this->getJsonDataStatus($result),
                    'message' => $translator->trans($message)
                ]
            );
        } else {
            $response =  new JsonResponse('An error occurs', JsonResponse::HTTP_NOT_IMPLEMENTED);
        }

        return $response;
    }

    protected function getEnabledReports()
    {
        // Diseases
        $diseasesWeekly = $this
            ->getDiseaseService()
            ->getDiseases(Constant::PERIOD_WEEKLY);
        $diseasesMonthly = $this
            ->getDiseaseService()
            ->getDiseases(Constant::PERIOD_MONTHLY);

        $site = $this->getSiteService()->getSiteWithoutDependencies($siteId);
        $homeSite = $this->getHomeSite();
        $permissions = $this->getUser() != null ? $this->getUser()->getDashboardPermissions() : [];

        $enableWeeklyReport = $this
            ->getSesDashboardPermissionHelper()
            ->isWeeklyReportEnabled($site, $homeSite, $permissions);
        $enableMonthlyReport = $this
            ->getSesDashboardPermissionHelper()
            ->isMonthlyReportEnabled($site, $homeSite, $permissions);

        return [
            'weekly' => (
                isset($diseasesWeekly)
                && count($diseasesWeekly) > 0
                && $enableWeeklyReport
            ),
            'monthly' => (
                isset($diseasesMonthly)
                && count($diseasesMonthly) > 0
                && $enableMonthlyReport
            )
        ];
    }
}
