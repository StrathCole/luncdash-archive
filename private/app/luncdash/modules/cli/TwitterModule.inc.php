<?php

namespace LUNCDash\Modules\CLI;

use Framework\Core\Modules\Base as ModuleBase;
use Framework\Ext\DataCache;
use Framework\Ext\Net;

class TwitterModule extends ModuleBase {
	const API_KEY = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
	const API_SECRET = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';

	private static $BEARER_TOKEN = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';

	private static function getBaerer() : string {
		$parameters = [
			'grant_type' => 'client_credentials',
		];

		$headers = [
			'Authorization' => 'Basic ' . base64_encode(self::API_KEY . ':' . self::API_SECRET),
		];

		$data = Net\Request::requestGET('https://api.twitter.com/oauth2/token?grant_type=client_credentials', $headers);
		$json = json_decode($data->getBody(), true);
		return $json['access_token'];
	}

	private static function getTweets(int $id, int $number = 5) {

		$headers = array(
			'Authorization' => 'Bearer ' . self::$BEARER_TOKEN
		);

		$url = 'https://api.twitter.com/2/users/' . $id . '/tweets?max_results=' . intval($number) . '&tweet.fields=author_id,created_at,id,text,entities';
		$data = Net\Request::requestGET($url, $headers);
		$json = json_decode($data->getBody(), true);

		return $json;
	}

	private static function getUserId(string $handle, bool $force_refresh = false) : ?int {
		$headers = array(
			'Authorization' => 'Bearer ' . self::$BEARER_TOKEN
		);

		$id = null;
		if(!$force_refresh) {
			$id = DataCache::get('twitter:id:' . $handle);
		}

		if(!$id) {
			$url = 'https://api.twitter.com/2/users/by/username/' . urlencode($handle);
			$data = Net\Request::requestGET($url, $headers);
			$json = json_decode($data->getBody(), true);
			if(!isset($json['data']['id'])) {
				return null;
			}
			$id = (int)($json['data']['id']);
			DataCache::set('twitter:id:' . $handle, $id, false, true, 2880);
		}

		return $id;
	}

	private static function getUserHandle(int $id, bool $force_refresh = false) : ?string {
		$headers = array(
			'Authorization' => 'Bearer ' . self::$BEARER_TOKEN
		);

		$handle = null;
		if(!$force_refresh) {
			$handle = DataCache::get('twitter:handle:' . $id);
		}

		if(!$handle) {
			$url = 'https://api.twitter.com/2/users/' . intval($id);
			$data = Net\Request::requestGET($url, $headers);
			$json = json_decode($data->getBody(), true);
			if(!isset($json['data']['username'])) {
				return null;
			}
			$handle = $json['data']['username'];
			DataCache::set('twitter:handle:' . $id, $handle, false, true, 2880);
		}

		return $handle;
	}

	public static function onGetFeeds() {
		if(!Net\Info::isCLI(true)) {
			die();
		}

		$handles = [
			'ColeStrathclyde',
			//'HappyCatKripto',
			'MetaGloriaNFT',
			'LUNCLetItBurn',
			//'LunaticsToken',
			//'savethemoon_io',
			'VegasMorph',
			'deploystarship',
			'LunaVShape',
			'edk208',
			'miata_io',
			'raider7019'
		];

		$tweetobj = \FrameworkModule\DatabaseManager\DatabaseObject::getInstance('tweets');

		foreach($handles as $handle) {
			$id = self::getUserId($handle);
			$tweets = self::getTweets($id);
			foreach($tweets['data'] as $tweet) {
				$date = \Framework\Core\Date\DateTime::getInstance($tweet['created_at'])->toSQL();

				if(!isset($tweet['entities'])) {
					$tweet['entities'] = [];
				}

				$tweetobj->setId($tweet['id'])
					->setHandle(self::getUserHandle($tweet['author_id']))
					->setAuthorId($tweet['author_id'])
					->setTweet($tweet['text'])
					->setTweetTime($date)
					->setEntities($tweet['entities'])
					->addToDatabase(\FrameworkModule\DatabaseManager\DatabaseObject::INSERT_IGNORE);
			}
		}

		exit;
	}
}
