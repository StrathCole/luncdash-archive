<?php

require "db.inc.php";
require "functions.inc.php";

/** SHOULD BE DEPRECATED */

$data = [
	'labels' => [],
	'datasets' => [
		[
			'label' => 'LUNC',
			'data' => [],
			'borderColor' => '#ffa600'
		],
		[
			'label' => 'USTC',
			'data' => [],
			'borderColor' => '#003f5c'
		]
	]
];

$wallet = $argv[1];
$name = '';
if(isset($argv[2])) {
	$name = $argv[2];
}

$qrystr = 'SELECT DATE(bl.time) as `date`, b.uluna, b.uusd FROM `balance` as b INNER JOIN `blocks` as bl ON (bl.block = b.block) WHERE b.wallet = ? GROUP BY DATE(bl.time)';
$result = $db->query($qrystr, $wallet);
while(($cur = $result->get())) {
	$data['labels'][] = $cur['date'];
	$data['datasets'][0]['data'][] = $cur['uluna'];
	$data['datasets'][1]['data'][] = $cur['uusd'];
}
$result->free();

$fp = fopen('/var/www/html/data' . $name . '.js', 'w');
fwrite($fp, json_encode($data));
fclose($fp);

file_put_contents('/var/www/html/update.js', strftime('%Y-%m-%d %H:%M:%S %Z', time()));
