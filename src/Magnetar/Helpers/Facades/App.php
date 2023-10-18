<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method setBasePath(string $base_path): void
	 * @method pathBase(string $rel_path=''): string
	 * @method setAppPath(string $path): self
	 * @method pathApp(string $rel_path=''): string
	 * @method setConfigPath(string $path): self
	 * @method pathConfig(string $rel_path=''): string
	 * @method setDataPath(string $path): self
	 * @method pathData(string $rel_path=''): string
	 * @method setPublicPath(string $path): self
	 * @method pathPublic(string $rel_path=''): string
	 * @method setAssetsPath(string $path): self
	 * @method pathAssets(string $rel_path=''): string
	 * @method setStoragePath(string $path): self
	 * @method pathStorage(string $rel_path=''): string
	 * @method setRoutingPath(string $path): self
	 * @method pathRouting(string $rel_path=''): string
	 * @method setThemesPath(string $path): self
	 * @method pathThemes(string $rel_path=''): string
	 * @method joinPath(string $base_path, string $rel_path=''): string
	 * @method hasBeenBootstrapped(): bool
	 * @method bootstrapWith(array $bootstrappers): void
	 * @method hasBootedServiceProviders(): bool
	 * @method registerConfiguredServiceProviders(): void
	 * @method bootServiceProviders(): void
	 * @method bootServiceProvider(Magnetar\Helpers\ServiceProvider|string $provider): void
	 * @method registerServiceProvider(Magnetar\Helpers\ServiceProvider|string $provider): Magnetar\Helpers\ServiceProvider
	 * @method resolveServiceProvider(string $provider): Magnetar\Helpers\ServiceProvider
	 * @method getServiceProvider(Magnetar\Helpers\ServiceProvider|string $provider): ?Magnetar\Helpers\ServiceProvider
	 * @method getServiceProviders(Magnetar\Helpers\ServiceProvider|string $provider): array
	 * @method registerCoreContainerAliases(): void
	 * @method make(callable|string $abstract, array $parameters=[]): mixed
	 * @method bound(string $abstract): bool
	 * @method flush(): void
	 * @method registerTerminateCallback(callable|array|string $callback): void
	 * @method terminate(): void
	 * @method setEnvironment(string $env): void
	 * @method environment(): string
	 * @method isDevEnv(): bool
	 * @method isTestEnv(): bool
	 * @method isLiveEnv(): bool
	 * @method when( $concrete): Magnetar\Container\ContextualBindingBuilder
	 * @method has(string $id): bool
	 * @method resolved(string $abstract): bool
	 * @method isShared(string $abstract): bool
	 * @method isAlias(string $name): bool
	 * @method bind(string $abstract, Closure|string|null $concrete=null, bool $shared=false): void
	 * @method hasMethodBinding(string $method): bool
	 * @method bindMethod(array|string $method, Closure $callback): void
	 * @method callMethodBinding(string $method, mixed $instance): mixed
	 * @method addContextualBinding(string $concrete, string $abstract, Closure|string $implementation): void
	 * @method bindIf(string $abstract, Closure|string|null $concrete=null, bool $shared=false): void
	 * @method singleton(string $abstract, Closure|string|null $concrete=null): void
	 * @method singletonIf(string $abstract, Closure|string|null $concrete=null): void
	 * @method scoped(string $abstract, Closure|string|null $concrete=null): void
	 * @method scopedIf(string $abstract, Closure|string|null $concrete=null): void
	 * @method extend(string $abstract, Closure $closure): void
	 * @method instance(string $abstract, mixed $instance): mixed
	 * @method tag(array|string $abstracts, mixed $tags): void
	 * @method tagged(string $tag): iterable
	 * @method alias(string $abstract, string $alias): void
	 * @method rebinding(string $abstract, Closure $callback): mixed
	 * @method refresh(string $abstract, mixed $target, string $method): mixed
	 * @method wrap(Closure $callback, array $parameters=[]): Closure
	 * @method call(callable|array|string $callback, array $parameters=[], ?string $defaultMethod=null): mixed
	 * @method factory(string $abstract): Closure
	 * @method makeWith(callable|string $abstract, array $parameters=[]): mixed
	 * @method get(string $id): mixed
	 * @method build(Closure|string $concrete): mixed
	 * @method beforeResolving(Closure|string $abstract, ?Closure $callback=null): void
	 * @method resolving(Closure|string $abstract, ?Closure $callback=null): void
	 * @method afterResolving(Closure|string $abstract, ?Closure $callback=null): void
	 * @method getBindings(): array
	 * @method getAlias(string $abstract): string
	 * @method forgetExtenders(string $abstract): void
	 * @method forgetInstance(string $abstract): void
	 * @method forgetInstances(): void
	 * @method forgetScopedInstances(): void
	 * @method getInstance(): static
	 * @method setInstance(?Magnetar\Container\Container $container=null): Magnetar\Container\Container|static
	 * @method offsetExists(mixed $key): bool
	 * @method offsetGet(mixed $key): mixed
	 * @method offsetSet(mixed $key, mixed $value): void
	 * @method offsetUnset(mixed $key): void
	 * 
	 * @see Magnetar\Application
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