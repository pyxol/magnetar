<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method setBasePath(string $base_path): void
	 * @method basePath(string $rel_path=''): string
	 * @method hasBeenBootstrapped(): bool
	 * @method bootstrapWith(array $bootstrappers): void
	 * @method registerCoreContainerAliases(): void
	 * @method when($concrete): Magnetar\Container\ContextualBindingBuilder
	 * @method bound(string $abstract): bool
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
	 * @method refresh($abstract, $target, $method)
	 * @method wrap(Closure $callback, array $parameters=[]): Closure
	 * @method call(callable|string $callback, array $parameters=[], string|null $defaultMethod=null): mixed
	 * @method factory(string $abstract): Closure
	 * @method makeWith(string|callable $abstract, array $parameters=[]): mixed
	 * @method make(string|callable $abstract, array $parameters=[]): mixed
	 * @method get(string $id): mixed
	 * @method build(Closure|string $concrete): mixed
	 * @method beforeResolving(Closure|string $abstract, Closure|null $callback=null): void
	 * @method resolving(Closure|string $abstract, Closure|null $callback=null): void
	 * @method afterResolving(Closure|string $abstract, Closure|null $callback=null): void
	 * @method getBindings(): array
	 * @method getAlias(string $abstract): string
	 * @method forgetExtenders(string $abstract): void
	 * @method forgetInstance(string $abstract): void
	 * @method forgetInstances(): void
	 * @method forgetScopedInstances(): void
	 * @method flush(): void
	 * @method offsetExists(mixed $key): bool
	 * @method offsetGet(mixed $key): mixed
	 * @method offsetSet(mixed $key, mixed $value): void
	 * @method offsetUnset($key): void
	 * @method __get(string $key): mixed
	 * @method __set(string $key, mixed $value): void
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