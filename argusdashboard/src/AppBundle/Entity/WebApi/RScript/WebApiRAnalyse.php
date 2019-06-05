<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 04/07/2018
 * Time: 12:05
 */

namespace AppBundle\Entity\WebApi\RScript;

use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\ExclusionPolicy("all")
 */
class WebApiRAnalyse
{
    /**
     * @JMS\Expose
     * @JMS\SerializedName("tit")
     */
    public $title;

    /**
     * @JMS\Expose
     * @JMS\SerializedName("siz")
     */
    public $size;

    /**
     * @JMS\Expose
     * @JMS\SerializedName("dat")
     */
    public $date;

    /**
     * @JMS\Expose
     * @JMS\SerializedName("ext")
     */
    public $extension;

}