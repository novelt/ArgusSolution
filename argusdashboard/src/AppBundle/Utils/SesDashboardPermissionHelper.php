<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 3/17/2016
 * Time: 5:39 PM
 */

namespace AppBundle\Utils;

use AppBundle\Entity\Constant;
use AppBundle\Entity\PermissionSite;
use AppBundle\Entity\Security\SesDashboardPermissionAction as Action;
use AppBundle\Entity\Security\SesDashboardPermissionRessource as Ressource;
use AppBundle\Entity\Security\SesDashboardPermissionState as State;
use AppBundle\Entity\Security\SesDashboardPermissionType as Type;
use AppBundle\Entity\Security\SesDashboardPermissionScope as Scope;
use AppBundle\Entity\Security\SesDashboardUser;
use AppBundle\Entity\SesDashboardSite;


class SesDashboardPermissionHelper
{
    /** @var  SesDashboardUser */
    private $user;

    public function  __construct($user)
    {
        $this->user = $user;
    }

    private function isAdmin()
    {
        if ($this->user != null) {
            return $this->user->isAdmin();
        }

        return false ;
    }

    /**
     * Return true if user can select this site regarding the permissions
     *
     * @param PermissionSite $site
     * @param SesDashboardSite $homeSite
     * @param $permissions
     * @return bool
     */
    public function isTreeViewNodeSelectable(PermissionSite $site, SesDashboardSite $homeSite, $permissions)
    {
        if ($this->isAdmin()) {
            return true ;
        }

        if ($site == null || $homeSite == null) {
            return false ;
        }

        $nodeLevel = self::getCurrentNodeLevel($site, $homeSite);
        $sameBranch = self::areSiteOnSameBranch($site, $homeSite);

        $result = self::existPermission(array(Action::ACTION_VIEW, Action::ACTION_VALIDATE, Action::ACTION_REJECT),
                                        array(Ressource::RESSOURCE_WEEKLY_REPORT, Ressource::RESSOURCE_MONTHLY_REPORT, Ressource::RESSOURCE_ALERT),
                                        array(State::STATE_ANY),
                                        array(Type::TYPE_ALLOW),
                                        $nodeLevel,
                                        $sameBranch,
                                        $permissions);

        return $result ;
    }

    /**
     * Return true if user can access the reports of this site
     *
     * @param PermissionSite $site
     * @param SesDashboardSite $homeSite
     * @param $permissions
     * @return bool
     */
    public function isTreeViewNodeReportable(PermissionSite $site, SesDashboardSite $homeSite, $permissions)
    {
        if ($this->isAdmin()) {
            return true ;
        }

        if ($site == null || $homeSite == null) {
            return false ;
        }

        $nodeLevel = self::getCurrentNodeLevel($site, $homeSite);
        $sameBranch = self::areSiteOnSameBranch($site, $homeSite);

        $result = self::existPermission(array(Action::ACTION_VIEW, Action::ACTION_DOWNLOAD),
            array(Ressource::RESSOURCE_DASHBOARD_REPORT),
            array(State::STATE_ANY),
            array(Type::TYPE_ALLOW),
            $nodeLevel,
            $sameBranch,
            $permissions);

        return $result ;
    }

    /**
     * Return true if user can export the reports
     *
     * @param PermissionSite $site
     * @param SesDashboardSite $homeSite
     * @param $permissions
     * @return bool
     */
    public function isTreeViewNodeExportable(PermissionSite $site, SesDashboardSite $homeSite, $permissions)
    {
        if ($this->isAdmin()) {
            return true ;
        }

        if ($site == null || $homeSite == null) {
            return false ;
        }

        $nodeLevel = self::getCurrentNodeLevel($site, $homeSite);
        $sameBranch = self::areSiteOnSameBranch($site, $homeSite);

        $result = self::existPermission(array(Action::ACTION_DOWNLOAD),
            array(Ressource::RESSOURCE_DASHBOARD_REPORT),
            array(State::STATE_ANY),
            array(Type::TYPE_ALLOW),
            $nodeLevel,
            $sameBranch,
            $permissions);

        return $result ;
    }

