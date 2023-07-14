<?php
	declare(strict_types=1);
	
	namespace Magnetar\Log;
	
	use BadMethodCallException;
	
	use Magnetar\Container\Container;
	
	class Logger {
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
		
		protected array $logs = [];
		
		//protected static ?Logger $logger = null;
		
		public function __construct(Container $container) {
			//if(null === static::$logger) {
			//	static::$logger = $container->instance('logger', $this);
			//}
		}
		
		/**
		 * Logs a message
		 * @param string $level
		 * @param string $message
		 * @param array $context
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
			if($print) {
				ob_start();
			}
			
			jbdump($this->getLogs($minLevel), false, 'Logger::dump');
			
			if($return) {
				return ob_get_clean();
			}
		}
		
		/**
		 * Magic method to log messages. Throws a BadMethodCallException if method isn't a known log level
		 * @param string $method
		 * @param array $args
		 * @return void
		 * 
		 * @throws BadMethodCallException
		 */
		public function __call(string $method, array $args): void {
			if(isset($this->logLevels[ $method ])) {
				$this->log($method, ...$args);
				
				return;
			}
			
			throw new BadMethodCallException('Method '. $method .' does not exist');
		}
	}