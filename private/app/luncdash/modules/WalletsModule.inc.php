<?php

namespace LUNCDash\Modules;

use Framework\Core\Database;
use Framework\Core\Date;
use Framework\Core\Date\Format;
use Framework\Core\Modules\Base as ModuleBase;
use Framework\Core\Template;
use Framework\Ext\Hooks;
use Framework\Ext\Net\Param;
use LUNCDash\Lib\Wallets;

class WalletsModule extends ModuleBase {
	/**
	 * @param string $action
	 */
	public static function onHoldings() {

		Template::load('charts/holdings');

		$wallet = Param::getString('wallet');
		if(!$wallet) {
			Hooks::call('error_404');
			exit;
		}

		$datafile = 'data_wallet_' . $wallet . '.js';

		if($wallet === 'binance') {
			$wallet = Wallets::BINANCE_WALLETS;
			$wallet_name = 'Binance wallets';
		} else {
			$wallet_name = Database::queryOneElement('SELECT `descr` FROM `wallet` WHERE `wallet` = ?', $wallet);
			if(!$wallet_name) {
				$wallet_name = $wallet;
			} else {
				$wallet_name .= ' <small>(' . $wallet . ')</small>';
			}

			$wallet = [$wallet];
		}

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

			$qrystr = 'SELECT SUM(x.uluna) as uluna, SUM(x.uusd) as uusd, x.date FROM (SELECT b.wallet, a.date, b.uluna, b.uusd FROM (SELECT DATE(bl.time) as `date`, MAX(b.block) as `latest` FROM `balance` as b INNER JOIN `blocks` as bl ON (bl.block = b.block) WHERE bl.time IS NOT NULL AND b.wallet IN ? GROUP BY DATE(bl.time)) as a INNER JOIN balance as b ON (b.block = a.latest) WHERE b.wallet in ? ORDER BY b.block) as x GROUP BY x.date';
			$result = Database::query($qrystr, $wallet, $wallet);
			while(($cur = $result->get())) {
				$data['data']['labels'][] = $cur['date'];
				$data['data']['datasets'][0]['data'][] = $cur['uluna'];
				$data['data']['datasets'][1]['data'][] = $cur['uusd'];
			}
			$result->free();

			file_put_contents($file, json_encode($data));
		}

		Template::assign('wallet_name', $wallet_name);

		Template::load('main');
		//Template::assign('META_DESC', HTML::toEntities($meta_data));
		Template::assign('LOAD_SCRIPT', 'linechart.js');
		Template::assign('PRE_SCRIPT', 'let data = ' . file_get_contents($file) . '; config.data = data.data;');
		Template::add(Template::get('charts/holdings'));
		Template::show();

		exit;
	}
}
