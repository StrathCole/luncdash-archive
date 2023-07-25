<?php

use Mpdf\Tag\P;

require "db.inc.php";
require "functions.inc.php";

$inscnt = 0;

function getSends($block, ?string $only = null) {
	global $db, $inscnt;

	$result = get('http://localhost:1317/cosmos/base/tendermint/v1beta1/blocks/' . $block);

	$json = json_decode($result, true);
	if(!isset($json['block']['data']['txs'])) {
		die("ERR\n");
	}

	$time = null;
	if(isset($json['block']['header']['time'])) {
		$time = $json['block']['header']['time'];
		$time = substr($time, 0, 26);
		try {
			$dt = new DateTime($time);
			$time = $dt->format('Y-m-d H:i:s');
			unset($dt);
			$qrystr = 'INSERT INTO `blocks` (`block`, `time`, `date`) VALUES (?, ?, DATE(?)) ON DUPLICATE KEY UPDATE `time` = ?, `date` = DATE(?)';
			$db->query($qrystr, $block, $time, $time, $time, $time);
		} catch(\Exception $e) {
			print "\nUnparsable time $time on block $block\n";
		}
	}

	print "\rBlock $block - $time";

	$tx = $json['block']['data']['txs'];
	foreach($tx as $trans) {
		$tx_hash = strtoupper(hash('sha256', base64_decode($trans)));
		print "\rBlock $block - $time: $tx_hash";

		/** SIMPLE DATA  */
		// first decode the tx to check if we need it at all.
		$lessdata = post('http://localhost:1317/txs/decode', json_encode([
			'tx' => $trans]));
		$lessdatatmp = json_decode($lessdata, true);
		if(!isset($lessdatatmp['result'])) {
			$lessdata = shell_exec('terrad tx decode ' . escapeshellarg($trans));
			$lessdatatmp = json_decode($lessdata, true);

			if(isset($lessdatatmp['body']['messages'][0]['@type']) && $lessdatatmp['body']['messages'][0]['@type'] === '/ibc.core.client.v1.MsgUpdateClient') {
				//print "SKIP update info\n";
				continue;
			}
			if(!isset($lessdatatmp['result'])) {
				//print $txdata;

				continue;
			}
		}

		$lessdata = $lessdatatmp['result'];
		if(!isset($lessdata['msg'])) {
			continue;
		}

		$ignore = true;
		foreach($lessdata['msg'] as $msg) {
			if(!in_array($msg['type'], ['oracle/MsgAggregateExchangeRateVote', 'oracle/MsgAggregateExchangeRatePrevote'], true)) {
				$ignore = false;
			}
		}

		if($only === 'contract') {
			$ignore = true;
			foreach($lessdata['msg'] as $msg) {
				if(in_array($msg['type'], ['wasm/MsgExecuteContract', 'wasm/MsgInstantiateContract'], true)) {
					$ignore = false;
				}
			}
		} elseif($only === 'txfees') {
			// only add fee to the tx
			if(isset($lessdata['fee']['amount'])) {
				foreach($lessdata['fee']['amount'] as $fee) {
					if(!isset($fee['denom']) || !isset($fee['amount'])) {
						print "\nERR in fee.\n";
						continue;
					}
					$inscnt++;
					if($inscnt % 1000 === 1) {
						if($inscnt > 1) {
							$db->query('COMMIT');
						}
						$db->query('START TRANSACTION');
					}
					$db->query('INSERT INTO `tx_fees_new` (`hash`, `block`, `fees`, `denom`) VALUES (?, ?, ? / 1000000, ?) ON DUPLICATE KEY UPDATE `fees` = ? / 1000000', $tx_hash, $block, $fee['amount'], $fee['denom'], $fee['amount']);
				}
			}

			continue;
		}

		if($ignore) {
			continue;
		}

		/** END SIMPLE DATA */

		// get status of transaction
		$tempdata = get('http://localhost:1317/cosmos/tx/v1beta1/txs/' . $tx_hash);
		$tempdata = json_decode($tempdata, true);
		if(!isset($tempdata['tx_response'])) {
			var_dump($tempdata);//exit;
			continue;
		}

		$failed = false;
		$code = 0;
		if(isset($tempdata['tx_response']['code']) && $tempdata['tx_response']['code'] != 0) {
			$failed = true;
			$code = (int)($tempdata['tx_response']['code']);
		}
		
		$txdata = $tempdata['tx']['body'];
		if(!isset($txdata['messages'])) {
			var_dump($txdata);//exit;
			continue;
		}

		$base_uluna = 0;
		$base_uusd = 0;
		foreach($txdata['messages'] as $msg) {
			if($msg['@type'] === '/cosmos.bank.v1beta1.MsgSend' && ($only === null || $only === 'send')) {
				foreach($msg['amount'] as $coin) {
					switch($coin['denom']) {
						case 'uluna':
							$base_uluna += $coin['amount'];
							break;
						case 'uusd':
							$base_uusd += $coin['amount'];
							break;
					}
					$qrystr = 'INSERT INTO `tx_new` (`hash`, `sender`, `recipient`, `contract`, `amount`, `denom`, `memo`, `block`, `tx_time`, `tx_date`, `failed`, `code`) VALUES (?, ?, ?, ?, ? / 1000000, ?, ?, ?, ?, DATE(?), ?, ?)';
					$db->query($qrystr, $tx_hash, $msg['from_address'], $msg['to_address'], null,$coin['amount'], substr($coin['denom'], 0, 7), substr($txdata['memo'], 0, 150), $block, $time, $time, $failed ? 1 : 0, $code);
				}
			} elseif(($msg['@type'] === '/cosmwasm.wasm.v1.MsgExecuteContract' || $msg['@type'] === '/cosmwasm.wasm.v1.MsgInstantiateContract') && ($only === null || $only === 'contract')) {
				$amount = 0;
				$denoms = [];
				if(isset($msg['funds']) && !empty($msg['funds'])) {
					foreach($msg['funds'] as $coin) {
						if(!in_array($coin['denom'], ['uusd', 'uluna'], true)) {
							continue;
						}
						$denoms[$coin['denom']] = $coin['amount'];
					}
				}
				if(empty($denoms)) {
					$denoms['uluna'] = 0;
				}
				if(!isset($msg['contract'])) {
					print "\nMissing contract\n";
					continue;
				}
				
				foreach($denoms as $denom => $amount) {
					switch($denom) {
						case 'uluna':
							$base_uluna += $amount;
							break;
						case 'uusd':
							$base_uusd += $amount;
							break;
					}

					$qrystr = 'INSERT INTO `tx_new` (`hash`, `sender`, `recipient`, `contract`, `amount`, `denom`, `memo`, `block`, `tx_time`, `tx_date`, `failed`, `code`) VALUES (?, ?, ?, ?, ? / 1000000, ?, ?, ?, ?, DATE(?), ?, ?)';
					$db->query($qrystr, $tx_hash, $msg['sender'], '', $msg['contract'], $amount, substr($denom, 0, 7), (isset($txdata['memo']) ? substr($txdata['memo'], 0, 150) : ''), $block, $time, $time, $failed ? 1 : 0, $code);
				}
			}
		}

		if($only === null && isset($tempdata['tx']['auth_info']['fee']['amount'])) {
			$uluna = 0;
			$uusd = 0;
			$tax_uluna = 0;
			$tax_uusd = 0;
			foreach($tempdata['tx']['auth_info']['fee']['amount'] as $fee) {
				$db->query('INSERT INTO `tx_fees_new` (`hash`, `block`, `fees`, `denom`) VALUES (?, ?, ? / 1000000, ?) ON DUPLICATE KEY UPDATE `fees` = ? / 1000000', $tx_hash, $block, $fee['amount'], $fee['denom'], $fee['amount']);
				
				if($fee['denom'] === 'uluna') {
					$uluna += $fee['amount'];
				} else {
					$uusd += $fee['amount'];
				}
			}

			if($base_uluna > 0 && $uluna >= floor($base_uluna * 0.012)) {
				$tax_uluna = $base_uluna * 0.012;
			}
			if($base_uusd > 0 && $uusd >= floor($base_uusd * 0.012)) {
				$tax_uusd = $base_uusd * 0.012;
			}

			$db->query('INSERT INTO `tx_fees` (`block`, `fees_uluna`, `tax_uluna`, `fee_uusd`, `tax_uusd`) VALUES (?, ? / 1000000, ? / 1000000, ? / 1000000, ? / 1000000) ON DUPLICATE KEY UPDATE `fees_uluna` = `fees_uluna` + (? / 1000000), `tax_uluna` = `tax_uluna` + (? / 1000000), `fee_uusd` = `fee_uusd` + (? / 1000000), `tax_uusd` = `tax_uusd` + (? / 1000000)', $block, $uluna, $tax_uluna, $uusd, $tax_uusd, $uluna, $tax_uluna, $uusd, $tax_uusd);
		}
	}
	return;
}

