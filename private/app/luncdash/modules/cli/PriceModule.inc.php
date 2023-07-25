<?php

namespace LUNCDash\Modules\CLI;

use Framework\Core\Database;
use Framework\Core\Date;
use Framework\Core\Date\DateTime;
use Framework\Core\Log;
use Framework\Core\Modules\Base as ModuleBase;
use Framework\Ext\CLI\Shell;
use Framework\Ext\DataCache;
use Framework\Ext\Net;
use Framework\Ext\Net\Param;
use Framework\Ext\Net\Request;
use LUNCDash\Lib\Chain;

class PriceModule extends ModuleBase {
	/**
	 */
	private static function startLocking(?string $add_ident = null) {
		if(!$add_ident) {
			$add_ident = '';
		} else {
			$add_ident = '-' . $add_ident;
		}

		$lock_file = sys_get_temp_dir() . '/price' . $add_ident . '.run';
		// Check whether another instance of this script is already running
		if(is_file($lock_file)) {
			clearstatcache();
			$pid = trim(file_get_contents($lock_file));
			if(preg_match('/^[0-9]+$/', $pid)) {
				if(file_exists('/proc/' . $pid)) {
					print date('d.m.Y-H:i') . ' - WARNING - There is already an instance of processing running with pid ' . $pid . '.' . "\n";
					exit(1);
				}
			}
			print date('d.m.Y-H:i') . ' - WARNING - There is already a lockfile set, but no process running with this pid (' . $pid . '). Continuing.' . "\n";
		}

		// Set Lockfile
		@file_put_contents($lock_file, getmypid());
	}

	private static function releaseLocking(?string $add_ident = null) {
		if(!$add_ident) {
			$add_ident = '';
		} else {
			$add_ident = '-' . $add_ident;
		}

		$lock_file = sys_get_temp_dir() . '/price' . $add_ident . '.run';
		@unlink($lock_file);
	}

	private static function getLatestBlock() {
		$result = Net\Request::requestGET('http://127.0.0.1:1317/blocks/latest');
        $result = json_decode($result->getBody(), true);
        if(!isset($result['block']['header']['height'])) {
			return null;
        }

        return $result['block']['header']['height'];
	}

	private static function getLatestBlockTime() {
		$result = Net\Request::requestGET('http://127.0.0.1:1317/blocks/latest');
        $result = json_decode($result->getBody(), true);
        if(!isset($result['block']['header']['time'])) {
			return null;
        }

        return DateTime::getInstance($result['block']['header']['time']);
	}

	public static function onUpdatePrice() {
		if(!Net\Info::isCLI(true)) {
			die();
		}

		self::startLocking();

		$block = Database::queryOneElement('SELECT `block` FROM `blocks` WHERE `price_uluna` IS NOT NULL ORDER BY `block` DESC');
		$prev = null;
		while(true) {
			
			$response = Net\Request::requestGET('http://localhost:1317/terra/oracle/v1beta1/denoms/uusd/exchange_rate', ['x-cosmos-block-height' => $block]);
			if($response->getStatusCode() === 400) {
				$latest = self::getLatestBlock();
				if($block > $latest) {
					//Log::warn('Block ' . $block . ' higher than latest block ' . $latest);
					sleep(4);
					continue;
				} else {
					$block++;
					continue;
				}
			}

			sleep(1);

			$json = json_decode($response->getBody(), true);
			$time = null;
			$price = null;
			if(!$json) {
				Log::warn('No json reply from chain.');
			} elseif(!isset($json['exchange_rate'])) {
				Log::warn('No json exchange rate from chain on block ' . $block . '.');
			}


			$price = $json['exchange_rate'];
			$response = Net\Request::requestGET('http://localhost:1317/cosmos/base/tendermint/v1beta1/blocks/' . $block);

			$json = json_decode($response->getBody(), true);
			if(isset($json['code'])) {
				Log::info('No new block currently. ' . $block);
				sleep(2);
				continue;
			} elseif(!isset($json['block']['header']['time'])) {
				Log::warn('No block data from chain.');
				Log::info('No new block currently. ' . $block);
				sleep(2);
				continue;
			}

			$time = $json['block']['header']['time'];
			$time = substr($time, 0, 26);
			try {
				$dt = new DateTime($time);
				$time = $dt->format('Y-m-d H:i:s');
				unset($dt);
			} catch(\Exception $e) {
				Log::warn("Unparsable time $time on block $block: " . $e->getMessage());
			}

			$qrystr = 'INSERT INTO `blocks` (`block`, `time`, `date`, `price_uluna`) VALUES (?, ?, DATE(?), ?) ON DUPLICATE KEY UPDATE `time` = ?, `date` = DATE(?), `price_uluna` = ?';
			Database::query($qrystr, $block, $time, $time, $price, $time, $time, $price);
			if($price != $prev) {
				self::onUpdateCandles();
			}
			$prev = $price;

			$block++;
			DataCache::set('price_uluna:block', $block, false, true);
		}

		self::releaseLocking();
		exit;
	}

