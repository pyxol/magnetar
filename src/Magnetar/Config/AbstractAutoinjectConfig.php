<?php
	declare(strict_types=1);
	
	namespace Magnetar\Config;
	
	abstract class AbstractAutoinjectConfig extends Config {
		protected string $autoInjectConfigKey;
		
		public function __construct() {
			// set the config
			$this->load($this->autoInjectConfigKey);
		}
	}