    public function isWeeklyReportEnabled($site, $homeSite, $permissions)
    {
        if ($this->isAdmin()) {
            return true ;
        }

        if ($site == null || $homeSite == null) {
            return false ;
        }

        $nodeLevel = self::getCurrentNodeLevel($site, $homeSite);
        $sameBranch = self::areSiteOnSameBranch($site, $homeSite);

        $result = self::existPermission(array(Action::ACTION_VIEW, Action::ACTION_VALIDATE, Action::ACTION_REJECT),
                                        array(Ressource::RESSOURCE_WEEKLY_REPORT),
                                        array(State::STATE_ANY),
                                        array(Type::TYPE_ALLOW),
                                        $nodeLevel,
                                        $sameBranch,
                                        $permissions);

        return $result ;
    }

    public function isMonthlyReportEnabled($site, $homeSite, $permissions)
    {
        if ($this->isAdmin()) {
            return true ;
        }

        if ($site == null || $homeSite == null) {
            return false ;
        }

        $nodeLevel = self::getCurrentNodeLevel($site, $homeSite);
        $sameBranch = self::areSiteOnSameBranch($site, $homeSite);

        $result = self::existPermission(array(Action::ACTION_VIEW, Action::ACTION_VALIDATE, Action::ACTION_REJECT),
            array(Ressource::RESSOURCE_MONTHLY_REPORT),
            array(State::STATE_ANY),
            array(Type::TYPE_ALLOW),
            $nodeLevel,
            $sameBranch,
            $permissions);

        return $result ;
    }

    public function isPendingStatusEnabled($site, $homeSite, $permissions, $period)
    {
        if ($this->isAdmin()) {
            return true ;
        }

        if ($site == null || $homeSite == null) {
            return false ;
        }

        $nodeLevel = self::getCurrentNodeLevel($site, $homeSite);
        $sameBranch = self::areSiteOnSameBranch($site, $homeSite);
        $result = false ;

        // Check if there is a specific permission which deny this action
        $deny = self::existPermission(array(Action::ACTION_VIEW, Action::ACTION_VALIDATE, Action::ACTION_REJECT),
            array(($period == Constant::PERIOD_WEEKLY ? Ressource::RESSOURCE_WEEKLY_REPORT : ($period == Constant::PERIOD_MONTHLY ? Ressource::RESSOURCE_MONTHLY_REPORT : $period))),
            array(State::STATE_PENDING),
            array(Type::TYPE_DENY),
            $nodeLevel,
            $sameBranch,
            $permissions);

        if (!$deny) {
            $result = self::existPermission(array(Action::ACTION_VIEW, Action::ACTION_VALIDATE, Action::ACTION_REJECT),
                array(($period == Constant::PERIOD_WEEKLY ? Ressource::RESSOURCE_WEEKLY_REPORT : ($period == Constant::PERIOD_MONTHLY ? Ressource::RESSOURCE_MONTHLY_REPORT : $period))),
                array(State::STATE_PENDING),
                array(Type::TYPE_ALLOW),
                $nodeLevel,
                $sameBranch,
                $permissions);
        }

        return $result ;
    }

    public function isValidatedStatusEnabled($site, $homeSite, $permissions, $period)
    {
        if ($this->isAdmin()) {
            return true ;
        }

        if ($site == null || $homeSite == null) {
            return false ;
        }

        $nodeLevel = self::getCurrentNodeLevel($site, $homeSite);
        $sameBranch = self::areSiteOnSameBranch($site, $homeSite);
        $result = false ;

        // Check if there is a specific permission which deny this action
        $deny = self::existPermission(array(Action::ACTION_VIEW, Action::ACTION_VALIDATE, Action::ACTION_REJECT),
            array(($period == Constant::PERIOD_WEEKLY ? Ressource::RESSOURCE_WEEKLY_REPORT : ($period == Constant::PERIOD_MONTHLY ? Ressource::RESSOURCE_MONTHLY_REPORT : $period))),
            array(State::STATE_VALIDATED),
            array(Type::TYPE_DENY),
            $nodeLevel,
            $sameBranch,
            $permissions);

        if (!$deny) {
            $result = self::existPermission(array(Action::ACTION_VIEW, Action::ACTION_VALIDATE, Action::ACTION_REJECT),
                array(($period == Constant::PERIOD_WEEKLY ? Ressource::RESSOURCE_WEEKLY_REPORT : ($period == Constant::PERIOD_MONTHLY ? Ressource::RESSOURCE_MONTHLY_REPORT : $period))),
                array(State::STATE_VALIDATED),
                array(Type::TYPE_ALLOW),
                $nodeLevel,
                $sameBranch,
                $permissions);
        }

        return $result ;
    }

