<?php
	// Link Wikipedia to Jungle content!
	global $db;
	
	$action = (!empty($_REQUEST['action'])?$_REQUEST['action']:"insert");
	
	if(!in_array($action, array("insert", "update", "delete"))) {
		ajax_die(array(
			'status'		=> "error",
			'status_msg'	=> "An incorrect action was requested.",
		));
	}
	
	$meta_entity_id = (!empty($_REQUEST['meta_entity_id'])?intval($_REQUEST['meta_entity_id']):false);
	$meta_key = (!empty($_REQUEST['meta_key'])?$_REQUEST['meta_key']:false);
	$meta_value = (!empty($_REQUEST['meta_value'])?$_REQUEST['meta_value']:false);
	
	if(empty($meta_entity_id) || empty($meta_key) || empty($meta_value)) {
		ajax_die(array(
			'status'		=> "error",
			'status_msg'	=> "Entity meta values are empty.",
		));
	}
	
	$meta = $db->get_row("SELECT * FROM `meta` WHERE `key` = '". $db->escape($meta_key) ."'");
	
	if(empty($meta['id'])) {
		// Add new meta key
		$db->insert('meta', array(
			'key'	=> $meta_key,
			'notes'	=> "Generated automatically by /templates/jungle/ajax/entity_meta.php at ". date("r") ." by IP ". getenv("REMOTE_ADDR"),
		));
		
		if(!empty($db->insert_id)) {
			$meta = $db->get_row("SELECT * FROM `meta` WHERE `id` = '". $db->escape($db->insert_id) ."'");
		}
	}
	
	if(empty($meta['id'])) {
		ajax_die(array(
			'status'		=> "error",
			'status_msg'	=> "Could not find the specified meta key.",
		));
	}
	
	// duplicate meta check (ignore allowed multiple meta keys)
	if($meta['multiple'] == "0") {
		$check = $db->get_var("
			SELECT `id`
			FROM `entity_meta`
			WHERE
					`entity_id` = '". $db->escape($meta_entity_id) ."'
				AND
					`meta_id` = '". $db->escape($meta['id']) ."'
		");
		
		if(!empty($check)) {
			ajax_die(array(
				'status' => "error",
				'status_msg' => "Blocked attempt to insert duplicate entry (". $meta_entity_id .".". $meta_key .") in meta table.",
			));
		}
	}
	
	
	switch($action) {
		//case 'update':
		//	$db->update("entity_meta", array(''), array('' => $))
		
		case 'insert':
			$db->insert("entity_meta", array(
				'entity_id'	=> $meta_entity_id,
				'meta_id'	=> $meta['id'],
				'value'		=> $meta_value
			));
			
			ajax_die(array(
				'status'		=> "success",
				'status_msg'	=> "Added '". $meta_key ."' to entity meta table.",
			));
		break;
	}
	
	die;
	
	