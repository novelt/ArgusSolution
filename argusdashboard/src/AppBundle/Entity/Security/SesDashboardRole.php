<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 3/17/2016
 * Time: 3:26 PM
 */

namespace AppBundle\Entity\Security;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="sesdashboard_role", options={"collate"="utf8_general_ci"})
 *
 * @UniqueEntity(fields="name", message="This name already exists.")
 *
 * @JMS\XmlRoot("role")
 * @JMS\ExclusionPolicy("all")
 */
class SesDashboardRole
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
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="SesDashboardUser", mappedBy="dashboardRoles")
     */
    private $dashboardUsers;

    /**
     * @ORM\OneToMany(targetEntity="SesDashboardPermission", mappedBy="dashboardRole", cascade={"remove", "persist"})
     * @JMS\Expose
     * @JMS\XmlList(entry = "permission")
     * @JMS\SerializedName("permissions")
     * @JMS\Groups({"permissions"})
     */
    private $dashboardPermissions;

    public function __construct()
    {
        $this->dashboardUsers = new ArrayCollection();
        $this->dashboardPermissions = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id ;
    }

    public function setId($id)
    {
        $this->id = $id ;
    }

    public function getName()
    {
        return $this->name ;
    }

    public function setName($name)
    {
        $this->name = $name;
    }


    public function getNbUsers()
    {
        if ($this->dashboardUsers != null) {
            return $this->dashboardUsers->count();
        }

        return 0;
    }

    public function getNbPermissions()
    {
        if ($this->dashboardPermissions != null) {
            return $this->dashboardPermissions->count();
        }

        return 0;
    }


    public function getDashboardPermissions()
    {
        return $this->dashboardPermissions;
    }

    /**
     * @param SesDashboardPermission $permission
     */
    public function addDashboardPermission($permission)
    {
        $this->dashboardPermissions->add($permission);
        $permission->setDashboardRole($this);
    }
}