<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 30/11/2017
 * Time: 11:09
 */

namespace AppBundle\Utils;


class DimDateHelper
{
    /**
     * Create DimDateId from DateTime
     *
     * @param \DateTime $date
     * @return int|null
     */
    public static function getDimDateIdFromDateTime($date)
    {
        if ($date == null || !$date instanceof \DateTime) {
            return null;
        }

        $year = $date->format('Y');
        $month = $date->format('m');
        $day = $date->format('d');

        return intval($year.$month.$day) ;
    }

    /**
     * Create DimDateId from string. Assume format is Y-m-d
     *
     * @param string $date
     * @return int|null
     */
    public static function getDimDateIdFromString($date)
    {
        $date = date_create_from_format('Y-m-d', $date);
        return DimDateHelper::getDimDateIdFromDateTime($date);
    }

    /**
     * Get the Today DimDateId
     *
     * @return int|null
     */
    public static function getDimDateTodayId()
    {
        return DimDateHelper::getDimDateIdFromDateTime(new \DateTime());
    }
}