    public function isRejectedStatusEnabled($site, $homeSite, $permissions, $period)
    {
        if ($this->isAdmin()) {
            return true ;
        }

        if ($site == null || $homeSite == null) {
            return false ;
        }

        $nodeLevel = self::getCurrentNodeLevel($site, $homeSite);
        $sameBranch = self::areSiteOnSameBranch($site, $homeSite);
        $result = false ;

        $deny = self::existPermission(array(Action::ACTION_VIEW, Action::ACTION_VALIDATE, Action::ACTION_REJECT),
            array(($period == Constant::PERIOD_WEEKLY ? Ressource::RESSOURCE_WEEKLY_REPORT : ($period == Constant::PERIOD_MONTHLY ? Ressource::RESSOURCE_MONTHLY_REPORT : $period))),
            array(State::STATE_REJECTED),
            array(Type::TYPE_DENY),
            $nodeLevel,
            $sameBranch,
            $permissions);

        if (!$deny){
            $result = self::existPermission(array(Action::ACTION_VIEW, Action::ACTION_VALIDATE, Action::ACTION_REJECT),
                array(($period == Constant::PERIOD_WEEKLY ? Ressource::RESSOURCE_WEEKLY_REPORT : ($period == Constant::PERIOD_MONTHLY ? Ressource::RESSOURCE_MONTHLY_REPORT : $period))),
                array(State::STATE_REJECTED),
                array(Type::TYPE_ALLOW),
                $nodeLevel,
                $sameBranch,
                $permissions);
        }

        return $result ;
    }

    public function isValidationActionEnabled($site, $homeSite, $permissions, $period)
    {
        if ($this->isAdmin()) {
            return true ;
        }

        if ($site == null || $homeSite == null) {
            return false ;
        }

        $nodeLevel = self::getCurrentNodeLevel($site, $homeSite);
        $sameBranch = self::areSiteOnSameBranch($site, $homeSite);
        $result = false ;

        $deny =  self::existPermission(array(Action::ACTION_VALIDATE),
            array(($period == Constant::PERIOD_WEEKLY ? Ressource::RESSOURCE_WEEKLY_REPORT : ($period == Constant::PERIOD_MONTHLY ? Ressource::RESSOURCE_MONTHLY_REPORT : $period))),
            array(State::STATE_ANY),
            array(Type::TYPE_DENY),
            $nodeLevel,
            $sameBranch,
            $permissions);

        if (! $deny) {
            $result = self::existPermission(array(Action::ACTION_VALIDATE),
                array(($period == Constant::PERIOD_WEEKLY ? Ressource::RESSOURCE_WEEKLY_REPORT : ($period == Constant::PERIOD_MONTHLY ? Ressource::RESSOURCE_MONTHLY_REPORT : $period))),
                array(State::STATE_ANY),
                array(Type::TYPE_ALLOW),
                $nodeLevel,
                $sameBranch,
                $permissions);
        }

        return $result ;
    }

    public function isRejectionActionEnabled($site, $homeSite, $permissions, $period)
    {
        if ($this->isAdmin()) {
            return true ;
        }

        if ($site == null || $homeSite == null) {
            return false ;
        }

        $nodeLevel = self::getCurrentNodeLevel($site, $homeSite);
        $sameBranch = self::areSiteOnSameBranch($site, $homeSite);
        $result = false ;

        $deny =  self::existPermission(array(Action::ACTION_REJECT),
            array(($period == Constant::PERIOD_WEEKLY ? Ressource::RESSOURCE_WEEKLY_REPORT : ($period == Constant::PERIOD_MONTHLY ? Ressource::RESSOURCE_MONTHLY_REPORT : $period))),
            array(State::STATE_ANY),
            array(Type::TYPE_DENY),
            $nodeLevel,
            $sameBranch,
            $permissions);

        if (! $deny) {
            $result = self::existPermission(array(Action::ACTION_REJECT),
                array(($period == Constant::PERIOD_WEEKLY ? Ressource::RESSOURCE_WEEKLY_REPORT : ($period == Constant::PERIOD_MONTHLY ? Ressource::RESSOURCE_MONTHLY_REPORT : $period))),
                array(State::STATE_ANY),
                array(Type::TYPE_ALLOW),
                $nodeLevel,
                $sameBranch,
                $permissions);
        }

        return $result ;
    }

