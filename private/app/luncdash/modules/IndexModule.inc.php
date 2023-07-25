<?php

namespace LUNCDash\Modules;

use Framework\Core\Database;
use Framework\Core\Date;
use Framework\Core\Date\DateTime;
use Framework\Core\Modules\Base as ModuleBase;
use Framework\Core\Template;
use Framework\Ext\DataCache;
use Framework\Ext\Fs\Locking;
use Framework\Ext\l10n\Locale;
use Framework\Ext\Net\Param;
use Framework\Ext\Net\Request;
use LUNCDash\Lib\Chain;
use LUNCDash\Lib\Wallets;

class IndexModule extends ModuleBase {
	/**
	 * @param string $action
	 */
	public static function onShow() {

		Locale::set('en');

		$bonded = Database::queryOneElement('SELECT `bonded_uluna` FROM `blocks` WHERE `bonded_uluna` > 0 ORDER BY `block` DESC LIMIT 0,1');
		$unbonded = Database::queryOneElement('SELECT `unbonded_uluna` FROM `blocks` WHERE `unbonded_uluna` > 0 ORDER BY `block` DESC LIMIT 0,1');

		$total_supply = Database::queryOneElement('SELECT `total_supply_uluna` FROM `blocks` WHERE `total_supply_uluna` > 0 ORDER BY `block` DESC LIMIT 0,1');
		$total_supply_ust = Database::queryOneElement('SELECT `total_supply_uusd` FROM `blocks` WHERE `total_supply_uusd` > 0 ORDER BY `block` DESC LIMIT 0,1');
		$foreign_supply_ust = Chain::getForeignUSTSupply();

		$circulating_supply = Database::queryOneElement('SELECT `circulating_uluna` FROM `blocks` WHERE `circulating_uluna` > 0 ORDER BY `block` DESC LIMIT 0,1');
		$circulating_supply_ust = Database::queryOneElement('SELECT `circulating_uusd` FROM `blocks` WHERE `circulating_uusd` > 0 ORDER BY `block` DESC LIMIT 0,1');
		$community_pool = Database::queryOneElement('SELECT `pool_uluna` FROM `blocks` WHERE `pool_uluna` > 0 ORDER BY `block` DESC LIMIT 0,1');
		$community_pool_ust = Database::queryOneElement('SELECT `pool_uusd` FROM `blocks` WHERE `pool_uusd` > 0 ORDER BY `block` DESC LIMIT 0,1');
		$circ_unofficial = Database::queryOneElement('SELECT (b.total_supply_uluna - b.bonded_uluna - b.unbonded_uluna - b.pool_uluna - ba.uluna) FROM `blocks` as b left join `balance` as ba ON (ba.wallet = ? AND ba.block = (b.block - (b.block % 100))) WHERE b.total_supply_uluna IS NOT NULL AND b.total_supply_uluna > 0 AND ba.wallet IS NOT NULL ORDER BY b.block DESC LIMIT 0,1', Wallets::ORACLE_WALLET);
		
		$height = DataCache::get('block:height');
		if(!$height) {
			$response = Request::requestGET('http://localhost:1317/cosmos/base/tendermint/v1beta1/blocks/latest');
			$height = json_decode($response->getBody(), true);
			if(!isset($height['block']['header']['height'])) {
				$height = null;
			} else {
				$height = $height['block']['header']['height'];
				DataCache::set('block:height', $height, false, true, 5, null, 's');
			}
		}
		$validators_at = Chain::VALIDATORS_AT;
		$until_validators = $validators_at - $height;
		if($until_validators > 0) {
			$attime = Database::queryOneElement('SELECT DATE_ADD(NOW(), INTERVAL TIMESTAMPDIFF(SECOND, `time`, NOW()) SECOND) FROM `blocks` WHERE `block` >= ' . ($height - $until_validators) . ' ORDER BY block ASC LIMIT 1');
		} else {
			$attime = Date::getTime();
		}
		$val_until = Date\Calc::getDateDiff(Date::getTime(), $attime, false);

		$burntax_at = Chain::BURNTAX_ACTIVE_AT;
		$until_burntax = $burntax_at - $height;
		if($until_burntax > 0) {
			$attime = Database::queryOneElement('SELECT DATE_ADD(NOW(), INTERVAL TIMESTAMPDIFF(SECOND, `time`, NOW()) SECOND) FROM `blocks` WHERE `block` >= ' . ($height - $until_burntax) . ' ORDER BY block ASC LIMIT 1');
		} else {
			$attime = Date::getTime();
		}
		$burntax_until = Date\Calc::getDateDiff(Date::getTime(), $attime, false);

		$uusd_price = DataCache::get('price:uusd:binance');
		$uluna_price = DataCache::get('price:uluna:binance');
		if(!$uusd_price) {
			$result = Request::requestGET('https://api.binance.com/api/v3/klines?symbol=USTCBUSD&interval=1m&limit=1');
			$tmp = json_decode($result->getBody(), true);
			$uusd_price = (float)$tmp[4];
			DataCache::set('price:uusd:binance', $uusd_price, false, true, 2);
		}
		if(!$uluna_price) {
			$result = Request::requestGET('https://api.binance.com/api/v3/klines?symbol=LUNCBUSD&interval=1m&limit=1');
			$tmp = json_decode($result->getBody(), true);
			$uluna_price = (float)$tmp[4];
			DataCache::set('price:uluna:binance', $uusd_price, false, true, 2);
		}
		if(!$uluna_price) {
			$factor = 100;
		} else {
			$factor = $uusd_price / $uluna_price;
			if(!$factor) {
				$factor = 100;
			}
		}
		$apy = DataCache::get('staking:apy');
		$apy_uluna = DataCache::get('staking:apy_uluna');
		$apy_uusd = DataCache::get('staking:apy_uusd');

		
		$rep = [

			'val_days' => ($val_until->invert ? 0 : $val_until->days),
			'val_days_1' => ($val_until->invert ? 0 : (int)($val_until->days / 10)),
			'val_days_2' => ($val_until->invert ? 0 : (int)($val_until->days % 10)),
			'val_hours' => ($val_until->invert ? 0 : $val_until->h),
			'val_hours_1' => ($val_until->invert ? 0 : (int)($val_until->h / 10)),
			'val_hours_2' => ($val_until->invert ? 0 : (int)($val_until->h % 10)),
			'val_minutes' => ($val_until->invert ? 0 : $val_until->i),
			'val_minutes_1' => ($val_until->invert ? 0 : (int)($val_until->i / 10)),
			'val_minutes_2' => ($val_until->invert ? 0 : (int)($val_until->i % 10)),
			'val_seconds' => ($val_until->invert ? 0 : $val_until->s),
			'val_seconds_1' => ($val_until->invert ? 0 : (int)($val_until->s / 10)),
			'val_seconds_2' => ($val_until->invert ? 0 : (int)($val_until->s % 10)),

			'burntax_days' => ($burntax_until->invert ? 0 : $burntax_until->days),
			'burntax_days_1' => ($burntax_until->invert ? 0 : (int)($burntax_until->days / 10)),
			'burntax_days_2' => ($burntax_until->invert ? 0 : (int)($burntax_until->days % 10)),
			'burntax_hours' => ($burntax_until->invert ? 0 : $burntax_until->h),
			'burntax_hours_1' => ($burntax_until->invert ? 0 : (int)($burntax_until->h / 10)),
			'burntax_hours_2' => ($burntax_until->invert ? 0 : (int)($burntax_until->h % 10)),
			'burntax_minutes' => ($burntax_until->invert ? 0 : $burntax_until->i),
			'burntax_minutes_1' => ($burntax_until->invert ? 0 : (int)($burntax_until->i / 10)),
			'burntax_minutes_2' => ($burntax_until->invert ? 0 : (int)($burntax_until->i % 10)),
			'burntax_seconds' => ($burntax_until->invert ? 0 : $burntax_until->s),
			'burntax_seconds_1' => ($burntax_until->invert ? 0 : (int)($burntax_until->s / 10)),
			'burntax_seconds_2' => ($burntax_until->invert ? 0 : (int)($burntax_until->s % 10)),

			'apy' => round($apy, 1),
			'apy_uluna' => round($apy_uluna, 1),
			'apy_uusd' => round($apy_uusd, 1)
		];

		Template::load('index');
		Template::replacements($rep);
		Template::assign('total_supply', preg_replace('/\.(\d+)$/', '.<small>$1</small>', number_format($total_supply, 6, '.', ',')));
		Template::assign('total_supply_ust', preg_replace('/\.(\d+)$/', '.<small>$1</small>', number_format($total_supply_ust, 6, '.', ',')));
		Template::assign('foreign_supply_ust', preg_replace('/\.(\d+)$/', '.<small>$1</small>', number_format($foreign_supply_ust, 6, '.', ',')));
		Template::assign('circulating_supply', preg_replace('/\.(\d+)$/', '.<small>$1</small>', number_format($circulating_supply, 6, '.', ',')));
		Template::assign('circulating_supply_ust', preg_replace('/\.(\d+)$/', '.<small>$1</small>', number_format($circulating_supply_ust, 6, '.', ',')));
		Template::assign('circulating_supply_unofficial', preg_replace('/\.(\d+)$/', '.<small>$1</small>', number_format($circ_unofficial, 6, '.', ',')));
		Template::assign('community_pool', preg_replace('/\.(\d+)$/', '.<small>$1</small>', number_format($community_pool, 6, '.', ',')));
		Template::assign('community_pool_ust', preg_replace('/\.(\d+)$/', '.<small>$1</small>', number_format($community_pool_ust, 6, '.', ',')));
		Template::assign('staking_ratio', 100 * (($bonded + $unbonded) / $total_supply));
		Template::assign('staking_ratio_bonded', 100 * ($bonded / $total_supply));
		Template::assign('burn_wallet', Wallets::BURN_WALLET);
		Template::assign('tax_active', ($height >= Chain::BURNTAX_ACTIVE_AT || Param::checkGet('testtax', '1')));

		Template::load('main');
		//Template::assign('META_DESC', HTML::toEntities($meta_data));
		Template::assign('LOAD_SCRIPT', 'luncprice.js');
		Template::add(Template::get('index'));
		Template::show();

		exit;
	}

}
