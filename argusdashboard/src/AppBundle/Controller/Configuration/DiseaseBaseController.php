<?php
/**
 * Disease Value Controller
 *
 * @author François Cardinaux
 */

namespace AppBundle\Controller\Configuration;

use AppBundle\AppBundle;
use AppBundle\Controller\BaseController;
use AppBundle\Form\DiseaseValueInsertionType;
use AppBundle\Form\DiseaseValueEditionType;
use AppBundle\Form\AnyListXmlLoaderType;
use AppBundle\Form\AnyListXmlSavingType;
use AppBundle\Entity\SesDashboardDiseaseValue;
use AppBundle\Entity\Import\Import;
use Symfony\Component\HttpFoundation\Request;
// use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Form\FormError;



class DiseaseBaseController extends BaseController
{

    protected function getDiseaseEntity($diseaseId)
    {
        $diseaseService = $this->getDiseaseService();
        return $diseaseService->getById($diseaseId);
    }

    protected function getDiseaseValueEntity($valueId)
    {
        $service = $this->getDiseaseValueService();
        return $service->getById($valueId);
    }
}