	public static function onUpdateApy() {
		if(!Net\Info::isCLI(true)) {
			die();
		}

		// update APY
		Shell::execSafe('terrad query txs --events "message.sender=terra1xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx&message.action=/cosmos.distribution.v1beta1.MsgWithdrawDelegatorReward&message.module=distribution" --output=json --limit 100');
		$ret = Shell::getLastExecReturnCode();
		$out = Shell::getLastExecOutput(true, true);
		if($ret == 0) {
			$json = json_decode($out, true);
			$pgs = $json['page_total'];
			if($pgs > 1) {
				Shell::execSafe('terrad query txs --events "message.sender=terra1xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx&message.action=/cosmos.distribution.v1beta1.MsgWithdrawDelegatorReward&message.module=distribution" --output=json --limit 100 --page ' . $pgs);
				$ret = Shell::getLastExecReturnCode();
				$out = Shell::getLastExecOutput(true, true);
			}
		}

		if($ret == 0) {
			$json = json_decode($out, true);
			$txs = $json['txs'];
			foreach($txs as $tx) {
				$txdata = $tx['tx']['body'];
				$found = false;
				foreach($txdata['messages'] as $msg) {
					if($msg['@type'] === '/cosmos.distribution.v1beta1.MsgWithdrawDelegatorReward') {
						$found = true;
						break;
					}
				}
				if(!$found) {
					continue;
				}

				$check = Database::queryOne('SELECT `block` FROM `withdrawals` WHERE `block` = ?', $tx['height']);
				if($check) {
					continue;
				}

				$inserted = false;
				$logs = $tx['logs'];
				foreach($logs as $log) {
					foreach($log['events'] as $event) {
						$valid = false;
						$amounts = null;
						foreach($event['attributes'] as $attr) {
							if($attr['key'] === 'spender' && $attr['value'] === 'terra1jv65s3grqf6v6jl3dp4t6c9t9rk99cd8pm7utl') {
								$valid = true;
							} elseif($attr['key'] === 'amount') {
								$amounts = explode(',', $attr['value']);
							}
						}
						if($valid && $amounts) {
							// add
							$match = null;
							foreach($amounts as $entry) {
								if(preg_match('/^(\d+)([a-z]+)$/', $entry, $match)) {
									$denom = $match[2];
									$amount = (int)$match[1];
									if(!in_array($denom, ['uusd', 'uluna'], true)) {
										continue;
									}
									$qrystr = 'INSERT INTO `withdrawals` (`block`, ??) VALUES (?, ? / 1000000) ON DUPLICATE KEY UPDATE ?? = ?? + (? / 1000000)';
									Database::query($qrystr, $denom, $tx['height'], $amount, $denom, $denom, $amount);
									$inserted = true;
								}
							}
							break;
						}
					}
				}

				if($inserted) {
					$result = Request::requestGET('http://localhost:1317/cosmos/staking/v1beta1/delegations/terra1xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx?pagination.limit=1000', ['x-cosmos-block-height' => $tx['height']]);
					$json = json_decode($result->getBody(), true);
					$sum = 0;
					if(isset($json['delegation_responses']) && !empty($json['delegation_responses'])) {
						foreach($json['delegation_responses'] as $del) {
							$sum += (int)$del['balance']['amount'];
						}
					} else {
						die("ERROR GETTING DELEGATIONS\n");
					}
					Database::query('UPDATE `withdrawals` SET `delegated` = ? / 1000000 WHERE `block` = ?', $sum, $tx['height']);
				}
			}
		} else {
			die("ERROR GETTING REWARD DATA\n");
		}

		$result = Request::requestGET('http://localhost:1317/cosmos/distribution/v1beta1/delegators/terra1xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx/rewards');
		$tmp = json_decode($result->getBody(), true);
		$total_uluna = 0;
		$total_uusd = 0;
		foreach($tmp['total'] as $den) {
			if($den['denom'] === 'uusd') {
				$total_uusd += (float)$den['amount'];
			} elseif($den['denom'] === 'uluna') {
				$total_uluna += (float)$den['amount'];
			}
		}
		$block_time = self::getLatestBlockTime();
		$block = self::getLatestBlock();

		$latest = Database::queryOne('SELECT w.*, b.time FROM `withdrawals` as w inner join `blocks` as b ON (b.block = w.block) ORDER BY w.block DESC LIMIT 0,1');
		$latest_time = DateTime::getInstance($latest['time']);
		$diff = Date\Calc::getDiff($latest_time, $block_time, 'min', false);

		$result = Request::requestGET('http://localhost:1317/cosmos/staking/v1beta1/delegations/terra1xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx?pagination.limit=1000');
		$json = json_decode($result->getBody(), true);
		$delegated = 0;
		$val_share = [];
		if(isset($json['delegation_responses']) && !empty($json['delegation_responses'])) {
			foreach($json['delegation_responses'] as $del) {
				$val_share[$del['delegation']['validator_address']] = ((int)$del['balance']['amount']) / 1000000;
				$delegated += ((int)$del['balance']['amount']) / 1000000;
			}
		}

		$gain_year_uluna = ($total_uluna / 1000000) * (365 * 1400 / $diff); 
		$gain_year_uusd = ($total_uusd / 1000000) * (365 * 1400 / $diff);
	
		$commission = 0;
		$cnt = 0;
		foreach($val_share as $val => $amount) {
			$result = Request::requestGET('http://localhost:1317/cosmos/staking/v1beta1/validators/' . $val);
			$tmp = json_decode($result->getBody(), true);
			$commission += ((float)($tmp['validator']['commission']['commission_rates']['rate']) * $amount);
			$cnt += $amount;
		}
		if($cnt > 0) {
			$commission = $commission / $cnt;
		}
		
		$gain_year_uluna = $gain_year_uluna / (1 - $commission);
		$gain_year_uusd = $gain_year_uusd / (1 - $commission);

		$result = Request::requestGET('https://api.binance.com/api/v3/klines?symbol=USTCBUSD&interval=1m&limit=1');
		$tmp = json_decode($result->getBody(), true);
		$tmp = reset($tmp);
		$uusd_price = (float)$tmp[4];

		$result = Request::requestGET('https://api.binance.com/api/v3/klines?symbol=LUNCBUSD&interval=1m&limit=1');
		$tmp = json_decode($result->getBody(), true);
		$tmp = reset($tmp);
		$uluna_price = (float)$tmp[4];
		$factor = $uusd_price / $uluna_price;
		if(!$factor) {
			$factor = 100;
		}
		$apy_uluna = 100 * $gain_year_uluna / $delegated;
		$apy_uusd = 100 * $gain_year_uusd * $factor / $delegated;
		$apy = $apy_uluna + $apy_uusd;
		$check = DataCache::get('staking:apy');

		var_dump($delegated, $commission, $total_uluna, $latest, $latest_time, $check, $apy, $apy_uluna, $apy_uusd, $uluna_price, $uusd_price, $factor, $gain_year_uluna, $gain_year_uusd, $diff);

		DataCache::set('staking:apy', $apy, false, true);
		DataCache::set('staking:apy_uluna', $apy_uluna, false, true);
		DataCache::set('staking:apy_uusd', $apy_uusd, false, true);

		Database::query('INSERT IGNORE INTO `apy` (`block`, `apy`, `apy_uusd`, `apy_uluna`) VALUES (?, ?, ?, ?)', $block, $apy, $apy_uusd, $apy_uluna);

		exit;
	}

