<?php
	$url = (!empty($site['args']['entity_url'])?trim($site['args']['entity_url']):false);
	$ext_type = false;
	$ext_value = false;
	
	if(empty($url)) {
		ajax_die(array(
			'status' 		=> "error",
			'status_msg'	=> "The external URL provided was empty or missing",
		));
	}
	
	switch(true) {
		// IMDb
		case preg_match("#^https?\:\/\/(?:www\.|m\.)?imdb\.com\/(.+?)$#si", $url, $match):
			if(!empty($match[1])) {
				if(preg_match("#(nm|tt|ch|co)([0-9]{6,8})#si", $match[1], $match2)) {
					if(!empty($match2[1]) && !empty($match2[2])) {
						$ext_type = "imdb";
						$ext_value = array(
							'id' => ltrim($match2[2], "0"),
						);
						
						switch($match2[1]) {
							case 'nm':	$ext_value['type'] = mkurl("name");			break;
							case 'tt':	$ext_value['type'] = mkurl("title");		break;
							case 'ch':	$ext_value['type'] = mkurl("character");	break;
							case 'co':	$ext_value['type'] = mkurl("company");		break;
							default:
								$ext_value['id'] = $match2[0];
						}
					}
				}
			}
		break;
		
		// Wikipedia
		case preg_match("#^https?\:\/\/(?:([A-Za-z0-9\-]+?)\.)wikipedia\.org\/wiki\/(.+?)$#si", $url, $match):
			if(!empty($match[2])) {
				$wiki_lang = (!empty($match[1])?strtolower($match[1]):"en");
				$wiki_title = str_replace("_", " ", array_shift(explode("?", array_shift(explode("#", $match[2])))));
				
				if(!empty($wiki_title) && !preg_match("#^((Main|User|Wikipedia|File|MediaWiki|Template|Help|Category|Portal|Book|Special|Media)( talk)?)\:#si", $wiki_title)) {
					$ext_type = "wikipedia_title";
					$ext_value = array(
						'lang'	=> $wiki_lang,
						'title'	=> $wiki_title,
					);
				}
			}
		break;
		
		/*
		case preg_match("#^https?\:\/\/#si", $url, $match):
			
		break;
		*/
		
		default:
			$ext_type = "url";
			$ext_value = array('url' => $url);
	}
	
	if(!empty($ext_type) && !empty($ext_value)) {
		ajax_die(array(
			'status' 	=> "success",
			'cargo'		=> array(
				'type'	=> mkurl( $ext_type ),
				'value'	=> $ext_value,
			),
		));
	}
	
	// default error
	ajax_die(array(
		'status' 		=> "error",
		'status_msg'	=> "We couldn't find what you were looking for",
	));