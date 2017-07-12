<?php
	function is_json($string) {
		json_decode($string);
		
		return (json_last_error() == JSON_ERROR_NONE);
	}
	
	function replace_shortcode_allmovie($string) {
		return preg_replace_callback("#\[([A-Za-z]+?)=([0-9]+?)\](.+?)\[\/\\1\]#si", function($match) {
			$type	= trim(strtolower($match[1]), " []=");
			$value	= trim(strtolower($match[2]), " []=");
			$text	= $match[3];
			
			//return "<a href=\"". site_url("/allmovie_id/". $value ."/") ."\" title=\"". esc_attr($text) ." via AllMovie\">". $text ."</a>";
			
			// values are lost, so just search
			if($type === "p") {
				$expected_type = 15;
			} elseif($type === "m") {
				$expected_type = 16;
			}
			
			return "<a href=\"". site_url("/search.php?query=". mkurl($text, "+") . (!empty($expected_type)?"&type=". $expected_type:"")) ."\" title=\"". esc_attr($text) ." via AllMovie\">". $text ."</a>";
		}, $string);
	}
	
	function replace_shortcode_wikipedia($string) {
		return preg_replace_callback("#\[([A-Za-z]+?)=([0-9]+?)\](.+?)\[\/\\1\]#si", function($match) {
			
		}, $string);
	}
	
	function replace_shortcode($string) {
		//return preg_replace("#\[([A-Za-z]+?)=([0-9]+?)\](.+?)\[\/\\1\]#si", "<a href=\"/j/\\2/\" title=\"Jungle Page for '\\3' (\\1)\" target=\"_blank\">\\3</a>", $string);
		return $string;
	}
	
	function text_to_sentences($text, $user_encasings=array()) {
		$text = trim($text);
		$user_encasings = (is_array($user_encasings)?$user_encasings:array());   // soft word encasings to ignore (parenthesis, brackets around words, etc)
		
		$hard_replacers = array(
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
		);
		
		
		$text = str_ireplace(array_keys($hard_replacers), array_values($hard_replacers), $text);
		
		$encasings = array_merge(array("(" => ")", "[" => "]"), $user_encasings);
		
		foreach($encasings as $encasing_open => $encasing_close) {
			$text = preg_replace_callback("#". preg_quote($encasing_open, "#") ."([^". preg_quote($encasing_close, "#") ."]+?)". preg_quote($encasing_close, "#") ."#si", function($matches) {
				return str_replace(".", "&#046;", $matches[0]);
			}, $text);
		}
		
		
		// ignore periods after abbreviations, eg: Dr. or Mrs.
		$text = preg_replace("#([^A-Za-z0-9]+)?(Mr|Mrs|Ms|Jr|Sr|Dr|Miss| c|etc|Rev|Vol|No)\.([\"|\s])#s", "\\1\\2&#046;\\3", $text);
		
		$raw_lines = explode(PHP_EOL, $text);
		
		$sentences = array();
		
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