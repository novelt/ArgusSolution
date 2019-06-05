<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 13/02/2018
 * Time: 15:35
 */

namespace AppBundle\Entity\WebApi;

use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\ExclusionPolicy("all")
 */
class WebApiThresholdData
{
    /**
     * @JMS\Expose
     * @JMS\Type("integer")
     */
    public $id;

    /**
     * @JMS\Expose
     * @JMS\SerializedName("per")
     */
    public $period;

    /**
     * @JMS\Expose
     * @JMS\SerializedName("wnb")
     * @JMS\Type("integer")
     */
    public $weekNumber;

    /**
     * @JMS\Expose
     * @JMS\SerializedName("mnb")
     * @JMS\Type("integer")
     */
    public $monthNumber;

    /**
     * @JMS\Expose
     * @JMS\SerializedName("yea")
     * @JMS\Type("integer")
     */
    public $year;

    /**
     * @JMS\Expose
     * @JMS\SerializedName("mval")
     * @JMS\Type("integer")
     */
    public $maxValue;

    /**
     * @JMS\Expose
     * @JMS\SerializedName("sid")
     * @JMS\Type("integer")
     */
    public $FK_SiteId;

    /**
     * @JMS\Expose
     * @JMS\SerializedName("did")
     * @JMS\Type("integer")
     */
    public $FK_DiseaseId;

    /**
     * @JMS\Expose
     * @JMS\SerializedName("dvid")
     * @JMS\Type("integer")
     */
    public $FK_DiseaseValueId;
}