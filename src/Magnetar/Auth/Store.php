<?php
	declare(strict_types=1);
	
	namespace Magnetar\Auth;
	
	use Magnetar\Auth\Exceptions\AuthStorageException;
	
	/**
	 * Session data storage adapter made to be extended by other adapters
	 */
	class Store {
		/**
		 * Name of the adapter
		 */
		const ADAPTER_NAME = '';
		
		/**
		 * Whether or not the adapter has been initialized. Prevents certain methods from being called after initialization
		 * @var bool
		 */
		protected bool $initialized = false;
		
		/**
		 * Namespace path to class for model to use for authentication
		 * @var string|null
		 */
		protected string|null $model_class=null;
		
		/**
		 * Constructor
		 */
		public function __construct(
			protected string $connection_name,
			protected array $connection_config = []
		) {
			$this->initialize();
		}
		
		/**
		 * Initialize the adapter
		 * @return void
		 */
		protected function initialize(): void {
			if($this->initialized) {
				return;
			}
			
			// set the initialized flag
			$this->initialized = true;
			
			// pull the configuration and check if it is valid
			$this->validation();
			
			// create the connection
			$this->bootup();
		}
		
		/**
		 * Validate runtime configuration
		 * @return void
		 * 
		 * @throws RuntimeException
		 * @throws \Magnetar\Auth\Exceptions\AuthStorageException
		 */
		protected function validation(): void {
			// individual adapters are encouraged to override this method
		}
		
		/**
		 * Boot up adapter using the configuration
		 * @return void
		 * 
		 * @throws RuntimeException
		 * @throws AuthStorageException
		 */
		protected function bootup(): void {
			// individual adapters must override this method
			throw new AuthStorageException('Authentication adapter needs to override the createConnection method.');
		}
	}