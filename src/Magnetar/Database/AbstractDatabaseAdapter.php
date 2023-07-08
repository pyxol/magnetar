<?php
	declare(strict_types=1);
	
	namespace Magnetar\Database;
	
	use \Exception;
	
	use Magnetar\Container\Container;
	
	abstract class AbstractDatabaseAdapter implements DatabaseInterface {
		/**
		 * Connect to a MariaDB database
		 * @param array $configuration Configuration to pass to the database adapter
		 * 
		 * @throws Exception
		 */
		public function __construct(Container $container) {
			// wire up to DB instance
			$this->wireUp($container);
		}
		
		abstract protected function wireUp(Container $container): void;
	}