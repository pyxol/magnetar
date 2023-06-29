<?php
	declare(strict_types=1);
	
	/**
	 * Escape a string for use inside HTML attributes
	 * @param string $string The string to escape
	 * @return string
	 */
	function esc_attr(string $string): string {
		$string = htmlentities($string, ENT_QUOTES, 'UTF-8');
		
		return $string;
	}
	
	/**
	 * Escape a string for use in HTML
	 * @param string $string The string to escape
	 * @return string
	 */
	function esc_html(string $string): string {
		$string = htmlentities($string, ENT_COMPAT, 'UTF-8');
		
		return $string;
	}
	
	/**
	 * Escape a string for use in a URL
	 * @param string $url The URL to escape
	 * @return string
	 * @see https://developer.wordpress.org/reference/functions/esc_url/
	 */
	function esc_url(string $url): string {
		if(empty($url)) {
			return $url;
		}
		
		$url = preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\\x80-\\xff]|i', '', $url);
		$strip = array('%0d', '%0a', '%0D', '%0A');
		$url = str_replace($strip, "", $url);
		$url = str_replace(';//', '://', $url);
		
		if(strpos($url, ':') === false && !in_array($url[0], array( '/', '#', '?')) && !preg_match('/^[a-z0-9-]+?\.php/i', $url)) {
			$url = 'http://' . $url;
		}
		
		return $url;
	}
	
	/**
	 * Escape a string for use in a 'tag'
	 * @param string $string The string to escape
	 * @return string
	 */
	function esc_tag(string $string): string {
		$string = strtolower(preg_replace("#[^A-Za-z0-9_:]#i", '', $string));
		
		return $string;
	}