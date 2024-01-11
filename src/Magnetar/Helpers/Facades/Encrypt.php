<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method static string generateKey()
	 * @method static string encrypt(string $string, bool $serialize)
	 * @method static mixed decrypt(string $payload, bool $unserialize)
	 * 
	 * @see \Magnetar\Encryption\Encryption
	 */
	class Encrypt extends Facade {
		/**
		 * Get the named key that this facade represents
		 * @return string
		 */
		protected static function getFacadeKey(): string {
			return 'encryption';
		}
	}