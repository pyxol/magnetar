<?php
	declare(strict_types=1);
	
	namespace Magnetar\Object\MediaFile;
	
	use Exception;
	
	use Beeyev\Thumbor\Thumbor;
	use Aws\S3\S3Client;
	use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
	use League\Flysystem\Filesystem;
	
	use Magnetar\Database\AbstractDatabase;
	use Magnetar\Object\AbstractObject;
	
	class MediaFile extends AbstractObject {
		protected ?AbstractDatabase $db = null;
		
		/**
		 * Get the entity
		 * @return void
		 * @throws Exception
		 */
		protected function pullObject(int $id): void {
			$this->object = $this->db->get_row("
				SELECT
					media_file.*
				FROM `media_file`
				WHERE
					media_file.id = :id
			", [
				'id' => $id,
			]);
			
			if(empty($this->object['id'])) {
				throw new Exception("Media File #". $id ." not found");
			}
			
			// parse meta data
			$this->object['meta_data'] = (!empty($this->object['meta_data'])?maybe_json_decode($this->object['meta_data']):[]);
			
			// parse cargo
			$this->object['cargo'] = (!empty($this->object['cargo'])?maybe_json_decode($this->object['cargo']):[]);
		}
		
		/**
		 * Get the path to the file relative to the CDN directory
		 * @return string
		 */
		public function getRelPath() {
			return trim($this->object['folder'], "/") ."/". ltrim($this->object['file_name'], "/");
		}
		
		/**
		 * Get the URL to the file
		 * @return string
		 */
		public function getUrl() {
			if(!empty($this->object['s3_bucket']) && !empty($this->object['s3_key'])) {
				// show s3-based uri
				return $this->getS3Uri($this->object['s3_bucket'], $this->object['s3_key']);
			}
			
			return cdn_url($this->getRelPath());
		}
		
		/**
		 * Get the URL to a thumbnail for this file. Returns false on error
		 * @param int|null $width Desired width of thumbnail
		 * @param int|null $height Desired height of thumbnail
		 * @return string|false
		 * @see https://github.com/cshum/imagor
		 * @see https://github.com/cshum/imagorvideo
		 */
		public function getThumb($width=THUMB_WIDTH, $height=null): string|false {
			try {
				if($this->isVideo()) {
					// videos
					// https://github.com/cshum/imagorvideo
					$thumbor = new Thumbor(THUMB_VIDEO_URL, THUMB_VIDEO_HASH_KEY);
					//$thumbor->addFilter('strip_icc');
					$thumbor->addFilter('seek', "0.1");
					//$thumbor->addFilter('blur', 1);
					
					$thumbor->resize($width, $height);
					
					//$thumbor->smartCropEnable();
					
					$thumbor->imageUrl($this->object['s3_key']);
					
					return $thumbor->get();
				}
				
				
				// images
				// https://github.com/cshum/imagor
				
				$thumbor = new Thumbor(THUMB_IMAGE_URL, THUMB_IMAGE_HASH_KEY);
				$thumbor->addFilter('strip_icc');
				
				if("gif" == get_file_ext($this->object['s3_key'])) {
					$thumbor->addFilter('format', "jpg");   // jpg=disables animation from webp/gif
				} else {
					$thumbor->addFilter('format', "webp");
				}
				
				//$thumbor->addFilter('blur', 1);
				$thumbor->resize($width, $height);
				$thumbor->smartCropEnable();
				
				$thumbor->imageUrl($this->object['s3_key']);
				
				return $thumbor->get();
			} catch(Exception $e) {
				return false;
			}
		}
		
		/**
		 * Get the filename
		 * @return string
		 */
		public function getFileName() {
			return $this->object['file_name'];
		}
		
		/**
		 * Get the file extension
		 * @throws Exception
		 * @return string
		 */
		public function getFileExt() {
			return get_file_ext($this->object['file_name']);
		}
		
		/**
		 * Get the content type
		 * @return string
		 */
		public function getContentType() {
			return $this->object['content_type'];
		}
		
		/**
		 * Get the file size
		 * @return int
		 */
		public function getFileSize() {
			return (int)$this->object['file_size'];
		}
		
		private function _getArrayedData($data_key, $specific_key=false, $default=null) {
			if(!isset($this->object[ $data_key ]) || !is_array($this->object[ $data_key ])) {
				return $default;
			}
			
			if(false !== $specific_key) {
				if(isset($this->object[ $data_key ][ $specific_key ])) {
					return $this->object[ $data_key ][ $specific_key ];
				}
				
				return $default;
			}
			
			return $this->object[ $data_key ];
		}
		
		/**
		 * Get metadata associated with the file
		 * @param string|false $specific_key Optional. Set to the meta key to return a single row
		 * @param mixed $default Return this if $specific_key is not found
		 * @return mixed
		 */
		public function getMetaData($specific_key=false, $default=null) {
			return $this->_getArrayedData('meta_data', $specific_key, $default);
		}
		
		/**
		 * Get cargo associated with the file
		 * @param string|false $specific_key Optional. Set to the cargo key to return a single row
		 * @param mixed $default Return this if $specific_key is not found
		 * @return mixed
		 */
		public function getCargo($specific_key=false, $default=null) {
			return $this->_getArrayedData('cargo', $specific_key, $default);
		}
		
		/**
		 * Get the timestamp this file was created
		 * @param string|false $date_format Set to date() format to format the date instead of returning the timestamp
		 * @return int|string
		 */
		public function getTimeCreated($date_format=false) {
			if($date_format && is_string($date_format)) {
				return date($date_format, $this->object['time_created']);
			}
			
			return $this->object['time_created'];
		}
		
		/**
		 * Get the timestamp this file was last updated
		 * @param string|false $date_format Set to date() format to format the date instead of returning the timestamp
		 * @return int|string
		 */
		public function getTimeUpdated($date_format=false, $default=false) {
			if(is_null($this->object['time_updated']) || ("" === $this->object['time_updated'])) {
				return $default;
			}
			
			if($date_format && is_string($date_format)) {
				return date($date_format, $this->object['time_updated']);
			}
			
			return $this->object['time_updated'];
		}
		
		/**
		 * Get the status of this file
		 * @return string
		 */
		public function getStatus() {
			return $this->object['status'];
		}
		
		/**
		 * Determine if this file is a video
		 * @return bool
		 */
		public function isVideo() {
			return in_array($this->getFileExt(), known_video_file_exts());
		}
		
		/**
		 * Determine if this file is an image
		 * @return bool
		 */
		public function isImage() {
			return in_array($this->getFileExt(), known_image_file_exts());
		}
		
		/**
		 * Determine if this file is an image
		 * @return bool
		 */
		public function isUnknownType() {
			return (!in_array($this->getFileExt(), known_image_file_exts()) && !in_array($this->getFileExt(), known_video_file_exts()));
		}
		
		/**
		 * Accept an S3 bucket name and S3 relative path and provide a public URL
		 * @param string $s3_bucket S3 bucket name
		 * @param string $s3_path S3 relative path
		 * @return string
		 */
		private function getS3Uri($s3_bucket, $s3_path) {
			// connect to s3 storage
			$s3 = new S3Client([
				'version' => "latest",
				'region' => S3_REGION,
				'endpoint' => S3_ENDPOINT,
				'use_path_style_endpoint' => true,
				'credentials' => [
					'key' => S3_ACCESS_KEY,
					'secret' => S3_SECRET_KEY,
				],
			]);
			
			// make adapter
			$s3_adapter = new AwsS3V3Adapter($s3, $s3_bucket);
			$s3_filesystem = new Filesystem($s3_adapter, [
				'public_url' => S3_PUBLIC_ENDPOINT . $s3_bucket,
			]);
			
			return $s3_filesystem->publicUrl($s3_path);
		}
	}