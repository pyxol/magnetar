<?php
	declare(strict_types=1);
	
	namespace Magnetar\Console;
	
	/**
	 * Command line output
	 */
	class Output {
		/**
		 * Constructor
		 * @param string|null|null $data The result data from a command line execution
		 */
		public function __construct(
			protected string|null $data=null
		) {
			
		}
	}