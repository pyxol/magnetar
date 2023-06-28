<?php
	// search by title
	
	if(empty($site['args']['query'])) {
		die(json_encode(array(
			'status'		=> "error",
			'status_msg'	=> "Your specified query is too short.",
		)));
	}
	
	$query = trim($site['args']['query']);
	$page = ((!empty($site['args']['page']) && is_numeric($site['args']['page']))?intval($site['args']['page']):1);
	$per_page = 25;
	
	//require_once($site['dir']['includes'] ."sphinx-2.0.5". DS ."sphinxapi.php");
	
	$cl = new SphinxClient();
	
	$cl->setServer("127.0.0.1", 9312);
	
	$cl->setConnectTimeout(2);		// no connection attempt longer than X second[s]
	$cl->setMaxQueryTime(1000);		// no query longer than X/1000 second[s]
	
	$cl->setFieldWeights(
		array(
			 'title' => 2
			,'excerpt' => 1
		)
	);
	
	$cl->setLimits((($page - 1) * $per_page), $per_page, 1000);
	
	$cl->setMatchMode(SPH_MATCH_ALL);
	
	if(!defined('SPH_RANK_PROXIMITY_BM25')):	define('SPH_RANK_PROXIMITY_BM25', 0);	endif;
	if(!defined('SPH_RANK_BM25')):	 			define('SPH_RANK_BM25', 1);			 endif;
	if(!defined('SPH_RANK_NONE')):	 			define('SPH_RANK_NONE', 2);			 endif;
	if(!defined('SPH_RANK_WORDCOUNT')):			define('SPH_RANK_WORDCOUNT', 3);		endif;
	if(!defined('SPH_RANK_PROXIMITY')):			define('SPH_RANK_PROXIMITY', 4);		endif;
	if(!defined('SPH_RANK_MATCHANY')): 			define('SPH_RANK_MATCHANY', 5);		 endif;
	if(!defined('SPH_RANK_FIELDMASK')):			define('SPH_RANK_FIELDMASK', 6);		endif;
	if(!defined('SPH_RANK_SPH04')):				define('SPH_RANK_SPH04', 7);			endif;
	if(!defined('SPH_RANK_TOTAL')):				define('SPH_RANK_TOTAL', 8);			endif;
	
	
	//$cl->setSortMode(SPH_SORT_RELEVANCE);
	$cl->setSortMode(SPH_SORT_EXTENDED, "title_length ASC, @weight DESC, @id ASC");
	
	$sph_result = $cl->Query($query, "index_imdb");
	
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
		$found_ids = array_map(function($match) {
			return $match['attrs']['seoid'];
		}, $sph_result['matches']);
		
		$db->select_db(IMDB_DATABASE);
		$results = $db->get_results("SELECT `seoid` as `imdb_key`, `title`, `type` FROM `entity` WHERE `seoid` IN ('". implode("','", $found_ids) ."') ORDER BY FIELD(`seoid`, '". implode("','", $found_ids) ."')");
		$db->select_db(DB_NAME);
	}
	
	
	die(json_encode(array(
		'status'		=> "success",
		//'status_msg'	=> "Returning results",
		'cargo'				=> array(
			'query'			=> $query,
			'page'			=> (int)$page,
			'per_page'		=> (int)$per_page,
			'total_found'	=> (int)$sph_result['total_found'],
			'results'		=> $results,
		),
	)));