<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 13/02/2018
 * Time: 10:46
 */

namespace AppBundle\Entity\WebApi;

use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\ExclusionPolicy("all")
 */
class WebApiReportData
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
     * @JMS\SerializedName("sid")
     * @JMS\Type("integer")
     */
    public $FK_SiteId;

    /**
     * @JMS\Expose
     * @JMS\SerializedName("sna")
     */
    public $FK_SiteName;

    /**
     * @JMS\Expose
     * @JMS\SerializedName("srid")
     * @JMS\Type("integer")
     */
    public $FK_SiteRelationShipId;

    /**
     * @JMS\Expose
     * @JMS\SerializedName("sdat")
     */
    public $startDate ;

    /**
     * @JMS\Expose
     * @JMS\SerializedName("edat")
     */
    public $endDate ;

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
     * @JMS\SerializedName("sts")
     */
    public $status;

    /**
     * @JMS\Expose
     * @JMS\SerializedName("agg")
     * @JMS\Type("boolean")
     */
    public $aggregate;

    /**
     * @JMS\Expose
     * @JMS\SerializedName("crd")
     */
    public $createdDate;

    /**
     * @JMS\Expose
     * @JMS\SerializedName("fvd")
     */
    public $firstValidationDate;

    /**
     * @JMS\Expose
     * @JMS\SerializedName("frd")
     */
    public $firstRejectionDate;

    /**
     * @JMS\Expose
     * @JMS\SerializedName("val")
     * @JMS\Type("boolean")
     */
    public $canValidate;

    /**
     * @JMS\Expose
     * @JMS\SerializedName("rej")
     * @JMS\Type("boolean")
     */
    public $canReject;
}