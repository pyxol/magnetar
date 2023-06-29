<?php
	declare(strict_types=1);
	
	namespace Magnetar\Object\EntityType;
	
	use Magnetar\Object\AbstractObject;
	use Magnetar\Database\AbstractDatabase;
	use Exception;
	
	class EntityType extends AbstractObject {
		protected EntityType|false|null $parent = null;
		
		protected ?array $parents = null;
		
		/**
		 * Pull the entity type from the database
		 * @return void
		 * @throws Exception
		 */
		protected function pullObject(int $id): void {
			$this->object = $this->db->get_row("
				SELECT
					entity_type.*
				FROM `entity_type`
				WHERE
					entity_type.id = :id
			", [
				'id' => $id
			]);
			
			if(empty($this->object['id'])) {
				throw new Exception("Entity Type not found");
			}
		}
		
		public static function getBySlug(string $slug, AbstractDatabase $db): EntityType {
			if(!preg_match("#^[A-Za-z0-9\-_]+$#si", $slug)) {
				throw new Exception("Invalid entity type slug");
			}
			
			$entity_type_id = $db->get_var("
				SELECT
					entity_type.id
				FROM `entity_type`
				WHERE
					entity_type.seoid = :slug
			", [
				'slug' => $slug
			]);
			
			if(empty($entity_type_id)) {
				throw new Exception("Entity Type not found");
			}
			
			return new EntityType((int)$entity_type_id, $db);
		}
		
		/**
		 * Get the parent entity type
		 * @return EntityType|false
		 */
		public function getParent() {
			if(!is_null($this->parent)) {
				return $this->parent;
			}
			
			$this->parent = false;
			
			if(!empty($this->object['parent_type'])) {
				$this->parent = new EntityType($this->object['parent_type'], $this->db);
			}
			
			return $this->parent;
		}
		
		/**
		 * Get an array of the parent entity types sorted chronologically (eg: [..., great-grandparent, grandparent, parent])
		 */
		public function getParents() {
			if(!is_null($this->parents)) {
				return $this->parents;
			}
			
			$this->parents = [];
			
			$parent = $this->getParent();
			
			while($parent) {
				array_unshift($this->parents, $parent);
				$parent = $parent->getParent();
			}
			
			return $this->parents;
		}
		
		/**
		 * Get the title
		 * @return string
		 */
		public function getTitle() {
			return $this->object['title'];
		}
		
		/**
		 * Get the plural title
		 * @return string
		 */
		public function getTitlePlural() {
			return $this->object['title_plural'];
		}
		
		/**
		 * Get the URL slug
		 * @return string
		 */
		public function getSlug() {
			return $this->object['seoid'];
		}
		
		/**
		 * Get the permalink for this entity type
		 * @param string $sub_section Sub section to link to. If empty, links to the entity type's index page
		 * @return string
		 * @uses site_url()
		 */
		public function getUrl($sub_section="") {
			$uri_path = "/entity/". $this->getSlug() ."/";
			
			if(!empty($sub_section) && preg_match("#^[A-Za-z0-9\-_]+#si", $sub_section) && ("" !== ($sub_section = strtolower(trim($sub_section))))) {
				$uri_path .= $sub_section ."/";
			}
			
			// @TODO replacement for site_url
			
			return site_url($uri_path);
		}
	}