<?php
/**
 * User: mvirtanen
 * Date: 2/23/2016
 */
namespace AppBundle\Controller;

use AppBundle\Entity\Import\Configuration\Roles;
use AppBundle\Entity\Import\Configuration\Users;
use AppBundle\Utils\Response\XmlResponse;
use AppBundle\Services\SecurityService;
use AppBundle\Entity\Security\SesDashboardUser;
use AppBundle\Entity\Security\SesDashboardRole;
use AppBundle\Entity\Security\SesDashboardPermission;
use AppBundle\Form\UserType;
use AppBundle\Form\UserEditType;
use AppBundle\Form\RoleType;
use AppBundle\Form\PermissionType;
use AppBundle\Form\AnyListXmlLoaderType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;

use JMS\Serializer\SerializationContext;



/**
 * Controller used to manage dashboard users
 *
 * @Route("/admin")
 */
class AdminController extends BaseController
{
    /****** USERS ************/

    /**
     * @Route("/users", name="admin_user_list")
     */
    public function usersAction()
    {
        return $this->render('admin/user/users.html.twig', ['users' => $this->getUsers()]);
    }

    /**
     * @Route("/user/{id}/edit", name="admin_user_edit")
     * @param SesDashboardUser $user
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function userEditAction(SesDashboardUser $user)
    {
        $form = $this->createForm(new UserEditType($this->getSupportedLocales()), $user);

        return $this->render('admin/user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    /**
     * @Route("/user/new", name="admin_user_new")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function userAddAction()
    {
        $user = new SesDashboardUser();
        $user->setEnabled(true);

        $form = $this->createForm(new UserType($this->getSupportedLocales()), $user, array('validation_groups'=>'Registration'));

        return $this->render('admin/user/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/user/create", name="admin_user_create")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function userCreateAction(Request $request)
    {
        $user = new SesDashboardUser();
        $form = $this->createForm(new UserType($this->getSupportedLocales()), $user, array('validation_groups'=>'Registration'));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->getUserManager()->updateUser($user);

            $this->addFlash(
                'notice',
                $this->getTranslator()->trans('User.Created',
                                                array('%userName%' => $user->getUsername()),
                                                'security')
            );

            return $this->redirect($this->generateUrl('admin_user_list'));
        }

        return $this->render('admin/user/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/user/{id}/update", name="admin_user_update")
     * @param SesDashboardUser $user
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function userUpdateAction(SesDashboardUser $user, Request $request)
    {
        $form = $this->createForm(new UserEditType($this->getSupportedLocales()), $user);
        //$form = $this->createForm(new UserEditType($this->getSupportedLocales()), $user, array('validation_groups'=>'Registration'));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getUserManager()->updateUser($user);

            $this->addFlash(
                'notice',
                $this->getTranslator()->trans('User.Updated',
                                            array('%userName%' => $user->getUsername()),
                                            'security')
            );

            return $this->redirect($this->generateUrl('admin_user_list'));
        }

        return $this->render('admin/user/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/user/{id}/delete", name="admin_user_delete")
     * @param SesDashboardUser $user
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function userDeleteAction(SesDashboardUser $user)
    {
        $this->getSecurityService()->deleteUser($user);
        $this->addFlash(
            'notice',
            $this->getTranslator()->trans('User.Deleted', array('%userName%' => $user->getUsername()), 'security')
        );

        return $this->redirect($this->generateUrl('admin_user_list'));
    }

    /**
     * @Route("/users/load-from-xml", name="admin_user_load")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function userLoadListFromXMLAction(Request $request)
    {
        $form = $this->createForm(AnyListXmlLoaderType::class, null, array('file_field_label' => 'Users (XML file)'));
        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('admin_user_list');
            }

            $errors = [];

            try {
                $newData = $form->getData();
                $fileNameAndPath = implode('/', array($newData['file']->getPath(), $newData['file']->getBaseName()));
                $xml = file_get_contents($fileNameAndPath);
                $users = null;

                if ($xml) {
                    /** @var $serializer */
                    $serializer = $this->getJmsSerializer();
                    /** @var Users $response */
                    $response = $serializer->deserialize($xml, Users::class, 'xml');
                    $users = $response->getDashboardUsers();
                }

                if ($users) {
                    /** @var SecurityService $securityService */
                    $securityService = $this->getSecurityService();
                    $errors = $securityService->importUsers($users);

                } else {
                    $errors[] = new FormError("No Users found in this file");
                }

            } catch (\Exception $exception) {
                $errors[] = new FormError($exception->getCode() . ': ' . $exception->getmessage());
            }

            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $form->addError($error);
                }
            } else {
                return $this->redirectToRoute('admin_user_list');
            }

        }

        return $this->render(
            'admin/user/load_from_xml.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * @Route("/users/save-to-xml", name="admin_user_save")
     *
     * @return XmlResponse
     */
    public function userSaveListFromXMLAction()
    {
        $serializer = $this->getJmsSerializer();
        $users = $this->getUserManager()->findUsers();

        $userList = new Users();
        $userList->setDashboardUsers($users);
        // User Serialization without permissions
        $xml = $serializer->serialize($userList, 'xml', SerializationContext::create()->setGroups(array('Default')));

        $response = new XmlResponse($xml, 200);
        $response->setFilename("users.xml");

        return $response;
    }

    private function getUsers()
    {
        $result = [];
        $users = $this->getUserManager()->findUsers();

        /** @var SesDashboardUser $user */
        foreach ($users as $user) {
            if ($user->getSite() != null) {
                $user->setSiteName($user->getSite()->getName());
            }
            $result[] = $user;
        }

        return $result;
    }

    /****** ROLES ************/

    /**
     * @Route("/roles", name="admin_role_list")
     */
    public function rolesAction()
    {
        return $this->render('admin/role/roles.html.twig', ['roles' => $this->getSecurityService()->getDashboardRoles()]);
    }

    /**
     * @Route("/role/new", name="admin_role_new")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function roleAddAction()
    {
        $role = new SesDashboardRole();
        $form = $this->createForm(new RoleType(), $role);

        return $this->render('admin/role/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/role/{id}/edit", name="admin_role_edit")
     * @param SesDashboardRole $role
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function roleEditAction(SesDashboardRole $role)
    {
        $form = $this->createForm(new RoleType(), $role);

        return $this->render('admin/role/edit.html.twig', [
            'form' => $form->createView(),
            'role' => $role
        ]);
    }

    /**
     * @Route("/role/create", name="admin_role_create")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function roleCreateAction(Request $request)
    {
        $role = new SesDashboardRole();
        $form = $this->createForm(new RoleType(), $role);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->getSecurityService()->createDashboardRole($role);

            $this->addFlash(
                'notice',
                $this->getTranslator()->trans('Role.Created',
                                            array('%roleName%' => $role->getName()),
                                            'security')
            );

            return $this->redirect($this->generateUrl('admin_role_list'));
        }
    }

    /**
     * @Route("/role/{id}/update", name="admin_role_update")
     *
     * @param SesDashboardRole $role
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function roleUpdateAction(SesDashboardRole $role, Request $request)
    {
        $form = $this->createForm(new RoleType(), $role);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->getSecurityService()->update() ;

            $this->addFlash(
                'notice',
                $this->getTranslator()->trans('Role.Updated',
                                            array('%roleName%' => $role->getName()),
                                            'security')
            );

            return $this->redirect($this->generateUrl('admin_role_list'));
        }
    }


    /**
     * @Route("/role/{id}/delete", name="admin_role_delete")
     * @param SesDashboardRole $role
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function roleDeleteAction(SesDashboardRole $role){
        $this->getSecurityService()->deleteDashboardRole($role) ;
        $this->addFlash(
            'notice',
            $this->getTranslator()->trans('Role.Deleted', array('%roleName%' => $role->getName()), 'security')
        );

        return $this->redirect($this->generateUrl('admin_role_list'));
    }

    /**
     * @Route("/roles/load-from-xml", name="admin_role_load")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function roleLoadListFromXMLAction(Request $request)
    {
        $form = $this->createForm(AnyListXmlLoaderType::class, null, array('file_field_label' => 'Roles (XML file)'));
        $form->handleRequest($request);

        if ($form->isValid()) {
            if ( $form->get('cancel')->isClicked() ) {
                return $this->redirectToRoute('admin_role_list');
            }

            $errors = [];

            try {
                $newData = $form->getData();
                $fileNameAndPath = implode('/', array($newData['file']->getPath(), $newData['file']->getBaseName()));
                $xml = file_get_contents($fileNameAndPath);
                $roles = null;

                if ($xml) {
                    /** @var $serializer */
                    $serializer = $this->getJmsSerializer();
                    /** @var Roles $response */
                    $response = $serializer->deserialize($xml, Roles::class, 'xml');
                    $roles = $response->getDashboardRoles();
                }

                if ($roles) {
                    /** @var SecurityService $securityService */
                    $securityService = $this->getSecurityService();
                    $errors = $securityService->importRoles($roles);

                } else {
                    $errors[] = new FormError("No Roles found in this file");
                }

            } catch (\Exception $exception) {
                $errors[] = new FormError($exception->getCode().': '.$exception->getmessage());
            }

            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $form->addError($error);
                }
            } else {
                return $this->redirectToRoute('admin_role_list');
            }
        }

        return $this->render(
            'admin/role/load_from_xml.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * @Route("/roles/save-to-xml", name="admin_role_save")
     *
     * @return XmlResponse
     */
    public function roleSaveListFromXMLAction()
    {
        $serializer = $this->getJmsSerializer();
        /** @var SecurityService $securityService */
        $securityService = $this->getSecurityService();
        $roles = $securityService->getDashboardRoles();

        $roleList = new Roles();
        $roleList->setDashboardRoles($roles);
        $xml = $serializer->serialize($roleList, 'xml', SerializationContext::create()->setGroups(array('Default', 'permissions')));

        $response = new XmlResponse($xml, 200);
        $response->setFilename("roles.xml");

        return $response;
    }


    /****** PERMISSIONS ************/

    /**
     * @Route("/role/{id}/permissions", name="admin_permissions_list")
     * @param SesDashboardRole $role
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function permissionsAction(SesDashboardRole $role){
        return $this->render('admin/permissions/permissions.html.twig', ['permissions' => $role->getDashboardPermissions(),
                                                                        'role' => $role]);
    }

    /**
     * @Route("/role/{id}/permission/new", name="admin_permission_new")
     * @return \Symfony\Component\HttpFoundation\Response
     * @param SesDashboardRole $role
     */
    public function permissionAddAction(SesDashboardRole $role){
        $data = ['roleId' => $role->getId(),
                'level' => 0];
        $form = $this->createForm(new PermissionType(), $data);

        return $this->render('admin/permissions/new.html.twig', [
            'form' => $form->createView(),
            'role' => $role
        ]);
    }

    /**
     * @Route("/role/{id}/permission/create", name="admin_permission_create")
     * @param SesDashboardRole $role
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function permissionCreateAction(SesDashboardRole $role, Request $request){

        $permission = new SesDashboardPermission();
        $form = $this->createForm(new PermissionType(), $permission);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $role->addDashboardPermission($permission);
            $this->getSecurityService()->createDashboardPermission($permission);

            $this->addFlash(
                'notice',
                $this->getTranslator()->trans('Permission.Created', array(), 'security')
            );
        }

        return self::permissionsAction($role);
    }

    /**
     * @Route("/role/{id}/permission/{permissionId}/delete", name="admin_permission_delete")
     * @param SesDashboardRole $role
     * @param integer $permissionId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function permissionDeleteAction(SesDashboardRole $role, $permissionId){

        $this->getSecurityService()->deleteDashboardPermission($permissionId);
        $this->addFlash(
            'notice',
            $this->getTranslator()->trans('Permission.Deleted', array(), 'security')
        );

        return self::permissionsAction($role);
    }
}