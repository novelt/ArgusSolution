<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 15/02/2017
 * Time: 15:18
 */

namespace AppBundle\Utils;


use AppBundle\Services\BaseService;
use Symfony\Bridge\Monolog\Logger;

class Parser extends BaseService
{
    public function __construct(Logger $logger)
    {
        parent::__construct($logger);
    }

    /**
     * @param $value
     * @return \DateTime|null
     */
    public function parseDate($value, \DateTimeZone $timezone = null, $format = null) {

        try {
            if ($value !== null && strlen(trim($value)) > 0) {
                try {
                    if($format !== null) {
                        return \DateTime::createFromFormat($format, $value, $timezone);
                    }

                    return new \DateTime($value, $timezone);
                } catch (\Exception $e) {
                }
            }
        }
        catch(\Exception $e){}

        return null;
    }

    /**
     * @param $value
     * @return int|null
     */
    public function parseInteger($value)
    {
        try {
            if ($value !== null && strlen(trim($value)) > 0) {
                $res = filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

                //$res is null when the value starts with '0'
                if($res === null) {
                    //last chance: checks it only contains integers
                    if(preg_match('/\d+/', $value)) {
                        $res = intval($value);
                    }
                }

                return $res;
            }
        }
        catch(\Exception $e){}

        return null;
    }

    /**
     * @param $value
     * @return bool|null
     */
    public function parseBoolean($value)
    {
        try {
            if($value !== null && !$value) {
                return false;
            }

            if ($value !== null && strlen(trim($value)) > 0) {
                $res = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

                if ($res === null) {
                    if (strtoupper($value) == "YES" || strtoupper($value) == "TRUE" || strtoupper($value) == "Y" || strtoupper($value) == "T") {
                        $res = true;
                    } else if ((strtoupper($value) == "NO" || strtoupper($value) == "FALSE" || strtoupper($value) == "N" || strtoupper($value) == "F")) {
                        $res = false;
                    }
                }

                return $res;
            }
        }
        catch(\Exception $e){}

        return null;
    }
}