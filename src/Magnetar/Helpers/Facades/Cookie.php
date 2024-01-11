<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method static void importCookiesFromRequest(\Magnetar\Http\Request $request)
	 * @method static array getCookies()
	 * @method static array getQueuedCookies()
	 * @method static ?\Magnetar\Http\CookieJar\Cookie get(string $name)
	 * @method static ?string getValue(string $name)
	 * @method static self set(string $name, mixed $value, ?int $expires_seconds, ?string $path, ?string $domain, ?bool $secure, ?bool $httponly)
	 * @method static self setCookie(\Magnetar\Http\CookieJar\Cookie $cookie)
	 * @method static self remove(string $name)
	 * @method static self unequeue(string $name)
	 * @method static self setDefaults(?int $expires_seconds, ?string $path, ?string $domain, ?bool $secure, ?bool $httponly)
	 * @method static int getDefaultExpiresSeconds()
	 * @method static string getDefaultPath()
	 * @method static string getDefaultDomain()
	 * @method static bool getDefaultSecure()
	 * @method static bool getDefaultHttpOnly()
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