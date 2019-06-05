<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 7/20/2015
 * Time: 4:44 PM
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SesAlertRepository")
 * @ORM\Table(name="sesdashboard_Alert", options={"collate"="utf8_general_ci"})
 * @ORM\HasLifecycleCallbacks()
 */
class SesAlert
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $contactName;

    /**
     * @ORM\Column(type="string")
     */
    private $contactPhoneNumber;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $FK_SiteId;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $FK_SiteRelationShipId;

    /**
     * @ORM\Column(type="string")
     */
    private $import_SiteName;

    /**
     * @ORM\Column(type="datetime")
     */
    private $receptionDate;

    /**
     * @ORM\Column(type="string", length=10000)
     */
    private $message;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isRead;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isArchived;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isDeleted;

    /**
     * @ORM\ManyToOne(targetEntity="SesDashboardSite", inversedBy="alerts")
     * @ORM\JoinColumn(name="FK_SiteId", referencedColumnName="id")
     */
    private $frontLineGroup;

    /**
     * @ORM\ManyToOne(targetEntity="SesDashboardSiteRelationShip", inversedBy="alerts")
     * @ORM\JoinColumn(name="FK_SiteRelationShipId", referencedColumnName="id")
     */
    private $siteRelationShip;

    private $messages;

    /**
     * Creation of an alert
     *
     * @param SesDashboardSite $site
     * @param SesDashboardSiteRelationShip $siteRelationShip
     * @param $importSiteName
     * @param $contactName
     * @param $contactPhoneNumber
     * @param $receptionDate
     * @param $message
     * @return SesAlert
     */
    public static function create(SesDashboardSite $site, SesDashboardSiteRelationShip $siteRelationShip, $importSiteName, $contactName, $contactPhoneNumber, $receptionDate, $message)
    {
        $instance = new self();
        $instance->setFrontLineGroup($site);
        $instance->setSiteRelationShip($siteRelationShip);
        $instance->setImportSiteName($importSiteName);
        $instance->setContactName($contactName);
        $instance->setContactPhoneNumber($contactPhoneNumber);
        $instance->setReceptionDate($receptionDate);
        $instance->setMessage($message);

        return $instance;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message ;
    }

    public function getFrontLineGroup()
    {
        return $this->frontLineGroup;
    }

    public function getFrontLineGroupName()
    {
        return $this->frontLineGroup->getName();
    }

    public function setFrontLineGroup($frontLineGroup)
    {
        return $this->frontLineGroup = $frontLineGroup;
    }

    /**
     * @return mixed
     */
    public function getSiteRelationShip()
    {
        return $this->siteRelationShip;
    }

    /**
     * @param mixed $siteRelationShip
     */
    public function setSiteRelationShip($siteRelationShip)
    {
        $this->siteRelationShip = $siteRelationShip;
    }

    public function addMessage($tMessage)
    {
        $this->messages[] = $tMessage;
    }

    public function getFormatMessages()
    {
        return $this->messages;
    }

    public function isRead()
    {
        return $this->isRead;
    }

    public function setRead($value)
    {
        $this->isRead = $value;
    }

    public function getReceptionDate()
    {
        return $this->receptionDate;
    }

    public function setReceptionDate($receptionDate)
    {
        $this->receptionDate = $receptionDate ;
    }

    public function setContactName($contactName)
    {
        $this->contactName = $contactName ;
    }

    public function setContactPhoneNumber($contactPhoneNumber)
    {
        $this->contactPhoneNumber = $contactPhoneNumber ;
    }

    public function setImportSiteName($importSiteName)
    {
        $this->import_SiteName = $importSiteName;
    }

    public function getContactName()
    {
        return $this->contactName;
    }

    public function getContactPhoneNumber()
    {
        return $this->contactPhoneNumber;
    }

    public function getFK_SiteId()
    {
        return $this->FK_SiteId;
    }

    /**
     * Get CSV Headers
     *
     * @return array
     */
    public static function getHeaderCsvRow()
    {
        $row = array() ;
        $row[] = 'Alert Id';
        $row[] = 'Contact Name';
        $row[] = 'Contact Phone Number';
        $row[] = 'Reception Date';
        $row[] = 'Message';
        $row[] = 'Read';
        $row[] = 'Site Id';

        return $row;
    }

    /**
     * Get CSV Alert format
     *
     * @return array
     */
    public function getCsvRow()
    {
        $row = array() ;
        $row[] = self::getId();
        $row[] = self::getContactName();
        $row[] = self::getContactPhoneNumber();
        $row[] = self::getReceptionDate()->format('d-m-Y');
        $row[] = self::getMessage();
        $row[] = self::isRead();
        $row[] = self::getFK_SiteId();

        foreach(self::getFormatMessages() as $message) {
            $row[] = $message[0];
            $row[] = $message[1];
        }

        return $row;
    }
}