    public function isAlertEnabled($site, $homeSite, $permissions)
    {
        if ($this->isAdmin()) {
            return true ;
        }

        if ($site == null || $homeSite == null) {
            return false ;
        }

        $nodeLevel = self::getCurrentNodeLevel($site, $homeSite);
        $sameBranch = self::areSiteOnSameBranch($site, $homeSite);
        $result = false ;

        $deny =   self::existPermission(array(Action::ACTION_VIEW),
            array(Ressource::RESSOURCE_ALERT),
            array(State::STATE_ANY),
            array(Type::TYPE_DENY),
            $nodeLevel,
            $sameBranch,
            $permissions);

        if (!$deny) {
            $result = self::existPermission(array(Action::ACTION_VIEW),
                array(Ressource::RESSOURCE_ALERT),
                array(State::STATE_ANY),
                array(Type::TYPE_ALLOW),
                $nodeLevel,
                $sameBranch,
                $permissions);
        }

        return $result ;
    }

    public function getAlertScope($site, $homeSite, $permissions)
    {
        $nodeLevel = self::getCurrentNodeLevel($site, $homeSite);
        $sameBranch = self::areSiteOnSameBranch($site, $homeSite);

        $scope = self::getScopeFromPermission(array(Action::ACTION_VIEW),
            array(Ressource::RESSOURCE_ALERT),
            array(State::STATE_ANY),
            array(Type::TYPE_ALLOW),
            $nodeLevel,$sameBranch, $permissions);

        return $scope ;
    }

    public function isWeeklyReportUploadEnabled(PermissionSite $site, $homeSite, $permissions)
    {
        if ($this->isAdmin()) {
            return true ;
        }

        if ($site == null || $homeSite == null) {
            return false ;
        }

        $nodeLevel = self::getCurrentNodeLevel($site, $homeSite);
        $sameBranch = self::areSiteOnSameBranch($site, $homeSite);

        $result = self::existPermission(array(Action::ACTION_UPLOAD),
            array(Ressource::RESSOURCE_WEEKLY_REPORT),
            array(State::STATE_ANY),
            array(Type::TYPE_ALLOW),
            $nodeLevel,
            $sameBranch,
            $permissions);

        return $result ;
    }

    /**
     *
     * @param SesDashboardSite $site
     * @param SesDashboardSite $homeSite
     * @param $permissions
     * @param $period
     * @return null
     */
    public function getChildSiteIdToFilter(SesDashboardSite $site, SesDashboardSite $homeSite, $permissions, $period)
    {
        $nodeLevel = self::getCurrentNodeLevel($site, $homeSite);
        $sameBranch = self::areSiteOnSameBranch($site, $homeSite);

        if ($nodeLevel <= 0){
            return null;
        }

        // Search SCOPE for permissions
        $scope = self::getScopeFromPermission(array(Action::ACTION_VIEW),
            array(($period == Constant::PERIOD_WEEKLY ? Ressource::RESSOURCE_WEEKLY_REPORT : ($period == Constant::PERIOD_MONTHLY ? Ressource::RESSOURCE_MONTHLY_REPORT : $period))),
            array(State::STATE_ANY),
            array(Type::TYPE_ALLOW),
            $nodeLevel,$sameBranch, $permissions);

        if ($scope == Scope::SCOPE_ALL){
            return null ;
        }

        // if SCOPE == SINGLE
        // if ($nodeLevel == 1) // filter on Site Id
        // if ($nodeLevel == 2) // Filter on Parent Site Id
        // etc...
        $filterSite = $homeSite;

        for($i = $nodeLevel -1 ; $i > 0 ; $i-- ){
            if ($filterSite->getParent() != null) {
                $filterSite = $filterSite->getParent();
            }
        }

        return $filterSite->getId() ;
    }

    /**
     * Return level difference between home site and site
     *
     * @param PermissionSite $site
     * @param SesDashboardSite $home
     * @return mixed
     */
    private function getCurrentNodeLevel(PermissionSite $site, SesDashboardSite $home)
    {
        if ($site == null || $home == null) {
            return 0;
        }

        $levelSite = $site->getLevel();
        $levelHome = $home->getLevel();

       return $levelHome - $levelSite;
    }

