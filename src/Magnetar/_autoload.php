<?php
	declare(strict_types=1);
	
	use Magnetar\Container\Container;
	use Magnetar\Helpers\Env;
	use Magnetar\Helpers\Facades\Config;
	use Magnetar\Helpers\Facades\Theme;
	use Magnetar\Helpers\Facades\URL;
	use Magnetar\Http\JsonResponse;
	use Magnetar\Http\RedirectResponse;
	use Magnetar\Logger;
	use Magnetar\Template\Template;
	
	if(!function_exists('app')) {
		/**
		 * Get an instance of the application container or an instance of a class from the container
		 * @param string|null|null $abstract Optional. The abstract class name to get an instance of. If null, the application instance is returned. Defaults to null
		 * @param array $params Optional. Parameters to pass to the class constructor. Not used if $abstract is null. Defaults to an empty array
		 * @return mixed
		 */
		function app(string|null $abstract=null, array $params=[]): mixed {
			if(null === $abstract) {
				// return the application instance
				return Container::getInstance();
			}
			
			// return an instance of the requested class from the container
			return Container::getInstance()->make($abstract, $params);
		}
	}
	
	if(!function_exists('cache')) {
		/**
		 * Get an instance of the cache manager
		 * @param string $key The cache key
		 * @param mixed $value Optional. The value to cache. If callable, this will be called and the value returned is stored in cache. If null, the stored cache value will be returned (defaults to null)
		 * @param string|null $connection_name The name of the cache connection to use (from config/cache.php). Defaults to null, the default connection
		 * @return mixed
		 * 
		 * @see \Magnetar\Cache\Memcached\MemcachedStore::get
		 */
		function cache(
			string $key,
			mixed $value,
			string|null $connection_name=null
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
	
	if(!function_exists('env')) {
		/**
		 * Get an environment variable
		 * @param string $key Environment variable key
		 * @param mixed $default Default value to return if the key is not found
		 * @return mixed Environment variable value
		 */
		function env(string $key, mixed $default): mixed {
			return Env::get($key, $default);
		}
	}
	
	if(!function_exists('json')) {
		/**
		 * Make a JSON Response instance with the specified data
		 * @param mixed $data Data to set for the response
		 * @return \Magnetar\Http\JsonResponse
		 */
		function json(mixed $data): JsonResponse {
			return (new JsonResponse())->setData($data);
		}
	}
	
	if(!function_exists('logs')) {
		/**
		 * Get the logger instance
		 * @return \Magnetar\Logger
		 */
		function logs(): Logger {
			return app('logger');
		}
	}
	
	if(!function_exists('redirect')) {
		/**
		 * Make a Redirect Response instance and tell it to redirect to the specified URL
		 * @param string $url URL to redirect to
		 * @return RedirectResponse
		 */
		function redirect(string $url): RedirectResponse {
			return (new RedirectResponse())->setURL($url);
		}
	}
	
	if(!function_exists('theme')) {
		/**
		 * Use a specific theme
		 * @param string|null $theme_name
		 * @return Theme The theme instance
		 */
		function theme(string|null $theme_name=null): Template {
			return app('theme')->theme($theme_name);
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
	