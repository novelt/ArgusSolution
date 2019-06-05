<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 3/17/2016
 * Time: 3:26 PM
 */

namespace AppBundle\Entity\Security;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity
 * @ORM\Table(name="sesdashboard_permission", options={"collate"="utf8_general_ci"})
 *
 * @JMS\ExclusionPolicy("all")
 */
class SesDashboardPermission
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @JMS\Expose
     */
    private $action;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @JMS\Expose
     */
    private $ressource;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @JMS\Expose
     */
    private $state;

    /**
     * @ORM\Column(type="integer", nullable=false)
     * @JMS\Expose
     */
    private $level;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @JMS\Expose
     */
    private $type;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @JMS\Expose
     */
    private $scope;

    /**
     * @ORM\ManyToOne(targetEntity="SesDashboardRole", inversedBy="dashboardPermissions")
     * @ORM\JoinColumn(name="dashboardRoleId", referencedColumnName="id")
     */
    private $dashboardRole;

    /**
     * @var int
     */
    private $dashboardRoleId;

    public function getId(){
        return $this->id;
    }

    public function getAction(){
        return $this->action;
    }

    public function setAction($action){
        $this->action = $action;
    }

    public function getRessource(){
        return $this->ressource;
    }

    public function setRessource($ressource){
        return $this->ressource = $ressource;
    }

    public function getState(){
        return $this->state;
    }

    public function setState($state){
        return $this->state = $state;
    }

    public function getLevel(){
        return $this->level;
    }

    public function setLevel($level){
        return $this->level = $level;
    }

    public function getType(){
        return $this->type;
    }

    public function setType($type){
        $this->type = $type;
    }

    public function getScope(){
        return $this->scope;
    }

    public function setScope($scope){
        return $this->scope = $scope;
    }

    public function setDashboardRole($dashboardRole)
    {
        $this->dashboardRole = $dashboardRole;
    }

    public function setDashboardRoleId($dashboardRoleId)
    {
        $this->dashboardRoleId = $dashboardRoleId;
    }

    public function getDashboardRoleId()
    {
        return $this->dashboardRoleId ;
    }

    public function isValidPermission()
    {
        return
            in_array($this->action, SesDashboardPermissionAction::getValues())
            && in_array($this->ressource, SesDashboardPermissionRessource::getValues())
            && in_array($this->state, SesDashboardPermissionState::getValues())
            && in_array($this->type, SesDashboardPermissionType::getValues())
            && in_array($this->scope, SesDashboardPermissionScope::getValues());
    }
}
