<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 29/05/2017
 * Time: 16:24
 */

namespace AppBundle\Services\Contact\DTO;

use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\ExclusionPolicy("all")
 */
class ContactDTO
{
    /**
     * @var int
     *
     * @JMS\Expose
     * @JMS\Type("integer")
     */
    public $id;

    /**
     * @var string
     *
     * @JMS\Expose
     * @JMS\Type("string")
     * @JMS\SerializedName("nam")
     */
    public $name;

    /**
     *  @var string
     *
     * @JMS\Expose
     * @JMS\Type("string")
     * @JMS\SerializedName("phn")
     */
    public $phoneNumber;

    /**
     * @var string
     *
     * @JMS\Expose
     * @JMS\Type("string")
     * @JMS\SerializedName("not")
     */
    public $notes;

    /**
     * @var string
     *
     * @JMS\Expose
     * @JMS\Type("string")
     * @JMS\SerializedName("ema")
     */
    public $email;

    /**
     * @var string
     *
     * @JMS\Expose
     * @JMS\Type("string")
     * @JMS\SerializedName("ie1")
     */
    public $imei1;

    /**
     * @var string
     *
     * @JMS\Expose
     * @JMS\Type("string")
     * @JMS\SerializedName("ie2")
     */
    public $imei2;

    /**
     * @var int
     *
     * @JMS\Expose
     * @JMS\Type("integer")
     * @JMS\SerializedName("sid")
     */
    public $siteId;

    /**
     * @var int
     *
     * @JMS\Expose
     * @JMS\Type("integer")
     * @JMS\SerializedName("tid")
     */
    public $contactTypeId;

    /**
     * @var double
     *
     * @JMS\Expose
     * @JMS\Type("double")
     * @JMS\SerializedName("lat")
     */
    public $latitude;

    /**
     * @var double
     *
     * @JMS\Expose
     * @JMS\Type("double")
     * @JMS\SerializedName("lon")
     */
    public $longitude;

    /**
     * @return mixed
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @param mixed $latitude
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }

    /**
     * @return mixed
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @param mixed $longitude
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }

    /**
     * @return int
     */
    public function getSiteId()
    {
        return $this->siteId;
    }

    /**
     * @param int $siteId
     */
    public function setSiteId($siteId)
    {
        $this->siteId = $siteId;
    }

    /**
     * @return int
     */
    public function getContactTypeId()
    {
        return $this->contactTypeId;
    }

    /**
     * @param int $contactTypeId
     */
    public function setContactTypeId($contactTypeId)
    {
        $this->contactTypeId = $contactTypeId;
    }

    /**
     * @return string
     */
    public function getImei1()
    {
        return $this->imei1;
    }

    /**
     * @param string $imei1
     */
    public function setImei1($imei1)
    {
        $this->imei1 = $imei1;
    }

    /**
     * @return string
     */
    public function getImei2()
    {
        return $this->imei2;
    }

    /**
     * @param string $imei2
     */
    public function setImei2($imei2)
    {
        $this->imei2 = $imei2;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param mixed $notes
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
    }

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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}