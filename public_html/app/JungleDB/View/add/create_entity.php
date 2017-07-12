<?php
	$entity_type_id = ((!empty($site['args']['entity_type']) && is_numeric($site['args']['entity_type']))?$site['args']['entity_type']:false);
	$entity_title = ((isset($site['args']['entity_title']) && (strlen(trim($site['args']['entity_title'])) > 0))?trim($site['args']['entity_title']):false);
	$entity_excerpt = (isset($site['args']['entity_excerpt'])?trim($site['args']['entity_excerpt']):"");
	
	if($entity_type_id === false) {
		redirect("/add/#=add_entity_type_not_found=");
		
		die;
	}
	
	$entity_types = getEntityTypes();
	
	//if(!array_key_exists($entity_type_id, $entity_types) || empty($entity_types[ $entity_type_id ]['can_entity']))
	if(empty($entity_types[ $entity_type_id ]['can_entity'])) {
		redirect("/add/#=add_entity_type_not_found=");
		
		die;
	}
	
	if($entity_title === false) {
		redirect("/add/new_entity/?id=". $entity_type_id ."#=add_title_missing=");
		
		die;
	}
	
	$entity_type = $entity_types[ $entity_type_id ];
	
	$entity_seoid_primary = mkurl($entity_title);
	$entity_seoid = $entity_seoid_primary;
	$i = 0;
	
	while(false !== $db->get_row("SELECT `id` FROM `entity` WHERE `type_id` = '". $db->escape($entity_type['id']) ."' AND `seoid` = '". $db->escape($entity_seoid) ."'")) {
		$entity_seoid = $entity_seoid_primary ."-". ++$i;
	}
	
	$insert_id = $db->insert("entity", array(
		'type_id'		=> $entity_type['id'],
		'seoid'			=> $entity_seoid,
		'title'			=> $entity_title,
		'excerpt'		=> $entity_excerpt,
		'date_created'	=> $db->sql_function('now'),
		'date_updated'	=> $db->sql_function('now'),
	));
	
	
	if(!empty($insert_id)) {
		redirect("/j/". $entity_type['seoid'] ."/". $entity_seoid ."/");
		
		die;
	} else {
		redirect("/add/new_entity/?id=". $entity_type_id ."#=add_title_missing=");
		
		die;
	}