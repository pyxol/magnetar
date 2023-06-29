<?php
	declare(strict_types=1);
	
	namespace Magnetar\Utilities;
	
	class HTML {
		/**
		 * Use specified HTML tags to hHighlight a string in a body of text
		 * @param string $string The string to highlight
		 * @param string $text The text to highlight the string in
		 * @param string $hilight_element Optional. The element to use for highlighting. Defaults to "mark".
		 * @return string
		 */
		public static function highlightText(
			string $string="",
			string $text="",
			string $hilight_element="mark"
		): string {
			if("" === ($string = strtolower(trim($string)))) {
				return $text;
			}
			
			$strings = explode(" ", $string);
			$strings = array_unique($strings);
			
			foreach($strings as $string) {
				if(in_array($string, [$hilight_element, "href", "src"])) {
					continue;
				}
				
				$text = preg_replace("#(". preg_quote($string, "#") .")#si", "<". $hilight_element .">\\1</". $hilight_element .">", $text);
			}
			
			return $text;
		}
		
		/**
		 * Convert raw text into HTML paragraphs, optionally cleaning the text
		 * @param string $string The string to clean
		 * @param bool $strip_html Optional. Whether to strip HTML tags. Defaults to true
		 * @param bool $escape_html Optional. Whether to escape HTML tags. Defaults to true
		 * @param callable|false $filter_each_paragraph_cb Optional. Defaults to false. A callback to filter each paragraph
		 * @return string
		 */
		public static function nl2p(
			string $string,
			bool $strip_html=true,
			bool $escape_html=true,
			callable|false $filter_each_paragraph_cb=false
		): string {
			$string = trim($string);
			
			$strings = preg_split("#\n{1,}#si", $string, -1, PREG_SPLIT_NO_EMPTY);
			
			if($strip_html) {
				$strings = array_map("strip_tags", $strings);
			}
			
			if($escape_html) {
				$strings = array_map("esc_html", $strings);
			}
			
			if((false !== $filter_each_paragraph_cb) && is_callable($filter_each_paragraph_cb)) {
				$strings = array_map($filter_each_paragraph_cb, $strings);
			}
			
			return "<p>". implode("</p><p>", $strings) ."</p>";
		}
	}