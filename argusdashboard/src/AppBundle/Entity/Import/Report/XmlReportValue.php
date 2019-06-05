<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 27/06/2016
 * Time: 14:30
 */

namespace AppBundle\Entity\Import\Report;

use JMS\Serializer\Annotation as JMS;

/**
 *  @JMS\XmlRoot("value")
 *  @JMS\ExclusionPolicy("all")
 */
class XmlReportValue
{
    /**
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    private $valueReference;

    /**
     * @JMS\Expose()
     * @JMS\Type("integer")
     */
    private $data;

    public function getValueReference()
    {
        return $this->valueReference;
    }

    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $valueReference
     */
    public function setValueReference($valueReference)
    {
        $this->valueReference = $valueReference;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }
}