	public static function onUpdateTax() {
		if(!Net\Info::isCLI(true)) {
			die();
		}

		self::startLocking('tax');

		$height = self::getLatestBlock();
		
		$latest = Database::queryOneElement('SELECT MAX(`block`) FROM `tax_epoch` WHERE 1');
		if(!$latest) {
			$latest = Chain::BURNTAX_ACTIVE_AT;
		}

		for($block = $latest + 1; $block <= $height; $block++) {
			$header = null;
			if($block !== $height) {
				$header = ['x-cosmos-block-height' => $block];
			}
			$result = Request::requestGET('http://localhost:1317/terra/treasury/v1beta1/tax_proceeds', $header);
			$denoms = json_decode($result->getBody(), true);
			$uluna = 0;
			$uusd = 0;
			if(!empty($denoms)) {
				$denoms = $denoms['tax_proceeds'];
				foreach($denoms as $denom) {
					switch($denom['denom']) {
						case 'uluna':
							$uluna = $denom['amount'];
							break;
						case 'uusd':
							$uusd = $denom['amount'];
							break;
					}
				}
			}

			$epoch = Chain::getEpoch($block);
			$prev_max = Database::queryOne('SELECT MAX(`tax_uluna`) as `uluna`, MAX(`tax_uusd`) as `uusd` FROM `tax_epoch` WHERE `epoch` = ?', $epoch - 1);
			if(!$prev_max) {
				$prev_max = [
					'uluna' => 0,
					'uusd' => 0
				];
			}

			$uluna += ($prev_max['uluna'] * 1000000);
			$uusd += ($prev_max['uusd'] * 1000000);

			Database::query('INSERT INTO `tax_epoch` (`epoch`, `block`, `tax_uluna`, `tax_uusd`) VALUES (?, ?, ? / 1000000, ? / 1000000) ON DUPLICATE KEY UPDATE `tax_uluna` = ? / 1000000, `tax_uusd` = ? / 1000000', $epoch, $block, $uluna, $uusd, $uluna, $uusd);
		}

		self::releaseLocking('tax');
		exit;
	}

