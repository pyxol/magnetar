<?php
	declare(strict_types=1);
	
	namespace Magnetar\Console;
	
	use \Exception;
	
	use Magnetar\Console\Output;
	
	/**
	 * Command line output error
	 */
	class ErrorOutput extends Output {
		/**
		 * Constructor
		 * @param string|null $data The result data from a command line execution
		 */
		public function __construct(
			protected string|null $data=null
		) {
			
		}
	}