    /**
     *  check if sites are on same branch
     *
     * @param PermissionSite $site
     * @param SesDashboardSite $homeSite
     * @return bool
     */
    public function areSiteOnSameBranch(PermissionSite $site, SesDashboardSite $homeSite)
    {
        if ($site == null || $homeSite == null) {
            return false ;
        }

        if (strpos( $homeSite->getPath(), $site->getPath()) !== false || strpos( $site->getPath(), $homeSite->getPath()) !== false) {
            return true;
        }

        return false;
    }

    /**
     * Get Sites scopes used to know on which sites the user can perform actions
     *
     * @param $permissions
     * @return array
     */
    public function getSitesScopes($permissions)
    {
        $results = [];

        $homeSingle = false ;
        $homeBrothers = false ;
        $childrenSingle = null ;
        $childrenBrothers = null ;
        $parentSingle = null ;
        $parentBrothers = null ;

        //Permission Validation /!\ ATTENTION, lorsque l'on dit que l'on peut valider au niveau Home, ce sont les rapports des Enfants du site que l'on peut valider
        //      Si il existe une permission de validation level == 0 scope SINGLE , on ajoute les enfants de home
        //      Si il existe une permission de validation level == 0 scope ALL , on ajoute les enfants de home + tous les frères  (same level)
        //      Si il existe une permission de validation level == -1 -> -10, SCOPE SINGLE , on ajoute les enfants des enfants du home site where (home level) - (site level) >= level
        //      Si il existe une permission de validation level == -1 -> -10, SCOPE ALL , on ajoute les enfants des enfants du home site where (home level) - (site level) >= level + tous les enfants des frères avec la même condition
        //      Si il existe une permission de validation level == 1 -> 10, SCOPE SINGLE , on ajoute home + les parents avec condition ...
        //      Si il existe une permission de validation level == 1 -> 10, SCOPE ALL , on ajoute home + les parents + frères de home et parents avec condition ...

        if ($this->existPermission([Action::ACTION_VALIDATE, Action::ACTION_REJECT],
                                [Ressource::RESSOURCE_MONTHLY_REPORT,Ressource::RESSOURCE_WEEKLY_REPORT],
                                [State::STATE_ANY],
                                [Type::TYPE_ALLOW],
                                0,
                                true,
                                $permissions)) {
            // Si il existe une permission de validation level == 0 scope SINGLE , on ajoute le home site id
            $homeSingle = true ;
        }

        if ($this->existPermission([Action::ACTION_VALIDATE, Action::ACTION_REJECT],
            [Ressource::RESSOURCE_MONTHLY_REPORT,Ressource::RESSOURCE_WEEKLY_REPORT],
            [State::STATE_ANY],
            [Type::TYPE_ALLOW],
            0,
            false,
            $permissions)) {
            // Si il existe une permission de validation level == 0 scope ALL , on ajoute le home site id + tous les frères (same level)
            $homeBrothers = true ;
        }

        for ($i=-1 ; $i >= -10 ; $i -- ) {
            if ($this->existPermission([Action::ACTION_VALIDATE, Action::ACTION_REJECT],
                [Ressource::RESSOURCE_MONTHLY_REPORT,Ressource::RESSOURCE_WEEKLY_REPORT],
                [State::STATE_ANY],
                [Type::TYPE_ALLOW],
                $i,
                true,
                $permissions)) {
                // Si il existe une permission de validation level == -1 -> -10, SCOPE SINGLE , on ajoute les enfants des enfants du home site where (home level) - (site level) >= level
                $childrenSingle = $i;
            }
        }

        for ($i=-1 ; $i >= -10 ; $i -- ) {
            if ($this->existPermission([Action::ACTION_VALIDATE, Action::ACTION_REJECT],
                [Ressource::RESSOURCE_MONTHLY_REPORT,Ressource::RESSOURCE_WEEKLY_REPORT],
                [State::STATE_ANY],
                [Type::TYPE_ALLOW],
                $i,
                false,
                $permissions)) {
                // Si il existe une permission de validation level == -1 -> -10, SCOPE ALL , on ajoute les enfants du home site where (home level) - (site level) >= level + tous les enfants des frères avec la même condition
                $childrenBrothers = $i;
            }
        }

        for ($i=1 ; $i <= 10 ; $i ++ ) {
            if ($this->existPermission([Action::ACTION_VALIDATE, Action::ACTION_REJECT],
                [Ressource::RESSOURCE_MONTHLY_REPORT,Ressource::RESSOURCE_WEEKLY_REPORT],
                [State::STATE_ANY],
                [Type::TYPE_ALLOW],
                $i,
                true,
                $permissions)) {
                // Si il existe une permission de validation level == 1 -> 10, SCOPE SINGLE , on ajoute les parents avec condition ...
                $parentSingle = $i;
            }
        }

        for ($i=1 ; $i <= 10 ; $i ++ ) {
            if ($this->existPermission([Action::ACTION_VALIDATE, Action::ACTION_REJECT],
                [Ressource::RESSOURCE_MONTHLY_REPORT,Ressource::RESSOURCE_WEEKLY_REPORT],
                [State::STATE_ANY],
                [Type::TYPE_ALLOW],
                $i,
                false,
                $permissions)) {
                //  Si il existe une permission de validation level == 1 -> 10, SCOPE ALL , on ajoute les parents + frères aux parents avec condition ...
                $parentBrothers = $i;
            }
        }

        $results['homeSingle'] = $homeSingle;
        $results['homeBrothers'] = $homeBrothers;
        $results['childrenSingle'] = $childrenSingle;
        $results['childrenBrothers'] = $childrenBrothers;
        $results['parentSingle'] = $parentSingle;
        $results['parentBrothers'] = $parentBrothers;

        return $results ;
    }

