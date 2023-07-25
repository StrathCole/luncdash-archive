<?php

namespace LUNCDash\Modules\CLI;

use Framework\Core\Database;
use Framework\Core\Date\DateTime;
use Framework\Core\Log;
use Framework\Core\Modules\Base as ModuleBase;
use Framework\Ext\DataCache;
use Framework\Ext\Net;
use Framework\Ext\Net\Request;
use LUNCDash\Lib\Chain;

class DelegationsModule extends ModuleBase {
	private static function _sort($a, $b) {
		if($a['added'] > $b['added']) {
			return -1;
		} elseif($a['added'] < $b['added']) {
			return 1;
		} else {
			return 0;
		}
	}
	public static function onCompareCrash() {
		if(!Net\Info::isCLI(true)) {
			die();
		}

		$wallets = [];
		print "\n";
		$wres = Database::query('SELECT `wallet` FROM `wallet` WHERE 1');
		$rows = $wres->rows();
		$i = 0;
		$e = 0;
		while(($cur = $wres->get())) {
			$i++;
			$wallet = $cur['wallet'];

			print "\r[" . $i . '/' . $rows . ' - ' . $e . '] ' . $wallet;

			$response = Net\Request::requestGET('http://localhost:1317/cosmos/staking/v1beta1/delegations/' . $wallet . '?pagination.limit=1000', ['x-cosmos-block-height' => 7614800]);
			$json = json_decode($response->getBody(), true);
			if(!isset($json['delegation_responses'])) {
				continue;
			}

			$post = 0;
			foreach($json['delegation_responses'] as $del) {
				$post += (int)($del['balance']['amount']);
			}
			if($post < 1000000) {
				continue;
			}
			
			$pre = 0;
			$response = Net\Request::requestGET('http://localhost:1317/cosmos/staking/v1beta1/delegations/' . $wallet . '?pagination.limit=1000', ['x-cosmos-block-height' => 7596200]);
			$json = json_decode($response->getBody(), true);
			if(isset($json['delegation_responses'])) {
				foreach($json['delegation_responses'] as $del) {
					$pre += (int)($del['balance']['amount']);
				}
			}

			$added = $post - $pre;
			if(abs($added) < 1000000000000) {
				continue;
			}

			$e++;
			$wallets[$wallet] = [
				'pre' => round($pre / 1000000),
				'post' => round($post / 1000000),
				'added' => round($added / 1000000),
			];
		}
		$wres->free();

		uasort($wallets, 'self::_sort');
		file_put_contents(getcwd() . '/wallets.json', json_encode($wallets, JSON_PRETTY_PRINT));
		print "\n";
		
		exit;
	}

}
