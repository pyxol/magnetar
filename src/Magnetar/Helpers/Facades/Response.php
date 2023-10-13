<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method status(int $code=200): self;
	 * @method setCookie(string $name, ?string $value=null, int $expires=0, string $path='', string $domain='', bool $secure=false, bool $httponly=false): self;
	 * @method redirect(string $path, int $response_code=302): void;
	 * @method setBody(string $body=''): self;
	 * @method json(mixed $body): self;
	 * @method send(): self;
	 * @method sendHeaders(): self;
	 * @method sendBody(): self;
	 * @method body(): string;
	 * @method statusCode(): int;
	 * @method header(string $header, ?string $value=null, int|bool $replace=true, ?int $response_code=0): self|string;
	 * @method setHeaders(array $headers): self;
	 * @method headers(): array;
	 * @method getHeader(string $name): ?string;
	 * @method hasHeader(string $name): bool;
	 * @method removeHeader(string $name): self;
	 * @method clearHeaders(): self;
	 * @method headersSent(): bool;
	 * @method sent(): bool;
	 * 
	 * @see Magnetar\Http\Response
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