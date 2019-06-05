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
class WebApiReportVersionData
{
    /**
     * @JMS\Expose
     * @JMS\Type("integer")
     */
    public $id;

    /**
     * @JMS\Expose
     * @JMS\SerializedName("cna")
     */
    public $contactName;

    /**
     * @JMS\Expose
     * @JMS\SerializedName("cpn")
     */
    public $contactPhoneNumber;

    /**
     * @JMS\Expose
     * @JMS\SerializedName("sts")
     */
    public $status;

    /**
     * @JMS\Expose
     * @JMS\Type("integer")
     * @JMS\SerializedName("rid")
     */
    public $FK_ReportId;

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


    /**
     * @JMS\Expose
     * @JMS\SerializedName("dis")
     */
    public $diseases;

    /**
     * @JMS\Expose
     * @JMS\Type("integer")
     * @JMS\SerializedName("thcp")
     */
    public $totalNbOfParticipatingHC;

    /**
     * @JMS\Expose
     * @JMS\Type("integer")
     * @JMS\SerializedName("thct")
     */
    public $totalNbOfHC;

    /**
     * @JMS\Expose
     * @JMS\SerializedName("aggs")
     */
    public $aggregations;
}