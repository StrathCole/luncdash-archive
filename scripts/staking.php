<?php

require "db.inc.php";
require "functions.inc.php";

function getBalances($block) {
	global $db;

	print "Block $block\n";

	$result = get('http://127.0.0.1:1317/cosmos/staking/v1beta1/pool?pagination.limit=1000', ['x-cosmos-block-height' => $block]);

	$json = json_decode($result, true);
	if(!isset($json['pool']) || !isset($json['pool']['bonded_tokens']) || !isset($json['pool']['not_bonded_tokens'])) { var_dump($json);die ("ERR\n"); }
	if(empty($json['pool'])) {
		return;
	}

	$uluna_bonded = $json['pool']['bonded_tokens'];
	$uluna_unbonded = $json['pool']['not_bonded_tokens'];

	$qrystr = 'INSERT INTO `blocks` (`block`, `bonded_uluna`, `unbonded_uluna`) VALUES (?, ? / 1000000, ? / 1000000) ON DUPLICATE KEY UPDATE `bonded_uluna` = ? / 1000000, `unbonded_uluna` = ? / 1000000';
	$db->query($qrystr, $block, $uluna_bonded, $uluna_unbonded, $uluna_bonded, $uluna_unbonded);
}

if(isset($argv[1])) {
	$start = $argv[1];
} else {
	$start = get_last_block_of('bonded') + 1;
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


