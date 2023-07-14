<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	/**
	 * __construct(
	 * @method group(string $prefixPath, callable $callback): void
	 * @method get($pattern): bool
	 * @method post(string $pattern): bool
	 * @method put(string $pattern): bool
	 * @method patch(string $pattern): bool
	 * @method delete(string $pattern): bool
	 * @method head(string $pattern): bool
	 * @method option(string $pattern): bool
	 * @method any(string $pattern): bool
	 * 
	 * @see Magnetar\Router\Router
	 */
	class Router extends Facade {
		/**
		 * Get the named key that this facade represents
		 * @return string
		 */
		protected static function getFacadeKey(): string {
			return 'router';
		}
	}