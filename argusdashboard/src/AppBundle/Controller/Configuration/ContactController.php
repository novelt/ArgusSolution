<?php
/**
 * Contact Controller
 *
 * @author François Cardinaux
 */

namespace AppBundle\Controller\Configuration;


use AppBundle\Controller\BaseController;
use AppBundle\Entity\Constant;
use AppBundle\Entity\SesDashboardSite;
use AppBundle\Form\ContactInsertionType;
use AppBundle\Form\ContactEditionType;
use AppBundle\Form\AnyListXmlLoaderType;
use AppBundle\Entity\SesDashboardContact;
use AppBundle\Entity\Import\Import;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Services\ContactService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;
use AppBundle\Utils\Response\XmlResponse;
use AppBundle\Utils\Response\CsvResponse;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class ContactController extends BaseController
{
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
     * Contact List Action
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws NotFoundHttpException
     */
    public function listAction()
    {
        return $this->render('configuration/contact/list.html.twig');
    }

    /**
     * Build Datatable structure
     *
     * @return JsonResponse
     */
    public function datatableAction()
    {
        /** @var ContactService $contactService */
        $contactService = $this->getContactService();
        $contacts = $contactService->getAllContactsArray();
        $contactsCount = count($contacts);

        $datatableDatas = [];
        $datatableDatas["draw"] = 1;
        $datatableDatas["recordsTotal"] = $contactsCount;
        $datatableDatas["recoredFiltered"] = $contactsCount;
        $datatableDatas["data"] = [];

        foreach ($contacts as $contact) {
            $datatableDatas['data'][] = [
                "dt_contact_id" => $contact["id"],
                "dt_contact_site_ref" => (isset($contact['site'])? $contact['site']['reference'] : ""),
                "dt_contact_phone" => $contact["phoneNumber"],
                "dt_contact_name" => $contact["name"],
                "dt_contact_email" => $contact["email"],
                "dt_contact_imei1" => $contact["imei"],
                "dt_contact_imei2" => $contact["imei2"],
                "dt_contact_alert_pref_gateway" => $contact["alertPreferredGateway"],
                "dt_contact_note" => $contact["note"],
                "dt_contact_deleted" => $contact["isDeleted"],
            ];
        }

        return new JsonResponse($datatableDatas);
    }


    /**
     * Contact Insertion Action
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addAction(Request $request)
    {
        $contactEntity = new SesDashboardContact();
        $form = $this->createForm(new ContactInsertionType($this->getSupportedLocales()), $contactEntity);
        $em = $this->getManager();

        if ($request->query->get('siteId') !== null) {
            $siteId = $request->query->get('siteId');
            $site = $em->getRepository("AppBundle:SesDashboardSite")->find($siteId);

            if ($site && $site instanceof SesDashboardSite) {
                $form->get('site')->setData($site);
            }
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contactEntity->setEnabled(Constant::JMS_YES);

            $em->persist($contactEntity);
            $em->flush();

            return $this->redirectToRoute('configuration_contacts');
        }

        return $this->render('configuration/contact/new.html.twig', array('form' => $form->createView()));
    }

    /**
     * Contact Edition Action
     *
     * @ParamConverter("contact", class=SesDashboardContact::class, options={"id" = "contactId"})
     * @param SesDashboardContact $contact
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(SesDashboardContact $contact, Request $request)
    {
        $form = $this->createForm(new ContactEditionType($this->getSupportedLocales()), $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($contact);
            $em->flush();

            return $this->redirectToRoute('configuration_contacts');
        }

        return $this->render(
            'configuration/contact/edit.html.twig',
            [
                'contactId' => $contact->getId(),
                'contactIsDisabled' => $contact->getDeleted(),
                'form' => $form->createView()
            ]
        );
    }

    /**
     * Contact Enabling or Disabling Action
     *
     * @ParamConverter("contact", class=SesDashboardContact::class, options={"id" = "contactId"})
     * @param SesDashboardContact $contact
     * @return JsonResponse
     */
    public function enableOrDisableAction(SesDashboardContact $contact)
    {
        $em = $this->getManager();
        $contact->setDeleted(!$contact->getDeleted());

        $em->persist($contact);
        $em->flush();

        return new JsonResponse(
            ["returnUrl" => $this->generateUrl('configuration_contacts')],
            JsonResponse::HTTP_OK
        );
    }

    /**
     * Remove contact from DB
     *
     * @ParamConverter("contact", class=SesDashboardContact::class, options={"id" = "contactId"})
     * @param SesDashboardContact $contact
     * @return JsonResponse
     */
    public function removeAction(SesDashboardContact $contact)
    {
        $em = $this->getManager();
        $em->remove($contact);
        $em->flush();

        return new JsonResponse(
            ["returnUrl" => $this->generateUrl('configuration_contacts')],
            JsonResponse::HTTP_OK
        );
    }

    public function loadListFromXMLAction(Request $request)
    {
        $form = $this->createForm(AnyListXmlLoaderType::class, null, array('file_field_label' => 'Contacts (XML file)'));
        $form->handleRequest($request);

        if ($form->isValid()) {
            if ( $form->get('cancel')->isClicked() ) {

                return $this->redirectToRoute('configuration_contacts');
            }

            try {

                $newData = $form->getData();
                $fileNameAndPath = implode('/', array($newData['file']->getPath(), $newData['file']->getBaseName()));
                $xml = file_get_contents($fileNameAndPath);
                $contacts = null;

                if ($xml) {
                    $serializer = $this->getJmsSerializer();
                    $response = $serializer->deserialize($xml, 'AppBundle\\Entity\\Import\\Import', 'xml');
                    $contacts = $response->getContacts();
                }

                if ($contacts) {
                    /** @var ContactService $contactService */
                    $contactService = $this->getContactService();

                    $saveContacts = [];
                    // Then, persists all new contacts
                    /** @var SesDashboardContact $contact */
                    foreach($contacts->getDashboardContacts() as $contact) {
                        // Check if Contact already exist or not (check on phoneNumber)
                        /** @var SesDashboardContact $contactAlreadyExist */
                        $contactAlreadyExist = $contactService->getByPhoneNumber($contact->getPhoneNumber());
                        $site = $this->getSiteService()->findOneBy(array('reference' => $contact->getSiteReference()));
                        $contactType = $this->getContactTypeService()->findOneBy(array('reference' => $contact->getContactTypeReference()));

                        if (! $contactAlreadyExist) {
                            if ($site != null) {
                                $contact->setSite($site);
                            }
                            $contact->setContactType($contactType);

                            $saveContacts[] = $contact;

                        } else {
                            $contactAlreadyExist->setContactType($contactType);
                            $contactAlreadyExist->setEmail($contact->getEmail());
                            $contactAlreadyExist->setDeleted($contact->getDeleted());
                            $contactAlreadyExist->setEnabled($contact->getEnabled());
                            $contactAlreadyExist->setImei($contact->getImei());
                            $contactAlreadyExist->setImei2($contact->getImei2());
                            $contactAlreadyExist->setName($contact->getName());
                            $contactAlreadyExist->setNote($contact->getNote());
                            if ($site != null) {
                                $contactAlreadyExist->setSite($site);
                            }

                            $saveContacts[] = $contactAlreadyExist;
                        }
                    }

                    $contactService->saveContacts($saveContacts);

                    return $this->redirectToRoute('configuration_contacts');
                }

            } catch (\Exception $exception) {
                $form->addError(new FormError($exception->getCode().': '.$exception->getmessage()));
            }
        }

        return $this->render(
            'configuration/contact/load_from_xml.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Export Contact Xml configuration file
     *
     * @return \Symfony\Component\HttpFoundation\Response;
     */
    public function saveListToXMLAction()
    {
        $serializer = $this->getJmsSerializer();
        $contactService = $this->getContactService();
        $contacts = $contactService->getAll();

        $import = new Import();
        $import->setContactEntities($contacts);
        $xml = $serializer->serialize($import, 'xml');

        $response = new XmlResponse($xml, 200);
        $response->setFilename(self::getContactFileName());

        return $response;
    }

    public function saveListToCSVAction()
    {
        $contactService = $this->getContactService();
        $data = $contactService->getContactForCsvExport();
        $response = new CsvResponse($data, 200, SesDashboardContact::getHeaderCsvRow());
        $response->setFilename("contacts.csv");

        return $response ;
    }

}