<?php

require "db.inc.php";
require "functions.inc.php";

$prev = null;

function getBalances($block) {
	global $db, $prev;

	print "Block $block\n";

	$result = get('http://127.0.0.1:1317/cosmos/distribution/v1beta1/community_pool?pagination.limit=1000', ['x-cosmos-block-height' => $block]);

	$json = json_decode($result, true);
	if(!isset($json['pool'])) { var_dump($json);die ("ERR\n"); }
	if(empty($json['pool'])) {
		return;
	}

	$uluna = 0;
	$uusd = 0;
	foreach($json['pool'] as $balance) {
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

	//if($prev === null || $prev['uluna'] != $uluna || $prev['uusd'] != $uusd) {
		$prev = [
			'uluna' => $uluna,
			'uusd' => $uusd
		];
		$qrystr = 'INSERT INTO `blocks` (`block`, `pool_uluna`, `pool_uusd`) VALUES (?, ? / 1000000, ? / 1000000) ON DUPLICATE KEY UPDATE `pool_uluna` = ? / 1000000, `pool_uusd` = ? / 1000000';
		$db->query($qrystr, $block, $uluna, $uusd, $uluna, $uusd);
	//}
}

if(isset($argv[1])) {
	$start = $argv[1];
} else {
	$start = get_last_block_of('pool') + 1;
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
	getBalances($b);
}


