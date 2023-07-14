<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	/**
	 * @method getPath(): string
	 * @method assignOverrideParameters(array $parameters): void
	 * @method getParameter(string $name, mixed $default=null): mixed
	 * @method getParameters(): array
	 * @method getRoute(): ?Route
	 * @method setRoute(Route $route): void
	 * 
	 * @see Magnetar\Http\Request
	 */
	class Request extends Facade {
		/**
		 * Get the named key that this facade represents
		 * @return string
		 */
		protected static function getFacadeKey(): string {
			return 'request';
		}
	}