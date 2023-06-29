<?php
	declare(strict_types=1);
	
	namespace Magnetar\Database;
	
	trait DatabaseTrait {
		/**
		 * Start the database-specific connection
		 * @return void
		 */
		abstract protected function wireUp(): void;
	}