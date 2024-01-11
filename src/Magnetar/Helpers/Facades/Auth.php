<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method static bool attempt(\Magnetar\Http\Request|array|null $credentials)
	 * @method static void actAs(\Magnetar\Model\Model $user)
	 * @method static bool check()
	 * @method static ?\Magnetar\Auth\User user()
	 * @method static string|int id()
	 * @method static void logout()
	 * @method static bool remember()
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