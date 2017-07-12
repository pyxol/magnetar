<?php
	// search by title
	if(empty($site['args']['query'])) {
		die(json_encode(array(
			'status'		=> "error",
			'status_msg'	=> "Your specified query is too short.",
		), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
	}
	
	if(!class_exists("SphinxClient")) {
		die(json_encode(array(
			'status'		=> "error",
			'status_msg'	=> "The required resources were not found.",
		), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
	}
	
	$query = preg_replace("#[^A-Za-z0-9 ]#si", "", $site['args']['query']);
	$limit = 7;
	
	try {
		$cl = new SphinxClient();
		$cl->setServer("127.0.0.1", 9312);
		$cl->setMatchMode(SPH_MATCH_EXTENDED2);
		$cl->setRankingMode(SPH_RANK_SPH04);
		$cl->setLimits(0, $limit);
		$sph_result = $cl->Query($cl->EscapeString($query), "index_jungle_autocomplete");
		
		if($cl->getLastWarning()) {
			die(json_encode(array(
				'status'		=> "error",
				'status_msg'	=> "Sphinx Warning: ". $cl->getLastWarning(),
			), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
		}
		
		if($cl->getLastError()) {
			die(json_encode(array(
				'status'		=> "error",
				'status_msg'	=> "Sphinx Error: ". $cl->getLastError(),
			), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
		}
		
		$cl->close();
	} catch(Exception $e) {
		die(json_encode(array(
			'status'		=> "error",
			'status_msg'	=> $e->getMessage(),
		), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
	}
	
	$cargo = array();
	
	if(!empty($sph_result['matches'])) {
		$cargo = $db->get_results("
			SELECT
				entity.*, media.folder as `media_folder`, media.file as `media_file`
			FROM `entity`
				LEFT JOIN `media` ON media.id = entity.media_id
			WHERE
				entity.id IN ('". implode("','", array_keys($sph_result['matches'])) ."')
			ORDER BY
				FIELD(entity.id, '". implode("','", array_keys($sph_result['matches'])) ."') ASC
		");
		
		if(!empty($cargo)) {
			foreach(array_keys($cargo) as $cargo_key) {
				if(!empty($cargo[$cargo_key]['media_folder']) && !empty($cargo[$cargo_key]['media_file'])) {
					$cargo[$cargo_key]['media_url'] = mediaThumbUrls($cargo[$cargo_key]['media_folder'], $cargo[$cargo_key]['media_file']);
				}
			}
		}
	}
	
	die(json_encode(array(
		'status' => "success",
		'cargo' => $cargo
	), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));