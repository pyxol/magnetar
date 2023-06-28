<?php
	namespace JungleDB;
	
	use \api as api;
	
	class Controller_Entity extends Abstract_Controller {
		private $section = "index";
		
		private $sections = array(
			// section => template
			'index'					=> "entity/index",
			'update'				=> "entity/update",
			'media'					=> "entity/media",
			'external-ids'			=> "entity/external_ids",
			'debug'					=> "entity/debug",
			
			'wikipedia'				=> "entity/wikipedia",
			'wikipedia-timeline'	=> "entity/wikipedia_timeline",
			'wikipedia-tables'		=> "entity/wikipedia_tables",
			
			'imdb'					=> "entity/imdb",
			'imdb-connections'		=> "entity/imdb_connections",
		);
		
		private $section_titles = array(
			'update'				=> "Update Information",
			'media'					=> "Media",
			'external-ids'			=> "External IDs",
			'debug'					=> "Raw Entity Debug",
		);
		
		public function get($entity_id=false, $section_key=false) {
			// Sub-view
			if($section_key) {
				$this->section = strtolower(trim($section_key));
			}
			
			
			// Entity
			if(empty($entity_id)) {
				return api::tpl()->set('error', "Entity not provided.")->view('error/404');
			}
			
			api::tpl()->entity = api::entity($entity_id);
			
			if(!api::tpl()->entity->getId()) {
				return api::tpl()->set('error', "Entity not found.")->view('error/404');
			}
			
			api::request()->setUrl("/j/". api::tpl()->entity->getId() ."/". (("index" !== $this->section)?$this->section .".html":""));
			
			
			// Entity Type
			api::tpl()->entity_type = api::tpl()->entity->getType();
			
			if(!api::tpl()->entity_type->getId()) {
				return api::tpl()->set('error', "Entity Type not found.")->view('error/404');
			}
			
			
			// Verify section
			
			if(!$this->section || !isset($this->sections[ $this->section ])) {
				return api::tpl()->set('error', "Couldn't find the entity page you were looking for.")->view('error/404');
			}
			
			
			
			// Meta
			api::tpl()->entity_meta = api::cache()->get(md5('new.jungledb.dev:entity:'. api::tpl()->entity->getId() .':meta_items'), function() {
				return api::db()->get_results("
					SELECT entity_meta.*, meta.key as meta_key, meta.multiple as meta_multiple
					FROM
						`entity_meta`
							LEFT JOIN `meta` ON meta.id = entity_meta.meta_id
					WHERE
						entity_meta.entity_id = '". api::db()->escape(api::tpl()->entity->getId()) ."'
				", 'id');
			});
			
			
			// Media
			api::tpl()->media = api::cache()->get(md5('new.jungledb.dev:entity:'. api::tpl()->entity->getId() .':media_ids'), function() {
				return api::db()->get_col("SELECT `media_id` FROM `entity_xref_media` WHERE entity_xref_media.entity_id = '". api::db()->escape(api::tpl()->entity->getId()) ."'");
			}, 900);
			
			// prep flash for media
			if(!empty(api::tpl()->media)) {
				foreach(api::tpl()->media as $media_id) {
					api::media( $media_id )->getId();
				}
			}
			
			
			// Template Variables
			api::tpl()->site_title = Config::get("site.name") ." - ". api::tpl()->entity->getTitle() . (isset($this->section_titles[ $this->section ])?" - ". $this->section_titles[ $this->section ]:"");
			
			
			// View
			api::tpl()->view( $this->sections[ $this->section ] );
		}
	}