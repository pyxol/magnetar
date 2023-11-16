<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method static void log(string $level, string $message, array $context)
	 * @method static array getLogs(int $minLevel)
	 * @method static mixed dump(int $minLevel, bool $return)
	 * 
	 * @see \Magnetar\Log\Logger
	 */
	class Log extends Facade {
		/**
		 * Get the named key that this facade represents
		 * @return string
		 */
		protected static function getFacadeKey(): string {
			return 'logger';
		}
	}