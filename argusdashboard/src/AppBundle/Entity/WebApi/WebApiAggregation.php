<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 02/03/2018
 * Time: 14:21
 */

namespace AppBundle\Entity\WebApi;

use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\ExclusionPolicy("all")
 */
class WebApiAggregation
{
    /**
     * @JMS\Expose
     * @JMS\SerializedName("sna")
     */
    public $siteName;

    /**
     * @JMS\Expose
     * @JMS\Type("integer")
     * @JMS\SerializedName("hcp")
     */
    public $nbOfParticipatingHC;

    /**
     * @JMS\Expose
     * @JMS\Type("integer")
     * @JMS\SerializedName("hct")
     */
    public $nbOfTotalHC;
}