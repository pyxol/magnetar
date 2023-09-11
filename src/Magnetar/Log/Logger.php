<?php
	declare(strict_types=1);
	
	namespace Magnetar\Log;
	
	use BadMethodCallException;
	
	use Magnetar\Container\Container;
	
	/**
	 * Class to log messages during runtime.
	 * Allows for log level names to be used as methods for logging messages.
	 * Example: Logger::info('This is an info message');
	 * 
	 * @todo Add support for logging to file
	 */
	class Logger {
		/**
		 * Log levels
		 * @var array
		 */
		protected array $logLevels = [
			'emergency' => 1,
			'alert' => 2,
			'critical' => 3,
			'error' => 4,
			'warning' => 5,
			'notice' => 6,
			'info' => 7,
			'debug' => 8,
		];
		
		/**
		 * The running logs array
		 * @var array
		 */
		protected array $logs = [];
		
		public function __construct(
			/**
			 * The application container
			 * @var Container
			 */
			protected Container $container
		) {
			
		}
		
		/**
		 * Logs a message
		 * @param string $level The log level to use
		 * @param string $message The message to log
		 * @param array $context The context to log
		 * @return void
		 */
		public function log(string $level, string $message, array $context=[]): void {
			$this->logs[] = [
				'level' => $level,
				'message' => $message,
				'context' => $context,
			];
		}
		
		/**
		 * Gets the logs
		 * @param int $minLevel The minimum log level to get
		 * @return array
		 */
		public function getLogs(int $minLevel=0): array {
			if($minLevel > 0) {
				return array_filter($this->logs, function($log) use ($minLevel) {
					return ($log['level'] >= $minLevel);
				});
			}
			
			return $this->logs;
		}
		
		/**
		 * Dumps the logs to the screen
		 * @param int $minLevel The minimum log level to dump
		 * @param bool $return Set to true to return the logs instead of printing them
		 * @return mixed
		 */
		public function dump(int $minLevel=0, bool $return=false): mixed {
			if($return) {
				ob_start();
			}
			
			//if(function_exists('jbdump')) {
			//	jbdump($this->getLogs($minLevel), false, 'Logger::dump');
			//} else {
				print "<pre>". htmlentities(print_r($this->getLogs($minLevel), true)) ."</pre>";
			//}
			
			if($return) {
				return ob_get_clean();
			}
		}
		
		/**
		 * Magic method to log messages. Throws a BadMethodCallException if method isn't a known log level
		 * @param string $method The method to call
		 * @param array $args The arguments to pass to the method
		 * @return void
		 * 
		 * @throws \BadMethodCallException
		 */
		public function __call(string $method, array $args): void {
			if(isset($this->logLevels[ $method ])) {
				$this->log($method, ...$args);
				
				return;
			}
			
			throw new BadMethodCallException('Method '. $method .' does not exist');
		}
	}