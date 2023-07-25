<?php

/*
  Main file of the portal (new namespaced version)
 */

setlocale(LC_ALL, ['en_US.utf8', 'en_US.utf-8', 'en_US.iso88591', 'en_US', 'en', 'us']);
mb_internal_encoding('UTF-8');

$wd = realpath(__DIR__);
$public_path = $wd;

// check for working dir override
if(file_exists($wd . '/.app.ini')) {
	$tmp = parse_ini_file($wd . '/.app.ini');
	if($tmp && isset($tmp['working_dir'])) {
		$tmp = $tmp['working_dir'];
		if(substr($tmp, 0, 1) !== '/') {
			$tmp = $wd . '/' . $tmp;
		}
		if(is_dir($tmp)) {
			$wd = realpath($tmp);
		}
	}
}

require $wd . '/lib/core/Autoloader.inc.php';
Autoloader::init();
Application::setPublicDir($public_path);
Application::run();

exit;
