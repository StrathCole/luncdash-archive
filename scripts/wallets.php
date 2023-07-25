<?php

require "db.inc.php";
require "functions.inc.php";

function getBalance($wallet, $block) {
	global $db;

	$block = intval($block / 100) * 100;

	$result = get('http://localhost:1317/cosmos/bank/v1beta1/balances/' . $wallet . '?pagination.limit=1000', ['x-cosmos-block-height' => $block]);

	$json = json_decode($result, true);
	if(!isset($json['balances'])) { die ("ERR\n"); }
	if(empty($json['balances'])) {
		$json['balances'] = [
			[
				'denom' => 'uluna',
				'amount' => 0
			],
			[
				'denom' => 'uusd',
				'amount' => 0
			]
		];
	}

	$uluna = 0;
	$uusd = 0;
	$usdr = 0;
	foreach($json['balances'] as $balance) {
		switch($balance['denom']) {
			case 'uusd':
				$uusd = $balance['amount'];
				break;
			case 'uluna':
				$uluna = $balance['amount'];
				break;
			case 'usdr':
				$usdr = $balance['amount'];
				break;
			default:
				continue 2;
		}
	}

	$qrystr = 'INSERT INTO `wallet` (`wallet`, `block`, `uluna`, `uusd`, `usdr`) VALUES (?, ?, ? / 1000000, ? / 1000000, ? / 1000000) ON DUPLICATE KEY UPDATE `block` = ?, `uluna` = ? / 1000000, `uusd` = ? / 1000000, `usdr` = ? / 1000000';
	$db->query($qrystr, $wallet, $block, $uluna, $uusd, $usdr, $block, $uluna, $uusd, $usdr);
}

if(isset($argv[1]) && $argv[1] !== 'prev') {
	$start = $argv[1];
} else {
	$start = get_last_block_of('wallet') + 1;
}
$last_tx = get_last_block_of('tx');
if(isset($argv[2])) {
	$end = $argv[2];
} else {
	$end = $last_tx;
}

if($end < $start) {
	print "no blocks\n";
	exit;
}

$to_update = [];
print "getting tx from block $start to $end\n";
$qrystr = 'SELECT DISTINCT `sender` as `wallet` FROM `tx` WHERE `block` BETWEEN ? AND ? UNION SELECT DISTINCT `recipient` as `wallet` FROM `tx` WHERE `block` BETWEEN ? AND ?';
$result = $db->query($qrystr, $start, $end, $start, $end);
$cnt = $result->rows();
print "Found " . $cnt . " wallets to process\n";
$i = 0;
while(($cur = $result->get())) {
	$i++;
	print $i . '/' . $cnt . ': ' . $cur['wallet'] . "\n";
	getBalance($cur['wallet'], $last_tx);
}
$result->free();
