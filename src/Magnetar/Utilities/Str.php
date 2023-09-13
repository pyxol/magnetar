<?php
	declare(strict_types=1);
	
	namespace Magnetar\Utilities;
	
	/**
	 * String utility static class
	 */
	class Str {
		/**
		 * Convert a string to a URL-friendly string
		 * @param string $string The string to convert
		 * @param string $separator Optional. The separator to use. Defaults to "-".
		 * @param bool $lowercase Optional. Whether to lowercase the string. Defaults to true.
		 * @param string $default Optional. The default string to return if the string is empty. Defaults to an empty string.
		 * @return string
		 */
		public static function mkurl(
			string $string,
			string $separator='-',
			bool $lowercase=true,
			string $default=''
		): string {
			if(!empty($lowercase)) {
				$string = strtolower($string);
			}
			
			$string = trim($string);
			$string = str_replace(["'"], '', $string);
			$string = preg_replace(
				"#([^A-Za-z0-9". (('-' !== $separator)?preg_quote($separator, '#'):'') ."])#si",
				$separator,
				$string
			);
			
			$string = preg_replace("#". preg_quote($separator, "#") ."{2,}#si", $separator, $string);
			//$string = preg_replace("#". preg_quote($separator, "#") ."(". preg_quote($separator, "#") ."+)#si", $separator, $string);
			
			if("" === ($string = trim($string, $separator))) {
				return $default;
			}
			
			return $string;
		}
		
		/**
		 * Strip zalgo text from a string
		 * @param string $string The string to strip
		 * @return string
		 * @see https://stackoverflow.com/a/32921891/103337
		 */
		public static function stripZalgoText(string $string=''): string {
			$string = preg_replace("~(?:[\p{M}]{1})([\p{M}])+?~uis", '', $string);
			
			return $string;
		}
		
		/**
		 * Format a phone number
		 * @param int|string $phoneNumber The phone number to format
		 * @return string
		 * @see https://stackoverflow.com/a/14167216/103337
		 */
		public static function formatPhoneNumber(int|string $phoneNumber): string {
			$phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
			
			if(strlen($phoneNumber) > 10) {
				$countryCode = substr($phoneNumber, 0, (strlen($phoneNumber) - 10));
				$areaCode = substr($phoneNumber, -10, 3);
				$nextThree = substr($phoneNumber, -7, 3);
				$lastFour = substr($phoneNumber, -4, 4);
				
				$phoneNumber = '+'. $countryCode .' ('. $areaCode .') '. $nextThree .'-'. $lastFour;
			} elseif(strlen($phoneNumber) == 10) {
				$areaCode = substr($phoneNumber, 0, 3);
				$nextThree = substr($phoneNumber, 3, 3);
				$lastFour = substr($phoneNumber, 6, 4);
				
				$phoneNumber = '('. $areaCode .') '. $nextThree .'-'. $lastFour;
			} elseif(strlen($phoneNumber) == 7) {
				$nextThree = substr($phoneNumber, 0, 3);
				$lastFour = substr($phoneNumber, 3, 4);
				
				$phoneNumber = $nextThree .'-'. $lastFour;
			}
			
			return $phoneNumber;
		}
		
		/**
		 * Convert text to sentences
		 * @license Proprietary
		 * @author Don Wilson <donwilson@gmail.com>
		 * @param string $text The text to convert
		 * @param array $user_encasings An array of encasings to ignore. Encasing are soft word boundaries to ignore (parenthesis, brackets around words, etc)
		 * @return array
		 */
		public static function text_to_sentences(
			string $text,
			array $user_encasings=[]
		): array {
			$text = trim($text);
			
			if(!is_array($user_encasings)) {
				$user_encasings = [];
			}
			
			$hard_replacers = [
				// quotation marks
				"‘" => "\"",
				"’" => "\"",
				"“" => "\"",
				"”" => "\"",
				"‹" => "\"",
				"›" => "\"",
				"«" => "\"",
				"»" => "\"",
				"''" => "\"",
				
				'—' => "-",
				'&emdash;' => "-",
				'&dash;' => "-",
				
				"..." => "&#133;",
				
				"U.S.A." => "U&#046;S&#046;A&#046;",
				"U.S.A" => "U&#046;S&#046;A",
				"U.S." => "U&#046;S&#046;",
				"U.S" => "U&#046;S",
				"B.B." => "B&#046;B&#046;",
				"B.B" => "B&#046;B&#046;",
				"B. B." => "B&#046;B&#046;",
				"B. B" => "B&#046;B&#046;",
				"a.m." => "a&#046;m&#046;",
				"a.m" => "a&#046;m&#046;",
				"p.m." => "a&#046;m&#046;",
				"p.m" => "a&#046;m&#046;",
			];
			
			
			$text = str_ireplace(array_keys($hard_replacers), array_values($hard_replacers), $text);
			
			$encasings = array_merge([
				'(' => ')',
				'[' => ']',
			], $user_encasings);
			
			foreach($encasings as $encasing_open => $encasing_close) {
				$text = preg_replace_callback("#". preg_quote($encasing_open, "#") ."([^". preg_quote($encasing_close, "#") ."]+?)". preg_quote($encasing_close, "#") ."#si", function($matches) {
					return str_replace(".", "&#046;", $matches[0]);
				}, $text);
			}
			
			
			// ignore periods after abbreviations, eg: Dr. or Mrs.
			$text = preg_replace("#([^A-Za-z0-9]+)?(Mr|Mrs|Ms|Jr|Sr|Dr|Miss| c|etc|Rev|Vol|No)\.([\"|\s])#s", "\\1\\2&#046;\\3", $text);
			
			$raw_lines = explode(PHP_EOL, $text);
			
			$sentences = [];
			
			foreach($raw_lines as $raw_line) {
				$raw_line = trim($raw_line);
				
				if(empty($raw_line)) {
					continue;
				}
				
				//$raw_section_texts = preg_split("#([^\.])\.(?:[". preg_quote("\"'])", "#") ."]*)?\s+([A-Z\[\"])#", $raw_line, null, PREG_SPLIT_DELIM_CAPTURE);
				//$raw_section_texts = preg_split("#(\p{L})\.(\P{L})?\s+(\P{L}|\P{Lu})?(\p{Lu})?#si", $raw_line, null, PREG_SPLIT_DELIM_CAPTURE);
				$raw_section_texts = preg_split("/(?<=[.?!])[\]|\)\"\']?\s+/", $raw_line, -1, PREG_SPLIT_NO_EMPTY);
				
				$sentences = array_merge($sentences, $raw_section_texts);
			}
			
			return $sentences;
		}
		
		/**
		 * Format a number of seconds into a time string (eg: 5025 => 1:23:45)
		 * @param int|float $seconds Number of seconds
		 * @param bool $include_ms Include milliseconds in the output
		 * @param int $precision Number of decimal places to include in the milliseconds
		 * @return string
		 */
		public static function formatSecondsIntoTime(
			int|float $seconds,
			bool $include_ms=false,
			int $precision=3
		): string {
			$seconds = ceil($seconds);
			
			$hours = floor($seconds / 3600);
			$mins = floor($seconds / 60 % 60);
			$secs = floor($seconds % 60);
			$milliseconds = ($seconds - floor($seconds));
			
			if(!empty($hours)) {
				return sprintf('%02d:%02d:%02d', $hours, $mins, $secs) . (($include_ms && ($precision > 0))?'.'. substr(preg_replace("#^0\.#si", '', (string)$milliseconds), 0, $precision):"");
			}
			
			return sprintf('%02d:%02d', $mins, $secs) . (($include_ms && ($precision > 0))?'.'. substr(preg_replace("#^0\.#si", '', (string)$milliseconds), 0, $precision):"");
		}
		
		/**
		 * Format a number of seconds into a time string for use with ffmpeg
		 * @param int|float $seconds Number of seconds
		 * @return string
		 * @see https://trac.ffmpeg.org/wiki/Seeking
		 * @see https://ffmpeg.org/ffmpeg-utils.html#time-duration-syntax
		 */
		public static function formatSecondsIntoFFMpegTime(int|float $seconds=0): string {
			$seconds = ceil($seconds);
			
			$hours = floor($seconds / 3600);
			$mins = floor($seconds / 60 % 60);
			$secs = floor($seconds % 60);
			//$milliseconds = ($seconds - floor($seconds));
			
			//return sprintf('%02d:%02d:%02d', $hours, $mins, $secs) . (!empty($milliseconds)?".". substr(preg_replace("#^0\.#si", "", $milliseconds), 0, 3):"");
			return sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
		}
		
		/**
		 * Generate a random string
		 * @param int $length Length of string. If 0 or less, defaults to 12
		 * @return string
		 */
		public static function getRandomCharacters(int $length=12): string {
			if($length <= 0) {
				$length = 12;
			}
			
			$string = '';
			$chars = array_merge(range('a', 'z'), range('A', 'Z'), range(0, 9));
			
			for($i = 0; $i < $length; $i++) {
				$string .= $chars[ array_rand($chars) ];
			}
			
			return $string;
		}
		
		/**
		 * Flip the case of a string, character by character. Example: "This is a 12345 TEST" returns "tHIS IS A 12345 test"
		 * @param string $str Raw string
		 * @return string
		 */
		public static function flipCase(string $str): string {
			$uppercase = range('A', 'Z');
			$lowercase = range('a', 'z');
			
			$final = [];
			$bits = str_split($str);
			
			foreach($bits as $bit) {
				if(false !== ($bit_key = array_search($bit, $uppercase, true))) {
					$final[] = $lowercase[ $bit_key ];
				} elseif(false !== ($bit_key = array_search($bit, $lowercase, true))) {
					$final[] = $uppercase[ $bit_key ];
				} else {
					$final[] = $bit;
				}
			}
			
			return implode("", $final);
		}
		
		/**
		 * Convert Document Editor styled quotes to regular quotes
		 * @param string $text String to modify
		 * @return string
		 */
		public static function replaceFancyTextToWebText(string $text): string {
			// Quotes cleanup
			return strtr($text, [
				"‘"				=> "'",
				"’"				=> "'",
				"&#8216;"		=> "'",
				"&#8217;"		=> "'",
				"&rsquo;"		=> "'",
				"&#132;"		=> "'",
				
				"“"				=> "\"",
				"”"				=> "\"",
				"&#8220;"		=> "\"",
				"&#8221;"		=> "\"",
			]);
		}
		
		/**
		 * remove emojis from a string (or replace them with a provided string)
		 * @param string $str String to filter emojis out
		 * @param string $replace_with Optional. Set this to whatever each emoji should be replaced with
		 * @return string
		 * @see https://stackoverflow.com/a/68155491/103337
		 */
		public static function stripEmojis(string $str, string $replace_with=""): string {
			// Match Enclosed Alphanumeric Supplement
			$regex_alphanumeric = '/[\x{1F100}-\x{1F1FF}]/u';
			$str = preg_replace($regex_alphanumeric, $replace_with, $str);
			
			// Match Miscellaneous Symbols and Pictographs
			$regex_symbols = '/[\x{1F300}-\x{1F5FF}]/u';
			$str = preg_replace($regex_symbols, $replace_with, $str);
			
			// Match Emoticons
			$regex_emoticons = '/[\x{1F600}-\x{1F64F}]/u';
			$str = preg_replace($regex_emoticons, $replace_with, $str);
			
			// Match Transport And Map Symbols
			$regex_transport = '/[\x{1F680}-\x{1F6FF}]/u';
			$str = preg_replace($regex_transport, $replace_with, $str);
			
			// Match Supplemental Symbols and Pictographs
			$regex_supplemental = '/[\x{1F900}-\x{1F9FF}]/u';
			$str = preg_replace($regex_supplemental, $replace_with, $str);
			
			// Match Miscellaneous Symbols
			$regex_misc = '/[\x{2600}-\x{26FF}]/u';
			$str = preg_replace($regex_misc, $replace_with, $str);
			
			// Match Dingbats
			$regex_dingbats = '/[\x{2700}-\x{27BF}]/u';
			$str = preg_replace($regex_dingbats, $replace_with, $str);
			
			return $str;
		}
		
		/**
		 * Convert a number of seconds into a human readable string
		 * @param int $sec Number of seconds
		 * @param bool $padHours Optional. Set to true to pad the hours with a leading zero
		 * @return string
		 */
		public static function seconds_to_hhmmss(int $sec, bool $padHours=false): string {
			$hms = "";
			
			//$hours = intval(intval($sec) / 3600);
			$hours = intval($sec / 3600);
			
			$hms .= ($padHours?str_pad((string)$hours, 2, '0', STR_PAD_LEFT) .':':$hours .':');
			
			$minutes = intval(($sec / 60) % 60);
			
			$hms .= str_pad((string)$minutes, 2, '0', STR_PAD_LEFT) .':';
			
			$seconds = intval($sec % 60);
			
			$hms .= str_pad((string)$seconds, 2, '0', STR_PAD_LEFT);
			
			return $hms;
		}
	}