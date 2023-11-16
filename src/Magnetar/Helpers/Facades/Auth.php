<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method static \Magnetar\Auth\AuthenticationAdapter connection(?string $connection_name)
	 * @method static ?string getDefaultConnectionName()
	 * @method static array getConnected()
	 * @method static \Magnetar\Auth\AuthenticationAdapter adapter(string $connection_name)
	 * @method static string getAdapterName()
	 * @method static void setModelClass(string $model_class)
	 * 
	 * @see \Magnetar\Auth\AuthManager
	 * @see \Magnetar\Auth\AuthenticationAdapter
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