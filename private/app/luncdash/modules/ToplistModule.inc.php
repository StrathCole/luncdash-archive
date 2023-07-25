<?php

namespace LUNCDash\Modules;

use Framework\Core\Database;
use Framework\Core\Modules\Base as ModuleBase;
use Framework\Core\Template;

class ToplistModule extends ModuleBase {
	/**
	 * @param string $action
	 */
	public static function onBurns() {

		Template::load('top/burns');

		Template::load('main');
		//Template::assign('META_DESC', HTML::toEntities($meta_data));
		Template::assign('LOAD_SCRIPT', 'burners.js');
		Template::assign('ADD_SCRIPT', 'refreshData();');
		Template::add(Template::get('top/burns'));
		Template::show();

		exit;
	}

	public static function onHolders() {
		//Template::enableCache(10);

		Template::load('top/holders');

		$total_supply = Database::queryOneElement('SELECT `total_supply_uluna` FROM `blocks` WHERE `total_supply_uluna` > 0 ORDER BY `block` DESC LIMIT 0,1');
		$total_supply_ust = Database::queryOneElement('SELECT `total_supply_uusd` FROM `blocks` WHERE `total_supply_uusd` > 0 ORDER BY `block` DESC LIMIT 0,1');

		$holders = [];
		$holders_ust = [];

		$qrystr = 'SELECT `uluna`, `wallet`, `descr` FROM `wallet` WHERE 1 ORDER BY `uluna` DESC LIMIT 50';
		$result = Database::query($qrystr);
		while(($cur = $result->get())) {
			$holders[] = [
				'uluna' => preg_replace('/\.(\d+)$/', '.<small>$1</small>', number_format($cur['uluna'], 6, '.', ',')),
				'wallet' => $cur['wallet'],
				'descr' => $cur['descr'],
				'percentage' => number_format(round(100 * ($cur['uluna'] / $total_supply), 2), 2, '.', ',')
			];
		}
		$result->free();

		$qrystr = 'SELECT `uusd`, `wallet`, `descr` FROM `wallet` WHERE 1 ORDER BY `uusd` DESC LIMIT 50';
		$result = Database::query($qrystr);
		while(($cur = $result->get())) {
			$holders_ust[] = [
				'uusd' => preg_replace('/\.(\d+)$/', '.<small>$1</small>', number_format($cur['uusd'], 6, '.', ',')),
				'wallet' => $cur['wallet'],
				'descr' => $cur['descr'],
				'percentage' => number_format(round(100 * ($cur['uusd'] / $total_supply_ust), 2), 2, '.', ',')
			];
		}
		$result->free();

		Template::assign('holders', $holders);
		Template::assign('holders_ust', $holders_ust);
		Template::assign('total_supply', preg_replace('/\.(\d+)$/', '.<small>$1</small>', number_format($total_supply, 6, '.', ',')));
		Template::assign('total_supply_ust', preg_replace('/\.(\d+)$/', '.<small>$1</small>', number_format($total_supply_ust, 6, '.', ',')));

		Template::load('main');
		//Template::assign('META_DESC', HTML::toEntities($meta_data));
		Template::assign('LOAD_SCRIPT', 'holders.js');
		Template::add(Template::get('top/holders'));
		Template::show();

		exit;
	}
}
