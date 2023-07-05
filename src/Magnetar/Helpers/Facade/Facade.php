<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facade;
	
	use Exception;
	use Closure;
	use RuntimeException;
	
	use Magnetar\Application;
	
	class Facade {
		/**
		 * The application instance
		 * @var Application
		 */
		protected static Application $app;
		
		/**
		 * The resolved object instances
		 * @var array
		 */
		protected static $resolvedInstance;
		
		/**
		 * Indicates if the resolved instance should be cached
		 * @var bool
		 */
		protected static $cached = true;
		
		 /**
		 * Get the root object behind the facade.
		 *
		 * @return mixed
		 */
		public static function getFacadeRoot() {
			return static::resolveFacadeInstance(static::getFacadeKey());
		}
		
		/**
		 * Run a Closure 
		 * @param Closure $callback
		 * @return void
		 */
		public static function resolved(Closure $callback): void {
			$accessor = static::getFacadeKey();
			
			if(true === static::$app->resolved($accessor)) {
				$callback(static::getFacadeRoot());
			}
			
			static::$app->afterResolving($accessor, function($service) use ($callback) {
				$callback($service);
			});
		}
		
		/**
		 * Hotswap the underlying instance behind the facade
		 * @param mixed $instance
		 * @return void
		 */
		public static function swap(mixed $instance): void {
			static::$resolvedInstance[ static::getFacadeKey() ] = $instance;
			
			if(isset(static::$app)) {
				static::$app->instance(static::getFacadeKey(), $instance);
			}
		}
		
		/**
		 * Get the named key that this facade represents
		 * @return string
		 */
		protected static function getFacadeKey(): string {
			throw new Exception("Base Facade class should not be used");
		}
		
		/**
		 * Return the facade's application instance
		 * @return Application
		 */
		public static function getFacadeApplication(): Application {
			return static::$app;
		}
		
		/**
		 * Set the facade's application instance
		 * @param Application $app
		 * @return void
		 */
		public static function setFacadeApplication(Application $app): void {
			static::$app = $app;
		}
		
		/**
		 * Resolve the facade root instance from app container
		 * @param string $name
		 * @return mixed
		 */
		protected static function resolveFacadeInstance(string $name): mixed {
			if(isset(static::$resolvedInstance[ $name ])) {
				return static::$resolvedInstance[ $name ];
			}
			
			if(static::$app) {
				if(static::$cached) {
					return static::$resolvedInstance[ $name ] = static::$app[ $name ];
				}
				
				return static::$app[ $name ];
			}
		}
		
		/**
		 * Clear a resolved facade instance.
		 *
		 * @param  string  $name
		 * @return void
		 */
		public static function clearResolvedInstance($name) {
			unset(static::$resolvedInstance[$name]);
		}
		
		/**
		 * Clear all of the resolved instances.
		 *
		 * @return void
		 */
		public static function clearResolvedInstances() {
			static::$resolvedInstance = [];
		}
		
		/**
		 * Get a list of default Facade aliases to register
		 * @return array
		 */
		public static function defaultAliases(): array {
			return [
				'Config' => Config::class,
				'DB' => DB::class,
			];
		}
		
		/**
		 * Handle dynamic, static calls to the object.
		 *
		 * @param  string  $method
		 * @param  array  $args
		 * @return mixed
		 *
		 * @throws RuntimeException
		 */
		public static function __callStatic($method, $args) {
			$instance = static::getFacadeRoot();
			
			if(!$instance) {
				throw new RuntimeException('A facade root has not been set.');
			}
			
			return $instance->$method(...$args);
		}
	}