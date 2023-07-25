<?php

namespace LUNCDash\Modules;

use Framework\Core\Database;
use Framework\Core\Date;
use Framework\Core\Date\Format;
use Framework\Core\Modules\Base as ModuleBase;
use Framework\Core\Template;
use LUNCDash\Lib\Wallets;

class VolumeModule extends ModuleBase {

	public static function onOnChain() {
		//Template::enableCache(20);

		Template::load('charts/onchain-volume');

		//$total_supply = Database::queryOneElement('SELECT `total_supply_uluna` FROM `blocks` WHERE `total_supply_uluna` > 0 ORDER BY `block` DESC LIMIT 0,1');

		$datafile = 'data_onchain_volume.js';

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
						],
						[
							'label' => 'LUNC (CEX int. excluded)',
							'data' => [],
							'borderColor' => '#aaa600'
						],
						[
							'label' => 'USTC (CEX int. excluded)',
							'data' => [],
							'borderColor' => '#00ecaa',
							'hidden' => true
						],
						[
							'label' => 'LUNC (CEX and depos./withdraw excluded)',
							'data' => [],
							'borderColor' => '#88a600'
						],
						[
							'label' => 'USTC (CEX and depos./withdraw  excluded)',
							'data' => [],
							'borderColor' => '#00ec88',
							'hidden' => true
						]
					]
				]
			];

			$qrystr = 'SELECT t.tx_date as `date`, SUM(IF(t.denom = \'uluna\', t.amount, null)) as uluna, SUM(IF(t.denom = \'uusd\', t.amount, null)) as uusd, SUM(IF(t.denom = \'uluna\' AND (ws.type != \'cex\' OR wr.type != \'cex\'), t.amount, null)) as uluna_nocex, SUM(IF(t.denom = \'uusd\' AND (ws.type != \'cex\' OR wr.type != \'cex\'), t.amount, null)) as uusd_nocex, SUM(IF(t.denom = \'uluna\' AND (ws.type != \'cex\' AND wr.type != \'cex\'), t.amount, null)) as uluna_plain, SUM(IF(t.denom = \'uusd\' AND (ws.type != \'cex\' AND wr.type != \'cex\'), t.amount, null)) as uusd_plain FROM `tx_new` as t FORCE KEY (`tx_time`) LEFT JOIN `wallet` as ws ON (ws.wallet = t.sender) LEFT JOIN `wallet` as wr ON (wr.wallet = t.recipient) WHERE t.tx_time >= DATE_SUB(CURDATE(), INTERVAL 60 DAY) AND t.failed = 0 GROUP BY t.tx_date';
			$result = Database::query($qrystr);
			while(($cur = $result->get())) {
				$data['data']['labels'][] = $cur['date'];
				$data['data']['datasets'][0]['data'][] = $cur['uluna'];
				$data['data']['datasets'][1]['data'][] = $cur['uusd'];
				$data['data']['datasets'][2]['data'][] = $cur['uluna_nocex'];
				$data['data']['datasets'][3]['data'][] = $cur['uusd_nocex'];
				$data['data']['datasets'][4]['data'][] = $cur['uluna_plain'];
				$data['data']['datasets'][5]['data'][] = $cur['uusd_plain'];
			}
			$result->free();

			file_put_contents($file, json_encode($data));
		}

		//Template::assign('total_supply', preg_replace('/\.(\d+)$/', '.<small>$1</small>', number_format($total_supply, 6, '.', ',')));

		Template::load('main');
		//Template::assign('META_DESC', HTML::toEntities($meta_data));
		Template::assign('LOAD_SCRIPT', 'linechart.js');
		Template::assign('PRE_SCRIPT', 'let data = ' . file_get_contents($file) . '; config.data = data.data;');
		Template::add(Template::get('charts/onchain-volume'));
		Template::show();

		exit;
	}

	public static function onBinance() {

		Template::load('charts/binance-volume');

		//$total_supply = Database::queryOneElement('SELECT `total_supply_uluna` FROM `blocks` WHERE `total_supply_uluna` > 0 ORDER BY `block` DESC LIMIT 0,1');

		$datafile = 'data_binance_volume.js';

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
							'label' => 'LUNC in',
							'data' => [],
							'borderColor' => '#a6ff00'
						],
						[
							'label' => 'LUNC out',
							'data' => [],
							'borderColor' => '#ff8f00'
						],
						[
							'label' => 'LUNC internal',
							'data' => [],
							'borderColor' => '#ffff00'
						],
						[
							'label' => 'USTC in',
							'data' => [],
							'borderColor' => '#00ecff',
							'hidden' => true
						],
						[
							'label' => 'USTC out',
							'data' => [],
							'borderColor' => '#d500ff',
							'hidden' => true
						],
						[
							'label' => 'USTC internal',
							'data' => [],
							'borderColor' => '#00aeff',
							'hidden' => true
						]
					]
				]
			];

			$wallet = Wallets::BINANCE_WALLETS;

			$tmpdata = [];

			$qrystr = 'SELECT DATE(b.time) as `date`, SUM(IF(t.denom = ?, t.amount, NULL)) as `uluna`, SUM(IF(t.denom = ?, t.amount, NULL)) as `uusd` FROM tx_new as t LEFT JOIN blocks as b ON (b.block = t.block) WHERE t.recipient = ? AND t.sender NOT IN ? AND t.failed = 0 GROUP BY DATE(b.time)';
			$result = Database::query($qrystr, 'uluna', 'uusd', 'terra1ncjg4a59x2pgvqy9qjyqprlj8lrwshm0wleht5', $wallet);
			while(($cur = $result->get())) {
				if(!isset($tmpdata[$cur['date']])) {
					$tmpdata[$cur['date']] = [
						'uluna_in' => 0,
						'uluna_out' => 0,
						'uluna_internal' => 0,
						'uusd_in' => 0,
						'uusd_out' => 0,
						'uusd_internal' => 0
					];
				}

				$tmpdata[$cur['date']]['uluna_in'] += $cur['uluna'];
				$tmpdata[$cur['date']]['uusd_in'] += $cur['uusd'];
			}
			$result->free();

			$qrystr = 'SELECT DATE(b.time) as `date`, SUM(IF(t.denom = ?, t.amount, NULL)) as `uluna`, SUM(IF(t.denom = ?, t.amount, NULL)) as `uusd` FROM tx_new as t LEFT JOIN blocks as b ON (b.block = t.block) WHERE t.recipient NOT IN ? AND t.sender IN ? AND t.failed = 0 GROUP BY DATE(b.time)';
			$result = Database::query($qrystr, 'uluna', 'uusd', $wallet, $wallet);
			while(($cur = $result->get())) {
				if(!isset($tmpdata[$cur['date']])) {
					$tmpdata[$cur['date']] = [
						'uluna_in' => 0,
						'uluna_out' => 0,
						'uluna_internal' => 0,
						'uusd_in' => 0,
						'uusd_out' => 0,
						'uusd_internal' => 0
					];
				}

				$tmpdata[$cur['date']]['uluna_out'] += $cur['uluna'];
				$tmpdata[$cur['date']]['uusd_out'] += $cur['uusd'];
			}
			$result->free();

			$qrystr = 'SELECT DATE(b.time) as `date`, SUM(IF(t.denom = ?, t.amount, NULL)) as `uluna`, SUM(IF(t.denom = ?, t.amount, NULL)) as `uusd` FROM tx_new as t LEFT JOIN blocks as b ON (b.block = t.block) WHERE t.recipient IN ? AND t.sender IN ? AND t.failed = 0 GROUP BY DATE(b.time)';
			$result = Database::query($qrystr, 'uluna', 'uusd', $wallet, $wallet);
			while(($cur = $result->get())) {
				if(!isset($tmpdata[$cur['date']])) {
					$tmpdata[$cur['date']] = [
						'uluna_in' => 0,
						'uluna_out' => 0,
						'uluna_internal' => 0,
						'uusd_in' => 0,
						'uusd_out' => 0,
						'uusd_internal' => 0
					];
				}

				$tmpdata[$cur['date']]['uluna_internal'] += $cur['uluna'];
				$tmpdata[$cur['date']]['uusd_internal'] += $cur['uusd'];
			}
			$result->free();

			ksort($tmpdata);

			foreach($tmpdata as $date => $amount) {
				$data['data']['labels'][] = $date;
				$data['data']['datasets'][0]['data'][] = $amount['uluna_in'];
				$data['data']['datasets'][1]['data'][] = $amount['uluna_out'];
				$data['data']['datasets'][2]['data'][] = $amount['uluna_internal'];
				$data['data']['datasets'][3]['data'][] = $amount['uusd_in'];
				$data['data']['datasets'][4]['data'][] = $amount['uusd_out'];
				$data['data']['datasets'][5]['data'][] = $amount['uusd_internal'];
			}

			file_put_contents($file, json_encode($data));
		}

		//Template::assign('total_supply', preg_replace('/\.(\d+)$/', '.<small>$1</small>', number_format($total_supply, 6, '.', ',')));

		Template::load('main');
		//Template::assign('META_DESC', HTML::toEntities($meta_data));
		Template::assign('LOAD_SCRIPT', 'linechart.js');
		Template::assign('PRE_SCRIPT', 'let data = ' . file_get_contents($file) . '; config.data = data.data;');
		Template::add(Template::get('charts/binance-volume'));
		Template::show();

		exit;
	}

}
