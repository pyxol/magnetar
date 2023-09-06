<?php
	declare(strict_types=1);
	
	namespace Magnetar\Util;
	
	/**
	 * Date utility static class
	 */
	class Date {
		/**
		 * Get the date since a given timestamp
		 * @param int|string $timestamp The timestamp to get the date since. If not a valid timestamp, strtotime() will be used to convert it
		 * @param string|false $append Optional. The string to append to the date
		 * @param string|false $prepend Optional. The string to prepend to the date
		 * @param int|false $relative_timestamp Optional. The timestamp to use as the current time. Defaults to current timestamp. If not a valid timestamp, strtotime() will be used to convert it
		 * @return string|false
		 */
		public static function sinceDate(
			int|string $timestamp,
			string|false $append=" ago",
			string|false $prepend=false,
			int|false $relative_timestamp=false
		): string|false {
			if(!preg_match("#^([0-9]+)$#si", $timestamp)) {
				$timestamp = strtotime($timestamp);
			}
			
			if(empty($timestamp)) {
				return false;
			}
			
			if((false === $relative_timestamp) || is_null($relative_timestamp)) {
				$relative_timestamp = time();
			}
			
			$date_diff = ($relative_timestamp - $timestamp);
			
			// provided timestamp is in the future but not by long
			if(($date_diff <= 0) && ($date_diff >= (-1 * 60 * 5))) {
				return "just now";
			}
			
			$ranges = [
				'millennium'	=> 1 * 60 * 60 * 24 * 365 * 1000,
				'century'		=> 1 * 60 * 60 * 24 * 365 * 100,
				'decade'		=> 1 * 60 * 60 * 24 * 365 * 10,
				'year'			=> 1 * 60 * 60 * 24 * 365,
				'month'			=> 1 * 60 * 60 * 24 * 30,
				'week'			=> 1 * 60 * 60 * 24 * 7,
				'day'			=> 1 * 60 * 60 * 24,
				'hour'			=> 1 * 60 * 60,
				'minute'		=> 1 * 60,
				'second'		=> 1,
			];
			
			foreach($ranges as $name => $seconds) {
				if($date_diff >= $seconds) {
					if($name == "second") {
						return "just now";
					}
					
					$div = floor( @($date_diff / $seconds) );
					return (!empty($prepend)?$prepend:"") . $div ." ". $name . (($div <> 1)?"s":"") . (!empty($append)?$append:"");
				}
			}
			
			return false;
		}
		
		/**
		 * Get the age of a person by their date of birth
		 * @param string|int $start The timestamp of the person's date of birth
		 * @param string|int|false $end Optional. The timestamp of the person's date of death. Defaults to false
		 * @return int
		 */
		public static function getAge(
			string|int $start,
			string|int|false $end=false
		): int {
			if(!is_numeric($start)) {
				// convert to timestamp
				$start = (int)strtotime($start);
			}
			
			if(false === $end) {
				// default to current timestamp
				$end = time();
			} elseif(!is_numeric($end)) {
				// convert to timestamp
				$end = (int)strtotime($end);
			}
			
			// if the month+day of start is later in the year than the month+day of end, then the person has not had their birthday yet
			if(date("md", $start) > date("md", $end)) {
				return ((date("Y", $end) - date("Y", $start)) - 1);
			}
			
			return (date("Y", $end) - date("Y", $start));
		}
	}