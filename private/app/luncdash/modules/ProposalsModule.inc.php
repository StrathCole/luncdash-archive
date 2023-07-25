<?php

namespace LUNCDash\Modules;

use Framework\Core\Database;
use Framework\Core\Date;
use Framework\Core\Date\Format;
use Framework\Core\Modules\Base as ModuleBase;
use Framework\Core\Template;
use Framework\Ext\DataCache;
use Framework\Ext\l10n\Locale;
use Framework\Ext\Net\Request;

class ProposalsModule extends ModuleBase {
	/**
	 * @param string $action
	 */
	public static function onShow() {

		Locale::set('en');

		//Template::enableCache(60, false, false, false, [], '*');
		Template::load('lists/proposals');
		Template::load('lists/proposals.entry');
		
		$list = '';
		$result = Database::query('SELECT `title`, `id`, `status`, `whitelisted` FROM `proposals` WHERE `status` != ? ORDER BY `id` DESC', 'deposit');
		while(($cur = $result->get())) {
			$votes = Database::queryAll('SELECT v.name, v.address, vo.vote FROM `votes` as vo INNER JOIN `validators` as v ON (v.operator_address = vo.validator) WHERE vo.proposal_id = ?', $cur['id']);
			$cur['votes'] = $votes;
			Template::replacements($cur);
			$list .= Template::get();
		}
		$result->free();
		
		Template::select('lists/proposals');
		Template::assign('proposals', $list);

		Template::load('main');
		//Template::assign('META_DESC', HTML::toEntities($meta_data));
		Template::add(Template::get('lists/proposals'));
		Template::show();

		exit;
	}
}
