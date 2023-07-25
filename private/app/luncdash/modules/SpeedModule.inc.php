<?php

namespace LUNCDash\Modules;

use Framework\Core\Database;
use Framework\Core\Date;
use Framework\Core\Date\Format;
use Framework\Core\Modules\Base as ModuleBase;
use Framework\Core\Template;

class SpeedModule extends ModuleBase {
	/**
	 * @param string $action
	 */
	public static function onBlockChain() {

		Template::load('charts/speed');

		$datafile = 'data_speed.js';

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
							'label' => 'Seconds per block',
							'data' => [],
							'borderColor' => '#ffa600'
						]
					]
				]
			];

			$qrystr = 'SELECT `date`, IF(CURDATE() = `date`, TIMESTAMPDIFF(SECOND, CURDATE(), NOW()), 86400)/COUNT(*) as bps FROM blocks WHERE 1 GROUP BY `date`';
			$result = Database::query($qrystr);
			while(($cur = $result->get())) {
				$data['data']['labels'][] = $cur['date'];
				$data['data']['datasets'][0]['data'][] = $cur['bps'];
			}
			$result->free();

			file_put_contents($file, json_encode($data));
		}

		Template::load('main');
		//Template::assign('META_DESC', HTML::toEntities($meta_data));
		Template::assign('LOAD_SCRIPT', 'linechart.js');
		Template::assign('PRE_SCRIPT', 'let data = ' . file_get_contents($file) . '; config.data = data.data;');
		Template::add(Template::get('charts/speed'));
		Template::show();

		exit;
	}
}
