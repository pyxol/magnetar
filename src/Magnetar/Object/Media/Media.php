<?php
	declare(strict_types=1);
	
	namespace Magnetar\Object\Media;
	
	use Exception;
	
	use Magnetar\Database\AbstractDatabase;
	use Magnetar\Object\AbstractObject;
	use Magnetar\Object\MediaFile\MediaFile;
	
	class Media extends AbstractObject {
		protected ?AbstractDatabase $db = null;
		
		protected ?array $files = null;
		protected ?string $file_type = null;
		
		/**
		 * Pull the media from the database
		 * @return void
		 * @throws Exception
		 */
		protected function pullObject(int $id): void {
			$this->object = $this->db->get_row("
				SELECT
					media.*
				FROM `media`
				WHERE
					media.id = :id
			", [
				'id' => $id,
			]);
			
			if(empty($this->object['id'])) {
				throw new Exception("Media not found");
			}
		}
		
		/**
		 * Get the array of files attached to this media
		 * @return array
		 */
		public function getFiles() {
			if(!is_null($this->files)) {
				return $this->files;
			}
			
			if(empty($this->id)) {
				throw new Exception("Media ID not in memory while fetching files");
			}
			
			$this->files = [];
			
			$raw_file_ids = $this->db->get_col("
				SELECT
					media_file.id
				FROM `media_file`
				WHERE
					media_file.media_id = :media_id
			", [
				'media_id' => $this->id,
			]);
			
			if(!empty($raw_file_ids)) {
				foreach($raw_file_ids as $raw_file_id) {
					$this->files[ $raw_file_id ] = new MediaFile($raw_file_id, $this->db);
				}
			}
			
			return $this->files;
		}
		
		/**
		 * Get the type of file (image|video|unknown)
		 * @return string
		 */
		public function getFileType() {
			if(!is_null($this->file_type)) {
				return $this->file_type;
			}
			
			$this->getFiles();
			
			if(empty($this->files)) {
				return $this->file_type = "unknown";
			}
			
			foreach($this->files as $file) {
				$content_type = $file->getContentType();
				
				if(preg_match("#^image/#si", $content_type)) {
					return $this->file_type = "image";
				}
				
				if(preg_match("#^video/#si", $content_type)) {
					return $this->file_type = "video";
				}
			}
			
			return $this->file_type = "unknown";
		}
		
		/**
		 * Get the title
		 * @return string
		 */
		public function getTitle() {
			return $this->object['title'];
		}
		
		/**
		 * Get the description
		 * @return string
		 */
		public function getDescription() {
			return $this->object['description'];
		}
		
		/**
		 * Get the CDN URL of the first file of this media
		 * @param mixed $default Return this if no URL is found
		 * @return mixed
		 */
		public function getCDNUrl(mixed $default=false): mixed {
			$files = $this->getFiles();
			
			if(empty($files)) {
				return $default;
			}
			
			$found_file = false;
			
			foreach($files as $file) {
				$found_file = $file->getUrl();
				
				if($found_file) {
					return $found_file;
				}
			}
			
			return $default;
		}
		
		/**
		 * Get a thumbnail CDN URL of the first file of this media
		 * @param mixed $default Return this if no media file is found
		 * @param int|false $width Optional width of the thumbnail
		 * @param int|false $height Optional height of the thumbnail
		 * @param mixed $default Return this if no URL is found
		 * @return mixed
		 */
		public function getThumb(int|false $width=false, int|false $height=false, mixed $default=false): mixed {
			$width = ((!empty($width) && is_numeric($width))?$width:false);
			$height = ((!empty($height) && is_numeric($height))?$height:false);
			
			if(empty($width) && empty($height)) {
				return $this->getCDNUrl();
			}
			
			$files = $this->getFiles();
			
			if(empty($files)) {
				return $default;
			}
			
			foreach($files as $file) {
				$thumb_uri = $file->getThumb($width, $height);
				
				if(!empty($thumb_uri)) {
					return $thumb_uri;
				}
			}
			
			return $default;
		}
		
		/**
		 * Get the content type
		 * @return string
		 */
		public function getContentType(): string {
			return $this->object['content_type'];
		}
	}