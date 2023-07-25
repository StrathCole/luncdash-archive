<?php

namespace LUNCDash\Modules;

use Framework\Core\Database;
use Framework\Core\Modules\Base as ModuleBase;
use Framework\Core\Template;
use Framework\Ext\l10n\Locale;

class StatisticsModule extends ModuleBase {
	/**
	 * @param string $action
	 */
	public static function onShow() {

		Locale::set('en');

		$above_100k = Database::queryOneElement('SELECT COUNT(*) FROM `wallet` WHERE `uluna` >= ?', 100000);
		$above_1m = Database::queryOneElement('SELECT COUNT(*) FROM `wallet` WHERE `uluna` >= ?', 1000000);
		$above_10m = Database::queryOneElement('SELECT COUNT(*) FROM `wallet` WHERE `uluna` >= ?', 10000000);
		$above_100m = Database::queryOneElement('SELECT COUNT(*) FROM `wallet` WHERE `uluna` >= ?', 100000000);
		$above_1b = Database::queryOneElement('SELECT COUNT(*) FROM `wallet` WHERE `uluna` >= ?', 1000000000);

		$above_100k_cleaned = Database::queryOneElement('SELECT COUNT(*) FROM `wallet` WHERE `uluna` >= ? AND `type` IN ?', 100000, ['', 'whale', 'project']);
		$above_1m_cleaned = Database::queryOneElement('SELECT COUNT(*) FROM `wallet` WHERE `uluna` >= ? AND `type` IN ?', 1000000, ['', 'whale', 'project']);
		$above_10m_cleaned = Database::queryOneElement('SELECT COUNT(*) FROM `wallet` WHERE `uluna` >= ? AND `type` IN ?', 10000000, ['', 'whale', 'project']);
		$above_100m_cleaned = Database::queryOneElement('SELECT COUNT(*) FROM `wallet` WHERE `uluna` >= ? AND `type` IN ?', 100000000, ['', 'whale', 'project']);
		$above_1b_cleaned = Database::queryOneElement('SELECT COUNT(*) FROM `wallet` WHERE `uluna` >= ? AND `type` IN ?', 1000000000, ['', 'whale', 'project']);

		$tx_7d = Database::queryOneElement('SELECT COUNT(*) FROM `tx` as t INNER JOIN `blocks` as b ON (b.block = t.block) WHERE t.denom = ? AND b.date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)', 'uluna');
		$tx_24h = Database::queryOneElement('SELECT COUNT(*) FROM `tx` as t INNER JOIN `blocks` as b ON (b.block = t.block) WHERE t.denom = ? AND b.time >= DATE_SUB(NOW(), INTERVAL 1 DAY)', 'uluna');

	
		$rep = [
			'above_100k' => $above_100k,
			'above_1m' => $above_1m,
			'above_10m' => $above_10m,
			'above_100m' => $above_100m,
			'above_1b' => $above_1b,
			'above_100k_cleaned' => $above_100k_cleaned,
			'above_1m_cleaned' => $above_1m_cleaned,
			'above_10m_cleaned' => $above_10m_cleaned,
			'above_100m_cleaned' => $above_100m_cleaned,
			'above_1b_cleaned' => $above_1b_cleaned,
			'tx_7d' => $tx_7d,
			'tx_24h' => $tx_24h
		];

		Template::load('statistics');
		Template::replacements($rep);

		Template::load('main');
		//Template::assign('META_DESC', HTML::toEntities($meta_data));
		Template::add(Template::get('statistics'));
		Template::show();

		exit;
	}
}
