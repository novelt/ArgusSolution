<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 2/3/2016
 * Time: 10:41 AM
 */

namespace AppBundle\Utils;

use DateTime;

class Epidemiologic
{
    /**
     *
     *  Return Mode of MySql Week number calculation
     *  WEEK(date[,mode])
     *  https://dev.mysql.com/doc/refman/5.5/en/date-and-time-functions.html#function_week
     *  Mode    First day of week    Range    Week 1 is the first week …
     *  0        Sunday                0-53    with a Sunday in this year
     *  1        Monday                0-53    with 4 or more days this year
     *  2        Sunday                1-53    with a Sunday in this year
     *  3        Monday                1-53    with 4 or more days this year
     *  4        Sunday                0-53    with 4 or more days this year
     *  5        Monday                0-53    with a Monday in this year
     *  6        Sunday                1-53    with 4 or more days this year
     *  7        Monday                1-53    with a Monday in this year
     *
     * @param $epiFirstDay
     * @return int
     */
    public static function GetMySqlWeekMode($epiFirstDay)
    {
        switch ($epiFirstDay) {
            case 1 :
                return 3;
            case 7 :
                return 6;

            // TODO Mysql WEEK function doesn't support mode when others days are first day of week
            default :
                return 3;
        }
    }

    /**
     * Return MySql WeekDay index for date (0 = Monday, 1 = Tuesday, … 6 = Sunday).
     * https://dev.mysql.com/doc/refman/5.5/en/date-and-time-functions.html#function_weekday
     *
     * @param $epiFirstDay
     * @return mixed
     */
    public static function GetMysqlWeekDay($epiFirstDay)
    {
        return $epiFirstDay - 1;
    }

    /**
     * Get epidemiology year and week from unix timestamp
     *
     *
     * @param $ts
     * @param $epiFirstDay
     * @return array
     */
    public static function Timestamp2Epi($ts, $epiFirstDay)
    {
        $result = array();

        // Case when Epi first Day is Monday (ISO 8601)
        if ($epiFirstDay == 1) {
            $result['Year'] = intval(date("o", $ts));
            $result['Week'] = intval(date("W", $ts));
        } else {
            $firstDayNameOfWeek = Epidemiologic::GetDayOfWeek($epiFirstDay);
            $lastDayNameOfWeek = Epidemiologic::GetDayOfWeek($epiFirstDay - 1);

            // Get Name of Day of week from Timestamp
            $dayName = date('l', $ts);
            $firstDayOfWeek = $ts;
            if ($dayName != $firstDayNameOfWeek) {
                $firstDayOfWeek = strtotime('Last ' . $firstDayNameOfWeek, $firstDayOfWeek);
            }

            $yearFirstDayOfWeek = intval(date("Y", $firstDayOfWeek));
            $yearLastDayOfWeek = intval(date("Y", strtotime('Next ' . $lastDayNameOfWeek, $firstDayOfWeek)));

            // We have got the Year of the first day of Week
            $result['Year'] = intval(date("Y", $firstDayOfWeek));

            //If years are different, we need to test the last day of week to know if this week in in next year or not
            if ($yearFirstDayOfWeek < $yearLastDayOfWeek) {
                $lastDayOfWeek = strtotime('Next ' . $lastDayNameOfWeek, $firstDayOfWeek);
                $dayNumber = date('d', $lastDayOfWeek);

                if ($dayNumber >= 4)
                    $result['Year']++;

            }

            $firstDayOfWeekOne = Epidemiologic::GetTimeStampForFirstDayOfWeekOne($epiFirstDay, $result['Year']);

            // We do the difference between sunday of week one and our sunday
            $diffDays = abs($firstDayOfWeek - $firstDayOfWeekOne) / 60 / 60 / 24;

            // We get the week number
            $weekNumber = round($diffDays / 7) + 1;

            $result['Week'] = $weekNumber;
        }

        return $result;
    }

    /**
     * Get TimeStamp for first day of Week regarding epi first day
     *
     * @param $weekNumber
     * @param $year
     * @param $epi
     * @return int|null
     */
    public static function GetFirstDayOfWeek($weekNumber, $year, $epi)
    {
        $ts = null;

        // Case when Epi first Day is Monday (ISO 8601)
        if ($epi == 1) {
            $date = new DateTime();
            $date->setISODate($year, $weekNumber, 1);
            $ts = mktime(0, 0, 0, date("m", $date->getTimestamp()), date("d", $date->getTimestamp()), date("Y", $date->getTimestamp()));
        } else {
            $firstDayOfWeekOne = self::GetTimeStampForFirstDayOfWeekOne($epi, $year);

            //Now we get the first day of Week number '$Week'
            $ts = strtotime('+' . (($weekNumber - 1) * 7) . ' days', $firstDayOfWeekOne);

            return $ts;
        }

        return $ts;
    }

