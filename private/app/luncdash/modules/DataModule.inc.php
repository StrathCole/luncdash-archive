<?php

namespace LUNCDash\Modules;

use Framework\Core\Database;
use Framework\Core\Date;
use Framework\Core\Log;
use Framework\Core\Modules\Base as ModuleBase;
use Framework\Core\Numbers\Format;
use Framework\Ext\DataCache;
use Framework\Ext\Fs\File;
use Framework\Ext\Fs\Locking;
use Framework\Ext\Net\Http;
use Framework\Ext\Net\Param;
use Framework\Ext\Net\Request;
use LUNCDash\Lib\Wallets;
use LUNCDash\Lib\Chain;

class DataModule extends ModuleBase {
	const CHART_COLORS = ['#555e7b', '#b7d968', '#b576ad', '#e04644', '#fde47f', '#7ccce5', '#ff8c94', '#d3ce3d', '#8e8cff', '#7ab317', '#604848', '#3299bb', '#f27435'];

	private static function sortByBurns($a, $b) {
		if($a['sum'] > $b['sum']) {
			return -1;
		} elseif($a['sum'] < $b['sum']) {
			return 1;
		} else {
			return 0;
		}
	}

	public static function onBurns() {
		$now = Date\Format::toSQL(Date::getTime());

		$data = [
			'last_update' => $now,
			'burns' => [],
			'top' => [],
			'total' => 0,
			'latest' => 0,
			'data' => [
				'labels' => [],
				'datasets' => [
					[
						'label' => 'LUNC',
						'data' => [],
						'backgroundColor' => self::CHART_COLORS
					]
				]
			]
		];

		$tax_burn = Chain::getTaxBurned('uluna');
		$qrystr = 'SELECT SUM(`amount`) FROM `tx_new` WHERE `recipient` = ? AND `denom` = ? AND `memo` LIKE ?';
		$burnalot = Database::queryOneElement($qrystr, Wallets::BURN_WALLET, 'uluna', '%burnalot%');
		
		$qrystr = 'SELECT SUM(`amount`) FROM `tx_new` WHERE `contract` = ? AND `denom` = ? AND `amount` > 0';
		$luncblaze = Database::queryOneElement($qrystr, Wallets::LUNCBLAZE_CONTRACT, 'uluna');

		$latest = 0;
		$entities = [
			'Burn Tax' => [
				'cnt' => 1,
				'sum' => $tax_burn
			],
			'luncblaze.com' => [
				'cnt' => 1,
				'sum' => $luncblaze
			],
			'burnalot' => [
				'cnt' => 1,
				'sum' => $burnalot
			]
		];
		$qrystr = 'SELECT t.sender, w.descr, t.memo, MAX(t.block) as `latest`, COUNT(*) as `cnt`, SUM(t.amount) as `amount` FROM `tx_new` as t LEFT JOIN `wallet` as w ON (w.wallet = t.sender) WHERE t.recipient = ? AND t.denom = ? GROUP BY t.sender, t.memo';
		$qrystr .= ' UNION ALL SELECT t.sender, w.descr, \'LUNCblaze portal burn\' as `memo`, MAX(t.block) as `latest`, COUNT(*) as `cnt`, SUM(t.amount) as `amount` FROM `tx_new` as t LEFT JOIN `wallet` as w ON (w.wallet = t.sender) WHERE t.contract = ? AND t.denom = ? AND t.amount > 0 GROUP BY t.sender, t.memo';
		$result = Database::query($qrystr, Wallets::BURN_WALLET, 'uluna', Wallets::LUNCBLAZE_CONTRACT, 'uluna');
		while(($cur = $result->get())) {
			$sender = $cur['sender'];
			$data['total'] += $cur['amount'];

			$entity = Wallets::getOwner($sender, $cur['memo']);
			if($entity === 'Binance Users' && $cur['amount'] > 100000000) {
				$entity = 'Binance';
			}
			if($cur['latest'] > $latest) {
				$latest = $cur['latest'];
			}

			if(!array_key_exists($entity, $entities)) {
				$entities[$entity] = [
					'cnt' => 0,
					'sum' => 0
				];
			}
			$entities[$entity]['cnt'] += $cur['cnt'];
			$entities[$entity]['sum'] += $cur['amount'];
		}
		$result->free();

		uasort($entities, 'self::sortByBurns');
		$entities = array_slice($entities, 0, 26);

		$others = 0;
		foreach($entities as $entity => $tmp) {
			if(count($data['data']['labels']) < 10) {
				$data['data']['labels'][] = $entity;
				$data['data']['datasets'][0]['data'][] = $tmp['sum'];
			} else {
				$others += $tmp['sum'];
			}
			if($entity === 'unknown') {
				continue;
			}
			$data['top'][] = [
				'name' => $entity,
				'amount' => preg_replace('/\.(\d+)$/', '.<small>$1</small>', number_format($tmp['sum'], 6, '.', ','))
			];
		}
		$data['data']['labels'][] = 'Others';
		$data['data']['datasets'][0]['data'][] = $others;

		$data['latest'] = $latest;
		$burned = $data['total'];
		$data['total'] = number_format($data['total'], 6, '.', ',');
		$data['total'] = preg_replace('/\.(\d+)$/', '.<small>$1</small>', $data['total']);
		$data['total'] .= '<br />incl. tax: ' . preg_replace('/\.(\d+)$/', '.<small>$1</small>', number_format($burned + $tax_burn, 6, '.', ','));

		$from = 0;
		$limit = 50;
		if(isset($_GET['block'])) {
			$from = intval($_GET['block']);
			$limit = 1000;
		}

		$qrystr = 'SELECT t.sender as `wallet`, w.descr, t.memo, t.block, t.amount, b.time FROM `tx_new` as t LEFT JOIN `wallet` as w ON (w.wallet = t.sender) LEFT JOIN `blocks` as b ON (b.block = t.block) WHERE t.recipient = ? AND t.denom = ? AND t.block  > ? AND t.sender != ? AND t.amount >= 20000';
		$qrystr .= ' UNION ALL SELECT t.sender as `wallet`, w.descr, \'LUNCblaze portal burn\' as `memo`, t.block, t.amount, b.time FROM `tx_new` as t LEFT JOIN `wallet` as w ON (w.wallet = t.sender) LEFT JOIN `blocks` as b ON (b.block = t.block) WHERE t.contract = ? AND t.denom = ? AND t.block  > ? AND t.amount > 0';
		$qrystr .= ' ORDER BY block DESC, `time` DESC LIMIT 0, ' . $limit;
		$result = Database::query($qrystr, Wallets::BURN_WALLET, 'uluna', $from, 'terra1zu6hjxuaenr5325ldlwwzklftmu06gk6krv2dd', Wallets::LUNCBLAZE_CONTRACT, 'uluna', $from);
		while(($cur = $result->get())) {
			$sender = $cur['wallet'];
			$entity = Wallets::getOwner($sender, $cur['memo']);
			if($entity === 'Binance Users' && $cur['amount'] > 100000000) {
				$entity = 'Binance';
			}
			$cur['descr'] = $entity;
			$cur['memo'] = str_replace('"', "'", $cur['memo']);

			$cur['class'] = 'low';
			if($cur['amount'] >= 1000000) {
				$cur['class'] = 'medium';
			} elseif($cur['amount'] >= 10000000) {
				$cur['class'] = 'high';
			} elseif($cur['amount'] >= 25000000) {
				$cur['class'] = 'veryhigh';
			} elseif($cur['amount'] >= 100000000) {
				$cur['class'] = 'crazy';
			} elseif($cur['amount'] >= 1000000000) {
				$cur['class'] = 'insane';
			}

			$cur['raw'] = $cur['amount'];
			$cur['amount'] = number_format($cur['amount'], 6, '.', ',');
			$cur['textamount'] = $cur['amount'];
			$cur['amount'] = preg_replace('/\.(\d+)$/', '.<small>$1</small>', $cur['amount']);
			$data['burns'][] = $cur;
		}
		$result->free();

		$data['burns'] = array_reverse($data['burns']);
		$data['burnalot'] = preg_replace('/\.(\d+)$/', '.<small>$1</small>', number_format($burnalot, 6, '.', ','));
		$data['luncblaze'] = preg_replace('/\.(\d+)$/', '.<small>$1</small>', number_format($luncblaze, 6, '.', ','));

		Http::sendHeader(200, 'application/json');
		print json_encode($data);
		exit;
	}

