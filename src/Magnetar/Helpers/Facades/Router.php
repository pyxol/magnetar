<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method processRequest(Magnetar\Http\Request $request): Magnetar\Http\Response
	 * @method attachContext(Magnetar\Router\RouteCollection $collection): void
	 * @method detachContext(): void
	 * @method isTrailingSlashOptional(): bool
	 * @method any(string $pattern, callable|array|string|null $callback=null): Magnetar\Router\Route
	 * @method get(string $pattern, callable|array|string|null $callback=null): Magnetar\Router\Route
	 * @method post(string $pattern, callable|array|string|null $callback=null): Magnetar\Router\Route
	 * @method put(string $pattern, callable|array|string|null $callback=null): Magnetar\Router\Route
	 * @method patch(string $pattern, callable|array|string|null $callback=null): Magnetar\Router\Route
	 * @method delete(string $pattern, callable|array|string|null $callback=null): Magnetar\Router\Route
	 * @method options(string $pattern, callable|array|string|null $callback=null): Magnetar\Router\Route
	 * @method group(string $pathPrefix, callable $callback): Magnetar\Router\RouteCollection
	 * @method redirect(string $pattern, string $redirect_path, int $response_code=302): Magnetar\Router\Route
	 * @method permanentRedirect(string $pattern, string $redirect_path): Magnetar\Router\Route
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