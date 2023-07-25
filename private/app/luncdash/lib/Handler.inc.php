<?php

namespace LUNCDash\Lib;

use Framework\Core\Template;
use Framework\Ext\Hooks;
use Framework\Ext\Net;
use Framework\Ext\Net\Http;

class Handler {
	public static function init() {
		Hooks::register('autoloader', __NAMESPACE__ . '\\Handler', 'hookAutoloader');
		if(Net\Info::isCLI() == false) {
			if(isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST']) {
				$host = $_SERVER['HTTP_HOST'];
				Template::setTheme('');
			}
		}
	}

	public static function hookAutoloader(string $hook_name, string $class_name, string $base_path) : ?string {
		$class_path = null;

		if(substr($class_name, 0, 1) === '\\') {
			$class_name = substr($class_name, 1);
		}

		if(Net\Info::isCLI() == false) {
			if(isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST']) {
				$host = $_SERVER['HTTP_HOST'];
			}
		}

		return $class_path;
	}
}
