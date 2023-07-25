<?php

namespace LUNCDash\Modules;

use Framework\Core\Database;
use Framework\Core\Date;
use Framework\Core\Date\Format;
use Framework\Core\Modules\Base as ModuleBase;
use Framework\Core\Template;
use Framework\Ext\DataCache;
use Framework\Ext\Net\Request;
use LUNCDash\Lib\Wallets;

class DelegationModule extends ModuleBase {
	/**
	 * @param string $action
	 */
	public static function onValidator() {

		Template::load('charts/delegations');

		
		exit;
	}
}
