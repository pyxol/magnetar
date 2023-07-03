<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http\Controller;
	
	use Magnetar\Kernel\Kernel;
	use Magnetar\Config\DatabaseConfig;
	use Magnetar\Database\MariaDB\Database;
	
	class Controller {
		protected Kernel $app;
		protected ?Database $database = null;
		
		// @TODO needs more work
		public function __construct(Kernel $kernel) {
			// assign app
			$this->app = $kernel;
		}
		
		/**
		 * Get the database connection
		 * @return Database
		 */
		protected function db(): Database {
			// get the database connection
			if(is_null($this->database)) {
				$this->database = new Database(
					new DatabaseConfig()
				);
			}
			
			return $this->database;
		}
	}