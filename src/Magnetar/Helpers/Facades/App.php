<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method static void setBasePath(string $base_path)
	 * @method static string pathBase(string $rel_path)
	 * @method static self setAppPath(string $path)
	 * @method static string pathApp(string $rel_path)
	 * @method static self setConfigPath(string $path)
	 * @method static string pathConfig(string $rel_path)
	 * @method static self setDataPath(string $path)
	 * @method static string pathData(string $rel_path)
	 * @method static self setPublicPath(string $path)
	 * @method static string pathPublic(string $rel_path)
	 * @method static self setAssetsPath(string $path)
	 * @method static string pathAssets(string $rel_path)
	 * @method static self setStoragePath(string $path)
	 * @method static string pathStorage(string $rel_path)
	 * @method static self setRoutingPath(string $path)
	 * @method static string pathRouting(string $rel_path)
	 * @method static self setThemesPath(string $path)
	 * @method static string pathThemes(string $rel_path)
	 * @method static string joinPath(string $base_path, string $rel_path)
	 * @method static bool hasBeenBootstrapped()
	 * @method static void bootstrapWith(array $bootstrappers)
	 * @method static bool hasBootedServiceProviders()
	 * @method static void registerConfiguredServiceProviders()
	 * @method static void bootServiceProviders()
	 * @method static void bootServiceProvider(\Magnetar\Helpers\ServiceProvider|string $provider)
	 * @method static \Magnetar\Helpers\ServiceProvider registerServiceProvider(\Magnetar\Helpers\ServiceProvider|string $provider)
	 * @method static \Magnetar\Helpers\ServiceProvider resolveServiceProvider(string $provider)
	 * @method static ?\Magnetar\Helpers\ServiceProvider getServiceProvider(\Magnetar\Helpers\ServiceProvider|string $provider)
	 * @method static array getServiceProviders(\Magnetar\Helpers\ServiceProvider|string $provider)
	 * @method static void registerCoreContainerAliases()
	 * @method static mixed make(callable|string $abstract, array $parameters)
	 * @method static bool bound(string $abstract)
	 * @method static void flush()
	 * @method static void registerTerminateCallback(callable|array|string $callback)
	 * @method static void terminate()
	 * @method static void setEnvironment(string $env)
	 * @method static string environment()
	 * @method static bool isDevEnv()
	 * @method static bool isTestEnv()
	 * @method static bool isLiveEnv()
	 * @method static \Magnetar\Container\ContextualBindingBuilder when( $concrete)
	 * @method static bool has(string $id)
	 * @method static bool resolved(string $abstract)
	 * @method static bool isShared(string $abstract)
	 * @method static bool isAlias(string $name)
	 * @method static void bind(string $abstract, Closure|string|null $concrete, bool $shared)
	 * @method static bool hasMethodBinding(string $method)
	 * @method static void bindMethod(array|string $method, Closure $callback)
	 * @method static mixed callMethodBinding(string $method, mixed $instance)
	 * @method static void addContextualBinding(string $concrete, string $abstract, Closure|string $implementation)
	 * @method static void bindIf(string $abstract, Closure|string|null $concrete, bool $shared)
	 * @method static void singleton(string $abstract, Closure|string|null $concrete)
	 * @method static void singletonIf(string $abstract, Closure|string|null $concrete)
	 * @method static void scoped(string $abstract, Closure|string|null $concrete)
	 * @method static void scopedIf(string $abstract, Closure|string|null $concrete)
	 * @method static void extend(string $abstract, Closure $closure)
	 * @method static mixed instance(string $abstract, mixed $instance)
	 * @method static void tag(array|string $abstracts, mixed $tags)
	 * @method static iterable tagged(string $tag)
	 * @method static void alias(string $abstract, string $alias)
	 * @method static mixed rebinding(string $abstract, Closure $callback)
	 * @method static mixed refresh(string $abstract, mixed $target, string $method)
	 * @method static Closure wrap(Closure $callback, array $parameters)
	 * @method static mixed call(callable|array|string $callback, array $parameters, ?string $defaultMethod)
	 * @method static Closure factory(string $abstract)
	 * @method static mixed makeWith(callable|string $abstract, array $parameters)
	 * @method static mixed get(string $id)
	 * @method static mixed build(Closure|string $concrete)
	 * @method static void beforeResolving(Closure|string $abstract, ?Closure $callback)
	 * @method static void resolving(Closure|string $abstract, ?Closure $callback)
	 * @method static void afterResolving(Closure|string $abstract, ?Closure $callback)
	 * @method static array getBindings()
	 * @method static string getAlias(string $abstract)
	 * @method static void forgetExtenders(string $abstract)
	 * @method static void forgetInstance(string $abstract)
	 * @method static void forgetInstances()
	 * @method static void forgetScopedInstances()
	 * @method static static getInstance()
	 * @method static \Magnetar\Container\Container|static setInstance(?\Magnetar\Container\Container $container)
	 * @method static bool offsetExists(mixed $key)
	 * @method static mixed offsetGet(mixed $key)
	 * @method static void offsetSet(mixed $key, mixed $value)
	 * @method static void offsetUnset(mixed $key)
	 * 
	 * @see \Magnetar\Application
	 */
	class App extends Facade {
		/**
		 * Get the named key that this facade represents
		 * @return string
		 */
		protected static function getFacadeKey(): string {
			return 'app';
		}
	}