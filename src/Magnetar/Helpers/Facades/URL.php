<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method static string to(string $path, array $params)
	 * @method static \Magnetar\Router\URLBuilder from(string $url)
	 * 
	 * @see \Magnetar\Router\URLGenerator
	 */
	class URL extends Facade {
		/**
		 * Get the named key that this facade represents
		 * @return string
		 */
		protected static function getFacadeKey(): string {
			return 'urlgenerator';
		}
	}