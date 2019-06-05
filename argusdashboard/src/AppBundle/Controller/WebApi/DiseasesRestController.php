<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 11/26/2015
 * Time: 3:43 PM
 */

namespace AppBundle\Controller\WebApi;

use AppBundle\Controller\BaseController;
use AppBundle\Entity\SesDashboardDisease;
use AppBundle\Services\DiseaseService;
use AppBundle\Entity\WebApi\WebApiDisease;
use AppBundle\Entity\WebApi\WebApiDiseaseValues;
use AppBundle\Entity\Constant;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class DiseasesRestController
 * @package AppBundle\Controller\WebApi
 *
 * Web Api Controller to expose Diseases
 */
class DiseasesRestController extends BaseController
{
    const I18N_DOMAIN = "business";

    public function getDiseasesAction()
    {
        /** @var DiseaseService $diseaseService */
        $diseaseService = $this->getDiseaseService();
        $diseases = $diseaseService->getDiseases(null, false, null, null) ;

        // Creer une classe spécifique pour exposer les Object via Web Apis
        // https://packagist.org/packages/bcc/auto-mapper-bundle

        return array('diseases' => $this->mappDiseases($diseases));
    }

    public function getDiseaseAction($id)
    {
        /** @var DiseaseService $diseaseService */
        $diseaseService = $this->getDiseaseService();
        $diseases[] = $diseaseService->getById($id) ;

        return array('diseases' => $this->mappDiseases($diseases));
    }

    /*
     * Convert SesDashboardDisease & values into WebAPi entities
     * AND Filter on Disease 'ALERT'
     */

    /**
     * @param $diseases SesDashboardDisease[]
     * @return array
     */
    private function mappDiseases($diseases)
    {
        $results = array();

        /* @var $translator TranslatorInterface */
        $translator = $this->container->get('translator');

        foreach($diseases as $d)
        {
            // We don't want to provide Disease 'ALERT'
            if ($d->getDisease() == Constant::DISEASE_ALERT)
            {
                continue;
            }

            $wd = new WebApiDisease() ;
            $wd->id = $d->getId();
            $wd->name = $d->getName();
            $wd->translatedName = $translator ->trans('disease.'.$d->getPrimaryKeyword(), array(), self::I18N_DOMAIN);
            $wd->disease = $d->getDisease();
            $wd->diseaseValues = array();

            foreach($d->getDiseaseValues() as $dv)
            {
                $wdv = new WebApiDiseaseValues();
                $wdv->id = $dv->getId();
                $wdv->value = $dv->getValue();
                $wdv->name = $dv->getFormatValue();
                $wdv->translatedName = $translator ->trans('diseaseValue.'.$dv->getPrimaryKeyword(), array(), self::I18N_DOMAIN);
                $wdv->period = $dv->getPeriod();

                $wd->diseaseValues[] = $wdv;
            }

            $results[] = $wd;
        }

        return $results ;
    }
}