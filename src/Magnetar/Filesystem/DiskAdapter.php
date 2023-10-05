<?php
	declare(strict_types=1);
	
	namespace Magnetar\Filesystem;
	
	use RuntimeException;
	
	use Magnetar\Filesystem\Exceptions\DiskAdapterException;
	
	/**
	 * Disk adapter
	 */
	class DiskAdapter {
		/**
		 * Name of the adapter
		 */
		const ADAPTER_NAME = '';
		
		/**
		 * Adapter constructor
		 * @param string $connection_name Name of the connection
		 * @param array $configuration Configuration data to wire up the connection
		 * 
		 * @throws RuntimeException
		 * @throws DiskAdapterException
		 */
		public function __construct(
			protected string $connection_name,
			protected array $connection_config = []
		) {
			// wire up to DB instance
			$this->wireUp();
		}
		
		/**
		 * Start the connection
		 * @return void
		 * 
		 * @throws RuntimeException
		 * @throws DiskAdapterException
		 */
		protected function wireUp(): self {
			// pull the configuration and check if it is valid
			$this->validateRuntime();
			
			// create the connection
			$this->createConnection();
			
			// run any post connection actions
			$this->postConnection();
			
			return $this;
		}
		
		/**
		 * Validate runtime configuration
		 * return void
		 * 
		 * @throws RuntimeException
		 * @throws DiskAdapterException
		 */
		protected function validateRuntime(): void {
			// individual adapters are encouraged to override this method
		}
		
		/**
		 * Create the connection to the disk (if necessary)
		 * @return void
		 * 
		 * throws DiskAdapterException
		 */
		protected function createConnection(): void {
			// individual adapters should override this method
			throw new DiskAdapterException("Do not use the base DiskAdapter class directly. Use a specific adapter instead.");
		}
		
		/**
		 * Post connection actions (typically character set)
		 * @return void
		 */
		protected function postConnection(): void {
			// individual adapters may override this method
		}
	}