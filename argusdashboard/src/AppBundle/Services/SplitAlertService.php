<?php

/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 7/22/2015
 * Time: 9:55 AM
 */

namespace AppBundle\Services;

use AppBundle\Entity\SesAlert;
use AppBundle\Entity\Constant;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;


class SplitAlertService
{

    private $alertDisease = null ;
    /**
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em =  $args->getEntityManager();

        if (!$entity instanceof SesAlert) {
            return;
        }

        $this->SplitAlertMessageData($entity, $em);

    }

    private function SplitAlertMessageData($entity, EntityManager $em)
    {
        // Retrieve Alerts Values from database
        if (null === $this->alertDisease)
        {
            $repository = $em->getRepository('AppBundle:SesDashboardDisease');
            $this->alertDisease = $repository->findAlertDiseaseWithValues();

            if (null === $this->alertDisease )
            {
                // TODO Log Error No Disease with Disease = ALERT
                return ;
            }
        }

        //Split Message with variables
        $message = $entity->getMessage() ;

        foreach ($this->alertDisease->getDiseaseValues()  as $dValue)
        {
            $index = stripos($message, $dValue->getValue()."=");

            if ($index !== false) // found it
            {
                if (false === stripos($message, ',',$index )) {
                    $alert = substr($message,$index);
                }
                else {
                    $alert = substr($message,$index, stripos($message, ',',$index ) - $index);
                }

                $alert = str_replace($this->alertDisease->getDisease(),'',$alert);
                $split = explode("=",$alert) ;
                if (isset($split[0])){
                    $split[0] = str_replace('_','',$split[0]);
                    $split[0] = str_replace('-','',$split[0]);
                }

                $entity->addMessage($split);
            }
        }

    }
}