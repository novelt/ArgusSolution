<?php

/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 4/12/2016
 * Time: 2:28 PM
 */
class PdfConverter
{
    const PDF_OPTIONS_ORIENTATION = '-O Landscape --page-size A4 --print-media-type ';
    const PDF_OPTIONS_JS_DELAY = '--javascript-delay 2000 ';
    const PDF_OPTIONS_WINDOWS_STATUS = ' '; // --window-status iframeLoaded
    const PDF_OPTIONS_FOOTER = '--footer-right "Page [page]/[topage]" --footer-font-size 8 --footer-line ';
    //const PDF_OPTIONS   = '-O Landscape --page-size A4 --print-media-type  --javascript-delay 2000 --window-status iframeLoaded --footer-right "Page [page]/[topage]" --footer-font-size 8 --footer-line '; // --load-error-handling ignore
    const HEADER   = ' --header-left "%header%" --header-font-size 8 --header-spacing 3 ';


    public function convertReport($queryString, $report){
        $sourceUrl = PhpReports::$config['report_base_pdf'] . $queryString;
        $downloadFilename = str_replace('.sql', '', $report).'_'.date('Ymd_Hi').'.pdf';

        self::convert(
            $this->getConvertPdfPath(),
            self::PDF_OPTIONS_ORIENTATION . self::PDF_OPTIONS_JS_DELAY . self::PDF_OPTIONS_WINDOWS_STATUS . self::PDF_OPTIONS_FOOTER,
            $sourceUrl,
            $downloadFilename,
            'attachment',
            '');
    }

    public function convertDashboard($name, $contentDisposition, $title){
        $sourceUrl = PhpReports::$config['dashboard_base_pdf'] . $name;
        $downloadFilename = $name.'.pdf';

        $header = ' ';
        if ($title != null){
            $header = str_replace('%header%',$title, self::HEADER) ;
        }

        self::convert(
            $this->getConvertPdfPath(),
            self::PDF_OPTIONS_ORIENTATION . self::PDF_OPTIONS_JS_DELAY . self::PDF_OPTIONS_FOOTER,
            $sourceUrl,
            $downloadFilename,
            $contentDisposition,
            $header);
    }

    private function getConvertPdfPath() {
        return (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ?
            PhpReports::$config['pdf_converter_path_win'] :
            PhpReports::$config['pdf_converter_path_linux'] ;
    }

    private function convert($pdfConverter, $pdfOptions, $sourceUrl, $downloadFilename, $contentDisposition, $header){

        try {

            if ($contentDisposition == null or $contentDisposition == '')
                $contentDisposition = 'attachment';

           /* if (!file_exists($pdfConverter)) {
                print 'Error : PDF generation error: No PDF converter found.';
                die();
            }*/

            $tmpPdf = @tempnam(__DIR__ .'/temp', 'pdf_');
            if (!$tmpPdf) {
                print 'Error : PDF generation error: unable to create temp file';
                die();
            }

            $cmd = $pdfConverter .' '. $pdfOptions .' '.$header .' "'. $sourceUrl .'" '.$tmpPdf;
            exec($cmd, $output, $rc);

            if ($rc) {
                print 'Error : PDF conversion error. Error code: $rc';
                die();
            }

            header("Content-type: application/pdf");
            header("Content-Disposition: ".$contentDisposition."; filename=" . $downloadFilename );
            header("Content-Transfer-Encoding: binary");
            readfile($tmpPdf);

        }
        catch(Exception $e) {
            print $e->getMessage();
        }
    }

}