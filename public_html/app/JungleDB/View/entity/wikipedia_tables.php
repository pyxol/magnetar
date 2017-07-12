<h2 class="page_title">Wikipedia Tables</h2>

<?php
	$parsed = false;
	
	if(!empty($entity_meta['external_id']['wikipedia'])) {
		$parsed = pullAndParse_wikipediaById($entity_meta['external_id']['wikipedia'], array('citations', 'wikipedia_meta'));
	}
	
	if(!empty($parsed)) {
		$wikipedia_id = $entity_meta['external_id']['wikipedia'];
		
		//print "<pre>". print_r($parsed,1) ."</pre>\n";
		
		if(!empty($parsed['tables'])) {
			foreach($parsed['tables'] as $table_id => $table) {
				wiki_displayTable($table, true);
			}
		} else {
			print "<p>No table data was found.</p>\n";
		}
		
		
		print "<p>debug: <a href=\"/dev/wiki.php?e=". $wikipedia_id ."\" target=\"_blank\" style=\"font-weight: bold;\">view full parse</a></p>\n";
	} else {
		print "<p>This entity is not attached to a specific wikipedia pageid.</p>";
	}