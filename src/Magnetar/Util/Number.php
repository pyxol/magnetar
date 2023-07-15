<?php
	declare(strict_types=1);
	
	namespace Magnetar\Util;
	
	class Number {
		/**
		 * Format a number to the shortened method. Eg 123456 => 123.4k
		 * @param int $num Number to shorten
		 * @return string
		 */
		public static function format_number_shorten(int $num): string {
			if($num < 1000) {
				return (string)$num;
			}
			
			$x = round($num);
			$x_number_format = number_format($x);
			$x_array = explode(',', $x_number_format);
			$x_parts = ['k', 'm', 'b', 't'];
			$x_count_parts = count($x_array) - 1;
			$x_display = $x;
			$x_display = $x_array[0] . ((0 !== (int)$x_array[1][0])?'.'. $x_array[1][0]:'');
			$x_display .= $x_parts[ ($x_count_parts - 1) ];
			
			return $x_display;
		}
		
		/**
		 * Pick the same 'random' number range every time using a unique identifier as a seed to PHP's random number generator
		 * @param int $number_from Starting number
		 * @param int $number_to Ending number
		 * @param int|string $seed Seed value
		 * @return mixed
		 */
		public static function pickNumberBySeed(
			int $number_from,
			int $number_to,
			int|string $seed
		): mixed {
			if($number_from == $number_to) {
				return $number_from;
			}
			
			$min_number = min($number_from, $number_to);
			$max_number = max($number_from, $number_to);
			
			if(!is_numeric($seed)) {
				$seed = preg_replace("#[^0-9]+#si", '', md5($seed));
			}
			
			mt_srand((int)$seed);
			
			$selected_value = mt_rand($number_from, $number_to);
			
			mt_srand((int)microtime(true));   // attempt to re-randomize the internal seed
			
			return $selected_value;
		}
	}