    /**
     * Get TimeStamp for last day of Week regarding epi first day
     *
     * @param $weekNumber
     * @param $year
     * @param $epi
     * @return int
     */
    public static function GetLastDayOfWeek($weekNumber, $year, $epi)
    {
        return strtotime("+ 6 days", Epidemiologic::GetFirstDayOfWeek($weekNumber, $year, $epi));
    }


    /**
     * Get Time stamp for first day of week regarding the configuration of first day
     *
     * @param $dayNumber
     * @param $year
     * @return int
     */
    public static function GetTimeStampForFirstDayOfWeekOne($dayNumber, $year)
    {
        $firstDayOfWeek = Epidemiologic::GetDayOfWeek($dayNumber);
        $lastDayOfWeek = Epidemiologic::GetDayOfWeek($dayNumber - 1);

        // We need to find last day of first week
        $lastDayOfFirstWeek = Epidemiologic::GetLastDayOfFirstWeek($lastDayOfWeek, $year);

        // We get the first day of Week 1
        $firstDayOfWeekOne = strtotime('Last ' . $firstDayOfWeek, $lastDayOfFirstWeek);

        return $firstDayOfWeekOne;
    }

    /**
     * Get number of weeks in one period
     *
     * @param $startDate
     * @param $endDate
     * @param $epiFirstDay
     * @return int
     */
    public static function GetNumberOfWeeks($startDate, $endDate, $epiFirstDay)
    {
        $numberOfWeeks = 0;
        $start = strtotime($startDate);
        $end = strtotime($endDate);

        while ($start < $end) {
            $numberOfWeeks++;
            $start = strtotime("+7 days", $start);
        }

        return $numberOfWeeks;
    }

    public static function getNumberOfWeeksInYear($year, $epiFirstday)
    {
        $timeStamp = self::GetFirstDayOfWeek(1, $year, $epiFirstday);
        //$weekNumber = self::Timestamp2Epi($firstDayOfFirstWeek, $epiFirstday)['Week'];
        //$year =  self::Timestamp2Epi($firstDayOfFirstWeek, $epiFirstday)['Year'];

        $numberOfWeek = 0;

        do {
            $numberOfWeek++;
            $timeStamp = strtotime("+7 days", $timeStamp);
            $weekNumber = self::Timestamp2Epi($timeStamp, $epiFirstday)['Week'];

        } while ($weekNumber != 1);

        return $numberOfWeek;
    }

    /**
     * Get number of months in one period
     *
     * @param $startDate
     * @param $endDate
     * @return int
     */
    public static function GetNumberOfMonths($startDate, $endDate)
    {
        $numberOfMonths = 0;
        $start = strtotime($startDate);
        $end = strtotime($endDate);

        while ($start < $end) {
            $numberOfMonths++;
            $start = strtotime("+1 month", $start);
        }

        return $numberOfMonths;
    }

    /**
     * Get month name from timestamp
     *
     * @param $ts
     * @return string
     */
    public static function GetMonthName($ts)
    {
        return strftime('%B', $ts);
    }

    /**
     * Get month number from timestamp
     *
     * @param $ts
     * @return string
     */
    public static function GetMonthNumber($ts)
    {
        return strftime('%m', $ts);
    }

    /**
     * Get year from timestamp
     *
     * @param $ts
     * @return int
     */
    public static function GetYear($ts)
    {
        return intval(date("Y", $ts));
    }

    /**
     * Get the last day of first week if this end day is at least number 4 of month
     *
     * @param $day
     * @param $year
     * @return int
     */
    private static function GetLastDayOfFirstWeek($day, $year)
    {
        $firstDayOfFirstWeek = strtotime('First ' . $day, mktime(0, 0, 0, 01, 0, $year));
        // Day number of this day
        $dayNumber = date('d', $firstDayOfFirstWeek);

        // We need that this day Number must be >= 4
        // If not we take the same day of next week
        if ($dayNumber < 4) {
            $firstDayOfFirstWeek = strtotime('Next ' . $day, $firstDayOfFirstWeek);
        }

        return $firstDayOfFirstWeek;
    }


    /**
     * Return name of Week day
     *
     * 1 : Monday
     * 2 : Tuesday
     * 3 : Wednesday
     * 4 : Thursday
     * 5 : Friday
     * 6 : Saturday
     * 7 : Sunday
     *
     * @param $dayNumber
     * @return mixed
     */
    private static function GetDayOfWeek($dayNumber)
    {
        /*  Julian Day
       * 0 : Monday
       * 6 : Sunday
       */
        return jddayofweek($dayNumber - 1, 1);
    }
}