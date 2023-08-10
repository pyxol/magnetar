<?php
	declare(strict_types=1);
	
	namespace Magnetar\Console;
	
	class Input {
		/**
		 * Constructor
		 * @param array $arguments Command line arguments
		 */
		public function __construct(
			protected array $arguments=[]
		) {
			
		}
	}