<?php

namespace LUNCDash\Modules;

use Framework\Core\Database;
use Framework\Core\Date;
use Framework\Core\Date\Format;
use Framework\Core\Modules\Base as ModuleBase;
use Framework\Core\Template;
use LUNCDash\Lib\Chain;

class BurnTaxModule extends ModuleBase {
	/**
	 * @param string $action
	 */
	public static function onShow() {

		Template::load('charts/burn-tax');

		$burned = Chain::getTaxBurned('uluna');
		$burned_ust = Chain::getTaxBurned('uusd');

		$datafile = 'data_burntax.js';

		$file = sys_get_temp_dir() . '/' . $datafile;
		if(file_exists($file) && filesize($file) > 10) {
			$mtime = filemtime($file);
		} else {
			$mtime = 0;
		}
		$lifetime = (60 * 10);
		if($mtime < time() - $lifetime) {
			$now = Format::toSQL(Date::getTime());

			$data = [
				'last_update' => $now,
				'data' => [
					'labels' => [],
					'datasets' => [
						[
							'label' => 'Burned LUNC',
							'data' => [],
							'borderColor' => '#ffa600',
							'yAxisID' => 'y',
						],
						[
							'label' => 'Burned USTC',
							'data' => [],
							'hidden' => true,
							'borderColor' => '#00ecff',
							'yAxisID' => 'y',
						],
						[
							'type' => 'bar',
							'label' => 'Burn volume LUNC',
							'data' => [],
							'backgroundColor' => '#903000',
							'yAxisID' => 'y1',
						],
						[
							'type' => 'bar',
							'label' => 'Burn volume USTC',
							'data' => [],
							'hidden' => true,
							'backgroundColor' => '#309000',
							'yAxisID' => 'y1',
						]
					]
				]
			];

			$pre = 0.0;
			$pre_usdt = 0.0;
			$qrystr = 'SELECT MAX(t.tax_uluna) as `uluna`, MAX(t.tax_uusd) as `uusd`, b.date FROM tax_epoch as t INNER JOIN `blocks` as b ON (b.block = t.block) GROUP BY b.date';
			$result = Database::query($qrystr);
			while(($cur = $result->get())) {
				$data['data']['labels'][] = $cur['date'];
				$data['data']['datasets'][0]['data'][] = $cur['uluna'];
				$data['data']['datasets'][1]['data'][] = $cur['uusd'];
				$data['data']['datasets'][2]['data'][] = (float)$cur['uluna'] - $pre;
				$data['data']['datasets'][3]['data'][] = (float)$cur['uusd'] - $pre_usdt;
				$pre = (float)$cur['uluna'];
				$pre_usdt = (float)$cur['uusd'];
			}
			$result->free();

			file_put_contents($file, json_encode($data));
		}

		Template::assign('burned_by_tax', preg_replace('/\.(\d+)$/', '.<small>$1</small>', number_format($burned, 6, '.', ',')));
		Template::assign('burned_by_tax_ust', preg_replace('/\.(\d+)$/', '.<small>$1</small>', number_format($burned_ust, 6, '.', ',')));
		
		Template::load('main');
		//Template::assign('META_DESC', HTML::toEntities($meta_data));
		Template::assign('LOAD_SCRIPT', 'mixchart.js');
		Template::assign('PRE_SCRIPT', 'let data = ' . file_get_contents($file) . '; config.data = data.data;');
		Template::add(Template::get('charts/burn-tax'));
		Template::show();

		exit;
	}

}
