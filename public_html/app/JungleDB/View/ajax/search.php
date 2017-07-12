<?php
	// search by title
	
	if(empty($site['args']['query'])) {
		die(json_encode(array(
			'status'		=> "error",
			'status_msg'	=> "Your specified query is too short.",
		)));
	}
	
	if(!class_exists("SphinxClient")) {
		die(json_encode(array(
			'status'		=> "error",
			'status_msg'	=> "The required resources were not found.",
		)));
	}
	
	$entity_types = getEntityTypes();
	
	$query = preg_replace("#[^A-Za-z0-9 ]#si", "", $site['args']['query']);
	$page = ((!empty($site['args']['page']) && is_numeric($site['args']['page']))?intval($site['args']['page']):1);
	$per_page = 20;
	
	$cl = new SphinxClient();
	
	$cl->setServer(SPHINX_HOST, SPHINX_PORT);
	
	$cl->setConnectTimeout(2);		// no connection attempt longer than X second[s]
	$cl->setMaxQueryTime(1000);		// no query longer than X/1000 second[s]
	
	$cl->setFieldWeights(array(
		'title'		=> 2,
		'excerpt'	=> 1,
	));
	
	if(!empty($site['args']['entity_type_limit'])) {
		$cl->setFilter('type_id', array($site['args']['entity_type_limit']));
	}
	
	$cl->setLimits((($page - 1) * $per_page), $per_page, 1000);
	
	$cl->setMatchMode(SPH_MATCH_ALL);
	$cl->setSortMode(SPH_SORT_EXPR, "@weight * entity_weight");
	$sph_result = $cl->Query($query, "index_jungle");
	
	if($cl->getLastWarning()) {
		die(json_encode(array(
			'status'		=> "error",
			'status_msg'	=> "Sphinx Warning: ". $cl->getLastWarning(),
		)));
	}
	
	if($cl->getLastError()) {
		die(json_encode(array(
			'status'		=> "error",
			'status_msg'	=> "Sphinx Error: ". $cl->getLastError(),
		)));
	}
	
	$num_results = $sph_result['total_found'];
	$time_taken = $sph_result['time'];
	
	if($num_results > 0) {
		$num_pages = ceil( @($search['num_results'] / $search['per_page']) );
	}
	
	$results = array();
	
	if(!empty($sph_result['matches'])) {
		$_results = $db->get_results("
			SELECT *
			FROM `entity`
			WHERE
				`id` IN ('". implode("','", array_keys($sph_result['matches'])) ."')
			ORDER BY
				FIELD(`id`, '". implode("','", array_keys($sph_result['matches'])) ."') ASC
		");
		
		foreach($_results as $_result) {
			$result = array(
				'id'		=> $_result['id'],
				'title'		=> $_result['title'],
				'excerpt'	=> $_result['excerpt'],
				//'link'		=> "/j/". $entity_types[ $_result['type_id'] ]['seoid'] ."/". $_result['seoid'] ."/",
				'link'		=> "/j/". $_result['id'] ."/",
				'media'		=> false,
			);
			
			if(!empty($_result['media_id'])) {
				$media = $db->get_row("SELECT * FROM `media` WHERE `id` = '". $db->escape($_result['media_id']) ."' AND `tmp_done` = '2'");
				
				if(!empty($media)) {
					$result['media'] = entity_thumbUrls($media);
				}
			}
			
			$results[] = $result;
		}
	}
	
	
	die(json_encode(array(
		'status'		=> "success",
		//'status_msg'	=> "Returning results",
		'cargo'				=> array(
			'query'			=> $query,
			'page'			=> $page,
			'per_page'		=> $per_page,
			'total_found'	=> $sph_result['total_found'],
			'results'		=> $results,
		),
	)));