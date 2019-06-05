<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 17/06/2016
 * Time: 14:52
 */

namespace AppBundle\Entity\Gateway;

use AppBundle\Entity\BaseEntity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as GEDMO;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Gateway\GatewayQueueRepository")
 * @ORM\Table(
 *     name="ses_gateway_queue", options={"collate"="utf8_general_ci"},
 *     indexes={
 *      @ORM\Index(name="ses_gateway_queue_phoneNumber_idx", columns={"phoneNumber"}),
 *      @ORM\Index(name="ses_gateway_queue_gatewayId_idx", columns={"gatewayId"}),
 *      @ORM\Index(name="ses_gateway_queue_pending_idx", columns={"pending"}),
 *      @ORM\Index(name="ses_gateway_queue_sent_idx", columns={"sent"}),
 *      @ORM\Index(name="ses_gateway_queue_creationDay_idx", columns={"creationDay"})
 *  }
 * )
 */
class GatewayQueue extends BaseEntity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="bigint")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private $phoneNumber;

    /**
     * @ORM\Column(type="string", length=1000, nullable=false)
     */
    private $message;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private $gatewayId;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default" = null})
     */
    private $pending;

    /**
     * @ORM\Column(type="datetime", nullable=true, options={"default" = null})
     */
    private $sent;

    /**
     * @ORM\Column(type="datetime", nullable=true, options={"default" = 0})
     */
    protected $creationDate;

    /**
     * @GEDMO\Timestampable(on="create")
     * @ORM\Column(type="date", nullable=true)
     */
    protected $creationDay;

    /**
     * @ORM\Column(type="datetime", nullable=true, options={"default" = null})
     */
    private $updateDate;

    /**
     * @ORM\Column(type="integer", nullable=false, options={"default" = 0})
     */
    private $failure;

    public function __construct($phoneNumber, $gatewayNumber, $message)
    {
        parent::__construct();
        $this->failure = 0;
        $this->phoneNumber = $phoneNumber ;
        $this->gatewayId = $gatewayNumber ;
        $this->message = $message;
    }


    /**
     * @return mixed
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param mixed $phoneNumber
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getGatewayId()
    {
        return $this->gatewayId;
    }

    /**
     * @param mixed $gatewayId
     */
    public function setGatewayId($gatewayId)
    {
        $this->gatewayId = $gatewayId;
    }

    /**
     * @return mixed
     */
    public function getPending()
    {
        return $this->pending;
    }

    /**
     * @param mixed $pending
     */
    public function setPending($pending)
    {
        $this->pending = $pending;
    }

    /**
     * @return mixed
     */
    public function getSent()
    {
        return $this->sent;
    }

    /**
     * @param mixed $sent
     */
    public function setSent($sent)
    {
        $this->sent = $sent;
    }

    /**
     * @return mixed
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param mixed $creationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * @return mixed
     */
    public function getUpdateDate()
    {
        return $this->updateDate;
    }

    /**
     * @param mixed $updateDate
     */
    public function setUpdateDate($updateDate)
    {
        $this->updateDate = $updateDate;
    }

    /**
     * @return mixed
     */
    public function getFailure()
    {
        return $this->failure;
    }

    /**
     * @param mixed $failure
     */
    public function setFailure($failure)
    {
        $this->failure = $failure;
    }
}