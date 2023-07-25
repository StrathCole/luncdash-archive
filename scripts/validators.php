<?php

require "db.inc.php";
require "functions.inc.php";

function getBalances($block) {
	global $db;

	print "Block $block\n";

	$result = get('http://127.0.0.1:1317/cosmos/staking/v1beta1/validators?pagination.limit=1000', ['x-cosmos-block-height' => $block]);

	$json = json_decode($result, true);
	if(!isset($json['validators'])) { var_dump($json);die ("ERR\n"); }
	if(empty($json['validators'])) {
		return;
	}

	$bonded = 0;
	$jailed = 0;
	$unbonded = 0;

	foreach($json['validators'] as $val) {
		if($val['jailed']) {
			$jailed++;
			continue;
		}
		switch($val['status']) {
			case 'BOND_STATUS_BONDED':
				$bonded++;
				break;
			case 'BOND_STATUS_UNBONDED':
				$unbonded++;
				break;
			default:
				continue 2;
		}
	}
	$qrystr = 'INSERT INTO `blocks` (`block`, `bonded_validators`, `unbonded_validators`, `jailed_validators`) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE `bonded_validators` = ?, `unbonded_validators` = ?, `jailed_validators` = ?';
	$db->query($qrystr, $block, $bonded, $unbonded, $jailed, $bonded, $unbonded, $jailed);
}

if(isset($argv[1])) {
	$start = $argv[1];
} else {
	$start = get_last_block_of('validators') + 1;
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


