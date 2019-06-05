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
class WebApiRScript
{
    /**
     * @JMS\Expose
     * @JMS\SerializedName("tit")
     */
    public $title;

    /**
     * @JMS\Expose
     * @JMS\SerializedName("dir")
     */
    public $directory;

    /**
     * @JMS\Expose
     * @JMS\SerializedName("fil")
     */
    public $fileName;
}