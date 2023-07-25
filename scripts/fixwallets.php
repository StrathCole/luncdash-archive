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
		return;
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

$end = get_latest_block();
$qrystr = 'SELECT `wallet` FROM `wallet` WHERE `block` < ? - 10000';
$result = $db->query($qrystr, $end);
$cnt = $result->rows();
print "Found " . $cnt . " wallets to process\n";
$i = 0;
while(($cur = $result->get())) {
	$i++;
	print $i . '/' . $cnt . ': ' . $cur['wallet'] . "\n";
	getBalance($cur['wallet'], $end);
}
$result->free();
