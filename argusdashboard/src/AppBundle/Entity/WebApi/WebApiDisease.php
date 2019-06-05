<?php

namespace AppBundle\Entity\WebApi;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

 /**
 * @JMS\ExclusionPolicy("all")
 */
class WebApiDisease
{
    /**
     * @JMS\Expose()
     * @JMS\SerializedName("id")
     */
    public $id;

    /**
     * @JMS\Expose()
     * @JMS\SerializedName("ref")
     */
    public $disease;

    /**
     * @JMS\Expose
     * @JMS\SerializedName("nam")
     */
    public $name;

    /**
     * @JMS\Expose
     */
    public $translatedName;

    /**
     * @JMS\Expose
     * @JMS\SerializedName("dval")
     */
    public $diseaseValues;

    public function __construct()
    {

    }
}
