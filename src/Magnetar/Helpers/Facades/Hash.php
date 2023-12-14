<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method static \Magnetar\Hashing\Hasher driver(?string $driver)
	 * @method static string getDefaultDriver()
	 * @method static string hash(string $string)
	 * @method static bool verify(string $string, string $hash)
	 * @method static array info(string $hashValue)
	 * @method static bool needsRehash(string $hashValue, ?array $override_options)
	 * 
	 * @see \Magnetar\Hashing\HashingManager
	 * @see \Magnetar\Hashing\Hasher
	 */
	class Hash extends Facade {
		/**
		 * Get the named key that this facade represents
		 * @return string
		 */
		protected static function getFacadeKey(): string {
			return 'hashing';
		}
	}