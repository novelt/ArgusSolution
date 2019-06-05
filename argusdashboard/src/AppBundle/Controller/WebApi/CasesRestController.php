<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 11/26/2015
 * Time: 3:43 PM
 */

namespace AppBundle\Controller\WebApi;

use FOS\RestBundle\Controller\Annotations\Get;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\WebApi\WebApiCase;
use AppBundle\Entity\SesReportValues;


/**
 * Class SitesRestController
 * @package AppBundle\Controller\WebApi
 *
 * Web Api Controller to expose Sites
 */
class CasesRestController extends Controller
{
    public function getCasesAction()
    {
        return $this->getCasesStatusPeriodDiseaseSiteAction(null, null, null, null, null);
    }

    /**
     * GET Route annotation.
     * @Get("/cases/{status}")
     */
    public function getCasesStatusAction($status)
    {
        return $this->getCasesStatusPeriodDiseaseSiteAction($status, null, null, null, null);
    }

    /**
     * GET Route annotation.
     * @Get("/cases/{status}/{startDate}/{endDate}")
     */
    public function getCasesStatusPeriodAction($status, $startDate, $endDate)
    {
        return $this->getCasesStatusPeriodDiseaseSiteAction($status, $startDate, $endDate, null, null);
    }

    /**
     * GET Route annotation.
     * @Get("/cases/{status}/{startDate}/{endDate}/{diseaseId}")
     */
    public function getCasesStatusPeriodDiseaseAction($status, $startDate, $endDate, $diseaseId)
    {
        return $this->getCasesStatusPeriodDiseaseSiteAction($status, $startDate, $endDate, $diseaseId, null);
    }

    /**
     * GET Route annotation.
     * @Get("/cases/{status}/{startDate}/{endDate}/{diseaseId}/{siteId}")
     */
    public function getCasesStatusPeriodDiseaseSiteAction($status, $startDate, $endDate, $diseaseId, $siteId)
    {
        $caseService = $this->container->get('CaseService');
        $diseaseName = null ;

        if ($diseaseId != null) {
            $diseasesService = $this->container->get('DiseaseService');
            $disease = $diseasesService->getById($diseaseId);
            $diseaseName = $disease->getDisease();
        }

        // TODO : Creer une classe spécifique pour exposer les Object via Web Apis
        // https://packagist.org/packages/bcc/auto-mapper-bundle

        $cases = $caseService->getAll($status, $startDate, $endDate, $diseaseName, $siteId ) ;
        return array('cases' => $this->mappCases($cases));
    }

    private function mappCases($cases)
    {
        $diseasesService = $this->container->get('DiseaseService');
        $diseases = $diseasesService->getAll() ;

        $results = array();

        foreach($cases as $c)
        {
            $wc = new WebApiCase() ;
            $wc->id = $c->getId();
            $wc->name = $c->getKey() ;
            $wc->value = $c->getValue() ;

            if ($c->getReport() != null)
            {
                $report = $c->getReport() ;

                $wc->diseaseReference = $report->getDisease() ;

                $disease = $this->getDiseaseByName($diseases, $wc->diseaseReference );
                if ($disease != null) {
                    $wc->diseaseId = $disease->getId();
                    $wc->diseaseName = $disease->getName();

                    $diseaseValue = $this->getDiseaseValueByName($disease, $wc->name );

                    if ($diseaseValue != null) {
                        $wc->diseaseValueId = $diseaseValue->getId();
                        $wc->diseaseValueReference = $diseaseValue->getValue();
                    }
                }

                $wc->receptionDate = $report->getReceptionDate() ;

                if ($report->getPartReport() != null)
                {
                    $partReport = $report->getPartReport() ;
                    $wc->status =  $partReport->getStatus() ;

                    if ($partReport->getFullReport() != null)
                    {
                        $fullReport = $partReport->getFullReport() ;
                        $wc->startDate = $fullReport->getStartDate() ;
                        $wc->weekNumber = $fullReport->getWeekNumber() != "" ? $fullReport->getWeekNumber() : null ;
                        $wc->monthNumber = $fullReport->getMonthNumber() != "" ? $fullReport->getMonthNumber() : null ;

                        if ($fullReport->getFrontLineGroup() != null)
                        {
                            $sesSite = $fullReport->getFrontLineGroup() ;
                            $wc->siteId = $sesSite->getId() ;
                            $wc->siteName = $sesSite->getName() ;
                            $wc->latitude = $sesSite->getLatitude() ;
                            $wc->longitude = $sesSite->getLongitude() ;
                        }
                    }
                }
            }

            // Remove cases without lat long
            if ($wc->latitude != null && $wc->longitude != null) {
                $results[] = $wc;
            }
        }

        return $results ;
    }

    private function getDiseaseByName($diseases, $disease)
    {
        foreach ($diseases as $d)
        {
            if ($d->getDisease() == $disease)
                return $d ;
        }
    }

    private function getDiseaseValueByName($disease, $diseaseValueName)
    {
        foreach ($disease->getDiseaseValues() as $dv) {
            if ($dv->getValue() == $diseaseValueName)
                return $dv;
        }

    }
}