<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 23/02/2017
 * Time: 10:44
 */

namespace AppBundle\Services\Timezone;

use AppBundle\Services\BaseService;
use Symfony\Bridge\Monolog\Logger;

class TimezoneService extends BaseService
{
    /**
     * @var Timezone[]
     */
    private $timezones;

    /**
     * @var Timezone
     */
    private $defaultTimezone;

    public function __construct(Logger $logger)
    {
        parent::__construct($logger);
    }

    /**
     * @return Timezone[]
     */
    public function getTimezones() {

        if($this->timezones === null) {

            $this->timezones = [];

            foreach (\DateTimeZone::listIdentifiers() as $timezone) {
                $parts = explode('/', $timezone);

                if (count($parts) > 2) {
                    $region = $parts[0];
                    $name = $parts[1].' - '.$parts[2];
                } elseif (count($parts) > 1) {
                    $region = $parts[0];
                    $name = $parts[1];
                } else {
                    $region = 'Other';
                    $name = $parts[0];
                }

                $tz = new Timezone();
                $tz->setId($timezone);
                $tz->setRegion($region);
                $tz->setName($name);

                $this->timezones[] = $tz;
            }
        }
        return $this->timezones;
    }

    /**
     * @return Timezone
     */
    public function getDefaultTimezone()
    {
        if($this->defaultTimezone === null) {
            $defaultTzId = date_default_timezone_get();
            $this->defaultTimezone = $this->getTimezoneById($defaultTzId);
        }

        if($this->defaultTimezone === null) {
            $this->logger->addWarning("No default timezone found");
        }

        return $this->defaultTimezone;
    }

    /**
     * @return \DateTimeZone
     */
    public function getDefaultDateTimezone() {
        $defaultTimezone = $this->getDefaultTimezone();

        //it should never be null
        if($defaultTimezone !== null) {
            return new \DateTimeZone($defaultTimezone->getId());
        }
    }

    /**
     * @param $id
     * @return Timezone|null
     */
    public function getTimezoneById($id)
    {
        $foundTimezone = null;
        $timezones = $this->getTimezones();

        $found = false;
        $i = 0;
        $nbTimezones = sizeof($timezones);

        while (!$found && $i < $nbTimezones) {
            $currentTz = $timezones[$i];

            if (strtoupper($currentTz->getId()) == strtoupper($id)) {
                $foundTimezone = $currentTz;
                $found = true;
            } else {
                $i++;
            }
        }

        return $foundTimezone;
    }

    /**
     * @param $id
     * @return \DateTimeZone|null
     */
    public function getDateTimezoneById($id) {
        try {
            $timezone = $this->getTimezoneById($id);

            if($timezone === null) {
                return null;
            }

            $dateTimeZone = new \DateTimeZone($id);

            if(!$dateTimeZone) {
                return null;
            }
            else {
                return $dateTimeZone;
            }
        }
        catch(\Exception $e) {
            return null;
        }
    }

    /**
     * @param Timezone $defaultTimezone
     */
    public function setDefaultTimezone($defaultTimezone)
    {
        $this->defaultTimezone = $defaultTimezone;
    }
}