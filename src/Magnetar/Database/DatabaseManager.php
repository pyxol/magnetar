<?php
	declare(strict_types=1);
	
	namespace Magnetar\Database;
	
	use Magnetar\Application;
	
	class DatabaseManager {
		protected Application $app;
		
		protected array $connections = [];
		
		public function __construct(Application $app) {
			$this->app = $app;
			
			// @TODO
			
			// take app->config->get('database') and create connection
		}
	}