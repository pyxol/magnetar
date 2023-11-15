<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method importCookiesFromRequest(Magnetar\Http\Request $request): void
	 * @method getCookies(): array
	 * @method getQueuedCookies(): array
	 * @method get(string $name): ?Magnetar\Http\CookieJar\Cookie
	 * @method getValue(string $name): ?string
	 * @method set(string $name, string $value, ?int $expires_seconds=null, ?string $path=null, ?string $domain=null, ?bool $secure=null, ?bool $httponly=null): self
	 * @method setCookie(Magnetar\Http\CookieJar\Cookie $cookie): self
	 * @method remove(string $name): self
	 * @method setDefaults(?int $expires_seconds=null, ?string $path=null, ?string $domain=null, ?bool $secure=null, ?bool $httponly=null): self
	 * @method getDefaultExpiresSeconds(): int
	 * @method getDefaultPath(): string
	 * @method getDefaultDomain(): string
	 * @method getDefaultSecure(): bool
	 * @method getDefaultHttpOnly(): bool
	 * 
	 * @see \Magnetar\Http\CookieJar\CookieJar
	 */
	class Cookie extends Facade {
		/**
		 * Get the named key that this facade represents
		 * @return string
		 */
		protected static function getFacadeKey(): string {
			return 'cookie';
		}
	}