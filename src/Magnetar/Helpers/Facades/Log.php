<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method log(string $level, string $message, array $context=[]): void
	 * @method getLogs(int $minLevel=0): array
	 * @method dump(int $minLevel=0, bool $return=false): mixed
	 * 
	 * @see Magnetar\Log\Logger
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