<?php

namespace LUNCDash\Lib;

use Framework\Core\Database;
use Framework\Core\Log;
use Framework\Ext\DataCache;
use Framework\Ext\Net\Param;
use Framework\Ext\Net\Request;

class Chain {
	const BLOCKS_PER_MINUTE = 10; 
	const BLOCKS_PER_HOUR = 600; 
	const BLOCKS_PER_DAY = 14400; 
	const BLOCKS_PER_WEEK = 100800; 
	const BLOCKS_PER_MONTH = 432000;
	const BLOCKS_PER_YEAR = 5256000;
	
	const FIRST_BLOCK = 4724001;
	const STAKING_AT = 9109990;
	const VALIDATORS_AT = 9988390;
	const BURNTAX_AT = 9346889;
	const BURNTAX_ACTIVE_AT = 9475200;
	
	public static function getEpoch(int $block) : int {
		$epoch = (int)($block / self::BLOCKS_PER_WEEK);

		return $epoch;
	}
	
	public static function lastBlockOfEpoch(int $epoch) : int {
		return ($epoch * self::BLOCKS_PER_WEEK) - 1;
	}

	public static function nextEpochAt(int $block) : int {
		$epoch = self::getEpoch($block);
		$epoch++;
		
		return $epoch * self::BLOCKS_PER_WEEK;
	}

	public static function getForeignUSTSupply() : float {
		$value = DataCache::get('ustc:foreign');
		if($value !== null) {
			return $value;
		}

		$result = Request::requestGET('http://localhost:1317/cosmos/bank/v1beta1/supply?pagination.limit=5000');
		$tmp = json_decode($result->getBody(), true);
		$tmp = $tmp['supply'];
		$supply = [];
		foreach($tmp as $entry) {
			$supply[$entry['denom']] = $entry['amount'] / 1000000;
		}

		$result = Request::requestGET('http://localhost:1317/terra/oracle/v1beta1/denoms/exchange_rates?pagination.limit=5000');
		$tmp = json_decode($result->getBody(), true);
		$tmp = $tmp['exchange_rates'];
		$rates = [];
		foreach($tmp as $entry) {
			$rates[$entry['denom']] = $entry['amount'];
		}

		$uusd_rate = $rates['uusd'];
		$ustc = 0;
		foreach($supply as $denom => $amount) {
			if($denom === 'uusd' || $denom === 'uluna') {
				continue;
			}

			if(!isset($rates[$denom])) {
				continue;
			}
			$rate = $uusd_rate / $rates[$denom];
			$ustc += ($rate * $amount);
			//Log::info($denom . ' (' . round($rate, 6) . '): supply ' . round($amount) . ' = ' . round($rate * $amount) . ' USTC');
		}

		DataCache::set('ustc:foreign', $ustc, false, true, 2);
		
		return $ustc;
	}

	public static function getTaxBurned(string $denom = 'uluna') : float {
		if(!Param::checkGet('taxtest')) {
			return Database::queryOneElement('SELECT MAX(??) as `amount` FROM `tax_epoch` WHERE 1', 'tax_' . $denom);
		}

		if(Param::checkGet('taxtest', '1')) {
			$latest_tx = Database::queryOneElement('SELECT MAX(`block`) FROM `tx`');
			$burnwallet = Database::queryOneElement('SELECT SUM(amount) FROM `tx` WHERE `recipient` = ? AND `denom` = ? AND `block` >= ?', Wallets::BURN_WALLET, $denom, self::BURNTAX_ACTIVE_AT);
			$luncblaze = Database::queryOneElement('SELECT SUM(amount) FROM `tx` WHERE `contract` = ? AND `denom` = ? AND `block` >= ?', Wallets::LUNCBLAZE_CONTRACT, $denom, self::BURNTAX_ACTIVE_AT);
			$total_supply_pre = Database::queryOneElement('SELECT ?? FROM `blocks` WHERE `block` < ? AND ?? != 0 ORDER BY `block` DESC LIMIT 0,1', 'total_supply_' . $denom, self::BURNTAX_ACTIVE_AT, 'total_supply_' . $denom);
			$total_supply_now = Database::queryOneElement('SELECT ?? FROM `blocks` WHERE `block` <= ? AND ?? != 0 ORDER BY `block` DESC LIMIT 0,1', 'total_supply_' . $denom, $latest_tx, 'total_supply_' . $denom);

			return $total_supply_pre - $total_supply_now - $burnwallet - $luncblaze;
		} else {
			$burned = Database::queryOneElement('SELECT SUM(??) FROM `tx_fees` WHERE 1', 'tax_' . $denom);
			return $burned;
		}
	}
}
