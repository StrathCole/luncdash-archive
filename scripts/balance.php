<?php

require "db.inc.php";
require "functions.inc.php";

$prev = null;

function getBalances($wallet, $block, $last_block = false) {
	global $db, $prev;

	print "Block $block\n";

	$result = get('http://localhost:1317/cosmos/bank/v1beta1/balances/' . $wallet . '?pagination.limit=1000', ['x-cosmos-block-height' => $block]);

	$json = json_decode($result, true);
	if(!isset($json['balances'])) { die ("ERR\n"); }
	if(empty($json['balances'])) {
		return;
	}

	$uluna = 0;
	$uusd = 0;
	foreach($json['balances'] as $balance) {
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

	$prev = [
		'uluna' => $uluna,
		'uusd' => $uusd
	];
	$qrystr = 'INSERT IGNORE INTO `balance` (`wallet`, `block`, `uluna`, `uusd`) VALUES (?, ?, ? / 1000000, ? / 1000000)';
	$db->query($qrystr, $wallet, $block, $uluna, $uusd);
}

$wallet = $argv[1];
if(isset($argv[2])) {
	$start = $argv[2];
} else {
	$start = get_last_block_of('balance', $wallet) + 1;
}
if(isset($argv[3])) {
	$end = $argv[3];
	$end = intval(ceil($end / 100)) * 100;
} else {
	$end = get_latest_block();
	$end = intval($end / 100) * 100;
}

$start = intval($start / 100) * 100;


for($b = $start; $b <= $end; $b+=100) {
	getBalances($wallet, $b, ($b === $end));
}


