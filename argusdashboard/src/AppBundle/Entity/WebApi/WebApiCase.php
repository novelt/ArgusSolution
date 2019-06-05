<?php

namespace AppBundle\Entity\WebApi;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;


 /**
 * @JMS\ExclusionPolicy("all")
 */
class WebApiCase
{
    // SesReport Values

	/**
     * @JMS\Expose
     */
    public $id;

    /**
     * @JMS\Expose
     */
    public $name;

    /**
     * @JMS\Expose
     */
    public $value;


    // SesReport

    /**
     * @JMS\Expose
     */
    public $diseaseName ;

    /**
     * @JMS\Expose
     */
    public $diseaseReference ;

    /**
     * @JMS\Expose
     */
    public $diseaseId ;

    /**
     * @JMS\Expose
     */
    public $diseaseValueId ;

    /**
     * @JMS\Expose
     */
    public $diseaseValueReference ;

    /**
     * @JMS\Expose
     */
    public $receptionDate ;


    // SesPartReport

    /**
     * @JMS\Expose
     */
    public $status ;

    // SesFullReport

    /**
     * @JMS\Expose
     */
    public $startDate ;

    /**
     * @JMS\Expose
     * @JMS\Type("integer")
     */
    public $weekNumber = null ;

    /**
     * @JMS\Expose
     * @JMS\Type("integer")
     */
    public $monthNumber = null ;


    // SesSite

    /**
     * @JMS\Expose
     */
    public $siteId ;

    /**
     * @JMS\Expose
     */
    public $siteName ;

    /**
     * @JMS\Expose
     * @JMS\Type("double")
     */
    public $longitude ;

    /**
     * @JMS\Expose
     * @JMS\Type("double")
     */
    public $latitude ;

    public function __construct()
    {

    }
}
