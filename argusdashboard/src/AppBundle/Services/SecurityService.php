<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 3/24/2016
 * Time: 2:17 PM
 */

namespace AppBundle\Services;

use AppBundle\Entity\Security\SesDashboardRole;
use AppBundle\Entity\Security\SesDashboardUser;
use AppBundle\Entity\Security\SesDashboardPermission;

use Doctrine\ORM\EntityManager;

use FOS\UserBundle\Model\UserManagerInterface;

use Symfony\Component\Form\FormError;


class SecurityService
{
    private $em;
    private $rolesRepository;
    private $permissionRepository;

    /** @var  SiteService */
    private $siteService;

    /** @var  UserManagerInterface */
    private $userManager;

    public function __construct(EntityManager $em, SiteService $siteService, UserManagerInterface $userManager)
    {
        $this->em = $em;
        $this->rolesRepository = $this->em->getRepository('AppBundle:Security\SesDashboardRole');
        $this->permissionRepository = $this->em->getRepository('AppBundle:Security\SesDashboardPermission');

        $this->siteService = $siteService;
        $this->userManager = $userManager;
    }

    public function getDashboardRoles(){
        return $this->rolesRepository->findAll();
    }

    public function getDashboardRole($roleId){
        return $this->rolesRepository->find($roleId);
    }


    /**
     * Create SesDashboardRole
     *
     * @param SesDashboardRole $dashboardRole
     */
    public function createDashboardRole(SesDashboardRole $dashboardRole)
    {
        $this->em->persist($dashboardRole);
        $this->em->flush();
    }

    /**
     * Delete a role
     *
     * @param $dashboardRole
     */
    public function deleteDashboardRole($dashboardRole){
        $this->em->remove($dashboardRole);
        $this->em->flush();
    }

    public function update(){
        $this->em->flush();
    }

    public function deleteUser($user)
    {
        $this->userManager->deleteUser($user);
    }

    public function createDashboardPermission($dashboardPermission){
        $this->em->persist($dashboardPermission);
        $this->em->flush();
    }

    public function deleteDashboardPermission($permissionId){
        $permission = $this->permissionRepository->find($permissionId);
        $this->em->remove($permission);
        $this->em->flush();
    }

    /**
     * Import Users
     *
     * @param array $users
     *
     * @return array
     */
    public function importUsers($users)
    {
        // Error list to display
        $errors = [];

        $usersUserName = $this->getAllUsersArrayByUserName();
        $rolesRoleName = $this->getAllRolesArrayByName();

        $allUsersToImport = [];

        /** @var SesDashboardUser $user */
        foreach ($users as $user) {
            // Check if userName is not null
            if ($user->getUsername() == null || $user->getUsername() == '') {
                $errors[] = new FormError("A user has no username defined");
                continue ;
            }

            // Check if roles are known
            /** @var SesDashboardRole $dashboardRole */
            $dashboardRoleUser = $user->getDashboardRoles();
            $user->clearDashboardRole();

            foreach ($dashboardRoleUser as $dashboardRole) {
                if (! array_key_exists($dashboardRole->getName(), $rolesRoleName)) {
                    $errors[] = new FormError(sprintf("The Dashboard Role [%s] is unknown for user [%s]", $dashboardRole->getName(), $user->getUsername()));
                    break;
                } else {
                    $user->addDashboardRole($rolesRoleName[$dashboardRole->getName()]);
                }
            }

            // Check if site reference is known
            if ($user->getSiteReference() != null && ($site = $this->siteService->findSiteByReference($user->getSiteReference())) != null) {
                $user->setSite($site);
            } else {
                $errors[] = new FormError(sprintf("Site reference not known for user [%s]", $user->getUsername()));
                continue;
            }

            // Check if user is known
            if (array_key_exists($user->getUsername(), $usersUserName)) {
                // It is an update of an existing user.
                /** @var SesDashboardUser $userToImport */
                $userToImport = $usersUserName[$user->getUsername()];
                $userToImport->setFirstName($user->getFirstName());
                $userToImport->setLastName($user->getLastName());
                $userToImport->setEmail($user->getEmail());
                $userToImport->setEnabled($user->isEnabled());
                $userToImport->setRoles($user->getRoles());
                $userToImport->setDashboardRoles($user->getDashboardRoles());
                $userToImport->setSite($user->getSite());
                $userToImport->setPlainPassword($user->getPlainPassword());

                $allUsersToImport[] = $userToImport;
            } else {

                // Check if plain password is set and is user is a new one
                if ($user->getPlainPassword() == null) {
                    $errors[] = new FormError(sprintf("No password defined for new user [%s]", $user->getUsername()));
                    continue ;
                }

                $allUsersToImport[] = $user;
            }
        }

        // If no errors, save entities
        if (count($errors) == 0) {
            /** @var SesDashboardUser $userToImport */
            foreach ($allUsersToImport as $userToImport) {
                $this->userManager->updateUser($userToImport);
            }
        }

        return $errors;
    }

    public function importRoles($roles)
    {
        // Error list to display
        $errors = [];

        // Get All roles
        $rolesRoleName = $this->getAllRolesArrayByName();

        /** @var SesDashboardRole $role */
        foreach ($roles as $role) {
            // Check permissions
            /** @var SesDashboardPermission $permission */
            foreach ($role->getDashboardPermissions() as $permission) {
                if ($permission->isValidPermission()) {
                    $permission->setDashboardRole($role);
                } else {
                    $errors[] = new FormError(sprintf("A permission for role [%s] contains an error", $role->getName()));
                }
            }
        }

        if (count($errors) == 0) {
            foreach ($roles as $role) {
                if (array_key_exists($role->getName(), $rolesRoleName)) {
                    // It is an update of an existing role.
                    // We need to remove all old permissions
                    /** @var SesDashboardRole $roleToImport */
                    $roleToImport = $rolesRoleName[$role->getName()];
                    /** @var SesDashboardPermission $permission */
                    foreach ($roleToImport->getDashboardPermissions() as $permission) {
                        $this->deleteDashboardPermission($permission);
                    }

                    // Attach new permission on existing entity
                    foreach ($role->getDashboardPermissions() as $permission) {
                        $roleToImport->addDashboardPermission($permission);
                    }
                } else {
                    $roleToImport = $role ;
                }
                $this->createDashboardRole($roleToImport);
            }
        }

        return $errors;
    }

    /**
     * Return an array of users with userName as key
     *
     * @return array
     */
    private function getAllUsersArrayByUserName()
    {
        $usersUserName = [];

        // Get All users
        $allUsers = $this->userManager->findUsers();
        /** @var SesDashboardUser $allUser */
        foreach ($allUsers as $allUser) {
            $usersUserName[$allUser->getUsername()] = $allUser;
        }

        return $usersUserName;
    }

    /**
     * Return an array of roles with name as key
     *
     * @return array
     */
    private function getAllRolesArrayByName()
    {
        $rolesRoleName = [];

        // Get All Roles
        $allRoles = $this->getDashboardRoles();
        /** @var SesDashboardRole $allRole */
        foreach ($allRoles as $allRole) {
            $rolesRoleName[$allRole->getName()] = $allRole;
        }

        return $rolesRoleName;
    }
}