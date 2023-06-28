<?php
	namespace Magnetar;
	
	use \api as api;
	
	class EntityType extends Abstract_Flash {
		use Trait_Flash;
		
		// Flash settings
		private static $flash__cache_expiry	= 604800;   // in seconds (default: 15 minutes)
		private static $flash__key_hash		= true;   // md5() cache key
		private static $flash__key_prefix	= "new.jungledb.dev:entity_type:by_id:";
		private static $flash__key_suffix	= "";
		
		
		//private $type_id;
		
		public function __construct($type_id) {
			//$this->type_id = $type_id;
			
			return $this->flash__init($type_id);
		}
		
		protected function flash__process($entity_type_ids) {
			return api::db()->get_results("
				SELECT *
				FROM `entity_type`
				WHERE
					`id` IN ('". implode("', '", $entity_type_ids) ."')
			", 'id');
		}
		
		
		// Data
		
		//public function getId() {
		//	return $this->type_id;
		//}
		
		public function getEntityId() {
			return $this->flash__get('entity_id');
		}
		
		public function getParentId() {
			return $this->flash__get('parent_id');
		}
		
		public function getSlug() {
			return $this->flash__get('seoid');
		}
		
		public function getTitle() {
			return $this->flash__get('title');
		}
		
		public function getTitlePlural() {
			return $this->flash__get('title_plural');
		}
		
		public function getCanEntity() {
			return $this->flash__get('can_entity');
		}
		
		public function getDisplayAlways() {
			return $this->flash__get('display_always');
		}
		
		public function getNotes() {
			return $this->flash__get('notes');
		}
		
		
		
		// Booleans
		
		public function canEntity() {
			return ($this->getCanEntity()?true:false);
		}
		
		public function canDisplayAlways() {
			return ($this->getDisplayAlways()?true:false);
		}
		
		
		// Other modules
		
		public function getEntity() {
			return new Entity( $this->flash__get('entity_id') );
		}
		
		public function getParent() {
			return new EntityType( $this->flash__get('parent_id') );
		}
	}