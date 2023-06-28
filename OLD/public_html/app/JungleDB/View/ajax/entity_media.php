<?php
	$action = (!empty($site['args']['do'])?strtolower(trim($site['args']['do'])):"");
	$entity_id = ((!empty($site['args']['entity_id']) && is_numeric($site['args']['entity_id']))?$site['args']['entity_id']:false);
	
	if(empty($entity_id)) {
		ajax_die(array(
			'status'		=> "error",
			'status_msg'	=> "Some variables provided were empty or missing",
		));
	}
	
	$entity = $db->get_row("SELECT * FROM `entity` WHERE `id` = '". $db->escape($entity_id) ."'");
	
	if(empty($entity)) {
		ajax_die(array(
			'status'		=> "error",
			'status_msg'	=> "Could not find the specified entity",
		));
	}
	
	$entity_type = $db->get_row("SELECT * FROM `entity_type` WHERE `id` = '". $db->escape($entity['type_id']) ."'");
	
	switch($action) {
		case 'set_primary':
			$media_id = ((!empty($site['args']['media_id']) && is_numeric($site['args']['media_id']))?$site['args']['media_id']:false);
			
			if(empty($media_id)) {
				ajax_die(array(
					'status'		=> "error",
					'status_msg'	=> "Some variables provided were empty or missing",
				));
			}
			
			$media = $db->get_row("SELECT * FROM `media` WHERE `id` = '". $db->escape($media_id) ."'");
			
			if(empty($media)) {
				ajax_die(array(
					'status'		=> "error",
					'status_msg'	=> "Could not find the specified media",
				));
			}
			
			// update an entity's primary media_id
			$xref = $db->get_row("SELECT * FROM `entity_xref_media` WHERE `entity_id` = '". $db->escape($entity['id']) ."' AND `media_id` = '". $db->escape($media_id) ."'");
			
			if(empty($xref)) {
				$db->insert("entity_xref_media", array('entity_id' => $entity['id'], 'media_id' => $media_id));
			}
			
			$db->update("entity", array('media_id' => $media_id), array('id' => $entity['id']));
			
			ajax_die(array(
				'status'	=> "success",
				'cargo'		=> array(
					'entity_id'	=> $entity['id'],
					'media_id'	=> $media_id,
					'media'		=> entity_thumbUrls($media)
				)
			));
		break;
		
		case 'connect':
			$media_id = ((!empty($site['args']['media_id']) && is_numeric($site['args']['media_id']))?$site['args']['media_id']:false);
			
			if(empty($media_id)) {
				ajax_die(array(
					'status'		=> "error",
					'status_msg'	=> "Some variables provided were empty or missing",
				));
			}
			
			$media = $db->get_row("SELECT * FROM `media` WHERE `id` = '". $db->escape($media_id) ."'");
			
			if(empty($media)) {
				ajax_die(array(
					'status'		=> "error",
					'status_msg'	=> "Could not find the specified media",
				));
			}
			
			$xref = $db->get_row("SELECT * FROM `entity_xref_media` WHERE `entity_id` = '". $db->escape($entity['id']) ."' AND `media_id` = '". $db->escape($media_id) ."'");
			
			if(empty($xref)) {
				$db->insert("entity_xref_media", array('entity_id' => $entity['id'], 'media_id' => $media_id));
			}
			
			ajax_die(array(
				'status'	=> "success",
			));
		break;
		
		case 'gallery_details':
			$gallery = array(
				'id'			=> $entity['id'],
				'url'			=> "/j/". $entity_type['seoid'] ."/". $entity['seoid'] ."/",
				'date_created'	=> $entity['date_created'],
				'date_updated'	=> $entity['date_updated'],
				'title'			=> $entity['title'],
				'excerpt'		=> $entity['excerpt'],
				'num_media'		=> 0,
				'medias'		=> array(),
			);
			
			$medias = false;
			$media_ids = $db->get_col("SELECT `media_id` FROM `entity_xref_media` WHERE `entity_id` = '". $db->escape($entity['id']) ."'");
			
			if(!empty($media_ids)) {
				$medias = $db->get_results("SELECT * FROM `media` WHERE `id` IN ('". implode("', '", $media_ids) ."')");
			}
			
			if(!empty($medias)) {
				$last_media_id = false;
				
				foreach($medias as $media) {
					if(!empty($last_media_id)) {
						$gallery['media'][ $last_media_id ]['next_id'] = $media['id'];
					}
					
					$_media = array(
						'id'		=> $media['id'],
						'type'		=> $media['type'],
						'server_id'	=> $media['server_id'],
						'folder'	=> $media['folder'],
						'file'		=> $media['file'],
						'date'		=> array(
							'created'		=> $media['date_created'],
							'downloaded'	=> $media['date_downloaded'],
						),
						'position'	=> ++$gallery['num_media'],
						'prev_id'	=> $last_media_id,
						'next_id'	=> false,
					);
					
					if(!empty($media['file_details'])) {
						$media['file_details'] = json_decode($media['file_details'], true);
						
						if($media['type'] === "image" && !empty($media['file_details'])) {
							$_media['dimensions'] = array(
								'width'		=> $media['file_details']['0'],
								'height'	=> $media['file_details']['1'],
							);
							
							$_media['media_url']	= entity_thumbUrls($media);
						}
					}
					
					$gallery['media'][ $media['id'] ] = $_media;
					
					$last_media_id = $media['id'];
				}
				
				reset($gallery['media']);
				$first_key = key($gallery['media']);
				end($gallery['media']);
				$last_key = key($gallery['media']);
				
				$gallery['media'][ $first_key ]['prev_id']	= $last_key;
				$gallery['media'][ $last_key ]['next_id']	= $first_key;
			}
			
			ajax_die($gallery);
		break;
	}
	
	ajax_die(array(
		'status'		=> "error",
		'status_msg'	=> "The action specified '". $action ."' is not supported",
	));