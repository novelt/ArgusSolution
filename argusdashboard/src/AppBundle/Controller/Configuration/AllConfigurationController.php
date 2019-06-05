<?php
/**
 * All Configuration Controller
 *
 * @author François Cardinaux
 */

namespace AppBundle\Controller\Configuration;

use AppBundle\AppBundle;
use AppBundle\Controller\BaseController;
use AppBundle\Form\AnyListXmlSavingType;
use AppBundle\Entity\Import\Import;
use AppBundle\Entity\SesDashboardSite;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;



class AllConfigurationController extends BaseController
{

    public function getTranslationsAction()
    {
        return new JsonResponse($this->getDatatablesTranslations());
    }

    public function saveListsToXMLAction(Request $request) {
        $successMessage = false;

        $filePath = $this->getParameter('configuration_file_path');
        $form = $this->createForm(AnyListXmlSavingType::class, null, array('configuration_file_path' => $filePath));

        $form->handleRequest($request);

        if ($form->isValid()) {
            if ( $form->get('cancel')->isClicked() ) {
                throw new \Exception('Unexpected behavior: no cancel button on this page.');
            }

            $data = $form->getData();

            $path = $data['path'];

            $path = realpath($path); // Removes any possible trailing slash.

            if ( ! $path ) {

                $form->get('path')->addError(new FormError('The path is invalid.'));

            } else if (  ! is_writable($path) ) {

                $form->get('path')->addError(new FormError('The path is not writable.'));

            } else {

                $serializer = $this->getJmsSerializer();
                $import = new Import();
                $fileNames = $this->getParameter('configuration_file_names');
                $successMessage = 'All list have been saved successfully.';

                // Sites

                $siteService = $this->getSiteService();
                $sites = $siteService->getAll();
                foreach ($sites as $key => $site) {
                    if (SesDashboardSite::ROOT_REFERENCE === $site->getReference()) {
                        unset($sites[$key]);
                        break;
                    }
                }
                $import->cleanEntities();
                $import->setSiteEntities($sites);
                $xml = $serializer->serialize($import, 'xml');
                file_put_contents($path.'/'.$fileNames['sites'], $xml);

                // Contacts

                $contactService = $this->getContactService();
                $contacts = $contactService->getAll();
                $import->cleanEntities();
                $import->setContactEntities($contacts);
                $xml = $serializer->serialize($import, 'xml');
                file_put_contents($path.'/'.$fileNames['contacts'], $xml);

                // Diseases

                $diseaseService = $this->getDiseaseService();
                $diseases = $diseaseService->getAll();
                $import->cleanEntities();
                $import->setDiseaseEntities($diseases);
                $xml = $serializer->serialize($import, 'xml');
                file_put_contents($path.'/'.$fileNames['diseases'], $xml);

                // Thresholds

                $thresholdService = $this->getThresholdService();
                $thresholds = $thresholdService->getAll();
                $import->cleanEntities();
                $import->setThresholdEntities($thresholds);
                $xml = $serializer->serialize($import, 'xml');
                file_put_contents($path.'/'.$fileNames['thresholds'], $xml);
            }

        }

        return $this->render(
            'configuration/save_any_to_xml.html.twig',
            array(
                'form' => $form->createView(),
                'success' => $successMessage,
                'titleKey' => 'Configuration.Titles.AllListsXmlSave',
                'actionPathKey' => 'configuration_save_all_to_xml',
                'cancelOrReturnPath' => false));
    }
}