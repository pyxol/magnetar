<?php
	declare(strict_types=1);
	
	namespace Magnetar;
	
	use Magnetar\Container\Container;
	use Magnetar\Helpers\ServiceProvider;
	use Magnetar\Log\LogServiceProvider;
	use Magnetar\Router\RouterServiceProvider;
	
	use Magnetar\Helpers\DeferrableServiceInterface;
	
	class Application extends Container {
		protected string|null $base_path = null;
		
		protected bool $bootstrapped = false;
		protected bool $bootedProviders = false;
		
		protected array $serviceProviders = [];
		
		/**
		 * Application constructor
		 * @param string|null $base_path The full directory path of the application including trailing string
		 * @return void
		 */
		public function __construct(string|null $base_path=null) {
			if($base_path) {
				$this->setBasePath($base_path);
			}
			
			$this->registerBaseBindings();
			$this->registerBaseServiceProviders();
			$this->registerCoreContainerAliases();
		}
		
		/**
		 * Set the base path of the application
		 * @param string $base_path
		 * @return void
		 */
		public function setBasePath(string $base_path): void {
			$this->base_path = realpath(rtrim($base_path, DIRECTORY_SEPARATOR)) . DIRECTORY_SEPARATOR;
		}
		
		/**
		 * Get the base path of the application
		 * @return string
		 */
		public function basePath(string $rel_path=''): string {
			return $this->base_path . ltrim($rel_path, DIRECTORY_SEPARATOR);
		}
		
		/**
		 * Has the application been bootstrapped?
		 * @return bool
		 */
		public function hasBeenBootstrapped(): bool {
			return $this->bootstrapped;
		}
		
		/**
		 * Run the given array of bootstrap classes
		 *
		 * @param string[] $bootstrappers
		 * @return void
		 */
		public function bootstrapWith(array $bootstrappers): void {
			$this->bootstrapped = true;
			
			foreach($bootstrappers as $bootstrapper) {
				$this->make($bootstrapper)->bootstrap($this);
			}
		}
		
		/**
		 * Has the application booted up it's service providers?
		 * @return bool
		 */
		public function hasBootedServiceProviders(): bool {
			return $this->bootedProviders;
		}
		
		/**
		 * Register all of the configured service providers
		 * @return void
		 */
		public function registerConfiguredServiceProviders(): void {
			$providers = $this['config']['app.providers'];
			
			foreach($providers as $provider) {
				//die("registering provider: $provider");
				
				// @TODO if provider instanceof DeferredServiceProvider, defer it here
				
				$this->registerServiceProvider($provider);
			}
		}
		
		/**
		 * Boot up the application's service providers
		 * @return void
		 */
		public function bootServiceProviders(): void {
			if($this->hasBootedServiceProviders()) {
				return;
			}
			
			$this->bootedProviders = true;
			
			foreach($this['config']['app.providers'] as $provider) {
				$this->bootServiceProvider($provider);
			}
		}
		
		/**
		 * Boot up a service provider if necessary
		 * @param ServiceProvider|string $provider
		 * @return void
		 */
		public function bootServiceProvider(ServiceProvider|string $provider): void {
			// @TODO fix this
			
			if(method_exists($provider, 'boot')) {
				$this->call([$provider, 'boot']);
			}
		}
		
		/**
		 * Register a service provider with the application
		 * @param ServiceProvider|string $provider
		 * @return ServiceProvider
		 */
		public function registerServiceProvider(ServiceProvider|string $provider): ServiceProvider {
			if($registered = $this->getServiceProvider($provider)) {
				return $registered;
			}
			
			if(is_string($provider)) {
				$provider = $this->resolveServiceProvider($provider);
			}
			
			// call the service provider's register method
			$provider->register();
			
			// @TODO bindings property on ServiceProvider instance
			// @TODO singletons property on ServiceProvider instance
			
			$this->markAsRegisteredServiceProvider($provider);
			
			if($this->hasBootedServiceProviders()) {
				$this->bootServiceProvider($provider);
			}
			
			return $provider;
		}
		
		/**
		 * Resolve a service provider instance from the class name
		 * @param string $provider
		 * @return ServiceProvider
		 */
		public function resolveServiceProvider(string $provider): ServiceProvider {
			return new $provider($this);
		}
		
		/**
		 * Get the registered service provider instance if it exists
		 * @param ServiceProvider|string $provider
		 * @return ServiceProvider|null
		 */
		public function getServiceProvider(ServiceProvider|string $provider): ServiceProvider|null {
			return array_values($this->getServiceProviders($provider))[0] ?? null;
		}
		
		/**
		 * Get the registered service provider instances if they exist
		 * @param ServiceProvider|string $provider
		 * @return array
		 */
		public function getServiceProviders(ServiceProvider|string $provider): array {
			$name = is_string($provider) ? $provider : get_class($provider);
			
			return array_filter($this->serviceProviders, function($value) use ($name) {
				return ($value instanceof $name);
			});
		}
		
		protected function markAsRegisteredServiceProvider(ServiceProvider $provider): void {
			$this->serviceProviders[] = $provider;
		}
		
		/**
		 * Register the container's basic bindings
		 *
		 * @return void
		 */
		protected function registerBaseBindings(): void {
			static::setInstance($this);
			
			$this->instance('app', $this);
			
			$this->instance(Container::class, $this);
		}
		
		/**
		 * Register the container's basic service providers
		 * @return void
		 */
		protected function registerBaseServiceProviders(): void {
			$this->registerServiceProvider(new LogServiceProvider($this));
			$this->registerServiceProvider(new RouterServiceProvider($this));
		}
		
		/**
		 * Register the container's core list of aliases
		 * @return void
		 * 
		 * @see Magnetar\Helpers\Facades\Facade::defaultAliases()
		 */
		public function registerCoreContainerAliases(): void {
			foreach([
				'app' => [
					self::class,
					\Magnetar\Application::class
				],
				'cache' => [
					\Magnetar\Cache\StoreManager::class
				],
				'config' => [
					\Magnetar\Config\Config::class
				],
				'database' => [
					\Magnetar\Database\ConnectionManager::class
				],
				'files' => [
					\Magnetar\Filesystem\Filesystem::class
				],
				'logger' => [
					\Magnetar\Log\Logger::class
				],
				'request' => [
					\Magnetar\Http\Request::class
				],
				'response' => [
					\Magnetar\Http\Response::class
				],
				'router' => [
					\Magnetar\Router\Router::class
				],
			] as $key => $aliases) {
				foreach($aliases as $alias) {
					$this->alias($key, $alias);
				}
			}
		}
		
		
		/**
		 * Extend the container's make method with service provider functionality
		 * @param string|callable $abstract
		 * @param array $parameters
		 * @return mixed
		 */
		public function make(string|callable $abstract, array $parameters=[]): mixed {
			$abstract = $this->getAlias($abstract);
			
			// @TODO check if deferred and load if necessary
			
			return parent::make($abstract, $parameters);
		}
		
		/**
		 * Extend the container's resolve method with service provider functionality
		 * @param string|callable $abstract
		 * @param array $parameters
		 * @param bool $raiseEvents
		 * @return mixed
		 */
		protected function resolve(
			string|callable $abstract,
			array $parameters=[],
			bool $raiseEvents=true
		): mixed {
			$abstract = $this->getAlias($abstract);
			
			// @TODO check if deferred and load if necessary
			
			return parent::resolve($abstract, $parameters, $raiseEvents);
		}
		
		
		public function bound(string $abstract): bool {
			$abstract = $this->getAlias($abstract);
			
			// @TODO check if deferred
			
			return parent::bound($abstract);
		}
		
		
		public function flush(): void {
			parent::flush();
			
			$this->serviceProviders = [];
		}
		
		/**
		 * Set the environment ("dev", "prod", etc)
		 * @param string $env
		 * @return void
		 */
		public function setEnvironment(string $env): void {
			$this['env'] = $env;
		}
		
		/**
		 * Get the environment ("dev", "prod", etc)
		 * @return string
		 */
		public function environment(): string {
			return $this['env'];
		}
		
		/**
		 * Is this the development environment?
		 */
		public function isDev(): bool {
			return ('dev' === $this['env']);
		}
		
		/**
		 * Is this the production environment?
		 * @return bool
		 */
		public function isProd(): bool {
			return ('prod' === $this['env']);
		}
	}