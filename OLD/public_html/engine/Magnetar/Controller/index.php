<?php
	namespace Magnetar;
	
	use \api as api;
	
	class Controller_index extends Abstract_Controller {
		public function get() {
			api::tpl()->site_title = "JungleDB";
			api::tpl()->body_class = "header_frontpage";
			
			api::tpl()->entities = api::cache()->get(md5('new.jungledb.dev:index:random_entities'), function() {
				// returns [entity_id[, ...]]
				
				$entities = array();
				$num_entities = $num_entities_needed = 18;
				$i = 0;
				
				$max_entity_id = api::db()->get_var("SELECT MAX(`id`) FROM `entity`");
				
				do {
					$random_entities = api::db()->get_results("SELECT `id`, `media_id` FROM `entity` WHERE `media_id` IS NOT NULL ORDER BY RAND() LIMIT 0, ". api::db()->escape($num_entities));
					
					if(empty($random_entities)) {
						break;
					}
					
					foreach($random_entities as $entity) {
						if(empty($entity['media_id']) || in_array($entity['id'], $entities)) {
							continue;
						}
						
						$entities[] = $entity['id'];
						$num_entities_needed--;
						
						if(++$i >= $num_entities) {
							break 2;
						}
					}
				} while($i < $num_entities);
				
				return $entities;
			}, 900);
			
			// prime cache
			foreach(api::tpl()->entities as $entity_id) {
				api::entity($entity_id);
			}
			
			foreach(api::tpl()->entities as $entity_id) {
				api::entity($entity_id)->getPrimaryMedia();
				api::entity($entity_id)->getType();
			}
			
			api::tpl()->view('index');
		}
	}