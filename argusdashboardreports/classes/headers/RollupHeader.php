<?php
class RollupHeader extends HeaderBase {
	static $validation = array(
		'columns'=>array(
			'required'=>true,
			'type'=>'object',
			'default'=>array()
		),
		'dataset'=>array(
			'required'=>false,
			'default'=>0
		),
		'defaultSum'=>array(
			'required'=>false,
			'type'=>'boolean',
			'default'=>false,
		)
	);
	
	public static function init($params, &$report) {
		//make sure at least 1 column is defined
		if(empty($params['columns'])) throw new Exception("Rollup header needs at least 1 column defined");
		
		if(!isset($report->options['Rollup'])) $report->options['Rollup'] = array();
		
		// If more than one dataset is defined, add the rollup header multiple times
		if(is_array($params['dataset'])) {
			$new_params = $params;
			foreach($params['dataset'] as $dataset) {
				$new_params['dataset'] = $dataset;
				$report->options['Rollup'][] = $new_params;
			}
		}
		// Otherwise, just add one rollup header
		else {
			$report->options['Rollup'][] = $params;
		}
	}
	
	public static function beforeRender(&$report) {			
		//cache for Twig parameters for each dataset/column
		$twig_params = array();
		
		// Now that we know how many datasets we have, expand out Rollup headers with dataset->true
		$new_rollups = array();
		foreach($report->options['Rollup'] as $i=>$rollup) {
			if($rollup['dataset']===true) {
				$copy = $rollup;
				foreach($report->options['DataSets'] as $i=>$dataset) {
					$copy['dataset'] = $i;
					$new_rollups[] = $copy;
				}
			}
			else {
				$new_rollups[] = $rollup;
			}
		}
		$report->options['Rollup'] = $new_rollups;
		
		// First get all the values
		foreach($report->options['Rollup'] as $rollup) {			
			// If we already got twig parameters for this dataset, skip it
			if(isset($twig_params[$rollup['dataset']])) continue;
			$twig_params[$rollup['dataset']] = array();
			foreach($report->options['DataSets'][$rollup['dataset']]['rows'] as $row) {
				foreach($row['values'] as $value) {
					if(!isset($twig_params[$rollup['dataset']][$value->key])) $twig_params[$rollup['dataset']][$value->key] = array('values'=>array());
					$twig_params[$rollup['dataset']][$value->key]['values'][] = $value->getValue();
				}
			}
		}

		// Then, calculate other statistical properties
		$m = array();
		foreach($twig_params as $dataset=>&$tp) {
			foreach($tp as $column=>&$params) {
				//get non-null values and sort them
				//echo $column;
				
				$real_values = array_filter($params['values'],function($a) {if($a === null || $a==='') return false; return true; });
				sort($real_values);
				
				$m[$column] = $params['sum'] = array_sum($real_values);
				$params['count'] = count($real_values);
				$params['mean'] = $params['average'] = $params['count'] != 0 ? round($params['sum'] / $params['count'],2) : 0;
				if (is_numeric($params['count'])) {  // $params['median'] = ($params['count'] % 2) ? ($real_values[$params['count'] / 2] + $real_values[$params['count'] / 2 + 1]) / 2 : $real_values[ceil($params['count'] / 2)];
				   if (($params['count'] % 2)
                       && array_key_exists((int)($params['count'] / 2), $real_values)
                       && array_key_exists((int)($params['count'] / 2 + 1), $real_values)
                       && is_numeric($real_values[$params['count'] / 2])
                       && is_numeric($real_values[$params['count'] / 2 + 1])) {
                       $params['median'] = ($real_values[$params['count'] / 2] + $real_values[$params['count'] / 2 + 1]) / 2;
                   } else {
                       $ceil = (int) ceil($params['count'] / 2);
                       if (array_key_exists($ceil, $real_values)) {
                           $params['median'] = $real_values[$ceil];
                       }
                   }
                } else {
                    $params['median'] = '';
                }
				$params['min'] = $params['count'] != 0 ? $real_values[0] : 0;
				$params['max'] = $params['count'] != 0 ? $real_values[$params['count']-1] : 0;
								
				$devs = array();
				foreach($real_values as $v) {
				    if (is_numeric($v)) {
                        $devs[] = pow($v - $params['mean'], 2);
                    }
                }

				if ((count($devs) - 1) != 0) { // avoid division by 0
                    $params['stdev'] = sqrt(array_sum($devs) / (count($devs) - 1));
                }
			}
		}

		//render each rollup row
		foreach($report->options['Rollup'] as $rollup) {
			if(!isset($report->options['DataSets'][$rollup['dataset']]['footer'])) $report->options['DataSets'][$rollup['dataset']]['footer'] = array();
			$columns = $rollup['columns'];
			$row = array(
				'values'=>array(),
				'rollup'=>true
			);

			$index = 0;
			foreach($twig_params[$rollup['dataset']] as $column=>$p) {

				if(isset($columns[$column])) {
					$p = array_merge($p,array('row'=>$twig_params[$rollup['dataset']]));
					$row['values'][] = new ReportValue(-1,$column,PhpReports::renderString($columns[$column],$p));
				}
				// Each row will have {{sum}} rollup
				elseif ($rollup['defaultSum'] == true){
					$p = array_merge($p,array('row'=>$twig_params[$rollup['dataset']]));
					$row['values'][] = new ReportValue(-1,$column,PhpReports::renderString('{{sum}}',$p));
				}
				else {
					$row['values'][] = new ReportValue(-1,$column,null);
				}

				$index ++ ;
			}
			$report->options['DataSets'][$rollup['dataset']]['footer'][] = $row;
		}
	}
}
