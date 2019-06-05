<?php

namespace AppBundle\Entity\Security;

use AppBundle\Entity\SesDashboardSite;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity
 * @ORM\Table(name="sesdashboard_user", options={"collate"="utf8_general_ci"})
 * @UniqueEntity("email")
 * @UniqueEntity("username")
 *
 * @JMS\XmlRoot("user")
 * @JMS\ExclusionPolicy("all")
 */
class SesDashboardUser extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @JMS\Expose()
     */
    protected $firstName;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @JMS\Expose()
     */
    protected $lastName;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $rootSite;

    /**
     * @var SesDashboardSite
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\SesDashboardSite", inversedBy="users")
     * @ORM\JoinColumn(name="rootSite", referencedColumnName="id", nullable=true)
     */
    private $site;

    /**
     * @var string
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    private $siteReference;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @JMS\Expose()
     */
    private $locale;

    /**
     * @ORM\ManyToMany(targetEntity="SesDashboardRole", inversedBy="dashboardUsers")
     * @ORM\JoinTable(name="sesdashboard_users_sesdashboard_roles")
     *
     * @JMS\XmlList(entry = "role")
     * @JMS\Expose()
     */
    private $dashboardRoles;

    private $siteName;

    private $permissions;

    public function __construct()
    {
        parent::__construct();
        $this->dashboardRoles = new ArrayCollection();
        $this->permissions = null ;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @return SesDashboardSite|null
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @param SesDashboardSite|null $site
     */
    public function setSite($site)
    {
        $this->site = $site;
    }

    public function getSiteName()
    {
        return $this->siteName;
    }

    public function setSiteName($siteName)
    {
        $this->siteName = $siteName;
        return $this;
    }

    public function setSiteReference($siteReference)
    {
        $this->siteReference = $siteReference;
    }

    public function getSiteReference()
    {
        return $this->siteReference;
    }

    public function getDashboardRoles()
    {
        return $this->dashboardRoles;
    }

    public function setDashboardRoles($dashboardRoles)
    {
        $this->clearDashboardRole();

        foreach($dashboardRoles as $dashboardRole) {
            $this->addDashboardRole($dashboardRole);
        }
    }

    public function addDashboardRole($dashboardRole)
    {
        $this->dashboardRoles->add($dashboardRole);
    }

    public function clearDashboardRole()
    {
        $this->dashboardRoles = new ArrayCollection();
    }

    public function getDashboardRolesListChoices()
    {
        $result = [] ;

        foreach ($this->dashboardRoles as $role) {
            $result[] = $role->getId();
        }

        return $result ;
    }

    public function getDashboardPermissions()
    {
        if ($this->permissions == null) {
            $this->permissions = new ArrayCollection();

            /** @var SesDashboardRole $role */
            foreach($this->getDashboardRoles() as $role) {
                foreach($role->getDashboardPermissions() as $permission) {
                    $this->permissions->add($permission);
                }
            }
        }

        return $this->permissions;
    }

    public function isAdmin()
    {
       foreach($this->getRoles() as $role) {
           if ($role == 'ROLE_ADMIN') {
               return true;
           }
       }

        return false;
    }

    public function setIsAdmin($isAdmin)
    {
        $this->setRoles([]);
        if ($isAdmin) {
            $this->addRole('ROLE_ADMIN');
        }
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    public function getLocale()
    {
        return $this->locale;
    }


    /**
     * @JMS\PreSerialize
     */
    public function prepareSerialization()
    {
        if ($this->site != null) {
            $this->siteReference = $this->site->getReference();
        }
    }
}