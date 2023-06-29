<?php
	declare(strict_types=1);
	
	namespace Magnetar\Config;
	
	class DatabaseConfig extends AbstractAutoinjectConfig {
		protected string $autoInjectConfigKey = 'database';
	}