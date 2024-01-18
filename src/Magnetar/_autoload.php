<?php
	declare(strict_types=1);
	
	use Magnetar\Container\Container;
	use Magnetar\Helpers\Env;
	use Magnetar\Group\Group;
	use Magnetar\Helpers\Facades\Config;
	use Magnetar\Helpers\Facades\Theme;
	use Magnetar\Helpers\Facades\URL;
	use Magnetar\Http\Response;
	use Magnetar\Http\JsonResponse;
	use Magnetar\Http\RedirectResponse;
	use Magnetar\Log\Logger;
	use Magnetar\Template\Template;
	
	if(!function_exists('app')) {
		/**
		 * Get an instance of the application container or an instance of a class from the container
		 * @param ?string $abstract Optional. The abstract class name to get an instance of. If null, the application instance is returned. Defaults to null
		 * @param array $params Optional. Parameters to pass to the class constructor. Not used if $abstract is null. Defaults to an empty array
		 * @return mixed
		 */
		function app(?string $abstract=null, array $params=[]): mixed {
			if(null === $abstract) {
				// return the application instance
				return Container::getInstance();
			}
			
			// return an instance of the requested class from the container
			return Container::getInstance()->make($abstract, $params);
		}
	}
	
	if(!function_exists('app_path')) {
		/**
		 * Get the path in the App directory
		 * @param string $rel_path Path relative to the App directory. Defaults to an empty string
		 * @return string
		 */
		function app_path(string $rel_path=''): string {
			return app()->pathApp($rel_path);
		}
	}
	
	if(!function_exists('asset')) {
		/**
		 * Get the URL to an asset
		 * @param string $rel_path Path relative to the asset directory. Defaults to an empty string
		 * @return string
		 * 
		 * @todo implement
		 */
		function asset(string $rel_path='', array $params=[]): string {
			return (string)URL::from( app('config')->get('app.asset_url') ?? app('config')->get('app.url') )
				->path($rel_path)
				->params($params);
		}
	}
	
	if(!function_exists('asset_path')) {
		/**
		 * Get the path in the asset directory
		 * @param string $rel_path Path relative to the asset directory. Defaults to an empty string
		 * @return string
		 */
		function asset_path(string $rel_path=''): string {
			return app()->pathAssets($rel_path);
		}
	}
	
	if(!function_exists('base_path')) {
		/**
		 * Get the path in the base directory
		 * @param string $rel_path Path relative to the base directory. Defaults to an empty string
		 * @return string
		 */
		function base_path(string $rel_path=''): string {
			return app()->pathBase($rel_path);
		}
	}
	
	if(!function_exists('cache')) {
		/**
		 * Get an instance of the cache manager
		 * @param string $key The cache key
		 * @param mixed $value Optional. The value to cache. If callable, this will be called and the value returned is stored in cache. If null, the stored cache value will be returned (defaults to null)
		 * @param ?string $connection_name The name of the cache connection to use (from config/cache.php). Defaults to null, the default connection
		 * @return mixed
		 * 
		 * @see \Magnetar\Cache\Memcached\MemcachedStore::get
		 */
		function cache(
			string $key,
			mixed $value,
			?string $connection_name=null
		): mixed {
			if(null !== $connection_name) {
				return app('cache')->connection($connection_name)->get($key, $value);
			}
			
			return app('cache')->get($key, $value);
		}
	}
	
	if(!function_exists('config')) {
		/**
		 * Get a config value
		 * @param string $key Config key
		 * @param mixed $default Optional. Value to return if the key is not found. Defaults to empty string
		 * @return mixed Config value
		 */
		function config(string $key, mixed $default=''): mixed {
			return Config::get($key, $default);
		}
	}
	
	if(!function_exists('config_path')) {
		/**
		 * Get the path in the config directory
		 * @param string $rel_path Path relative to the config directory. Defaults to an empty string
		 * @return string
		 */
		function config_path(string $rel_path=''): string {
			return app()->pathConfig($rel_path);
		}
	}
	
	if(!function_exists('cookie')) {
		/**
		 * Get or set a cookie. If $value is null, the cookie value from the request is returned. If $value is false, the cookie will be deleted
		 * @param ?string $name Cookie name. If null, the cookie jar will be returned. Defaults to null
		 * @param mixed $value Optional. Cookie value. If null, the cookie value will be returned. If false, the cookie will be deleted. Defaults to null
		 * @param ?int $expires_seconds Optional. Number of seconds until the cookie expires. If null, the default expiration time from config will be used. Defaults to null
		 * @param ?string $path Optional. Cookie path. If null, the default path from config will be used. Defaults to null
		 * @param ?string $domain Optional. Cookie domain. If null, the default domain from config will be used. Defaults to null
		 * @param ?bool $secure Optional. Whether the cookie should only be sent over HTTPS. If null, the default secure setting from config will be used. Defaults to null
		 * @param ?bool $httponly Optional. Whether the cookie should only be accessible over HTTP. If null, the default httponly setting from config will be used. Defaults to null
		 * @return mixed
		 * 
		 * @see \Magnetar\Http\CookieJar\CookieJar
		 * @see \Magnetar\Http\CookieJar\CookieJar::get
		 * @see \Magnetar\Http\CookieJar\CookieJar::set
		 */
		function cookie(
			?string $name=null,
			mixed $value=null,
			?int $expires_seconds=null,
			?string $path=null,
			?string $domain=null,
			?bool $secure=null,
			?bool $httponly=null
		): mixed {
			if(null === $name) {
				// get the cookie jar
				return app('cookie');
			} elseif(null === $value) {
				// get the cookie value
				return app('cookie')->get($name);
			} elseif(false === $value) {
				// delete the cookie
				return app('cookie')->remove($name);
			}
			
			return app('cookie')->set(
				$name,
				$value,
				$expires_seconds,
				$path,
				$domain,
				$secure,
				$httponly
			);
		}
	}
	
	if(!function_exists('data_path')) {
		/**
		 * Get the path to a file in the data directory
		 * @param string $rel_path Path relative to the data directory. Defaults to an empty string
		 * @return string
		 */
		function data_path(string $rel_path=''): string {
			return app()->pathData($rel_path);
		}
	}
	
	if(!function_exists('decrypt')) {
		/**
		 * Decrypt a string that was encrypted via Encryption->encrypt
		 * @param string $payload The encrypted payload
		 * @param bool $unserialize Whether to unserialize the decrypted string
		 * @return mixed
		 * 
		 * @throws \Magnetar\Encryption\Exceptions\EncryptionException
		 */
		function decrypt(string $payload, bool $unserialize=true): mixed {
			return app('encryption')->decrypt($payload, $unserialize);
		}
	}
	
	if(!function_exists('display')) {
		/**
		 * Generate a Response by processing a template from the active theme
		 * @param string $template_name Template name
		 * @param mixed $data Optional. Data to pass to the template file
		 * @return Response
		 */
		function display(string $template_name, mixed $data=[]): Response {
			return app('theme')->renderResponse($template_name, $data);
		}
	}
	
	if(!function_exists('display_tpl')) {
		/**
		 * Render a template from the active theme
		 * @param string $template_name Template name
		 * @param mixed $data Optional. Data to pass to the template file
		 * @return void
		 */
		function display_tpl(string $template_name, mixed $data=[]): void {
			print Theme::tpl($template_name, $data);
		}
	}
	
	if(!function_exists('encrypt')) {
		/**
		 * Encrypt a variable using this app's encryption config
		 * @param mixed $value What's being encrypted
		 * @param bool $serialize Whether to serialize the string before encrypting it
		 * @return string
		 * 
		 * @throws \Magnetar\Encryption\Exceptions\EncryptionException
		 */
		function encrypt(mixed $value, bool $serialize=true): string {
			return app('encryption')->encrypt($value, $serialize);
		}
	}
	
	if(!function_exists('env')) {
		/**
		 * Get an environment variable
		 * @param string $key Environment variable key
		 * @param mixed $default Default value to return if the key is not found. Defaults to null
		 * @return mixed Environment variable value
		 */
		function env(string $key, mixed $default=null): mixed {
			return Env::get($key, $default);
		}
	}
	
	if(!function_exists('group')) {
		/**
		 * Create a new Group instance with the specified items
		 * @param mixed $items Items to add to the group
		 * @return Group
		 */
		function group(mixed $items=[]): Group {
			return new Group($items);
		}
	}
	
	if(!function_exists('hasher')) {
		/**
		 * Get the hasher instance
		 * @param ?string $driver Optional. The name of the driver to use. Defaults to null, the default driver
		 * @return \Magnetar\Hashing\Hasher
		 */
		function hasher(?string $driver=null): \Magnetar\Hashing\Hasher {
			return app('hashing')->driver($driver);
		}
	}
	
	if(!function_exists('json')) {
		/**
		 * Make a JSON Response instance with the specified data
		 * @param mixed $data Data to set for the response
		 * @return JsonResponse
		 */
		function json(mixed $data): JsonResponse {
			return (new JsonResponse)->json($data);
		}
	}
	
	if(!function_exists('logger')) {
		/**
		 * Log a debug message to the logger
		 * @return void
		 */
		function logger(string $message, array $context=[]): void {
			app('logger')->debug($message, $context);
		}
	}
	
	if(!function_exists('logs')) {
		/**
		 * Get the logger instance
		 * @return Logger
		 */
		function logs(): Logger {
			return app('logger');
		}
	}
	
	if(!function_exists('public_path')) {
		/**
		 * Get the path in the public directory
		 * @param string $rel_path Path relative to the public directory. Defaults to an empty string
		 * @return string
		 */
		function public_path(string $rel_path=''): string {
			return app()->pathPublic($rel_path);
		}
	}
	
	if(!function_exists('redirect')) {
		/**
		 * Make a Redirect Response instance and tell it to redirect to the specified URL
		 * @param string $url URL to redirect to
		 * @param int $response_code Optional. HTTP status code. Defaults to 307. Any non-3xx code will throw an exception
		 * @return RedirectResponse
		 * 
		 * @throws \Magnetar\Http\Exceptions\InvalidRedirectURLException
		 */
		function redirect(string $url, int $response_code=307): RedirectResponse {
			return (new RedirectResponse)->responseCode($response_code)->to($url);
		}
	}
	
	if(!function_exists('response')) {
		/**
		 * Make a Response instance and set various properties
		 * @param string $body Response body
		 * @param int $response_code Optional. HTTP status code. Defaults to 200
		 * @param array $headers Optional. HTTP headers to set. Defaults to an empty array
		 * @return Response
		 */
		function response(
			string $body,
			int $response_code=200,
			array $headers=[]
		): Response {
			return (new Response)->responseCode($response_code)->setHeaders($headers)->body($body);
		}
	}
	
	if(!function_exists('request')) {
		/**
		 * Get the request instance
		 * @return \Magnetar\Http\Request
		 */
		function request(): \Magnetar\Http\Request {
			return app('request');
		}
	}
	
	if(!function_exists('route')) {
		/**
		 * Get the route instance
		 * @return ?\Magnetar\Router\Route
		 */
		function route(): ?\Magnetar\Router\Route {
			return app('route') ?? null;
		}
	}
	
	if(!function_exists('routing_path')) {
		/**
		 * Get the path to a file in the routing directory
		 * @param string $rel_path Path relative to the storage directory. Defaults to an empty string
		 * @return string
		 */
		function routing_path(string $rel_path=''): string {
			return app()->pathRouting($rel_path);
		}
	}
	
	if(!function_exists('storage_path')) {
		/**
		 * Get the path to a file in the storage directory
		 * @param string $rel_path Path relative to the storage directory. Defaults to an empty string
		 * @return string
		 */
		function storage_path(string $rel_path=''): string {
			return app()->pathStorage($rel_path);
		}
	}
	
	if(!function_exists('theme')) {
		/**
		 * Use a specific theme
		 * @param ?string $theme_name
		 * @return Theme The theme instance
		 */
		function theme(?string $theme_name=null): Template {
			return app('theme')->theme($theme_name);
		}
	}
	
	if(!function_exists('themes_path')) {
		/**
		 * Get the path to a file in the themes directory
		 * @param string $rel_path Path relative to the themes directory. Defaults to an empty string
		 * @return string
		 */
		function themes_path(string $rel_path=''): string {
			return app()->pathThemes($rel_path);
		}
	}
	
	if(!function_exists('theme_path')) {
		/**
		 * Get the path to a file in the active theme directory
		 * @param string $rel_path Path relative to the active theme directory. Defaults to an empty string
		 * @return string
		 */
		function theme_path(string $rel_path=''): string {
			return app()->pathThemes(
				app()->joinPath(config('theme.default'), $rel_path)
			);
		}
	}
	
	if(!function_exists('tpl')) {
		/**
		 * Render a template from the active theme
		 * @param string $template_name Template name
		 * @param mixed $data Optional. Data to pass to the template file
		 * @return mixed
		 */
		function tpl(string $template_name, mixed $data=[]): mixed {
			return app('theme')->tpl($template_name, $data);
		}
	}
	
	if(!function_exists('url')) {
		/**
		 * Generate a URL
		 * @param string $url The URL to generate
		 * @param array $params Optional. Parameters to set in the URL
		 * @return string The generated URL
		 */
		function url(string $url, array $params=[]): string {
			return URL::to($url, $params);
		}
	}
	