<h2 class="section_title">IMDb: Database</h2>

<?php
	if(!empty($entity_meta['external_id']['imdb'])) {
		// Wikipedia MongoDB
		try {
			$mongo = new Mongo();
			$imdb_wikipedia = $mongo->wikipedia->meta_external->findOne(array(
				'external_links.imdb'	=> $entity_meta['external_id']['imdb'],
			));
		} catch(Exception $e) {
			print "<h3>Mongo Error</h3>\n";
			print "<p>". $e->getMessage() ."</p>\n";
		}
		
		if(!empty($imdb_wikipedia)) {
			print "<h3>imdb_wikipedia</h3>\n";
			new dBug($imdb_wikipedia);
		} else {
			print "<p>No results from IMDb Database...</p>";
		}
		
		
		// database=imdb
		$db->select_db(IMDB_DATABASE);
		$imdb_db = $db->get_row("SELECT * FROM `entity` WHERE `seoid` = '". $db->escape($entity_meta['external_id']['imdb']) ."'");
		
		if(!empty($imdb_db['id'])) {
			$imdb_db_meta = $db->get_results("SELECT * FROM `entity_meta` WHERE `entity_id` = '". $db->escape($imdb_db['id']) ."' ORDER BY `meta_key` ASC");
			$imdb_db_connections = $db->get_results("SELECT * FROM `entity_connection` WHERE `entity_from` = '". $db->escape($imdb_db['seoid']) ."' OR `entity_to` = '". $db->escape($imdb_db['seoid']) ."' ORDER BY `connection` ASC");
			
			$imdb_db_media = $db->get_results("
				SELECT media.*
				FROM `entity_media`
					LEFT JOIN `media` ON media.id = entity_media.media_id
				WHERE entity_media.entity_id = '". $db->escape($imdb_db['id']) ."'
			");
			
			if(!empty($imdb_db_meta)) {
				foreach(array_keys($imdb_db_meta) as $idm_key) {
					json_decode($imdb_db_meta[ $idm_key ]['value']);
					
					if(JSON_ERROR_NONE === json_last_error()) {
						$imdb_db_meta[ $idm_key ]['value'] = json_decode($imdb_db_meta[ $idm_key ]['value'], true);
					}
				}
			}
			
			if(!empty($imdb_db_connections)) {
				foreach(array_keys($imdb_db_connections) as $idc_key) {
					json_decode($imdb_db_connections[ $idc_key ]['value']);
					
					if(JSON_ERROR_NONE === json_last_error()) {
						$imdb_db_connections[ $idc_key ]['value'] = json_decode($imdb_db_connections[ $idc_key ]['value'], true);
					}
				}
			}
		}
		
		$db->select_db(DB_NAME);
		
		if(!empty($imdb_db['id'])) {
			print "<h3>imdb_db</h3>\n";
			new dBug($imdb_db);
			
			print "<h4>imdb_db_meta</h4>\n";
			new dBug($imdb_db_meta);
			
			print "<h4>imdb_db_connections</h4>\n";
			new dBug($imdb_db_connections);
			
			print "<h4>imdb_db_media</h4>\n";
			new dBug($imdb_db_media);
		} else {
			print "<p>No results from IMDb Database...</p>";
		}
	} else {
		print "IMDb ID not linked to this entity.";
	}