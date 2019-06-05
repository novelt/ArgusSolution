<?php

/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 26/10/2017
 * Time: 10:32
 */

namespace AppBundle\Entity\Messages;

use AppBundle\Entity\BaseEntity;
use AppBundle\Entity\SesDashboardContact;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as GEDMO;
use Symfony\Component\Validator\Constraints\DateTime; // gedmo annotations

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Messages\IncomingSmsRepository")
 * @ORM\Table(
 *     name="ses_incoming_sms", options={"collate"="utf8_general_ci"},
 *     indexes={
 *      @ORM\Index(name="ses_incoming_sms_phoneNumber_idx", columns={"phoneNumber"}),
 *      @ORM\Index(name="ses_incoming_sms_gatewayId_idx", columns={"gatewayId"}),
 *      @ORM\Index(name="ses_incoming_sms_status_idx", columns={"status"}),
 *      @ORM\Index(name="ses_incoming_sms_type_idx", columns={"type"}),
 *      @ORM\Index(name="ses_incoming_sms_pending_idx", columns={"pending"}),
 *      @ORM\Index(name="ses_incoming_sms_creationDay_idx", columns={"creationDay"})
 *  }
 * )
 */
class IncomingSms extends BaseEntity
{
    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $phoneNumber;

    /**
     * @var \DateTime
     * @GEDMO\Timestampable(on="create")
     * @ORM\Column(type="date", nullable=true)
     */
    private $creationDay;

    /**
     * @var string
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $message;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $gatewayId;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=false, options={"default" = 0})
     */
    private $status;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=false, options={"default" = 0})
     */
    private $type;

    /**
     * @var \DateTime
     * @GEDMO\Timestampable(on="update")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updateDate;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $comments;

    /**
     * @var integer
     * @ORM\Column(type="integer", nullable=true)
     */
    private $FK_ContactId;

    /**
     * @var SesDashboardContact
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\SesDashboardContact", inversedBy="incomingSms")
     * @ORM\JoinColumn(name="FK_ContactId", referencedColumnName="id", nullable=true)
     */
    private $contact;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=true, options={"default" = null})
     */
    private $pending;

    /**
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param string $phoneNumber
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getGatewayId()
    {
        return $this->gatewayId;
    }

    /**
     * @param string $gatewayId
     */
    public function setGatewayId($gatewayId)
    {
        $this->gatewayId = $gatewayId;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return \DateTime
     */
    public function getUpdateDate()
    {
        return $this->updateDate;
    }

    /**
     * @param \DateTime $updateDate
     */
    public function setUpdateDate($updateDate)
    {
        $this->updateDate = $updateDate;
    }

    /**
     * @return string
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param string $comments
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
    }

    /**
     * @return int
     */
    public function getFKContactId()
    {
        return $this->FK_ContactId;
    }

    /**
     * @param int $FK_ContactId
     */
    public function setFKContactId($FK_ContactId)
    {
        $this->FK_ContactId = $FK_ContactId;
    }

    /**
     * @return SesDashboardContact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param SesDashboardContact $contact
     */
    public function setContact($contact)
    {
        $this->contact = $contact;
    }

    /**
     * @return bool
     */
    public function isPending()
    {
        return $this->pending;
    }

    /**
     * @param bool $pending
     */
    public function setPending($pending)
    {
        $this->pending = $pending;
    }
}

abstract class IncomingSmsStatus
{
    const STATUS_NEW = 0;                                   // Just arrived
    const STATUS_NOT_PROCESSED = 1;                         // Not Processed
    const STATUS_PROCESSED = 2;                             // Processed
    const STATUS_IGNORED = 3;                               // Ignored
    const STATUS_PHONE_NUMBER_UNKNOWN = 4;                  // Unknown contact number
    const STATUS_PHONE_NUMBER_GATEWAY = 5;                  // Number corresponding to a gateway number
    const STATUS_CONTACT_NO_SITE = 6;                       // The contact is attached to no site
    const STATUS_COMBINED_MULTIPART = 7;                    // Has been generated by combining multi-part messages

    const STATUS_ERROR = 10;                                // Error
}

abstract class IncomingSmsType
{
    const TYPE_UNKNOWN = 0 ;                                // Unknown Message
    const TYPE_ARGUS = 1 ;                                  // ARGUS Message
    const TYPE_CONFIG = 2 ;                                 // CONFIG Message
    const TYPE_OTHER = 3 ;                                  // Other messages : - AVADAR ? ARGUS Case ?
}