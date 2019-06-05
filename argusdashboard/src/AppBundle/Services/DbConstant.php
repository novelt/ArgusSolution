<?php

/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 7/22/2015
 * Time: 9:55 AM
 */

namespace AppBundle\Services;

use AppBundle\Entity\SesDashboardDisease;
use AppBundle\Entity\SesFullReport;
use AppBundle\Entity\SesReport;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;


class DbConstant
{
    private $nbDiseasePerPeriod = array();
    private $nbDiseaseValuesPerPeriod = array();
    private $diseases = array() ;
    private $epiFirstDay = 1;
    private $minutesBeforeRejectingReport = 1440; // 24 hours

    /**
     * Used to buid doctrine requests, to differentiate the null and IS NULL values
     */
    const NULL = "DbConstantIsNull"; //do not write here "NULL" or something else that could be wrongly interpreted
    const NOT_NULL = "DbConstantIsNotNull";

    // private $siteLevel = array();
    // private $sitePath = array();

    public function __construct($epiFirstDay, $minutes_before_rejecting_report)
    {
        $this->epiFirstDay = $epiFirstDay;
        $this->minutesBeforeRejectingReport = $minutes_before_rejecting_report ;
    }

    /**
     * @param LifecycleEventArgs $args
     *
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em =  $args->getEntityManager();

        if ($entity instanceof SesFullReport) {
            // Set Epidemiologic first day of week to calculate Week Number
            $entity->setEpiFirstDay($this->epiFirstDay);
            $entity->setNbOfDisease($this->getNumberOfDisease($entity->getPeriod(), $em));
            $entity->setNbOfDiseaseValues($this->getNumberOfDiseaseValues($entity->getPeriod(), $em));
            $entity->setNbMinutesBeforeRejectingReport($this->minutesBeforeRejectingReport) ;
        }
        else if ($entity instanceof SesReport){
            // Set Disease
            $entity->setDiseaseEntity($this->getDiseaseFromDiseaseReference($entity->getDisease(), $em));
        }
    }

    private function getNumberOfDisease($period, EntityManager $em, $includeAlerts = true)
    {
        if (array_key_exists($period,$this->nbDiseasePerPeriod)) {
            return $this->nbDiseasePerPeriod[$period];
        }

        $repository = $em->getRepository('AppBundle:SesDashboardDisease');
        $diseases = $repository->findDiseases($period, $includeAlerts, null, null);

        if ($diseases != null) {
            $this->nbDiseasePerPeriod[$period] = count($diseases);
        }

        return $this->nbDiseasePerPeriod[$period];
    }

    private function getNumberOfDiseaseValues($period, EntityManager $em, $includeAlerts = true)
    {
        if (array_key_exists($period, $this->nbDiseaseValuesPerPeriod)) {
            return $this->nbDiseaseValuesPerPeriod[$period];
        }

        $repository = $em->getRepository('AppBundle:SesDashboardDisease');
        $nbDiseases = $repository->findDiseaseValues($period, $includeAlerts = true);

        if ($nbDiseases != null) {
            $this->nbDiseaseValuesPerPeriod[$period] = $nbDiseases;
        }

        return $nbDiseases;
    }

    private function getDiseaseFromDiseaseReference($diseaseReference, EntityManager $em)
    {
        if (isset($this->diseases) && count($this->diseases) > 0 && array_key_exists($diseaseReference,$this->diseases )) {
            /** @var SesDashboardDisease $disease */
            return $this->diseases[$diseaseReference];
        }

        if (count($this->diseases) == 0) {
            $repository = $em->GetRepository('AppBundle:SesDashboardDisease');
            $diseases = $repository->findAll();
            /** @var SesDashboardDisease $disease */
            foreach ($diseases as $disease) {
                $this->diseases[$disease->getDisease()] = $disease;
            }
        }

        if (array_key_exists($diseaseReference, $this->diseases)) {
            return $this->diseases[$diseaseReference];
        }

        return null ;
    }
}