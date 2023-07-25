<?php

namespace LUNCDash\Modules\CLI;

use Framework\Core\Database;
use Framework\Core\Exceptions\ParamException;
use Framework\Core\Modules\Base as ModuleBase;
use Framework\Core\Security;
use Framework\Ext\Net;
use Framework\Ext\Net\Http;
use Framework\Ext\Net\Param;
use FrameworkModule\Sys\DatabaseUpdater;

class UpdaterModule extends ModuleBase {
	public static function onUpdateDatabase() {
		if(!Net\Info::isCLI(true)) {
			die();
		}

		$dry = true;
		if(Param::checkGet('execute')) {
			$dry = false;
		}
		$changed = DatabaseUpdater::run($dry, true);
		print "$changed databases with updates\n";
		if($changed > 0) {
			exit(0);
		} else {
			exit(100);
		}
	}

}
