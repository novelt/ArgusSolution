<?php

/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 3/8/2016
 * Time: 10:10 AM
 */
class DashboardRestApi
{
    static function getUrl($type, $range, $site, $disease, $period, $locale){

        $addressBase = PhpReports::$config['ArgusDashboardHost'];
        $services = PhpReports::$config['ArgusDashboardReportServices'];

        if (empty($locale)) {
            $address = $addressBase . substr($services, 1, strlen($services) - 1) . $type;
        } else {
            $address = $addressBase . $locale . $services . $type;
        }

        $start = strtotime($range['start']);
        $end = strtotime($range['end']);

        $address .= "/" .date('Y-m-d',$start);
        $address .= "/" .date('Y-m-d',$end);
        $address .= "/" .$site ;
        $address .= "/" .$disease ;
        $address .= "/" .$period ;

        return $address;
    }
}
