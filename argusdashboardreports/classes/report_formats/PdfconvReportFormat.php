<?php

class PdfconvReportFormat extends ReportFormatBase
{
    public static function display(&$report, &$request) {
		
        $report->options['inline_email'] = true;
        $report->use_cache = true;

        try {
                $html = $report->renderReportPage('html/pdf');
                echo $html;
        }
        catch(Exception $e) {

        }
    }
}
