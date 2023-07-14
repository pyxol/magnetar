<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	/**
	 * @method status($code=200): Magnetar\Http\Response
	 * @method header(string $header, bool|int $replace=true, int|null $response_code=0): Magnetar\Http\Response
	 * @method setCookie($name, $value=null, $expires=0, $path="", $domain="", $secure=false, $httponly=false): Magnetar\Http\Response
	 * @method redirect(string $path, int $response_code=302): void
	 * @method send($body=""): void
	 * @method json(array $body): void
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