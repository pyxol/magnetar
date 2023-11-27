<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method static self responseCode(int $response_code)
	 * @method static self body(string $body)
	 * @method static self sendBody()
	 * @method static self setCookie(\Magnetar\Http\CookieJar\Cookie $cookie)
	 * @method static self send()
	 * @method static self sendHeaders()
	 * @method static self|string header(string $header, ?string $value, int|bool $replace, ?int $response_code)
	 * @method static array headers()
	 * @method static self setHeaders(array $headers)
	 * @method static self removeHeader(string $name)
	 * @method static self clearHeaders()
	 * @method static ?string getHeader(string $name)
	 * @method static bool hasHeader(string $name)
	 * @method static bool sentHeaders()
	 * @method static bool sent()
	 * @method static self redirect(string $path, int $response_code)
	 * @method static self json(mixed $body)
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