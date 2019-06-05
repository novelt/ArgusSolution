<?php

namespace AppBundle\Entity\WebApi;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

 /**
 * @JMS\ExclusionPolicy("all")
 */
class WebApiDiseaseValues
{
    /**
     * @JMS\Expose()
     */
    public $id;

    /**
     * @JMS\Expose()
     * @JMS\SerializedName("val")
     */
    public $value;

    /**
     * @JMS\Expose()
     * @JMS\SerializedName("mval")
     */
    public $thresholdMaxValue;

    /**
     * @JMS\Expose()
     * @JMS\SerializedName("nam")
     */
    public $name;

    /**
     * @JMS\Expose()
     */
    public $translatedName;

    /**
     * @JMS\Expose
     * @JMS\SerializedName("new")
     * @JMS\Type("boolean")
     */
    public $isDifferent;

    /**
     * @JMS\Expose
     * @JMS\SerializedName("mor")
     * @JMS\Type("boolean")
     */
    public $isMore;

    /**
     * @JMS\Expose
     * @JMS\SerializedName("les")
     * @JMS\Type("boolean")
     */
    public $isLess;


	/**
     * @JMS\Expose()
     * @JMS\SerializedName("per")
     */
    public $period;

    public function __construct()
    {

    }
}
