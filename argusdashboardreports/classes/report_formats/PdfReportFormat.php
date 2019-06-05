<?php
class PdfReportFormat extends ReportFormatBase {
    
    public static function display(&$report, &$request) {

        $report->options['inline_email'] = true;
        $report->use_cache = true;

        $converter = new PdfConverter();
        $converter->convertReport($_SERVER['QUERY_STRING'], $_GET['report']);
    }
}
