<?php
	
	function getEntityTypes() {
		global $site;
		global $db;
		
		if(isset($site['scope']['entities']['entity_types']['by_id'])) {
			return $site['scope']['entities']['entity_types']['by_id'];
		}
		
		$_ets = $db->get_results("SELECT * FROM `entity_type` ORDER BY `title`");
		$site['scope']['entities']['entity_types']['by_id'] = array();
		
		if(!empty($_ets)) {
			foreach($_ets as $_et) {
				if(empty($_et['parent_id'])) {
					$_et['parent_id'] = "0";
				}
				
				$site['scope']['entities']['entity_types']['by_id'][ $_et['id'] ] = $_et;
			}
		}
		
		return $site['scope']['entities']['entity_types']['by_id'];
	}
	
	function getEntityTypes_sortedByParentID() {
		global $site;
		
		if(isset($site['scope']['entities']['entity_types']['by_parent_id'])) {
			return $site['scope']['entities']['entity_types']['by_parent_id'];
		}
		
		getEntityTypes();
		
		if(isset($site['scope']['entities']['entity_types']['by_id'])) {
			$site['scope']['entities']['entity_types']['by_parent_id'] = array();
			
			foreach($site['scope']['entities']['entity_types']['by_id'] as $et) {
				$site['scope']['entities']['entity_types']['by_parent_id'][ $et['parent_id'] ][] = $et;
			}
			
			return $site['scope']['entities']['entity_types']['by_parent_id'];
		}
		
		return false;
	}
	
	function getEntityTypeById($id) {
		global $site;
		
		getEntityTypes();
		
		if(isset($site['scope']['entities']['entity_types']['by_id'][ $id ])) {
			return $site['scope']['entities']['entity_types']['by_id'][ $id ];
		}
		
		return false;
	}
	
	function getEntityTypeIdAndChildIds($id) {
		global $site;
		
		getEntityTypes();
		getEntityTypes_sortedByParentID();
		
		$selfAndDescendants = array();
		$selfAndDescendants[] = $id;
		
		if(!empty($site['scope']['entities']['entity_types']['by_parent_id'][ $id ])) {
			foreach($site['scope']['entities']['entity_types']['by_parent_id'][ $id ] as $child) {
				if(!in_array($child['id'], $selfAndDescendants)) {
					$selfAndDescendants = array_merge($selfAndDescendants, getEntityTypeIdAndChildIds($child['id']));
				}
			}
		}
		
		return array_unique($selfAndDescendants);
	}
	
	
	//function encode_entity_seoid($e_type, $e_seoid) {
	//	return substr(md5($e_type), 0, 5) . ":". $e_seoid;
	//}
	
	