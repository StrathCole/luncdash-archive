<?php

namespace LUNCDash\Modules\CLI;

use Framework\Core\Database;
use Framework\Core\Date\DateTime;
use Framework\Core\Log;
use Framework\Core\Modules\Base as ModuleBase;
use Framework\Ext\DataCache;
use Framework\Ext\Net;
use Framework\Ext\Net\Request;
use LUNCDash\Lib\Bech32;
use LUNCDash\Lib\Chain;
use LUNCDash\Lib\Wallets;

class ValidatorsModule extends ModuleBase {
	private static function getLatestBlock() {
		$result = Net\Request::requestGET('http://localhost:1317/cosmos/base/tendermint/v1beta1/blocks/latest');
        $result = json_decode($result->getBody(), true);
        if(!isset($result['block']['header']['height'])) {
			return null;
        }

        return $result['block']['header']['height'];
	}

	public static function onUpdateValidators() {
		if(!Net\Info::isCLI(true)) {
			die();
		}

		$result = Request::requestGET('http://localhost:1317/cosmos/staking/v1beta1/validators?pagination.limit=5000');
		$validators = json_decode($result->getBody(), true);
		$validators = $validators['validators'];
		
		foreach($validators as $val) {
			$address = $val['operator_address'];
			$dec = Bech32::decode($address);
			$operator = Bech32::encode('terra', $dec[1]);
			$name = $val['description']['moniker'];

			Database::query('INSERT INTO `validators` (`address`, `operator_address`, `name`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `name` = ?', $address, $operator, $name, $name);
		}
		exit;
	}

	public static function onUpdateDelegations() {
		exit;
	}

	public static function onUpdateVotes() {
		if(!Net\Info::isCLI(true)) {
			die();
		}

		$result = Request::requestGET('https://assets.terra.money/station/proposals.json');
		$whitelisted_proposals = json_decode($result->getBody(), true);
		
		$result = Request::requestGET('http://localhost:1317/cosmos/staking/v1beta1/validators?pagination.limit=5000');
		$validators = json_decode($result->getBody(), true);
		$validators = $validators['validators'];
		
		$val_addresses = [];

		foreach($validators as $val) {
			$address = $val['operator_address'];
			$dec = Bech32::decode($address);
			$operator = Bech32::encode('terra', $dec[1]);
			$name = $val['description']['moniker'];
			$val_addresses[] = $operator;

			Database::query('INSERT INTO `validators` (`address`, `operator_address`, `name`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `name` = ?', $address, $operator, $name, $name);
		}

		$result = Request::requestGET('http://localhost:1317/cosmos/gov/v1beta1/proposals?pagination.limit=5000&pagination.reverse=true');
		$props = json_decode($result->getBody(), true);
		$props = $props['proposals'];
		
		foreach($props as $prop) {
			$status = '';
			switch($prop['status']) {
				case 'PROPOSAL_STATUS_PASSED':
					$status = 'passed';
					break;
				case 'PROPOSAL_STATUS_REJECTED':
					$status = 'rejected';
					break;
				case 'PROPOSAL_STATUS_VOTING_PERIOD':
					$status = 'voting';
					break;
				case 'PROPOSAL_STATUS_DEPOSIT_PERIOD':
					$status = 'deposit';
					break;
			}
	
			$start_time = $prop['voting_start_time'];
			if($start_time === '0001-01-01T00:00:00Z') {
				$start_time = null;
			} else {
				$start_time = DateTime::getInstance($prop['voting_start_time'])->toSQL();
			}

			$end_time = $prop['voting_end_time'];
			if($end_time === '0001-01-01T00:00:00Z') {
				$end_time = null;
			} else {
				$end_time = DateTime::getInstance($prop['voting_end_time'])->toSQL();
			}

			$completed = false;
			$check = Database::queryOneElement('SELECT `status` FROM `proposals` WHERE `id` = ?', $prop['proposal_id']);
			if($check !== 'voting') {
				$completed = true;
			}

			$whitelisted = in_array($prop['proposal_id'], $whitelisted_proposals) ? 1 : 0;

			Database::query('INSERT INTO `proposals` (`id`, `title`, `description`, `status`, `start_time`, `end_time`, `whitelisted`) VALUES (?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `status` = ?, `start_time` = ?, `end_time` = ?, `whitelisted` = ?', $prop['proposal_id'], $prop['content']['title'], $prop['content']['description'], $status, $start_time, $end_time, $whitelisted, $status, $start_time, $end_time, $whitelisted);


			if($completed) {
				continue;
			}

			if($status === 'deposit') {
				continue;
			} elseif($status !== 'voting') {
				$block = Database::queryOneElement('SELECT `block` FROM `blocks` WHERE `time` < ? ORDER BY `block` DESC LIMIT 0,1', $end_time);
				$result = Request::requestGET('http://localhost:1317/cosmos/gov/v1beta1/proposals/' . $prop['proposal_id'] . '/votes?pagination.limit=5000',  ['x-cosmos-block-height' => $block]);
			} else {
				$result = Request::requestGET('http://localhost:1317/cosmos/gov/v1beta1/proposals/' . $prop['proposal_id'] . '/votes?pagination.limit=5000');
			}

			print 'Proposal on ' . $prop['proposal_id'] . "\n";

			$votes = json_decode($result->getBody(), true);
			$votes = $votes['votes'];
			foreach($votes as $vote) {
				if(!in_array($vote['voter'], $val_addresses, true)) {
					continue;
				}
				$vote_enum = '';
				switch($vote['option']) {
					case 'VOTE_OPTION_YES':
						$vote_enum = 'yes';
						break;
					case 'VOTE_OPTION_NO':
						$vote_enum = 'no';
						break;
					case 'VOTE_OPTION_NO_WITH_VETO':
						$vote_enum = 'veto';
						break;
					case 'VOTE_OPTION_ABSTAIN':
						$vote_enum = 'abstain';
						break;
				}

				Database::query('INSERT INTO `votes` (`proposal_id`, `validator`, `vote`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `vote` = ?', $prop['proposal_id'], $vote['voter'], $vote_enum, $vote_enum);
			}

		}

		exit;
	}
}
