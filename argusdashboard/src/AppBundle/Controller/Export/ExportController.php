<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 09/08/2016
 * Time: 15:24
 */

namespace AppBundle\Controller\Export;


use AppBundle\Controller\BaseController;
use AppBundle\Entity\SesAlert;
use AppBundle\Entity\SesFullReport;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Utils\Response\CsvResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends BaseController
{
    /**
     * Export Big Data into a CSV file format
     *
     * @param $display
     * @param $startDate
     * @param $endDate
     * @param $period
     * @return StreamedResponse
     */
    public function exportReportDataAction($display, $startDate, $endDate, $period){
        // get the service container to pass to the closure
        set_time_limit(0);

        // Test request variables
        $display = ($display == 'null' ? 'ALL' : $display);
        $startDate = ($startDate == 'null' ? null : $startDate);
        $endDate = ($endDate == 'null' ? null : $endDate);
        $period = ($period == 'null' ? null : $period);

        $container = $this->container;

        $response = new StreamedResponse(function() use($container, $display, $startDate, $endDate, $period) {

            gc_enable();

            $em = $container->get('doctrine')->getManager();

            // Deactivate Sql logger to avoid memory issues
            $em->getConnection()->getConfiguration()->setSQLLogger(null);

            // The getExportQuery method returns a query that is used to retrieve
            // all the objects (lines of your csv file) you need. The iterate method
            // is used to limit the memory consumption
            $results = $em->getRepository('AppBundle:SesFullReport')->getExportQuery($display, $startDate, $endDate, $period)->iterate();
            $handle = fopen('php://output', 'r+');

            // Add Headers to Csv file
            $headerRow = SesFullReport::getHeaderCsvRow();
            fputcsv($handle, $headerRow);

            while (false !== ($row = $results->next())) {
                $temp = $row[0]->getCsvRow();

                for($i=0;$i<count($temp);$i++) {
                    fputcsv($handle, $temp[$i]);
                }

                // used to limit the memory consumption
                $em->detach($row[0]);
                $em->clear();

                unset($temp);
                gc_collect_cycles();
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition','attachment; filename="Argus.csv"');

        return $response;
    }



    public function exportAlertDataAction($startDate, $endDate){
        // get the service container to pass to the closure
        set_time_limit(0);

        // Test request variables
        $startDate = ($startDate == 'null' ? null : $startDate);
        $endDate = ($endDate == 'null' ? null : $endDate);

        $container = $this->container;

        $response = new StreamedResponse(function() use($container, $startDate, $endDate) {

            gc_enable();

            $em = $container->get('doctrine')->getManager();

            // Deactivate Sql logger to avoid memory issues
            $em->getConnection()->getConfiguration()->setSQLLogger(null);

            // The getExportQuery method returns a query that is used to retrieve
            // all the objects (lines of your csv file) you need. The iterate method
            // is used to limit the memory consumption
            $results = $em->getRepository('AppBundle:SesAlert')->getExportQuery($startDate, $endDate)->iterate();
            $handle = fopen('php://output', 'r+');

            // Add Headers to Csv file
            $headerRow = SesAlert::getHeaderCsvRow();
            fputcsv($handle, $headerRow);

            while (false !== ($row = $results->next())) {

                $csvRow = $row[0]->getCsvRow();

                fputcsv($handle, $csvRow);

                // used to limit the memory consumption
                $em->detach($row[0]);
                $em->clear();

                unset($temp);
                gc_collect_cycles();
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition','attachment; filename="Alerts.csv"');

        return $response;
    }
}