	public static function onUpdateCandles() {
		if(!Net\Info::isCLI(true)) {
			die();
		}

		\Framework\Ext\l10n\Locale::set('en');

		$valids = ['5m', '15m', '30m', '1h', '4h', '1d'];
		
		foreach($valids as $period) {
			$candles = [];
			$now = Date\DateTime::getInstance();

			switch($period) {
				case '5m':
					$qrystr = 'SELECT `time`, DATE_SUB(DATE_SUB(`time`, INTERVAL MINUTE(`time`) % 5 MINUTE), INTERVAL SECOND(`time`) SECOND) as `candle`, `price_uluna` FROM `blocks` WHERE `price_uluna` IS NOT NULL AND `time` >= DATE_SUB(DATE_SUB(NOW(), INTERVAL 300 MINUTE), INTERVAL MINUTE(NOW()) % 5 MINUTE) ORDER BY `time`';
					break;
				case '15m':
					$qrystr = 'SELECT `time`, DATE_SUB(DATE_SUB(`time`, INTERVAL MINUTE(`time`) % 15 MINUTE), INTERVAL SECOND(`time`) SECOND) as `candle`, `price_uluna` FROM `blocks` WHERE `price_uluna` IS NOT NULL AND `time` >= DATE_SUB(DATE_SUB(NOW(), INTERVAL 900 MINUTE), INTERVAL MINUTE(NOW()) % 15 MINUTE) ORDER BY `time`';
					break;
				case '30m':
					$qrystr = 'SELECT `time`, DATE_SUB(DATE_SUB(`time`, INTERVAL MINUTE(`time`) % 30 MINUTE), INTERVAL SECOND(`time`) SECOND) as `candle`, `price_uluna` FROM `blocks` WHERE `price_uluna` IS NOT NULL AND `time` >= DATE_SUB(DATE_SUB(NOW(), INTERVAL 1800 MINUTE), INTERVAL MINUTE(NOW()) % 15 MINUTE) ORDER BY `time`';
					break;
				case '1h':
					$qrystr = 'SELECT `time`, DATE_SUB(DATE_SUB(`time`, INTERVAL MINUTE(`time`) MINUTE), INTERVAL SECOND(`time`) SECOND) as `candle`, `price_uluna` FROM `blocks` WHERE `price_uluna` IS NOT NULL AND `time` >=  DATE_SUB(DATE_SUB(NOW(), INTERVAL 60 HOUR), INTERVAL MINUTE(NOW()) MINUTE) ORDER BY `time`';
					break;
				case '4h':
					$qrystr = 'SELECT `time`, DATE_SUB(DATE_SUB(DATE_SUB(`time`, INTERVAL HOUR(`time`) % 4 HOUR), INTERVAL MINUTE(`time`) MINUTE), INTERVAL SECOND(`time`) SECOND) as `candle`, `price_uluna` FROM `blocks` WHERE `price_uluna` IS NOT NULL AND `time` >= DATE_SUB(DATE_SUB(NOW(), INTERVAL 10 DAY), INTERVAL HOUR(NOW()) % 4 HOUR) ORDER BY `time`';
					break;
				case '1d':
					$qrystr = 'SELECT `time`, DATE(`time`) as `candle`, `price_uluna` FROM `blocks` WHERE `price_uluna` IS NOT NULL AND `time` >= DATE(DATE_SUB(NOW(), INTERVAL 60 DAY)) ORDER BY `time`';
					break;
			}
			$result = Database::query($qrystr);

			$last = null;
			while(($cur = $result->get())) {
				$c_time = $cur['candle'];
				$last = $c_time;
				$price = floatval($cur['price_uluna']);
				$price = round($price, 10);
				if(!isset($candles[$c_time])) {
					$candles[$c_time] = [
						'x' => Date\DateTime::getInstance($c_time)->getTimestamp() * 1000,
						'o' => $price,
						'h' => $price,
						'l' => $price,
						'c' => $price
					];
				}

				$candles[$c_time]['c'] = $price;
				if($price > $candles[$c_time]['h']) {
					$candles[$c_time]['h'] = $price;
				}
				if($price < $candles[$c_time]['l']) {
					$candles[$c_time]['l'] = $price;
				}
			}
			$result->free();

			if($last) {
				$candles[$last]['x'] = $now->getTimestamp() * 1000;
			}

			DataCache::setJSON('candles:' . $period, $candles, true, 300, null, 's');
		}

		return;
	}
}
