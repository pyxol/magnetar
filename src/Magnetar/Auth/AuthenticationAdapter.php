<?php
	declare(strict_types=1);
	
	namespace Magnetar\Auth;
	
	use Magnetar\Auth\Exceptions\AuthenticationAdapterException;
	
	class AuthenticationAdapter {
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
			$this->validateRuntime();
			
			// create the connection
			$this->createConnection();
			
			// run any post connection actions
			$this->postConnection();
		}
		
		/**
		 * Validate runtime configuration
		 * @return void
		 * 
		 * @throws RuntimeException
		 * @throws AuthenticationAdapterException
		 */
		protected function validateRuntime(): void {
			// individual adapters are encouraged to override this method
		}
		
		/**
		 * Create the connection to the adapter using the configuration
		 * @return void
		 * 
		 * @throws RuntimeException
		 * @throws AuthenticationAdapterException
		 */
		protected function createConnection(): void {
			// individual adapters must override this method
			throw new AuthenticationAdapterException('Authentication adapter needs to override the createConnection method.');
		}
		
		/**
		 * Post connection actions
		 * @return void
		 */
		protected function postConnection(): void {
			// individual adapters may override this method
		}
		
		/**
		 * Get the adapter name
		 * @return string The name of the adapter
		 */
		public function getAdapterName(): string {
			return self::ADAPTER_NAME;
		}
		
		/**
		 * Set the model class to use for authentication
		 * @param string $model_class
		 * @return void
		 */
		public function setModelClass(string $model_class): void {
			if($this->initialized) {
				throw new Exceptions\AlreadyInitializedException('Cannot set model class after initialization');
			}
			
			$this->model_class = $model_class;
		}
	}