<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method create(): Magnetar\Http\Request
	 * @method headers(): array
	 * @method header(string $name): ?string
	 * @method hasHeader(string $name): bool
	 * @method path(): string
	 * @method method(): ?Magnetar\Router\Enums\HTTPMethodEnum
	 * @method assignOverrideParameters(array $parameters): void
	 * @method parameter(string $name, mixed $default=null): mixed
	 * @method get(string $name, mixed $default=null): mixed
	 * @method parameters(): array
	 * @method body(): string
	 * @method file(string $input_name): Magnetar\Http\UploadedFile|array|null
	 * @method files(): array
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