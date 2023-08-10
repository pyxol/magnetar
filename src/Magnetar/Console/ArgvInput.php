<?php
	declare(strict_types=1);
	
	namespace Magnetar\Console;
	
	use Magnetar\Console\Input;
	
	class ArgvInput extends Input {
		/**
		 * Script name
		 * @var string|null
		 */
		protected string|null $script_name;
		
		/**
		 * Constructor
		 * @param array|null $arguments Command line arguments
		 */
		public function __construct(
			array|null $arguments=null
		) {
			$arguments ??= $_SERVER['argv'] ?? [];
			
			$this->script_name = array_shift($arguments);
			
			parent::__construct($arguments);
		}
	}