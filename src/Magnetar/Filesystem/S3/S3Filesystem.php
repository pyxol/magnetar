<?php
	declare(strict_types=1);
	
	namespace Magnetar\Filesystem\S3;
	
	use Aws\S3\S3Client;
	use League\Flysystem\Filesystem;
	use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
	use Magnetar\Filesystem\Exceptions\DiskAdapterException;
	use Magnetar\Helpers\Logic;
	
	/**
	 * A wrapper for the Flysystem S3 filesystem adapter that allows connecting to multiple buckets
	 */
	class S3Filesystem {
		/**
		 * S3Client instance
		 * @var \Aws\S3\S3Client|null
		 */
		protected S3Client|null $s3_client = null;
		
		/**
		 * An array of Flysystem S3 filesystems adapters, keyed by bucket name
		 * @var array<string, \League\Flysystem\Filesystem>
		 */
		protected array $buckets = [];
		
		/**
		 * Constructor
		 * @param array $connection_config Connection configuration
		 */
		public function __construct(
			protected array $config
		) {
			
		}
		
		/**
		 * Validate the connection configuration
		 * @param array $config Connection configuration
		 * @return void
		 */
		public static function validateConfig(array $config): void {
			// check if the connection configuration has an access key
			if(!isset($config['key'])) {
				throw new DiskAdapterException('Access key not set');
			}
			
			// check if the connection configuration has an access secret
			if(!isset($config['secret'])) {
				throw new DiskAdapterException('Access secret not set');
			}
			
			// check if the connection configuration has a region
			if(!isset($config['region'])) {
				throw new DiskAdapterException('Region not set');
			}
			
			// check if the connection configuration has a default bucket
			if(!isset($config['bucket'])) {
				throw new DiskAdapterException('Default bucket name not set');
			}
			
			// optional:
			//// check if the connection configuration has an endpoint
			//if(!isset($config['endpoint'])) {
			//	throw new DiskAdapterException('Endpoint not set');
			//}
			
			// check if the connection configuration has a default bucket
			//if(!isset($config['use_path_style_endpoint'])) {
			//	throw new DiskAdapterException('Value for "use path style endpoint" not set');
			//}
		}
		
		/**
		 * Get the S3Client instance (create it if it doesn't exist)
		 * @return \Aws\S3\S3Client
		 */
		protected function getS3Client(): S3Client {
			// create the S3Client instance
			return $this->s3_client ??= new S3Client($this->prepareS3ClientConfig());
		}
		
		/**
		 * Prepares the S3Client configuration array
		 * @return array
		 */
		protected function prepareS3ClientConfig(): array {
			$cfg = [
				'version' => "latest",
				'region' => $this->config['region'],
				'credentials' => [
					'key' => $this->config['key'],
					'secret' => $this->config['secret'],
				],
			];
			
			if(isset($this->config['endpoint']) && !empty($this->config['endpoint'])) {
				$cfg['endpoint'] = $this->config['endpoint'];
			}
			
			if(isset($this->config['use_path_style_endpoint']) && Logic::isTrue( $this->config['use_path_style_endpoint'] )) {
				$cfg['use_path_style_endpoint'] = true;
			}
			
			return $cfg;
		}
		
		/**
		 * Make an AwsS3V3Adapter instance
		 * @param string $bucket_name
		 * @return AwsS3V3Adapter
		 */
		protected function makeBucketAdapter(string $bucket_name): AwsS3V3Adapter {
			return new AwsS3V3Adapter(
				$this->getS3Client(),
				$bucket_name
			);
		}
		
		/**
		 * Get the public endpoint URL for a bucket
		 * @param string $bucket_name
		 * @return string
		 */
		protected function publicEndpointURL(string $bucket_name): string {
			return ($this->config['url'] ?? $this->config['endpoint']) . $bucket_name;
		}
		
		/**
		 * Prepares the Flysystem filesystem configuration array
		 * @param string $bucket_name Bucket name
		 * @return array
		 */
		protected function prepareFilesystemConfig(string $bucket_name): array {
			return [
				'visibility' => 'public',
				'case_sensitive' => true,
				'public_url' => $this->publicEndpointURL($bucket_name),
			];
		}
		
		/**
		 * Connect to a bucket
		 * @param string $bucket_name
		 * @return \League\Flysystem\Filesystem
		 */
		protected function connectBucket(string $bucket_name): Filesystem {
			return $this->buckets[ $bucket_name ] = new Filesystem(
				$this->makeBucketAdapter($bucket_name),
				$this->prepareFilesystemConfig($bucket_name)
			);
		}
		
		/**
		 * Get the Flysystem filesystem instance for a bucket
		 * @param string|null $bucket_name Optional. Bucket name. Defaults to the default bucket name
		 * @return \League\Flysystem\Filesystem
		 */
		public function bucket(string|null $bucket_name=null): Filesystem {
			if(null === $bucket_name) {
				$bucket_name = $this->config['bucket'] ?? throw new DiskAdapterException('Default bucket name not set');
			}
			
			return $this->buckets[ $bucket_name ] ??= $this->connectBucket($bucket_name);
		}
		
		
		protected array $times = [];
		
		/**
		 * Passes method calls to the default bucket
		 * @param string $method The method name
		 * @param array $args The method arguments
		 * @return mixed
		 * 
		 * @see \League\Flysystem\Filesystem
		 */
		public function __call(string $method, array $args): mixed {
			$this->times[] = "Calling ". $method ." [". implode(', ', $args) ."] on default bucket\n";
			
			if(count($this->times) > 5) {
				print "<pre>". print_r($this->times, true) ."</pre>";
				die;
			}
			
			return $this->bucket()->$method(...$args);
		}
	}