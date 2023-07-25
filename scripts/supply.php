<?php

require "db.inc.php";
require "functions.inc.php";

$prev = null;

function getBalances($block, $last = false) {
	global $db, $prev;

	print "Block $block\n";

	$result = get('http://127.0.0.1:1317/cosmos/bank/v1beta1/supply?pagination.limit=1000', ['x-cosmos-block-height' => $block]);

	$json = json_decode($result, true);
	if(!isset($json['supply'])) { var_dump($json);die ("ERR\n"); }
	if(empty($json['supply'])) {
		return;
	}

	$uluna = 0;
	$uusd = 0;
	foreach($json['supply'] as $balance) {
		switch($balance['denom']) {
			case 'uusd':
				$uusd = $balance['amount'];
				break;
			case 'uluna':
				$uluna = $balance['amount'];
				break;
			default:
				continue 2;
		}
	}

	//if($prev === null || $last || $prev['uluna'] != $uluna || $prev['uusd'] != $uusd) {
		$prev = [
			'uluna' => $uluna,
			'uusd' => $uusd
		];
		$qrystr = 'INSERT INTO `blocks` (`block`, `total_supply_uluna`, `total_supply_uusd`) VALUES (?, ? / 1000000, ? / 1000000) ON DUPLICATE KEY UPDATE `total_supply_uluna` = ? / 1000000, `total_supply_uusd` = ? / 1000000';
		$db->query($qrystr, $block, $uluna, $uusd, $uluna, $uusd);
	//}
}

if(isset($argv[1])) {
	$start = $argv[1];
} else {
	$start = get_last_block_of('supply') + 1;
}
if(isset($argv[2])) {
	$end = $argv[2];
	$end = intval(ceil($end / 100)) * 100;
} else {
	$end = get_latest_block();
	$end = intval($end / 100) * 100;
}

$start = intval($start / 100) * 100;

for($b = $start; $b <= $end; $b+=100) {
	getBalances($b, ($b === $end));
}