	public static function onWallet() {
		$wallet = trim(Param::postString('wallet'));
		Http::sendHeader(200, 'text/html');
		if(!$wallet) {
			print '<em>missing data</em>';
			exit;
		}

		if(substr($wallet, 0, 5) !== 'terra') {
			print '<em>Invalid wallet address. Has to start with terra.</em>';
			exit;
		} elseif(strpos($wallet, ' ') !== false) {
			print '<em>Invalid wallet address. No spaces allowed.</em>';
			exit;
		}

		$qrystr = 'SELECT `descr` FROM `wallet` WHERE `wallet` = ?';
		$name = Database::queryOneElement($qrystr, $wallet);
		if(!$name) {
			print '<em>No owner found</em>';
		} else {
			print '<strong>' . htmlspecialchars($name) . '</strong>';
		}
		exit;
	}

	public static function onBlockHeight() {
		$height = DataCache::get('block:height');
		if(!$height) {
			$response = Request::requestGET('http://localhost:1317/cosmos/base/tendermint/v1beta1/blocks/latest');
			$height = json_decode($response->getBody(), true);
			if(!isset($height['block']['header']['height'])) {
				$height = null;
			} else {
				$height = $height['block']['header']['height'];
				DataCache::set('block:height', $height, false, true, 10, null, 's');
			}
		}

		//$staking_at = Chain::STAKING_AT;
		$validators_at = Chain::VALIDATORS_AT;
		$tax_at = Chain::BURNTAX_AT;
		$tax_active_at = Chain::BURNTAX_ACTIVE_AT;
		
		$secs_epoch = Database::queryOneElement('SELECT TIMESTAMPDIFF(SECOND, `time`, NOW()) FROM `blocks` WHERE `block` >= ? ORDER BY `block` ASC LIMIT 1', $height - Chain::BLOCKS_PER_WEEK);
		$blocks_day = Database::queryOneElement('SELECT `block` FROM `blocks` WHERE `time` >= DATE_SUB(NOW(), INTERVAL 1 DAY) AND `block` >= ? ORDER BY `block` ASC LIMIT 1', $height - (Chain::BLOCKS_PER_DAY * 2));
		
		$chain_speed = 86400 / ($height - $blocks_day);
		$days_epoch = $secs_epoch / (3600 * 24);
		
		$until_staking = 'ACTIVE';/*$staking_at - $height;
		if($until_staking > 0) {
			$attime = Database::queryOneElement('SELECT DATE_ADD(NOW(), INTERVAL TIMESTAMPDIFF(SECOND, `time`, NOW()) SECOND) FROM `blocks` WHERE `block` >= ' . ($height - $until_staking) . ' ORDER BY block ASC LIMIT 1');
			$until_staking = Format::toString($until_staking, 0, 'en');
			$until_staking .= '<small class="fs-6"><br />(est. ' . Date\DateTime::getInstance($attime)->format('M d \'y h:ia') . ' UTC)</small>';
		} else {
			$until_staking = 'ACTIVE';
		}*/
		$until_validators = $validators_at - $height;
		if($until_validators > 0) {
			$attime = Database::queryOneElement('SELECT DATE_ADD(NOW(), INTERVAL TIMESTAMPDIFF(SECOND, `time`, NOW()) SECOND) FROM `blocks` WHERE `block` >= ' . ($height - $until_validators) . ' ORDER BY block ASC LIMIT 1');
			$until_validators = Format::toString($until_validators, 0, 'en');
			$until_validators .= '<small class="fs-6"><br />(est. ' . Date\DateTime::getInstance($attime)->format('M d \'y h:ia') . ' UTC)</small>';
		} else {
			$until_validators = 'ACTIVE';
		}
		$until_tax = $tax_at - $height;
		if($until_tax > 0) {
			$attime = Database::queryOneElement('SELECT DATE_ADD(NOW(), INTERVAL TIMESTAMPDIFF(SECOND, `time`, NOW()) SECOND) FROM `blocks` WHERE `block` >= ' . ($height - $until_tax) . ' ORDER BY block ASC LIMIT 1');
			$until_tax = Format::toString($until_tax, 0, 'en');
			$until_tax .= '<small class="fs-6"><br />(est. ' . Date\DateTime::getInstance($attime)->format('M d \'y h:ia') . ' UTC)</small>';
		} else {
			$until_tax = 'ACTIVE';
		}
		$until_taxa = $tax_active_at - $height;
		if($until_taxa > 0) {
			$attime = Database::queryOneElement('SELECT DATE_ADD(NOW(), INTERVAL TIMESTAMPDIFF(SECOND, `time`, NOW()) SECOND) FROM `blocks` WHERE `block` >= ' . ($height - $until_taxa) . ' ORDER BY block ASC LIMIT 1');
			$until_taxa = Format::toString($until_taxa, 0, 'en');
			$until_taxa .= '<small class="fs-6"><br />(est. ' . Date\DateTime::getInstance($attime)->format('M d \'y h:ia') . ' UTC)</small>';
		} else {
			$until_taxa = 'ACTIVE';
		}
		
		$epoch = Chain::getEpoch($height);
		$next_epoch = $epoch + 1;
		$next_epoch_block = Chain::nextEpochAt($height);
		$epoch_blocks = $next_epoch_block - $height;
		$attime = Database::queryOneElement('SELECT DATE_ADD(NOW(), INTERVAL TIMESTAMPDIFF(SECOND, `time`, NOW()) SECOND) FROM `blocks` WHERE `block` >= ' . ($height - $epoch_blocks) . ' ORDER BY block ASC LIMIT 1');
		$until_epoch = Format::toString($epoch_blocks, 0, 'en') . '<small class="fs-6"><br />(est. ' . Date\DateTime::getInstance($attime)->format('M d \'y h:ia') . ' UTC)</small>';

		$burn_tax_uluna = null;
		$burn_tax_uusd = null;
		if(!Param::checkGet('taxtest')) {
			$burn_tax_uluna = DataCache::get('burntax:uluna');
		}
		if($burn_tax_uluna === null) {
			$burn_tax_uluna = Chain::getTaxBurned('uluna');
			if(!Param::checkGet('taxtest')) {
				DataCache::set('burntax:uluna', $burn_tax_uluna, false, true, 1);
			}
		}

		if(!Param::checkGet('taxtest')) {
			$burn_tax_uusd = DataCache::get('burntax:uusd');
		}
		if($burn_tax_uusd === null) {
			$burn_tax_uusd = Chain::getTaxBurned('uusd');
			if(!Param::checkGet('taxtest')) {
				DataCache::set('burntax:uusd', $burn_tax_uusd, false, true, 1);
			}
		}

		$burntax_data = '<span class="badge fs-4">' . preg_replace('/\.(\d+)$/', '.<small>$1</small>', number_format($burn_tax_uluna, 6, '.', ',')) . ' LUNC</span><br />
		<span class="badge fs-4">' . preg_replace('/\.(\d+)$/', '.<small>$1</small>', number_format($burn_tax_uusd, 6, '.', ',')) . ' USTC</span>';
		
		Http::sendHeader(200, 'application/json', ['Access-Control-Allow-Origin' => 'https://luncdash.com']);
		if($height) {
			print json_encode([
				'height' => Format::toString($height, 0, 'en') . '<small class="fs-6"><br />(' . Format::toString($chain_speed, 2, 'en') . 's / block)</small>',
				'staking' => $until_staking,
				'validators' => $until_validators,
				'tax' => $until_tax,
				'taxa' => $until_taxa,
				'epoch' => $epoch . '<small class="fs-6"><br />(' . Format::toString($days_epoch, 2, 'en') . ' days / epoch)</small>',
				'epoch_next' => $next_epoch,
				'epoch_blocks' => $epoch_blocks,
				'epoch_until' => $until_epoch,
				'taxburn' => $burntax_data,
				//'staked' => preg_replace('/\.(\d+)$/', '.<small>$1</small>', number_format($staked, 6, '.', ',')) . ' LUNC'
					]);
		}
		exit;
	}

	public static function onCandles() {
		\Framework\Ext\l10n\Locale::set('en');

		$valids = ['5m', '15m', '30m', '1h', '4h', '1d'];
		$period = Param::getString('p');
		if(!in_array($period, $valids, true)) {
			$period = '15m';
		}
		
		$candles = DataCache::getJSON('candles:' . $period);
		if(!$candles) {
			$candles = [];
		}

		$data = [
			'datasets' => [
				[
					'label' => 'LUNC/USD',
					/*'color' => [
						'up' => '#01ff01',
						'down' => '#fe0000',
						'unchanged' => '#999'
					],*/
					'data' => array_values($candles)
				]
			]
		];

		Http::sendHeader(200, 'application/json', ['Access-Control-Allow-Origin' => 'https://luncdash.com']);
		print json_encode([
			'data' => $data
		]);
		exit;
	}
}
