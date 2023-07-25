<?php

namespace LUNCDash\Lib;

use Framework\Core\Date;
use Framework\Core\Numbers;
use Framework\Core\Date\DateTime;

class Votings {
	public static function getStatus(string $status) : string {
		switch($status) {
			case 'passed':
				return 'Passed';
			case 'denied':
				return 'Denied';
			case 'failed':
				return 'Failed';
			default:
				return 'Active';
		}
	}

	public static function getStatusClass(string $status) : string {
		switch($status) {
			case 'passed':
				return 'bg-success';
			case 'denied':
				return 'bg-danger';
			case 'failed':
				return 'bg-danger';
			default:
				return 'bg-primary';
		}
	}

	public static function getOptionClass(string $option) : string {
		switch($option) {
			case 'yes':
				return 'success';
			case 'no':
				return 'danger';
			case 'abstain':
				return 'primary';
			default:
				return 'light';
		}
	}

	public static function getOptionText(string $option) : string {
		switch($option) {
			case 'yes':
				return 'Yes';
			case 'no':
				return 'No';
			case 'abstain':
				return 'Abstain';
			default:
				return $option;
		}
	}

	public static function getType(string $type) : string {
		if($type === 'rules') {
			return 'Rule change proposal';
		}
		return ucfirst($type) . ' proposal';
	}

	public static function getRemaining(string $deadline) : string {
		$ends = DateTime::getInstance($deadline);
		$now = DateTime::getInstance();
		$diff = Date\Calc::getDateDiff($now, $ends);

		$remaining = ($diff->d > 0 ? $diff->d . ' day(s), ' : '') . Numbers\Format::prependZeros($diff->h) . ':' . Numbers\Format::prependZeros($diff->i) . 'h';

		if($diff->invert) {
			$return = 'Ended ' . $remaining . ' ago';
		} else {
			$return = 'Ends in ' . $remaining;
		}

		return $return;
	}

	public static function getSubmitted(string $timestamp) : string {
		$ends = DateTime::getInstance($timestamp);
		$now = DateTime::getInstance();
		$diff = Date\Calc::getDateDiff($now, $ends);

		$remaining = ($diff->d > 0 ? $diff->d . ' day(s), ' : '') . Numbers\Format::prependZeros($diff->h) . ':' . Numbers\Format::prependZeros($diff->i) . 'h';

		$return = 'Submitted ' . $remaining . ' ago';
		return $return;
	}
}