    /**
     *
     * Principal method managing user permissions
     *
     * @param $actions
     * @param $ressources
     * @param $states
     * @param $types
     * @param $level
     * @param $branch
     * @param $permissions
     * @return bool
     */
    private function existPermission($actions, $ressources, $states, $types, $level, $branch, $permissions){

        $result = false ;

        foreach($permissions as $permission){

            if (in_array($permission->getAction(), $actions)){
                // Action found

                if (in_array(Ressource::RESSOURCE_ANY, $ressources) || in_array($permission->getRessource(), $ressources) || $permission->getRessource() == Ressource::RESSOURCE_ANY){
                    // Ressource found

                    if (in_array(State::STATE_ANY, $states) || in_array($permission->getState(), $states) ||  $permission->getState() == State::STATE_ANY) {
                        // State found

                        if (in_array($permission->getType(), $types)){
                            // Type found

                            if ($permission->getType() == Type::TYPE_ALLOW) {

                                if (($permission->getLevel() == 0 && $level == 0) ||
                                    ($permission->getLevel() < 0 && $level < 0 && $level >= $permission->getLevel()) ||
                                    ($permission->getLevel() > 0 && $level > 0 && $level <= $permission->getLevel())
                                ) {
                                    //Level OK

                                    if ($permission->getScope() == Scope::SCOPE_ALL) {
                                        // Don't care about branch
                                        return true;
                                    } elseif ($permission->getScope() == Scope::SCOPE_SINGLE) {
                                        if ($branch) {
                                            return true;
                                        }
                                    }

                                }
                            }
                            elseif ($permission->getType() == Type::TYPE_DENY)
                            {
                                // Check on the specific level
                                if ($permission->getLevel() == $level){
                                    if ($permission->getScope() == Scope::SCOPE_ALL) {
                                        // Don't care about branch
                                        return true;
                                    } elseif ($permission->getScope() == Scope::SCOPE_SINGLE) {
                                        if ($branch) {
                                            return true;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $result ;
    }

    private function getScopeFromPermission($actions, $ressources, $states, $types, $level, $branch, $permissions)
    {
        if ($this->isAdmin()) {
            return Scope::SCOPE_ALL ;
        }

        foreach($permissions as $permission){

            if (in_array($permission->getAction(), $actions)){
                // Action found

                if (in_array(Ressource::RESSOURCE_ANY, $ressources) || in_array($permission->getRessource(), $ressources) || $permission->getRessource() == Ressource::RESSOURCE_ANY){
                    // Ressource found

                    if (in_array(State::STATE_ANY, $states) || in_array($permission->getState(), $states) ||  $permission->getState() == State::STATE_ANY) {
                        // State found

                        if (in_array($permission->getType(), $types)){
                            // Type found

                            if (($permission->getLevel() > 0 && $level >= 0 && $level <= $permission->getLevel()))
                            {
                                return $permission->getScope();
                            }

                            if (($permission->getLevel() < 0 && $level <= 0 && $level >= $permission->getLevel()))
                            {
                                return $permission->getScope();
                            }
                        }
                    }
                }
            }
        }

        return Scope::SCOPE_NONE;
    }
}