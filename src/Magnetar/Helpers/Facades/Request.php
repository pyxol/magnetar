<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method static void processRequestCookies()
	 * @method static \Magnetar\Http\Request create()
	 * @method static array headers()
	 * @method static ?string header(string $name)
	 * @method static bool hasHeader(string $name)
	 * @method static string path()
	 * @method static ?\Magnetar\Router\Enums\HTTPMethodEnum method()
	 * @method static void assignOverrideParameters(array $parameters)
	 * @method static mixed parameter(string $name, mixed $default)
	 * @method static mixed get(string $name, mixed $default)
	 * @method static bool isset(string $name)
	 * @method static array parameters()
	 * @method static void setParameter(string $name, mixed $value)
	 * @method static void removeParameter(string $name)
	 * @method static array cookies()
	 * @method static mixed cookie(string $name, mixed $default)
	 * @method static void setCookie(string $name, mixed $value)
	 * @method static string body()
	 * @method static \Magnetar\Http\UploadedFile|array|null file(string $input_name)
	 * @method static array files()
	 * @method static bool accepts(array|string $content_type)
	 * @method static bool acceptsAnyContentType()
	 * @method static bool wantsJson()
	 * @method static bool acceptsJson()
	 * @method static bool isAjax()
	 * @method static bool isJson()
	 * 
	 * @see \Magnetar\Http\Request
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