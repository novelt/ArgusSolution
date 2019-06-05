<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 27/06/2016
 * Time: 14:48
 */

namespace AppBundle\Entity\Import\Report;

use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\XmlRoot("alert")
 * @JMS\ExclusionPolicy("all")
 */
class XmlAlert
{
    /**
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    private $contact;

    /**
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    private $phoneNumber;

    /**
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    private $site;

    /**
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    private $receptionDate;

    /**
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    private $message;


    public function getSiteReference()
    {
        return $this->site ;
    }

    public function getContactName()
    {
        return $this->contact;
    }

    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    public function getReceptionDate()
    {
        $date = date_create($this->receptionDate);
        return $date ;
    }

    public function getMessage()
    {
        return $this->message;
    }
}