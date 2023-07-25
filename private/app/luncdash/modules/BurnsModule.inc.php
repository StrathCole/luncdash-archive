<?php

namespace LUNCDash\Modules;

use Framework\Core\Database;
use Framework\Core\Date;
use Framework\Core\Date\Format;
use Framework\Core\Modules\Base as ModuleBase;
use Framework\Core\Template;
use LUNCDash\Lib\Chain;
use LUNCDash\Lib\Wallets;

class BurnsModule extends ModuleBase {
	/**
	 * @param string $action
	 */
	public static function onChart() {

		Template::load('charts/burns');

		$total_burned = Database::queryOneElement('SELECT SUM(amount) FROM `tx` WHERE `recipient` = ? AND `denom` = ?', Wallets::BURN_WALLET, 'uluna');
		$total_burned += Database::queryOneElement('SELECT SUM(amount) FROM `tx_new` WHERE `contract` = ? AND `denom` = ?', Wallets::LUNCBLAZE_CONTRACT, 'uluna');
		$tax_burn = Chain::getTaxBurned('uluna');
		
		$datafile = 'data_burns.js';

		$file = sys_get_temp_dir() . '/' . $datafile;
		if(file_exists($file) && filesize($file) > 10) {
			$mtime = filemtime($file);
		} else {
			$mtime = 0;
		}
		$lifetime = (60 * 2);
		if($mtime < time() - $lifetime) {
			$now = Format::toSQL(Date::getTime());

			$tmpdata = [];
			$qrystr = 'SELECT SUM(tx.amount) as `uluna`, b.date FROM tx_new  as tx inner join blocks as b on (b.block = tx.block) WHERE tx.recipient = ? AND tx.denom = ? AND b.date >= ? GROUP BY b.date';
			$result = Database::query($qrystr, Wallets::BURN_WALLET, 'uluna', '2022-01-01');
			while(($cur = $result->get())) {
				if(!isset($tmpdata[$cur['date']])) {
					$tmpdata[$cur['date']] = [
						'wallet' => 0,
						'luncblaze' => 0
					];
				}
				$tmpdata[$cur['date']]['wallet'] += $cur['uluna'];
			}
			$result->free();

			$qrystr = 'SELECT SUM(tx.amount) as `uluna`, b.date FROM tx_new as tx inner join blocks as b on (b.block = tx.block) WHERE tx.contract = ? AND tx.denom = ? AND b.date >= ? GROUP BY b.date';
			$result = Database::query($qrystr, Wallets::LUNCBLAZE_CONTRACT, 'uluna', '2022-01-01');
			while(($cur = $result->get())) {
				if(!isset($tmpdata[$cur['date']])) {
					$tmpdata[$cur['date']] = [
						'wallet' => 0,
						'luncblaze' => 0
					];
				}
				$tmpdata[$cur['date']]['luncblaze'] += $cur['uluna'];
			}
			$result->free();

			ksort($tmpdata);

			$data = [
				'last_update' => $now,
				'data' => [
					'labels' => [],
					'datasets' => [
						[
							'label' => 'LUNC burns (LUNCblaze)',
							'data' => [],
							'borderColor' => '#00C5FF',
							'yAxisID' => 'y',
						],
						[
							'label' => 'LUNC burns (wallet)',
							'data' => [],
							'borderColor' => '#ffa600',
							'yAxisID' => 'y',
						],
						[
							'label' => 'LUNC burns',
							'data' => [],
							'borderColor' => '#309000',
							'yAxisID' => 'y',
						],
						[
							'type' => 'bar',
							'label' => 'LUNCblaze volume',
							'data' => [],
							'backgroundColor' => '#00C5FF',
							'stack' => 'Volume',
							'yAxisID' => 'y1',
						],
						[
							'type' => 'bar',
							'label' => 'Burn wallet volume',
							'data' => [],
							'backgroundColor' => '#ffa600',
							'stack' => 'Volume',
							'yAxisID' => 'y1',
						]
					]
				]
			];
 
			$sums = [
				'wallet' => 0.0,
				'luncblaze' => 0.0
			];
			foreach($tmpdata as $date => $cur) {
				$data['data']['labels'][] = $date;
				$sums['luncblaze'] += $cur['luncblaze'];
				$sums['wallet'] += $cur['wallet'];
				$data['data']['datasets'][0]['data'][] = $sums['luncblaze'];
				$data['data']['datasets'][1]['data'][] = $sums['wallet'];
				$data['data']['datasets'][2]['data'][] = $sums['wallet'] + $sums['luncblaze'];
				$data['data']['datasets'][3]['data'][] = $cur['luncblaze'];
				$data['data']['datasets'][4]['data'][] = $cur['wallet'];
			}
			$result->free();

			file_put_contents($file, json_encode($data));
		}

		Template::assign('total_burned', preg_replace('/\.(\d+)$/', '.<small>$1</small>', number_format($total_burned, 6, '.', ',')));
		Template::assign('total_burned_tax', preg_replace('/\.(\d+)$/', '.<small>$1</small>', number_format($total_burned + $tax_burn, 6, '.', ',')));
		Template::assign('burn_wallet', Wallets::BURN_WALLET);

		Template::load('main');
		//Template::assign('META_DESC', HTML::toEntities($meta_data));
		Template::assign('LOAD_SCRIPT', 'mixchartstack.js');
		Template::assign('PRE_SCRIPT', 'let data = ' . file_get_contents($file) . '; config.data = data.data;');
		Template::add(Template::get('charts/burns'));
		Template::show();

		exit;
	}
}
