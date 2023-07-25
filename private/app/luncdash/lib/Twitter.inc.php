<?php

namespace LUNCDash\Lib;

use Framework\Core\Strings\HTML;

class Twitter {
	const SEARCH_LINK = 'https://twitter.com/search?q=';
	const HASHTAG_LINK = 'https://twitter.com/hashtag/';
	const MENTION_LINK = 'https://twitter.com/';

	private static $replace_offset = 0;

	private static function sortEntities($a, $b) {
		if($a['start'] < $b['start']) {
			return -1;
		} elseif($a['start'] > $b['start']) {
			return 1;
		} else {
			return 0;
		}
	}

	private static function replaceEntity(string $text, array $entity) : string {

		$link = null;
		$show = null;
		switch($entity['type']) {
			case 'hashtags':
				$link = self::HASHTAG_LINK . urlencode($entity['tag']);
				$show = '#' . $entity['tag'];
				break;
			case 'cashtags':
				$show = '$' . $entity['tag'];
				$link = self::SEARCH_LINK . urlencode($show);
				break;
			case 'mentions':
				$link = self::MENTION_LINK . urlencode($entity['username']);
				$show = '@' . $entity['username'];
				break;
			case 'urls':
				$link = (isset($entity['expanded_url']) ? $entity['expanded_url'] : $entity['url']);
				$show = (isset($entity['display_url']) ? $entity['display_url'] : $entity['url']);
				break;
			default:
				return $text;
		}

		$wrap_text = '<a href="' . HTML::toEntities($link) . '" target="_blank" rel="noopener">' . HTML::toEntities($show) . '</a>';

		$wrapped = '';
		if($entity['start'] > 0) {
			$wrapped = mb_substr($text, 0, $entity['start'] + self::$replace_offset);
		}
		$wrapped .= $wrap_text;
		$wrapped .= mb_substr($text, $entity['end'] + self::$replace_offset);

		self::$replace_offset = self::$replace_offset + mb_strlen($wrap_text) - ($entity['end'] - $entity['start']);

		return $wrapped;
	}

	public static function getHTML(string $tweet, ?array $entities = []) : string {
		self::$replace_offset = 0;

		$html = $tweet;

		$types = ['hashtags', 'cashtags', 'mentions', 'urls'];

		$tmp = [];
		if(!is_array($entities)) {
			$entities = [];
		}
		foreach($types as $type) {
			if(!isset($entities[$type])) {
				$entities[$type] = [];
			}

			foreach($entities[$type] as $entity) {
				$entity['type'] = $type;
				$tmp[] = $entity;
			}
		}
		$entities = $tmp;

		usort($entities, 'self::sortEntities');

		foreach($entities as $entity) {
			$html = self::replaceEntity($html, $entity);
		}

		$html = nl2br($html, true);
		return $html;
	}
}
