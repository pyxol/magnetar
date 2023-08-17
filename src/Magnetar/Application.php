<?php
	declare(strict_types=1);
	
	namespace Magnetar;
	
	use Magnetar\Container\Container;
	use Magnetar\Helpers\ServiceProvider;
	use Magnetar\Log\LogServiceProvider;
	use Magnetar\Router\RouterServiceProvider;
	
	// @TODO
	//use Magnetar\Helpers\DeferrableServiceInterface;
	// @TODO
	
	class Application extends Container {
		/**
		 * The application's base path
		 * @var string
		 */
		protected string|null $base_path = null;
		
		/**
		 * Has the application been bootstrapped?
		 * @var bool
		 */
		protected bool $bootstrapped = false;
		
		/**
		 * Has the application booted the service providers?
		 * @var bool
		 */
		protected bool $bootedServiceProviders = false;
		
		/**
		 * The application's loaded service providers. Key is the class name, value is set to true
		 * @var array
		 */
		protected array $loadedServiceProviders = [];
		
		/**
		 * The application's service providers
		 * @var array
		 */
		protected array $serviceProviders = [];
		
		/**
		 * An array of registered callbacks that run during application termination
		 * @var array
		 */
		protected array $terminateCallbacks = [];
		
		/**
		 * Path to the application's app directory
		 * @var string
		 */
		protected ?string $path_app;
		
		/**
		 * Path to the application's config directory
		 * @var string
		 */
		protected ?string $path_config;
		
		/**
		 * Path to the application's data directory
		 * @var string
		 */
		protected ?string $path_data;
		
		/**
		 * Path to the application's public directory
		 * @var string
		 */
		protected ?string $path_public;
		
		/**
		 * Path to the application's assets directory
		 * @var string
		 */
		protected ?string $path_assets;
		
		/**
		 * Path to the application's storage directory
		 * @var string
		 */
		protected ?string $path_storage;
		
		/**
		 * Path to the application's routing directory
		 * @var string
		 */
		protected ?string $path_routing;
		
		/**
		 * Path to the application's themes directory
		 * @var string
		 */
		protected ?string $path_themes;
		
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
			
			$this->instance('path', $this->pathApp());
			$this->instance('path.base', $this->pathBase());
			$this->instance('path.config', $this->pathConfig());
			$this->instance('path.data', $this->pathData());
			$this->instance('path.public', $this->pathPublic());
			$this->instance('path.assets', $this->pathAssets());
			$this->instance('path.storage', $this->pathStorage());
			$this->instance('path.routing', $this->pathRouting());
			$this->instance('path.themes', $this->pathThemes());
		}
		
		/**
		 * Get the base path of the application
		 * @return string
		 */
		public function pathBase(string $rel_path=''): string {
			return $this->base_path . ltrim($rel_path, DIRECTORY_SEPARATOR);
		}
		
		/**
		 * Set the path to the application's app directory
		 * @param string $path
		 * @return self
		 */
		public function setAppPath(string $path): self {
			$this->path_app = $path;
			
			$this->instance('path.app', $path);
			
			return $this;
		}
		
		/**
		 * Get the path to the application's app directory
		 * @param string $rel_path
		 * @return string
		 */
		public function pathApp(string $rel_path=''): string {
			return $this->joinPath($this->path_app ?? $this->pathBase('app'), $rel_path);
		}
		
		/**
		 * Set the path to the application's config directory
		 * @param string $path
		 * @return self
		 */
		public function setConfigPath(string $path): self {
			$this->path_config = $path;
			
			$this->instance('path.config', $path);
			
			return $this;
		}
		
		/**
		 * Get the path to the application's config directory
		 * @param string $rel_path
		 * @return string
		 */
		public function pathConfig(string $rel_path=''): string {
			return $this->joinPath($this->path_config ?? $this->pathBase('config'), $rel_path);
		}
		
		/**
		 * Set the path to the application's data directory
		 * @param string $path
		 * @return self
		 */
		public function setDataPath(string $path): self {
			$this->path_data = $path;
			
			$this->instance('path.data', $path);
			
			return $this;
		}
		
		/**
		 * Get the path to the application's data directory
		 * @param string $rel_path
		 * @return string
		 */
		public function pathData(string $rel_path=''): string {
			return $this->joinPath($this->path_data ?? $this->pathBase('data'), $rel_path);
		}
		
		/**
		 * Set the path to the application's public directory
		 * @param string $path
		 * @return self
		 */
		public function setPublicPath(string $path): self {
			$this->path_public = $path;
			
			$this->instance('path.public', $path);
			
			return $this;
		}
		
		/**
		 * Get the path to the application's public directory
		 * @param string $rel_path
		 * @return string
		 */
		public function pathPublic(string $rel_path=''): string {
			return $this->joinPath($this->path_public ?? $this->pathBase('public'), $rel_path);
		}
		
		/**
		 * Set the path to the application's assets directory
		 * @param string $path
		 * @return self
		 */
		public function setAssetsPath(string $path): self {
			$this->path_assets = $path;
			
			$this->instance('path.assets', $path);
			
			return $this;
		}
		
		/**
		 * Get the path to the application's assets directory
		 * @param string $rel_path
		 * @return string
		 */
		public function pathAssets(string $rel_path=''): string {
			return $this->joinPath($this->path_assets ?? $this->pathBase('assets'), $rel_path);
		}
		
		/**
		 * Set the path to the application's storage directory
		 * @param string $path
		 * @return self
		 */
		public function setStoragePath(string $path): self {
			$this->path_storage = $path;
			
			$this->instance('path.storage', $path);
			
			return $this;
		}
		
		/**
		 * Get the path to the application's storage directory
		 * @param string $rel_path
		 * @return string
		 */
		public function pathStorage(string $rel_path=''): string {
			return $this->joinPath($this->path_storage ?? $this->pathBase('storage'), $rel_path);
		}
		
		/**
		 * Set the path to the application's routing directory
		 * @param string $path
		 * @return self
		 */
		public function setRoutingPath(string $path): self {
			$this->path_routing = $path;
			
			$this->instance('path.routing', $path);
			
			return $this;
		}
		
		/**
		 * Get the path to the application's routing directory
		 * @param string $rel_path
		 * @return string
		 */
		public function pathRouting(string $rel_path=''): string {
			return $this->joinPath($this->path_routing ?? $this->pathBase('routing'), $rel_path);
		}
		
		/**
		 * Set the path to the application's themes directory
		 * @param string $path
		 * @return self
		 */
		public function setThemesPath(string $path): self {
			$this->path_themes = $path;
			
			$this->instance('path.themes', $path);
			
			return $this;
		}
		
		/**
		 * Get the path to the application's themes directory
		 * @param string $rel_path
		 * @return string
		 */
		public function pathThemes(string $rel_path=''): string {
			return $this->joinPath($this->path_themes ?? $this->pathBase('themes'), $rel_path);
		}
		
		/**
		 * Join a base path and a relative path
		 * @param string $base_path Base path
		 * @param string $rel_path Relative path
		 * @return string
		 */
		public function joinPath(string $base_path, string $rel_path=''): string {
			return $base_path . (('' !== $rel_path) ? DIRECTORY_SEPARATOR . ltrim($rel_path, DIRECTORY_SEPARATOR) : '');
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
			return $this->bootedServiceProviders;
		}
		
		/**
		 * Register all of the configured service providers
		 * @return void
		 */
		public function registerConfiguredServiceProviders(): void {
			$providers = $this['config']['app.providers'];
			
			foreach($providers as $provider) {
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
			
			$this->bootedServiceProviders = true;
			
			foreach($this->serviceProviders as $provider) {
				$this->bootServiceProvider($provider);
			}
		}
		
		/**
		 * Boot up a service provider if necessary
		 * @param ServiceProvider|string $provider
		 * @return void
		 */
		public function bootServiceProvider(ServiceProvider|string $provider): void {
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
			
			// register a service provider's bindings
			if(property_exists($provider, 'bindings')) {
				foreach($provider->bindings as $abstract => $concrete) {
					$this->bind($abstract, $concrete);
				}
			}
			
			// register a service provider's singletons
			if(property_exists($provider, 'singletons')) {
				foreach($provider->singletons as $abstract => $concrete) {
					$this->singleton(
						(is_int($abstract) ? $concrete : $abstract),
						$concrete
					);
				}
			}
			
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
			$name = (is_string($provider) ? $provider : get_class($provider));
			
			return array_filter($this->serviceProviders, function($value) use ($name) {
				return ($value instanceof $name);
			});
		}
		
		/**
		 * Mark a service provider as registered
		 * @param ServiceProvider $provider
		 * @return void
		 */
		protected function markAsRegisteredServiceProvider(ServiceProvider $provider): void {
			$this->serviceProviders[] = $provider;
			$this->loadedServiceProviders[ get_class($provider) ] = true;
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
		
		/**
		 * {@inheritDoc}
		 */
		public function bound(string $abstract): bool {
			$abstract = $this->getAlias($abstract);
			
			// @TODO check if deferred
			
			return parent::bound($abstract);
		}
		
		/**
		 * Empty out the container's bindings and resolved instances
		 * @return void
		 */
		public function flush(): void {
			parent::flush();
			
			$this->buildStack = [];
			$this->loadedServiceProviders = [];
			$this->serviceProviders = [];
			$this->terminateCallbacks = [];
			$this->reboundCallbacks = [];
			$this->beforeResolvingCallbacks = [];
			$this->resolvingCallbacks = [];
			$this->afterResolvingCallbacks = [];
			$this->globalBeforeResolvingCallbacks = [];
			$this->globalResolvingCallbacks = [];
			$this->globalAfterResolvingCallbacks = [];
		}
		
		/**
		 * Register a callback to run during application termination
		 * @param callable|array|string $callback The callback to run
		 * @return void
		 */
		public function registerTerminateCallback(callable|array|string $callback): void {
			$this->terminateCallbacks[] = $callback;
		}
		
		/**
		 * Terminate the application by calling any registerd termination callbacks
		 * @return void
		 */
		public function terminate(): void {
			// allow for terminating callbacks to produce their own terminating callbacks
			$i = 0;
			
			while($i < count($this->terminateCallbacks)) {
				$this->call($this->terminateCallbacks[ $i ]);
				
				$i++;
			}
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