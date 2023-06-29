<?php
	declare(strict_types=1);
	
	namespace Magnetar\Object\Entity;
	
	use Magnetar\Object\AbstractObject;
	use Magnetar\Object\EntityType\EntityType;
	use Magnetar\Object\Media\Media;
	use Magnetar\Utilities\Str;
	use Magnetar\Utilities\JSON;
	use Exception;
	
	class Entity extends AbstractObject {
		protected EntityType|false|null $type = null;
		
		protected ?array $meta = null;
		protected ?array $media = null;
		
		/**
		 * Pull the entity from the database
		 * @return void
		 * @throws Exception
		 */
		protected function pullObject(int $id): void {
			$this->object = $this->db->get_row("
				SELECT
					entity.*
				FROM `entity`
				WHERE
					entity.id = :id
			", [
				'id' => $id,
			]);
			
			if(empty($this->object['id'])) {
				throw new Exception("Entity not found");
			}
		}
		
		/**
		 * Get the entity's meta values. Set $specific_key to pull single value. or return $default if not found
		 * @param string|null $specific_key Optional. Set to string of meta key name to return single value
		 * @param mixed $default Optional. Only used in conjunction with $specific_key. Return this if meta key(s) not found
		 * @return mixed
		 */
		public function getMeta(
			string|null $specific_key=null,
			mixed $default=false
		): mixed {
			if(is_null($this->meta)) {
				$this->meta = $this->db->get_col_assoc("
					SELECT
						entity_meta.meta_key, entity_meta.meta_value
					FROM `entity_meta`
					WHERE
						entity_meta.entity_id = :entity_id
				", [
					'entity_id' => $this->id,
				], 'meta_key', 'meta_value');
				
				if(!empty($this->meta)) {
					$this->meta = array_map(
						[Magnetar\Utilities\JSON::class, 'maybe_decode'],
						$this->meta
					);
				}
			}
			
			// specific key?
			if(!is_null($specific_key)) {
				return $this->meta[ $specific_key ] ?? $default;
			}
			
			return $this->meta;
		}
		
		/**
		 * Get an array of all attached media
		 * @param int|false $id Optional. If set, return only the media with this ID
		 * @return array|Media
		 */
		public function getMedia(int|false $id=false): array|Media {
			if(!is_null($this->media)) {
				if($id !== false) {
					return $this->media[ $id ] ?? [];
				}
				
				return $this->media;
			}
			
			$this->media = [];
			
			$raw_media_ids = $this->db->get_col("
				SELECT
					media.id
				FROM `entity_xref_media`
					LEFT JOIN `media` ON media.id = entity_xref_media.media_id
				WHERE
					entity_xref_media.entity_id = :entity_id
			", [
				'entity_id' => $this->object['id'],
			]);
			
			if(!empty($raw_media_ids)) {
				foreach($raw_media_ids as $raw_media_id) {
					$this->media[ $raw_media_id ] = new Media($raw_media_id, $this->db);
				}
			}
			
			if($id !== false) {
				return $this->media[ $id ] ?? [];
			}
			
			return $this->media;
		}
		
		/**
		 * Get an EntityType instance of the Entity Type this entity is linked to. Returns false if no entity type is linked
		 * @return EntityType|false
		 */
		public function type(): EntityType|false {
			if(!is_null($this->type)) {
				return $this->type;
			}
			
			if(empty($this->object['entity_type_id'])) {
				return $this->type = false;
			}
			
			return $this->type = new EntityType($this->object['entity_type_id'], $this->db);
		}
		
		/**
		 * Get the thumbnail ID
		 * @return int|false
		 */
		public function getThumbnailID(): int|false {
			return $this->object['thumb'] ?? false;
		}
		
		/**
		 * Get the thumbnail URL
		 * @param int|false $width Optional. Width of the thumbnail
		 * @param int|false $height Optional. Height of the thumbnail
		 * @return string
		 */
		public function getThumb(
			int|false $width=THUMB_WIDTH,
			int|false $height=false
		): string {
			try {
				$entity_image_id = $this->getThumbnailID();
				
				if(empty($entity_image_id)) {
					throw new Exception("Entity Image ID not found");
				}
				
				$image = new Media($entity_image_id, $this->db);
				return $image->getThumb($width, $height);
			} catch(Exception $e) {
				return "https://via.placeholder.com/256x144.png?text=". ucwords(substr(ltrim($this->getTitle()), 0, 1));
			}
		}
		
		/**
		 * Get the title of the entity
		 * @return string
		 */
		public function getTitle(): string {
			return $this->object['title'] ?? '';
		}
		
		/**
		 * Get the slug of the entity
		 * @uses mkurl()
		 * @return string
		 */
		public function getSlug(): string {
			return Str::mkurl(
				$this->getTitle(),
				'-',
				true,
				'entity'
			);
		}
		
		/**
		 * Get the URL of the entity
		 * @param string $sub_section Optional. Sub section of the entity
		 * @param int|false $sub_section_id Optional. ID of the sub section
		 * @return string
		 * @uses site_url()
		 */
		public function getUrl(
			string $sub_section='',
			int|false $sub_section_id=false
		): string {
			$slug = $this->getSlug();
			
			$uri_path = "/entity/". $this->type()->getSlug() ."/". $this->object['id'] ."-". $slug ."/";
			
			if(!empty($sub_section) && preg_match("#^[A-Za-z0-9\-_]+#si", $sub_section) && ("" !== ($sub_section = strtolower(trim($sub_section, " /"))))) {
				$uri_path .= $sub_section ."/";
				
				if(!empty($sub_section_id) && is_numeric($sub_section_id)) {
					$uri_path .= $sub_section_id ."/";
				}
			}
			
			return site_url($uri_path);
		}
		
		/**
		 * Get the background URL of the entity
		 * @param string $default Optional. Default URL to return if no background is found
		 * @return string
		 */
		public function getBackgroundUrl(string $default=""): string {
			$medias = $this->getMedia();
			
			if(empty($medias)) {
				return $default;
			}
			
			// first try to use for "Snapshot for ..."
			foreach($medias as $media) {
				if(preg_match("#^Snapshot for #si", $media->getTitle())) {
					return $media->getCDNUrl();
				}
			}
			
			// then try to use for "Box art for ..."
			if(empty($background_url)) {
				foreach($medias as $media) {
					if(preg_match("#^Box art for #si", $media->getTitle())) {
						return $media->getCDNUrl();
					}
				}
			}
			
			return $default;
		}
		
		/**
		 * Get all external IDs
		 * @return array
		 */
		public function getExternalIDs(): array {
			$raw_ext_ids = $this->db->get_col_assoc("
				SELECT
					entity_external_id.key, entity_external_id.value
				FROM `entity_external_id`
				WHERE
					entity_external_id.entity_id = :id
			", [
				'id' => $this->object['id'],
			], 'key', 'value');
			
			if(empty($raw_ext_ids)) {
				return [];
			}
			
			$ext_ids = [];
			
			foreach($raw_ext_ids as $raw_ext_key => $raw_ext_value) {
				if("" === ($raw_ext_key = strtolower(trim($raw_ext_key)))) {
					continue;
				}
				
				//$ext_ids[ strtolower(trim($raw_ext_key)) ] = trim($raw_ext_value);
				$ext_ids[ $raw_ext_key ] = JSON::maybe_decode($raw_ext_value);
				
				if(is_array($ext_ids[ $raw_ext_key ])) {
					$ext_ids[ $raw_ext_key ] = array_map("trim", $ext_ids[ $raw_ext_key ]);
				} else {
					$ext_ids[ $raw_ext_key ] = trim($ext_ids[ $raw_ext_key ]);
				}
			}
			
			return $ext_ids;
		}
		
		/**
		 * Get an external ID
		 * @param string $entity_key Key of the external ID
		 * @return string|false
		 */
		public function getExternalID(string $entity_key): string|false {
			$ext_ids = $this->getExternalIDs();
			
			return $ext_ids[ $entity_key ] ?? false;
		}
	}