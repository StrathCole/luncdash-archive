<?php

namespace LUNCDash\Modules;

use Framework\Core\Database;
use Framework\Core\Date;
use Framework\Core\Date\Format;
use Framework\Core\Modules\Base as ModuleBase;
use Framework\Core\Template;

class StakingModule extends ModuleBase {
	/**
	 * @param string $action
	 */
	public static function onPool() {

		Template::load('charts/staking-pool');

		$total_supply = Database::queryOneElement('SELECT `total_supply_uluna` FROM `blocks` WHERE `total_supply_uluna` > 0 ORDER BY `block` DESC LIMIT 0,1');
		$bonded = Database::queryOneElement('SELECT `bonded_uluna` FROM `blocks` WHERE `bonded_uluna` > 0 ORDER BY `block` DESC LIMIT 0,1');
		$unbonded = Database::queryOneElement('SELECT `unbonded_uluna` FROM `blocks` WHERE `unbonded_uluna` > 0 ORDER BY `block` DESC LIMIT 0,1');

		$datafile = 'data_stakingpool.js';

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
							'label' => 'Bonded tokens',
							'data' => [],
							'borderColor' => '#ffa600'
						],
						[
							'label' => 'Not bonded tokens',
							'data' => [],
							'borderColor' => '#00ecff',
						]
					]
				]
			];

			$qrystr = 'SELECT bl.date, bl2.bonded_uluna as `bonded`, bl2.unbonded_uluna as `unbonded` FROM (SELECT DATE(bl.time) as `date`, MAX(bl.block) as `latest` FROM `blocks` as bl WHERE bl.bonded_uluna > 0 AND bl.time IS
			NOT NULL GROUP BY DATE(bl.time)) as bl INNER JOIN blocks as bl2 ON (bl2.block = bl.latest) WHERE bl.date >= ?';
			$result = Database::query($qrystr, '2022-01-01');
			while(($cur = $result->get())) {
				$data['data']['labels'][] = $cur['date'];
				$data['data']['datasets'][0]['data'][] = $cur['bonded'];
				$data['data']['datasets'][1]['data'][] = $cur['unbonded'];
			}
			$result->free();

			file_put_contents($file, json_encode($data));
		}

		Template::assign('bonded', preg_replace('/\.(\d+)$/', '.<small>$1</small>', number_format($bonded, 6, '.', ',')));
		Template::assign('unbonded', preg_replace('/\.(\d+)$/', '.<small>$1</small>', number_format($unbonded, 6, '.', ',')));
		Template::assign('staking_ratio', 100 * (($bonded + $unbonded) / $total_supply));
		Template::assign('staking_ratio_bonded', 100 * ($bonded / $total_supply));
		
		Template::load('main');
		//Template::assign('META_DESC', HTML::toEntities($meta_data));
		Template::assign('LOAD_SCRIPT', 'linechart.js');
		Template::assign('PRE_SCRIPT', 'let data = ' . file_get_contents($file) . '; config.data = data.data;');
		Template::add(Template::get('charts/staking-pool'));
		Template::show();

		exit;
	}

	public static function onValidators() {

		Template::load('charts/validators');

		$bonded = Database::queryOneElement('SELECT `bonded_validators` FROM `blocks` WHERE `bonded_validators` > 0 ORDER BY `block` DESC LIMIT 0,1');
		$unbonded = Database::queryOneElement('SELECT `unbonded_validators` FROM `blocks` WHERE `bonded_validators` > 0 ORDER BY `block` DESC LIMIT 0,1');
		$jailed = Database::queryOneElement('SELECT `jailed_validators` FROM `blocks` WHERE `bonded_validators` > 0 ORDER BY `block` DESC LIMIT 0,1');

		$datafile = 'data_validators.js';

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
							'label' => 'Bonded',
							'data' => [],
							'borderColor' => '#ffa600'
						],
						[
							'label' => 'Unbonded',
							'data' => [],
							'borderColor' => '#00ecff',
						],
						[
							'label' => 'Jailed',
							'data' => [],
							'borderColor' => '#ee00ac',
						]
					]
				]
			];

			$qrystr = 'SELECT bl.date, bl2.bonded_validators as `bonded`, bl2.unbonded_validators as `unbonded`, bl2.jailed_validators as `jailed` FROM (SELECT DATE(bl.time) as `date`, MAX(bl.block) as `latest` FROM `blocks` as bl WHERE bl.bonded_validators > 0 AND bl.time IS
			NOT NULL GROUP BY DATE(bl.time)) as bl INNER JOIN blocks as bl2 ON (bl2.block = bl.latest) WHERE bl.date >= ?';
			$result = Database::query($qrystr, '2022-01-01');
			while(($cur = $result->get())) {
				$data['data']['labels'][] = $cur['date'];
				$data['data']['datasets'][0]['data'][] = $cur['bonded'];
				$data['data']['datasets'][1]['data'][] = $cur['unbonded'];
				$data['data']['datasets'][2]['data'][] = $cur['jailed'];
			}
			$result->free();

			file_put_contents($file, json_encode($data));
		}

		Template::assign('bonded', number_format($bonded, 0, '.', ','));
		Template::assign('unbonded', number_format($unbonded, 0, '.', ','));
		Template::assign('jailed', number_format($jailed, 0, '.', ','));

		Template::load('main');
		//Template::assign('META_DESC', HTML::toEntities($meta_data));
		Template::assign('LOAD_SCRIPT', 'linechart.js');
		Template::assign('PRE_SCRIPT', 'let data = ' . file_get_contents($file) . '; config.data = data.data;');
		Template::add(Template::get('charts/validators'));
		Template::show();

		exit;
	}
}
