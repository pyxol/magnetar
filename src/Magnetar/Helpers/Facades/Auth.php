<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method static bool attempt(mixed $credentials, bool $remember)
	 * @method static bool check()
	 * @method static \Magnetar\Auth\User user()
	 * @method static int id()
	 * @method static void logout()
	 * 
	 * @see \Magnetar\Auth\AuthManager
	 */
	class Auth extends Facade {
		/**
		 * Get the named key that this facade represents
		 * @return string
		 */
		protected static function getFacadeKey(): string {
			return 'auth';
		}
	}