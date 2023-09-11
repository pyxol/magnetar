<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method group(string $prefixPath, callable $callback): void;
	 * @method processRequest(Magnetar\Http\Request $request): Magnetar\Http\Response;
	 * @method any(string $pattern, callable|array|null $callback=null): void;
	 * @method get(string $pattern, callable|array|null $callback=null): void;
	 * @method post(string $pattern, callable|array|null $callback=null): void;
	 * @method put(string $pattern, callable|array|null $callback=null): void;
	 * @method patch(string $pattern, callable|array|null $callback=null): void;
	 * @method delete(string $pattern, callable|array|null $callback=null): void;
	 * @method head(string $pattern, callable|array|null $callback=null): void;
	 * @method option(string $pattern, callable|array|null $callback=null): void;
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