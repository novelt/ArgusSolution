<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 27/06/2016
 * Time: 12:26
 */

namespace AppBundle\Entity\Import\Report;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 *  @JMS\XmlRoot("report")
 *  @JMS\ExclusionPolicy("all")
 */
class XmlReport
{
    /**
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    private $contact;

    /**
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    private $phoneNumber;

    /**
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    private $site;

    /**
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    private $receptionDate;

    /**
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    private $disease;

    /**
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    private $period;

    /**
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    private $startDate;

    /**
     * @JMS\Expose()
     * @JMS\Type("integer")
     */
    private $week;

    /**
     * @JMS\Expose()
     * @JMS\Type("integer")
     */
    private $month;

    /**
     * @JMS\Expose()
     * @JMS\Type("integer")
     */
    private $year;

    /**
     * @JMS\Expose()
     * @JMS\Type("integer")
     */
    private $reportId;

    /**
     * @JMS\Expose()
     * @JMS\Type("AppBundle\Entity\Import\Report\XmlReportValues")
     */
    private $values;

    public function addValue($valueReference, $data) {
        $reportValue = new XmlReportValue();
        $reportValue->setValueReference($valueReference);
        $reportValue->setData($data);

        if($this->values === null) {
            $this->values = new XmlReportValues();
        }

        $this->values->addReportValue($reportValue);
    }


    public function getSiteReference()
    {
        return $this->site ;
    }

    public function getPeriod()
    {
        return $this->period;
    }

    public function getStartDate()
    {
        $date = date_create($this->startDate);
        return $date ;
    }

    public function getWeekNumber()
    {
        return $this->week;
    }

    public function getMonthNumber()
    {
        return $this->month;
    }

    public function getYear()
    {
        return $this->year;
    }

    public function getContactName()
    {
        return $this->contact;
    }

    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    public function getDisease()
    {
        return $this->disease;
    }

    /**
     * @param mixed $phoneNumber
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @param mixed $receptionDate
     */
    public function setReceptionDate($receptionDate)
    {
        $this->receptionDate = $receptionDate;
    }

    /**
     * @param mixed $disease
     */
    public function setDisease($disease)
    {
        $this->disease = $disease;
    }

    /**
     * @param mixed $period
     */
    public function setPeriod($period)
    {
        $this->period = $period;
    }

    /**
     * @param mixed $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @param mixed $year
     */
    public function setYear($year)
    {
        $this->year = $year;
    }

    /**
     * @param mixed $reportId
     */
    public function setReportId($reportId)
    {
        $this->reportId = $reportId;
    }

    public function getReceptionDate()
    {
        $date = date_create($this->receptionDate);
        return $date ;
    }

    public function getReportId()
    {
        return $this->reportId;
    }

    public function getReportValues()
    {
        return $this->values ;
    }

    /**
     * @return mixed
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param mixed $contact
     */
    public function setContact($contact)
    {
        $this->contact = $contact;
    }

    /**
     * @return mixed
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @param mixed $site
     */
    public function setSite($site)
    {
        $this->site = $site;
    }

    /**
     * @return mixed
     */
    public function getWeek()
    {
        return $this->week;
    }

    /**
     * @param mixed $week
     */
    public function setWeek($week)
    {
        $this->week = $week;
    }

    /**
     * @return mixed
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * @param mixed $month
     */
    public function setMonth($month)
    {
        $this->month = $month;
    }

    /**
     * @return mixed
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @param mixed $values
     */
    public function setValues($values)
    {
        $this->values = $values;
    }
}