<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method create(): Magnetar\Http\Request;
	 * @method headers(): array;
	 * @method header(string $name): ?string;
	 * @method hasHeader(string $name): bool;
	 * @method path(): string;
	 * @method getMethod(): ?string;
	 * @method assignOverrideParameters(array $parameters): void;
	 * @method getParameter(string $name, mixed $default=null): mixed;
	 * @method getParameters(): array;
	 * @method body(): string;
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