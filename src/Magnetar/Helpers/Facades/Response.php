<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method responseCode(int $response_code=200): self
	 * @method body(string $body=''): self
	 * @method sendBody(): self
	 * @method setCookie(string $name, ?string $value=null, ?int $expires=null): self
	 * @method send(): self
	 * @method sendHeaders(): self
	 * @method header(string $header, ?string $value=null, int|bool $replace=true, ?int $response_code=0): self|string
	 * @method headers(): array
	 * @method setHeaders(array $headers): self
	 * @method removeHeader(string $name): self
	 * @method clearHeaders(): self
	 * @method getHeader(string $name): ?string
	 * @method hasHeader(string $name): bool
	 * @method sentHeaders(): bool
	 * @method sent(): bool
	 * @method redirect(string $path, int $response_code=302): self
	 * @method json(mixed $body): self
	 * 
	 * @see \Magnetar\Http\Response
	 */
	class Response extends Facade {
		/**
		 * Get the named key that this facade represents
		 * @return string
		 */
		protected static function getFacadeKey(): string {
			return 'response';
		}
	}