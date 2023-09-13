<?php
	declare(strict_types=1);
	
	namespace Magnetar\Utilities;
	
	/**
	 * Array utility static class
	 */
	class Arr {
		/**
		 * Pick the same 'random' value from an array every time using a unique identifier as a seed to PHP's random number generator. Returns null if no values are in array
		 * @param array $array Array of values to pick from
		 * @param int|string $seed Seed value
		 * @return mixed
		 */
		public static function pickArrayValueBySeed(
			array $array,
			int|string $seed
		): mixed {
			if(empty($array)) {
				return null;
			}
			
			if(!is_numeric($seed)) {
				$seed = preg_replace("#[^0-9]+#si", '', md5($seed));
			}
			
			mt_srand((int)$seed);
			
			$selected_value = $array[ array_rand($array) ];
			
			//mt_srand(microtime(true));   // attempt to re-randomize the internal seed
			//mt_srand((int)(time() + microtime(true)));   // re-randomize the internal seed
			mt_srand();
			
			return $selected_value;
		}
		
		/**
		 * Shuffle an array randomly the same every time using a unique identifier as a seed to PHP's random number generator. Does not affect the source variable. Returns null if no values are in array
		 * @param array $array Array of values to shuffle
		 * @param int|string $seed Seed value
		 * @return mixed
		 */
		public static function shuffleArrayBySeed(
			array $array,
			int|string $seed
		): mixed {
			if(empty($array)) {
				return null;
			}
			
			if(!is_numeric($seed)) {
				$seed = preg_replace("#[^0-9]+#si", '', md5($seed));
			}
			
			mt_srand((int)$seed);
			
			$values = $array;
			
			shuffle($values);
			
			//mt_srand(microtime(true));   // attempt to re-randomize the internal seed
			//mt_srand((int)(time() + microtime(true)));   // re-randomize the internal seed
			mt_srand();
			
			return $values;
		}
	}