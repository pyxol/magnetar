<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers;
	
	class DefaultFacadeAliases {
		/**
		 * The default list of facade aliases
		 * @var array
		 */
		protected array $aliases = [];
		
		/**
		 * Constructor method
		 * @param array|null $aliases
		 */
		public function __construct(
			?array $aliases=null
		) {
			$this->aliases = $aliases ?? [
				'App' => Facades\App::class,
				'Auth' => Facades\Auth::class,
				'Cache' => Facades\Cache::class,
				'Config' => Facades\Config::class,
				'Cookie' => Facades\Cookie::class,
				'DB' => Facades\DB::class,
				'Encrypt' => Facades\Encrypt::class,
				'File' => Facades\File::class,
				'Hash' => Facades\Hash::class,
				'Log' => Facades\Log::class,
				'Pipeline' => Facades\Pipeline::class,
				'Queue' => Facades\Queue::class,
				'Request' => Facades\Request::class,
				'Response' => Facades\Response::class,
				'Router' => Facades\Router::class,
				'Theme' => Facades\Theme::class,
				'URL' => Facades\URL::class,
			];
		}
		
		/**
		 * Add more facade aliases to the default list
		 * @param array $aliases
		 * @return self
		 */
		public function merge(array $aliases): self {
			$this->aliases = array_merge($this->aliases, $aliases);
			
			return new static($this->aliases);
		}
		
		/**
		 * Get an array of default facade aliases
		 */
		public function toArray(): array {
			return $this->aliases;
		}
	}