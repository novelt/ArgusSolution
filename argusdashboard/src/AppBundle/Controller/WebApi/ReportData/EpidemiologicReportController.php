<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 19/04/2018
 * Time: 17:31
 */

namespace AppBundle\Controller\WebApi\ReportData;


use AppBundle\Controller\BaseController;

use AppBundle\Entity\Constant;
use AppBundle\Entity\Security\SesDashboardUser;
use AppBundle\Entity\SesDashboardSite;
use AppBundle\Services\DashboardService;
use AppBundle\Utils\Epidemiologic;
use FOS\RestBundle\Controller\Annotations\Get;

/**
 * Web APi used to provide an API to download the epidemiologic report from the Angular app
 *
 * Class ReportDataRestController
 * @package AppBundle\Controller\WebApi\ReportData
 */
class EpidemiologicReportController extends BaseController
{
    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * * @Get("/weeklyEpiReportDetails/{siteId}/{year}/{weekNumber}")
     */
    public function getWeeklyEpidemiologicReportUrlAction($siteId = null, $year = null, $weekNumber = null)
    {
        /** @var SesDashboardUser $user */
        $user = $this->getUser();

        /** @var SesDashboardSite $ite */
        $siteId = $this->getIntegerParameter($siteId);
        $year = $this->getIntegerParameter($year);
        $weekNumber = $this->getIntegerParameter($weekNumber);

        if ($siteId == null) {
            $site = $this->getHomeSite();
        } else {
            $site = $this->getSiteService()->find($siteId);
        }

        $period = Constant::PERIOD_WEEKLY;

        $today = new \DateTime();
        $epi = Epidemiologic::Timestamp2Epi($today->getTimestamp(), $this->GetEpiFirstDay());

        if ($year == null) {
            $year = $epi['Year'];
        }

        if ($weekNumber == null) {
            $weekNumber = $epi['Week'];
        }

        /** @var DashboardService $dashboardService */
        $dashboardService = $this->getDashboardService();

        $translator = $this->getTranslator();
        $autoValidationEnabled = $this->isAutoValidationEnabled();

        return $dashboardService->createDashboardJsonFile($user, $site, $period, $weekNumber, 0, $year, $autoValidationEnabled, $translator);
    }
}