<?php

namespace LUNCDash\Modules;

use Framework\Core\Database;
use Framework\Core\Date;
use Framework\Core\Date\Format;
use Framework\Core\Modules\Base as ModuleBase;
use Framework\Core\Template;
use LUNCDash\Lib\Wallets;

class SupplyModule extends ModuleBase {
	/**
	 * @param string $action
	 */
	public static function onTotalSupply() {

		Template::load('charts/supply');

		$total_supply = Database::queryOneElement('SELECT `total_supply_uluna` FROM `blocks` WHERE `total_supply_uluna` > 0 ORDER BY `block` DESC LIMIT 0,1');
		$total_supply_ust = Database::queryOneElement('SELECT `total_supply_uusd` FROM `blocks` WHERE `total_supply_uusd` > 0 ORDER BY `block` DESC LIMIT 0,1');

		$datafile = 'data_supply.js';

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
							'label' => 'LUNC',
							'data' => [],
							'borderColor' => '#ffa600'
						],
						[
							'label' => 'USTC',
							'data' => [],
							'borderColor' => '#00ecff',
							'hidden' => true
						]
					]
				]
			];

			$qrystr = 'SELECT bl.date, bl2.total_supply_uluna as `uluna`, bl2.total_supply_uusd as `uusd` FROM (SELECT DATE(bl.time) as `date`, MAX(bl.block) as `latest` FROM `blocks` as bl WHERE bl.total_supply_uluna > 0 AND bl.time IS
			NOT NULL GROUP BY DATE(bl.time)) as bl INNER JOIN blocks as bl2 ON (bl2.block = bl.latest) WHERE bl.date >= ?';
			$result = Database::query($qrystr, '2022-01-01');
			while(($cur = $result->get())) {
				$data['data']['labels'][] = $cur['date'];
				$data['data']['datasets'][0]['data'][] = $cur['uluna'];
				$data['data']['datasets'][1]['data'][] = $cur['uusd'];
			}
			$result->free();

			file_put_contents($file, json_encode($data));
		}

		Template::assign('total_supply', preg_replace('/\.(\d+)$/', '.<small>$1</small>', number_format($total_supply, 6, '.', ',')));
		Template::assign('total_supply_ust', preg_replace('/\.(\d+)$/', '.<small>$1</small>', number_format($total_supply_ust, 6, '.', ',')));

		Template::load('main');
		//Template::assign('META_DESC', HTML::toEntities($meta_data));
		Template::assign('LOAD_SCRIPT', 'linechart.js');
		Template::assign('PRE_SCRIPT', 'let data = ' . file_get_contents($file) . '; config.data = data.data;');
		Template::add(Template::get('charts/supply'));
		Template::show();

		exit;
	}
	public static function onCirculatingSupply() {

		Template::load('charts/circulating');

		$circulating_supply = Database::queryOneElement('SELECT `circulating_uluna` FROM `blocks` WHERE `circulating_uluna` > 0 ORDER BY `block` DESC LIMIT 0,1');
		$circulating_supply_ust = Database::queryOneElement('SELECT `circulating_uusd` FROM `blocks` WHERE `circulating_uusd` > 0 ORDER BY `block` DESC LIMIT 0,1');

		$circ_unofficial = Database::queryOneElement('SELECT (b.total_supply_uluna - b.bonded_uluna - b.unbonded_uluna - b.pool_uluna - ba.uluna) FROM `blocks` as b left join `balance` as ba ON (ba.wallet = ? AND ba.block = (b.block - (b.block % 100))) WHERE b.total_supply_uluna IS NOT NULL AND b.total_supply_uluna > 0 AND ba.wallet IS NOT NULL ORDER BY b.block DESC LIMIT 0,1', Wallets::ORACLE_WALLET);
		
		$datafile = 'data_c_supply.js';

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
							'label' => 'LUNC',
							'data' => [],
							'borderColor' => '#ffa600'
						],
						[
							'label' => 'LUNC (unofficial)',
							'data' => [],
							'borderColor' => '#aa7800'
						],
						[
							'label' => 'USTC',
							'data' => [],
							'borderColor' => '#00ecff',
							'hidden' => true
						]
					]
				]
			];

			$qrystr = 'SELECT bl.date, bl2.circulating_uluna as `uluna`, bl2.circulating_uusd as `uusd`, (bl2.total_supply_uluna - bl2.bonded_uluna - bl2.unbonded_uluna - bl2.pool_uluna - ba.uluna) as `uluna_unofficial` FROM (SELECT DATE(bl.time) as `date`, MAX(bl.block) as `latest` FROM `blocks` as bl INNER JOIN `balance` as ba ON (ba.block = (bl.block - (bl.block % 100)) AND ba.wallet = ?) WHERE bl.circulating_uluna > 0 AND bl.time IS NOT NULL GROUP BY DATE(bl.time)) as bl INNER JOIN blocks as bl2 ON (bl2.block = bl.latest) INNER JOIN `balance` as ba ON (ba.block = (bl2.block - (bl2.block % 100)) AND ba.wallet = ?)  WHERE bl.date >= ?';
			$result = Database::query($qrystr, Wallets::ORACLE_WALLET, Wallets::ORACLE_WALLET, '2022-01-01');
			while(($cur = $result->get())) {
				$data['data']['labels'][] = $cur['date'];
				$data['data']['datasets'][0]['data'][] = $cur['uluna'];
				$data['data']['datasets'][1]['data'][] = $cur['uluna_unofficial'];
				$data['data']['datasets'][2]['data'][] = $cur['uusd'];
			}
			$result->free();

			file_put_contents($file, json_encode($data));
		}

		Template::assign('circulating_supply', preg_replace('/\.(\d+)$/', '.<small>$1</small>', number_format($circulating_supply, 6, '.', ',')));
		Template::assign('circulating_supply_unofficial', preg_replace('/\.(\d+)$/', '.<small>$1</small>', number_format($circ_unofficial, 6, '.', ',')));
		Template::assign('circulating_supply_ust', preg_replace('/\.(\d+)$/', '.<small>$1</small>', number_format($circulating_supply_ust, 6, '.', ',')));

		Template::load('main');
		//Template::assign('META_DESC', HTML::toEntities($meta_data));
		Template::assign('LOAD_SCRIPT', 'linechart.js');
		Template::assign('PRE_SCRIPT', 'let data = ' . file_get_contents($file) . '; config.data = data.data;');
		Template::add(Template::get('charts/circulating'));
		Template::show();

		exit;
	}

	public static function onCommunityPool() {

		Template::load('charts/pool');

		$community_pool = Database::queryOneElement('SELECT `pool_uluna` FROM `blocks` WHERE `pool_uluna` > 0 ORDER BY `block` DESC LIMIT 0,1');
		$community_pool_ust = Database::queryOneElement('SELECT `pool_uusd` FROM `blocks` WHERE `pool_uusd` > 0 ORDER BY `block` DESC LIMIT 0,1');

		$datafile = 'data_pool.js';

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
							'label' => 'LUNC',
							'data' => [],
							'borderColor' => '#ffa600'
						],
						[
							'label' => 'USTC',
							'data' => [],
							'borderColor' => '#00ecff',
							'hidden' => true
						]
					]
				]
			];

			$qrystr = 'SELECT bl.date, bl2.pool_uluna as `uluna`, bl2.pool_uusd as `uusd` FROM (SELECT DATE(bl.time) as `date`, MAX(bl.block) as `latest` FROM `blocks` as bl WHERE bl.pool_uluna > 0 AND bl.time IS
			NOT NULL GROUP BY DATE(bl.time)) as bl INNER JOIN blocks as bl2 ON (bl2.block = bl.latest) WHERE bl.date >= ?';
			$result = Database::query($qrystr, '2022-01-01');
			while(($cur = $result->get())) {
				$data['data']['labels'][] = $cur['date'];
				$data['data']['datasets'][0]['data'][] = $cur['uluna'];
				$data['data']['datasets'][1]['data'][] = $cur['uusd'];
			}
			$result->free();

			file_put_contents($file, json_encode($data));
		}

		Template::assign('community_pool', preg_replace('/\.(\d+)$/', '.<small>$1</small>', number_format($community_pool, 6, '.', ',')));
		Template::assign('community_pool_ust', preg_replace('/\.(\d+)$/', '.<small>$1</small>', number_format($community_pool_ust, 6, '.', ',')));

		Template::load('main');
		//Template::assign('META_DESC', HTML::toEntities($meta_data));
		Template::assign('LOAD_SCRIPT', 'linechart.js');
		Template::assign('PRE_SCRIPT', 'let data = ' . file_get_contents($file) . '; config.data = data.data;');
		Template::add(Template::get('charts/pool'));
		Template::show();

		exit;
	}

}
