<h2 class="section_title">Wikipedia Text</h2>

<?php
	$parsed = false;
	
	if(!empty($entity_meta['external_id']['wikipedia'])) {
		$parsed = pullAndParse_wikipediaById($entity_meta['external_id']['wikipedia'], array('citations', 'wikipedia_meta'));
	}
	
	if(!empty($parsed)) {
		$wikipedia_id = $entity_meta['external_id']['wikipedia'];
		
		//print "<pre>". print_r($parsed,1) ."</pre>\n";
		
		if(!empty($parsed['sections'])) {
			foreach($parsed['sections'] as $section) {
				ob_start();
				
				parse_wiki_syntax_section($section, 1);
				
				$syntax_contents = ob_get_clean();
				
				$syntax_contents = preg_replace_callback("#\[\s*wiki_table\s*=\s*\"?([A-Fa-f0-9]{8})\"?\s*\]#si", function($match) {
					global $parsed;
					
					return wiki_displayTable($parsed['tables'][ trim($match[1], " =\"]") ], false, false);
				}, $syntax_contents);
				
				print $syntax_contents;
			}
		}
		
		print "<p>debug: <a href=\"/dev/wiki.php?e=". $wikipedia_id ."\" target=\"_blank\" style=\"font-weight: bold;\">view full parse</a></p>\n";
	} else {
		print "<p>This entity is not attached to a specific wikipedia pageid.</p>";
	}