<?php
	namespace Magnetar;
	
	use \api as api;
	
	class Media extends Abstract_Flash {
		use Trait_Flash;
		
		// Flash settings
		private static $flash__cache_expiry	= 604800;   // in seconds (default: 15 minutes)
		private static $flash__key_hash		= true;   // md5() cache key
		private static $flash__key_prefix	= "new.jungledb.dev:media:by_id:";
		private static $flash__key_suffix	= "";
		
		
		//private $media_id;
		
		public function __construct($media_id) {
			//$this->media_id = $media_id;
			
			return $this->flash__init($media_id);
		}
		
		protected function flash__process($media_ids) {
			return api::db()->get_results("
				SELECT *
				FROM `media`
				WHERE
					`id` IN ('". implode("', '", $media_ids) ."')
			", 'id');
		}
		
		
		// Data
		
		//public function getId() {
		//	return $this->media_id;
		//}
		
		public function getStatus() {
			return $this->flash__get('status');
		}
		
		public function getDateCreated() {
			return $this->flash__get('date_created');
		}
		
		public function getDateUpdated() {
			return $this->flash__get('date_updated');
		}
		
		public function getDateDownloaded() {
			return $this->flash__get('date_downloaded');
		}
		
		public function getType() {
			return $this->flash__get('type');
		}
		
		public function getServerId() {
			return $this->flash__get('server_id');
		}
		
		public function getFolder() {
			return $this->flash__get('folder');
		}
		
		public function getFile() {
			return $this->flash__get('file');
		}
		
		public function getHash() {
			return $this->flash__get('hash');
		}
		
		public function getHashBinary() {
			return $this->flash__get('hash_binary');
		}
		
		public function getFileSize() {
			return $this->flash__get('file_size');
		}
		
		public function getFileMimeType() {
			return $this->flash__get('file_mime_type');
		}
		
		public function getFileMimeSubType() {
			return $this->flash__get('file_mime_subtype');
		}
		
		public function getFileMimeEncoding() {
			return $this->flash__get('file_mime_encoding');
		}
		
		public function getFileDetails() {
			return $this->flash__get('file_details');
		}
		
		public function getDescription() {
			return $this->flash__get('description');
		}
		
		public function getSourceType() {
			return $this->flash__get('source_type');
		}
		
		public function getSourceRaw() {
			return json_decode($this->flash__get('source_raw'), true);
		}
		
		public function getSourceUrl() {
			return $this->flash__get('source_url');
		}
		
		public function getSourceUrlHash() {
			return $this->flash__get('source_url_hash');
		}
		
		
		// Dynamic methods
		public function getThumb($size="o") {
			return "//cdn-". str_pad(rand(1,3), 2, "0", STR_PAD_LEFT) .".jungledb.cdn/". $this->getFolder() ."/". (("o" !== $size)?preg_replace("#\.([A-Za-z]+)$#si", "_". $size .".\\1", $this->getFile()):$this->getFile());
		}
		
		
		// Other methods
		public function getServer() {
			return new Server( $this->getServerId() );
		}
	}