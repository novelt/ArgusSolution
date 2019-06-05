<?php

namespace AppBundle\Entity\WebApi;

use AppBundle\Entity\PermissionSite;

use JMS\Serializer\Annotation as JMS;


 /**
 * @JMS\ExclusionPolicy("all")
 */
class WebApiSite implements PermissionSite
{
	/**
     * @JMS\Expose
     * @JMS\Type("integer")
     */
    public $id;

	/**
     * @JMS\Expose
     * @JMS\SerializedName("ref")
     */
    public $reference;

    /**
     * @JMS\Expose
     * @JMS\SerializedName("nam")
     */
    public $name;

    /**
     * @JMS\Expose
     * @JMS\Type("integer")
     * @JMS\SerializedName("pid")
     */
    public $parentId ;

     /**
     * @JMS\Expose
     * @JMS\Type("double")
     * @JMS\SerializedName("lon")
     */
    public $longitude;

    /**
     * @JMS\Expose
     * @JMS\Type("double")
     * @JMS\SerializedName("lat")
     */
    public $latitude;

    /**
     * @JMS\Expose
     * @JMS\Type("integer")
     * @JMS\SerializedName("lvl")
     */
    public $level;

    /**
     * @JMS\Expose
     * @JMS\Type("boolean")
     * @JMS\SerializedName("hom")
     */
    public $home;

    /**
     * @JMS\Expose
     * @JMS\Type("boolean")
     * @JMS\SerializedName("hpa")
     */
    public $homePath;

    /**
     * @JMS\Expose
     * @JMS\Type("boolean")
     * @JMS\SerializedName("acc")
     */
    public $accessible;

    /**
     * @JMS\Expose
     * @JMS\Type("boolean")
     * @JMS\SerializedName("exp")
     */
    public $export;

    /**
     * @JMS\Expose
     * @JMS\Type("boolean")
     * @JMS\SerializedName("delw")
     */
    public $deletedWeekly;

    /**
     * @JMS\Expose
     * @JMS\Type("boolean")
     * @JMS\SerializedName("delm")
     */
    public $deletedMonthly;

    /**
     * @JMS\Expose
     * @JMS\Type("boolean")
     * @JMS\SerializedName("uplw")
     */
    public $uploadableWeekly;

    /**
     * @JMS\Expose
     * @JMS\SerializedName("chi")
     */
    public $children;

    public $path;

    /**
     * @var string
     */
    public $reportDataSourceCode;

    /**
     * @var int
     */
    public $reportDataSourceId;

    public function __construct()
    {

    }

    public function getId()
    {
        return $this->id;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function getReportDataSourceId()
    {
        // TODO: Implement getReportDataSourceId() method.
    }
}
