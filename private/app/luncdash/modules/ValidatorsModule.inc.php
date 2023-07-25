<?php

namespace LUNCDash\Modules;

use Framework\Core\Database;
use Framework\Core\Date;
use Framework\Core\Date\Format;
use Framework\Core\Modules\Base as ModuleBase;
use Framework\Core\Template;
use Framework\Ext\DataCache;
use Framework\Ext\l10n\Locale;
use Framework\Ext\Net\Request;

class ValidatorsModule extends ModuleBase {
	private function sortValidators($a, $b) {
		$a['delegator_shares'] = (int)$a['delegator_shares'];
		$b['delegator_shares'] = (int)$b['delegator_shares'];
		if($a['delegator_shares'] > $b['delegator_shares']) {
			return -1;
		} elseif($a['delegator_shares'] < $b['delegator_shares']) {
			return 1;
		} else {
			return 0;
		}
	}

	/**
	 * @param string $action
	 */
	public static function onShow() {

		Locale::set('en');

		//Template::enableCache(600, false, false, false, [], '*');
		Template::load('lists/validators');

		// staking pool!
		$bonded = Database::queryOneElement('SELECT `bonded_uluna` FROM `blocks` WHERE `bonded_uluna` > 0 ORDER BY `block` DESC LIMIT 0,1');

		$result = Request::requestGET('http://localhost:1317/terra/oracle/v1beta1/params');
		$params = json_decode($result->getBody(), true);
		$params = $params['params'];
		
		$result = Request::requestGET('http://localhost:1317/cosmos/slashing/v1beta1/params');
		$slash_params = json_decode($result->getBody(), true);
		$slash_params = $params['params'];

		$result = Request::requestGET('http://localhost:1317/cosmos/staking/v1beta1/validators?pagination.limit=1000&status=BOND_STATUS_BONDED');
		$validators = json_decode($result->getBody(), true);
		$validators = $validators['validators'];
		usort($validators, 'self::sortValidators');
		
		$list = [];
		$n = 0;
		foreach($validators as $validator) {
			$n++;
			$validator['tokens'] = round((int)($validator['tokens']) / 1000000, 6);
			$validator['delegator_shares'] = round((int)($validator['delegator_shares']) / 1000000, 6);
			$validator['commission']['commission_rates']['rate'] = round($validator['commission']['commission_rates']['rate'] * 100);
			$validator['commission']['commission_rates']['max_rate'] = round($validator['commission']['commission_rates']['max_rate'] * 100);
			$validator['commission']['commission_rates']['max_change_rate'] = round($validator['commission']['commission_rates']['max_change_rate'] * 100);
			$validator['commission']['update_time'] = Date\DateTime::getInstance($validator['commission']['update_time'])->toSQL(false);
			$validator['voting_power'] = round(100 * ($validator['delegator_shares'] / $bonded), 2);
			$fair_rate = round($validator['voting_power'] >= 1 ? $validator['voting_power'] * 0.5 : 0, 1);

			$missed_votes = DataCache::get('val:votes:missed:' . $validator['operator_address']);
			if($missed_votes === null) {
				$result = Request::requestGET('http://localhost:1317/terra/oracle/v1beta1/validators/' . $validator['operator_address'] . '/miss');
				$json = json_decode($result->getBody(), true);
				$missed_votes = (isset($json['miss_counter']) ? $json['miss_counter'] : 0);
				DataCache::set('val:votes:missed:' . $validator['operator_address'], $missed_votes, false, true, 30);
			}
			// 432000 blocks = around 30 days
			// vote is every 5 blocks
			$blocks = $params['slash_window'];
			$votes = $blocks; // $params['vote_period'];
			$uptime = 100 * (($votes - $missed_votes) / $votes);

			$blocks_slash = $slash_params['signed_blocks_window'];
			
			$share = $validator['delegator_shares'];
			$unit = '';
			while($share > 900) {
				$share = $share / 1000;
				switch($unit) {
					case '':
						$unit = 'K';
						break;
					case 'K':
						$unit = 'M';
						break;
					case 'M':
						$unit = 'B';
						break;
					case 'B':
						$unit = 'T';
						break;
				}
			}

			$list[] = [
				'num' => $n,
				'name' => $validator['description']['moniker'],
				'address' => $validator['operator_address'],
				'voting_power' => $validator['voting_power'],
				'commission' => $validator['commission']['commission_rates']['rate'],
				'uptime' => $uptime,
				'missed' => $missed_votes,
				'votes' => $votes,
				'fair_commission' => ($validator['commission']['commission_rates']['rate'] >= $fair_rate ? true : false),
				'low_commission' => ($validator['commission']['commission_rates']['rate'] >= $fair_rate * 0.5 ? false : true),
				'high_commission' => ($validator['commission']['commission_rates']['rate'] > 20 ? true : false),
				'delegation_share' => $share,
				'delegation_share_unit' => $unit,
				'fair_amount' => $fair_rate,
				'jailed' => ($validator['jailed'] ? true : false)
			];
		}

		//terrad query txs --events "message.sender=terra120ppepaj2lh5vreadx42wnjjznh55vvktwj679&message.action=/cosmos.gov.v1beta1.MsgVote"

		Template::assign('validators', $list);

		Template::load('main');
		//Template::assign('META_DESC', HTML::toEntities($meta_data));
		Template::assign('PRE_SCRIPT', '$(\'#validators-list\').DataTable();');
		Template::add(Template::get('lists/validators'));
		Template::show();

		exit;
	}

