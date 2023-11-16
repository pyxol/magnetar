<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method static \Magnetar\Http\Response processRequest(\Magnetar\Http\Request $request)
	 * @method static void attachContext(\Magnetar\Router\RouteCollection $collection)
	 * @method static void detachContext()
	 * @method static bool isTrailingSlashOptional()
	 * @method static \Magnetar\Router\Route any(string $pattern, callable|array|string|null $callback)
	 * @method static \Magnetar\Router\Route get(string $pattern, callable|array|string|null $callback)
	 * @method static \Magnetar\Router\Route post(string $pattern, callable|array|string|null $callback)
	 * @method static \Magnetar\Router\Route put(string $pattern, callable|array|string|null $callback)
	 * @method static \Magnetar\Router\Route patch(string $pattern, callable|array|string|null $callback)
	 * @method static \Magnetar\Router\Route delete(string $pattern, callable|array|string|null $callback)
	 * @method static \Magnetar\Router\Route options(string $pattern, callable|array|string|null $callback)
	 * @method static \Magnetar\Router\RouteCollection group(string $pathPrefix, callable $callback)
	 * @method static \Magnetar\Router\Route redirect(string $pattern, string $redirect_path, int $response_code)
	 * @method static \Magnetar\Router\Route permanentRedirect(string $pattern, string $redirect_path)
	 * 
	 * @see \Magnetar\Router\Router
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