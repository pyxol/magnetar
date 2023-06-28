<?php
	namespace Magnetar;
	
	use \api as api;
	
	class Entity extends Abstract_Flash {
		use Trait_Flash;
		
		// Flash settings
		private static $flash__cache_expiry	= 86400;   // in seconds (default: 15 minutes)
		private static $flash__key_hash		= true;   // md5() cache key
		private static $flash__key_prefix	= "new.jungledb.dev:entity:by_id:";
		private static $flash__key_suffix	= "";
		
		
		//private $entity_id				= null;
		
		public function __construct($entity_id) {
			//$this->entity_id = $entity_id;
			
			return $this->flash__init($entity_id);
		}
		
		protected function flash__process($entity_ids) {
			return api::db()->get_results("
				SELECT *
				FROM `entity`
				WHERE
					`id` IN ('". implode("', '", $entity_ids) ."')
			", 'id');
		}
		
		
		// Data
		
		//public function getId() {
		//	return $this->entity_id;
		//}
		
		public function getSlug() {
			return $this->flash__get('seoid');
		}
		
		public function getTitle() {
			return $this->flash__get('title');
		}
		
		public function getTypeId() {
			return $this->flash__get('type_id');
		}
		
		public function getPrimaryMediaId() {
			return $this->flash__get('media_id');
		}
		
		public function getDateCreated() {
			return $this->flash__get('date_created');
		}
		
		public function getDateUpdated() {
			return $this->flash__get('date_created');
		}
		
		
		// Dynamic methods
		
		public function getUrl($section="index") {
			return "/j/". $this->getId() ."/". ((!empty($section) && ("index" !== $section))?$section .".html":"");
		}
		
		
		// Other methods
		
		public function getType() {
			return api::entity_type( $this->getTypeId() );
		}
		
		public function getPrimaryMedia() {
			return api::media( $this->getPrimaryMediaId() );
		}
	}