if(isset($argv[1])) {
	$start = $argv[1];
} else {
	$start = get_last_block_of('tx_new');
	if(!$start) {
		$start = 4724001;
	} else {
		$start += 1;
	}
}
if(isset($argv[2])) {
	$end = $argv[2];
} else {
	$end = get_latest_block();
}

$only = null;
if(isset($argv[3])) {
	$only = $argv[3];
	if($only !== 'contract' && $only !== 'send' && $only !== 'txfees') {
		die("Invalid only\n");
	}
}

print "\n";
if(isset($argv[4]) && $argv[4] === 'DESC') {
	for($b = $end; $b >= $start; $b--) {
		getSends($b, $only);
	}
} else {
	for($b = $start; $b <= $end; $b++) {
		getSends($b, $only);
	}
}
print "\n";

print "$inscnt queries\n";
if($only === 'txfees') {
	$db->query('COMMIT');
}
/*
if($only === null) {
	$circulating_uluna = get('https://columbus-fcd.terra.dev/v1/circulatingsupply/uluna');
	$circulating_ust = get('https://columbus-fcd.terra.dev/v1/circulatingsupply/uusd');
	$qrystr = 'UPDATE `blocks` SET `circulating_uluna` = ?, `circulating_uusd` = ? WHERE `block` >= ? AND `block` % 100 = 0 ORDER BY `block` DESC LIMIT 1';
	$db->query($qrystr, intval($circulating_uluna) / 1000000, intval($circulating_ust) / 1000000, $start - 100);
}*/