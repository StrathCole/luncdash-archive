<?php

namespace LUNCDash\Modules;

use Framework\Core\Modules\Base as ModuleBase;
use Framework\Core\Template;
use Framework\Ext\l10n\Locale;
use FrameworkModule\DatabaseManager\DatabaseObject;
use LUNCDash\Lib\Twitter;

class NewsModule extends ModuleBase {
	/**
	 * @param string $action
	 * @deprecated sunset … not used anymore
	 */
	public static function onShow() {

		Locale::set('en');

		$tweets = DatabaseObject::getInstance('tweets');

		$tweets->findInDatabase(null, null, 50, ['tweet_time' => 'DESC']);
		$rows = $tweets->getArrayFromResult();

		foreach($rows as &$row) {
			$row['html'] = Twitter::getHTML($row['tweet'], $row['entities']);
		}
		unset($row);

		$rep = [
			'tweets' => $rows
		];

		Template::load('news');
		Template::replacements($rep);

		Template::load('main');
		//Template::assign('META_DESC', HTML::toEntities($meta_data));
		Template::add(Template::get('news'));
		Template::show();

		exit;
	}
}