	public static function onValidators() {

		Template::load('charts/validators');

		$bonded = Database::queryOneElement('SELECT `bonded_validators` FROM `blocks` WHERE `bonded_validators` > 0 ORDER BY `block` DESC LIMIT 0,1');
		$unbonded = Database::queryOneElement('SELECT `unbonded_validators` FROM `blocks` WHERE `bonded_validators` > 0 ORDER BY `block` DESC LIMIT 0,1');
		$jailed = Database::queryOneElement('SELECT `jailed_validators` FROM `blocks` WHERE `bonded_validators` > 0 ORDER BY `block` DESC LIMIT 0,1');

		$datafile = 'data_validators.js';

		$file = sys_get_temp_dir() . '/' . $datafile;
		if(file_exists($file) && filesize($file) > 10) {
			$mtime = filemtime($file);
		} else {
			$mtime = 0;
		}
		$lifetime = (60 * 10);
		if($mtime < time() - $lifetime) {
			$now = Format::toSQL(Date::getTime());

			$data = [
				'last_update' => $now,
				'data' => [
					'labels' => [],
					'datasets' => [
						[
							'label' => 'Bonded',
							'data' => [],
							'borderColor' => '#ffa600'
						],
						[
							'label' => 'Unbonded',
							'data' => [],
							'borderColor' => '#00ecff',
						],
						[
							'label' => 'Jailed',
							'data' => [],
							'borderColor' => '#ee00ac',
						]
					]
				]
			];

			$qrystr = 'SELECT bl.date, bl2.bonded_validators as `bonded`, bl2.unbonded_validators as `unbonded`, bl2.jailed_validators as `jailed` FROM (SELECT DATE(bl.time) as `date`, MAX(bl.block) as `latest` FROM `blocks` as bl WHERE bl.bonded_validators > 0 AND bl.time IS
			NOT NULL GROUP BY DATE(bl.time)) as bl INNER JOIN blocks as bl2 ON (bl2.block = bl.latest) WHERE bl.date >= ?';
			$result = Database::query($qrystr, '2022-01-01');
			while(($cur = $result->get())) {
				$data['data']['labels'][] = $cur['date'];
				$data['data']['datasets'][0]['data'][] = $cur['bonded'];
				$data['data']['datasets'][1]['data'][] = $cur['unbonded'];
				$data['data']['datasets'][2]['data'][] = $cur['jailed'];
			}
			$result->free();

			file_put_contents($file, json_encode($data));
		}

		Template::assign('bonded', number_format($bonded, 0, '.', ','));
		Template::assign('unbonded', number_format($unbonded, 0, '.', ','));
		Template::assign('jailed', number_format($jailed, 0, '.', ','));

		Template::load('main');
		//Template::assign('META_DESC', HTML::toEntities($meta_data));
		Template::assign('LOAD_SCRIPT', 'linechart.js');
		Template::assign('PRE_SCRIPT', 'let data = ' . file_get_contents($file) . '; config.data = data.data;');
		Template::add(Template::get('charts/validators'));
		Template::show();

		exit;
	}
}
