<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method to(string $path, array $params=[]): string
	 * @method from(string $url): Magnetar\Router\URLBuilder
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