<?php
class XlsReportFormat extends XlsReportBase {
	public static function display(&$report, &$request) {
		// First let set up some headers
		$file_name = preg_replace(array('/[\s]+/','/[^0-9a-zA-Z\-_\.]/'),array('_',''),$report->options['Name']);

		//always use cache for Excel reports
		$report->use_cache = true;

		//run the report
		$report->run();

		if(!$report->options['DataSets']) return;

		// Check titles to not exceed 30 cars here
        foreach ($report->options['DataSets'] as $key => $dataSet) {
            if (isset($dataSet['title']) && strlen($dataSet['title']) > 30) {
                $report->options['DataSets'][$key]['title'] = substr($dataSet['title'], 0, 30);
            }
        }

		$objPHPExcel = parent::getExcelRepresantation($report);

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$file_name.'.xls"');
		header('Pragma: no-cache');
		header('Expires: 0');
		
		$objWriter->save('php://output');
	}
}
