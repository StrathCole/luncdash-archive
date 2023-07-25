<?php

$db = new pdb('luncdash', 'localhost', 'luncdash', 'luncdashdbpassword');


function get($url, $add_headers = []) {
	$ch = curl_init();

	// set url
	curl_setopt($ch, CURLOPT_URL, $url);

	//return the transfer as a string
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	$headers = [
		'Accept: application/json'
	];
	foreach($add_headers as $key => $val) {
		$headers[] = $key . ': ' . $val;
	}
	curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	// $output contains the output string
	$output = curl_exec($ch);

	// close curl resource to free up
	// system resources
	curl_close($ch);

	return $output;
}

function post($url, $data) {

	$ch = curl_init();

	$headers = [
		"Accept: application/json",
		"Content-type: application/json"
	];

	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

	// Receive server response ...
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	$server_output = curl_exec($ch);

	curl_close($ch);

	return $server_output;
}

function get_latest_block() {
	$result = get('http://127.0.0.1:1317/blocks/latest');
	$result = json_decode($result, true);
	if(!isset($result['block']['header']['height'])) {
		var_dump($result);
		die('ERROR GETTING BLOCK!' . "\n");
	}

	$block = $result['block']['header']['height'];
	print "Latest block is $block\n";

	return $block;
}

/**
 * @global pdb $db
 * @param type $type
 * @param type $key
 */
function get_last_block_of($type, $key = null) {
	global $db;

	$value = null;

	switch($type) {
		case 'balance':
			$qrystr = 'SELECT MAX(`block`) as `max` FROM `balance` WHERE `wallet` = ?';
			$value = $db->query_one($qrystr, $key);
			break;
		case 'pool':
			$qrystr = 'SELECT MAX(`block`) as `max` FROM `blocks` WHERE `pool_uluna` != 0';
			$value = $db->query_one($qrystr, $key);
			break;
		case 'bonded':
			$qrystr = 'SELECT MAX(`block`) as `max` FROM `blocks` WHERE `bonded_uluna` != 0';
			$value = $db->query_one($qrystr, $key);
			break;
		case 'validators':
			$qrystr = 'SELECT MAX(`block`) as `max` FROM `blocks` WHERE `bonded_validators` != 0';
			$value = $db->query_one($qrystr, $key);
			break;
		case 'supply':
			$qrystr = 'SELECT MAX(`block`) as `max` FROM `blocks` WHERE `total_supply_uluna` != 0';
			$value = $db->query_one($qrystr, $key);
			break;
		case 'price':
			$qrystr = 'SELECT MAX(`block`) as `max` FROM `blocks` WHERE `price_uluna` IS NOT NULL';
			$value = $db->query_one($qrystr, $key);
			break;
		case 'tx':
			$qrystr = 'SELECT MAX(`block`) as `max` FROM `tx` WHERE 1';
			$value = $db->query_one($qrystr, $key);
			break;
		case 'tx_new':
			$qrystr = 'SELECT MAX(`block`) as `max` FROM `tx_new` WHERE 1';
			$value = $db->query_one($qrystr, $key);
			break;
		case 'wallet':
			$qrystr = 'SELECT MAX(`block`) as `max` FROM `wallet` WHERE 1';
			$value = $db->query_one($qrystr, $key);
			break;
	}

	if(!$value || !$value['max']) {
		die("ERROR last block of $type\n");
	}

	$value = $value['max'];

	print "Last block for $type " . ($key ? ' - ' . $key : '') . " is $value\n";

	return $value;
}
