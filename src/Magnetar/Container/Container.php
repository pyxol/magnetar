<?php
	declare(strict_types=1);
	
	namespace Magnetar\Container;
	
	use ArrayAccess;
	use Closure;
	use Exception;
	use TypeError;
	use InvalidArgumentException;
	use ReflectionClass;
	use ReflectionException;
	use ReflectionFunction;
	use ReflectionParameter;
	
	use Magnetar\Container\ContainerInterface;
	use Magnetar\Container\Helper;
	use Magnetar\Container\BoundMethod;
	use Magnetar\Container\RewindableGenerator;
	use Magnetar\Container\ContextualBindingBuilder;
	use Magnetar\Container\Exceptions\InstanceNotFoundException;
	use Magnetar\Container\Exceptions\ResolvingDependenciesException;
	use Magnetar\Container\Exceptions\SelfAliasException;
	use Magnetar\Container\Exceptions\UninstantiableException;
	use Magnetar\Container\Exceptions\BuildResolutionException;
	
	/**
	 * A dependency injection container
	 */
	class Container implements ArrayAccess, ContainerInterface {
		/**
		 * Currently available global container instance (if any)
		 * @var static
		 */
		protected static $instance;
		
		/**
		 * An array of the types that have been resolved
		 * @var array
		 */
		protected array $resolved = [];
		
		/**
		 * The container's bindings
		 * @var array
		 */
		protected array $bindings = [];
		
		/**
		 * The container's method bindings
		 * @var array
		 */
		protected array $methodBindings = [];
		
		/**
		 * Storage for the container's instances
		 * @var array
		 */
		protected array $instances = [];
		
		/**
		 * Storage for the container's aliases
		 * @var array
		 */
		protected array $scopedInstances = [];
		
		/**
		 * Known aliases for abstracts
		 */
		protected array $aliases = [];
		
		/**
		 * Registered aliases referenced by the abstract name
		 * @var array
		 */
		protected array $abstractAliases = [];
		
		/**
		 * The extension closures for services.
		 *
		 * @var array[]
		 */
		protected array $extenders = [];
		
		/**
		 * Tags for a binding
		 *
		 * @var array
		 */
		protected array $tags = [];
		
		/**
		 * The container's build stack
		 * @var array
		 */
		protected array $buildStack = [];
		
		/**
		 * Parameter override stack
		 * @var array
		 */
		protected array $with = [];
		
		/**
		 * The container's contextual bindings
		 * @var array
		 */
		protected array $contextual = [];
		
		/**
		 * The container's rebound callbacks
		 * @var array
		 */
		protected array $reboundCallbacks = [];
		
		/**
		 * The container's global before resolving callbacks
		 * @var array
		 */
		protected array $globalBeforeResolvingCallbacks = [];
		
		/**
		 * The container's before resolving callbacks
		 * @var array
		 */
		protected array $globalResolvingCallbacks = [];
		
		/**
		 * The container's after resolving callbacks
		 * @var array
		 */
		protected array $globalAfterResolvingCallbacks = [];
		
		/**
		 * The container's before resolving callbacks
		 * @var array
		 */
		protected array $beforeResolvingCallbacks = [];
		
		/**
		 * The container's resolving callbacks
		 * @var array
		 */
		protected array $resolvingCallbacks = [];
		
		/**
		 * The container's after resolving callbacks
		 * @var array
		 */
		protected array $afterResolvingCallbacks = [];
		
		/**
		 * Define a contextual binding
		 * @param array|string $concrete
		 * @return Magnetar\Container\ContextualBindingBuilder
		 */
		public function when($concrete): ContextualBindingBuilder {
			$aliases = [];
			
			foreach (Helper::arrayWrap($concrete) as $c) {
				$aliases[] = $this->getAlias($c);
			}
			
			return new ContextualBindingBuilder($this, $aliases);
		}
		
		/**
		 * Determine if the given abstract type has been bound
		 * @param string $abstract
		 * @return bool
		 */
		public function bound(string $abstract): bool {
			return (
				isset($this->bindings[ $abstract ])
				|| isset($this->instances[ $abstract ])
				|| $this->isAlias($abstract)
			);
		}
		
		/**
		 * {@inheritdoc}
		 * @return bool
		 */
		public function has(string $id): bool {
			return $this->bound($id);
		}
		
		/**
		 * Get the concrete instance of the given abstract type
		 * @param string $abstract
		 * @return bool
		 */
		public function resolved(string $abstract): bool {
			if($this->isAlias($abstract)) {
				$abstract = $this->getAlias($abstract);
			}
			
			return (
				isset($this->resolved[ $abstract ])
				|| isset($this->instances[ $abstract ])
			);
		}
		
		/**
		 * Determine if the given abstract type is shared
		 * @param string $abstract
		 * @return bool
		 */
		public function isShared(string $abstract): bool {
			return (
				isset($this->instances[ $abstract ])
				|| (
					isset($this->bindings[ $abstract ]['shared'])
					&& (true === $this->bindings[ $abstract ]['shared'])
				)
			);
		}
		
		/**
		 * Determine if the given name is an alias
		 * @param string $name
		 * @return bool
		 */
		public function isAlias(string $name): bool {
			return isset($this->aliases[ $name ]);
		}
		
		/**
		 * Register a binding
		 * @param string $abstract
		 * @param Closure|null $concrete
		 * @param bool $shared
		 * @return void
		 */
		public function bind(
			string $abstract,
			Closure|string|null $concrete=null,
			bool $shared=false
		): void {
			// forget old references
			$this->dropStaleInstances($abstract);
			
			if(null === $concrete) {
				$concrete = $abstract;
			}
			
			if(!($concrete instanceof Closure)) {
				if(!is_string($concrete)) {
					throw new TypeError(self::class .'::bind(): Argument #2 (concrete) must be of type Closure|string|null');
				}
				
				$concrete = $this->getClosure($abstract, $concrete);
			}
			
			// add new reference
			$this->bindings[ $abstract ] = compact('concrete', 'shared');
			
			// If the abstract type was already resolved in this container we'll fire the
			// rebound listener so that any objects which have already gotten resolved
			// can have their copy of the object updated via the listener callbacks.
			if($this->resolved($abstract)) {
				$this->rebound($abstract);
			}
		}
		
		/**
		 * Get the Closure to be used when building a type
		 *
		 * @param string $abstract
		 * @param string $concrete
		 * @return Closure
		 */
		protected function getClosure(string $abstract, string $concrete): Closure {
			return function(
				Container $container,
				array $parameters=[]
			) use ($abstract, $concrete): mixed {
				if($abstract == $concrete) {
					return $container->build($concrete);
				}
				
				return $container->resolve($concrete, $parameters);
			};
		}
		
		/**
		 * Determine if the container has a method binding
		 * @param string $method
		 * @return bool
		 */
		public function hasMethodBinding(string $method): bool {
			return isset($this->methodBindings[ $method ]);
		}
		
		/**
		 * Bind a callback to resolve with Container::call
		 * @param array|string $method
		 * @param Closure $callback
		 * @return void
		 */
		public function bindMethod(array|string $method, Closure $callback): void {
			$this->methodBindings[ $this->parseBindMethod($method) ] = $callback;
		}
		
		/**
		 * Get the method to be bound in class@method format
		 * @param array|string $method
		 * @return string
		 */
		protected function parseBindMethod(array|string $method): string {
			if(is_array($method)) {
				return $method[0] .'@'. $method[1];
			}
			
			return $method;
		}
		
		/**
		 * Get the method binding for the given method
		 * @param string $method
		 * @param mixed $instance
		 * @return mixed
		 */
		public function callMethodBinding(string $method, mixed $instance): mixed {
			return call_user_func($this->methodBindings[ $method ], $instance, $this);
		}
		
		/**
		 * Add a contextual binding to the container
		 * @param string $concrete
		 * @param string $abstract
		 * @param Closure|string $implementation
		 * @return void
		 */
		public function addContextualBinding(
			string $concrete,
			string $abstract,
			Closure|string $implementation
		): void {
			$this->contextual[ $concrete ][ $this->getAlias($abstract) ] = $implementation;
		}
		
		/**
		 * Register a binding if it hasn't already been registered
		 * @param string $abstract
		 * @param Closure|string|null $concrete
		 * @param bool $shared
		 * @return void
		 */
		public function bindIf(
			string $abstract,
			Closure|string|null $concrete=null,
			bool $shared=false
		): void {
			if(!$this->bound($abstract)) {
				$this->bind($abstract, $concrete, $shared);
			}
		}
		
		/**
		 * Register a shared binding in the container
		 * @param string $abstract The abstract type to bind
		 * @param Closure|string|null $concrete The concrete to bind
		 * @return void
		 */
		public function singleton(
			string $abstract,
			Closure|string|null $concrete=null
		): void {
			$this->bind($abstract, $concrete, true);
		}
		
		/**
		 * Register a shared binding if it hasn't already been registered
		 * @param string $abstract The abstract type to bind
		 * @param Closure|string|null $concrete The concrete to bind
		 * @return void
		 */
		public function singletonIf(
			string $abstract,
			Closure|string|null $concrete=null
		): void {
			if(!$this->bound($abstract)) {
				$this->singleton($abstract, $concrete);
			}
		}
		
		/**
		 * Register a scoped binding in the container
		 * @param string $abstract The abstract type to bind
		 * @param Closure|string|null $concrete The concrete to bind
		 * @return void
		 */
		public function scoped(
			string $abstract,
			Closure|string|null $concrete=null
		): void {
			$this->scopedInstances[] = $abstract;
			
			$this->singleton($abstract, $concrete);
		}
		
		/**
		 * Register a scoped binding if it hasn't already been registered
		 * @param string $abstract The abstract type to bind
		 * @param Closure|string|null $concrete The concrete to bind
		 * @return void
		 */
		public function scopedIf(
			string $abstract,
			Closure|string|null $concrete=null
		): void {
			if(!$this->bound($abstract)) {
				$this->scoped($abstract, $concrete);
			}
		}
		
		/**
		 * 'Extend' an abstract type in the container
		 * @param string $abstract The abstract type to extend
		 * @param Closure $closure The closure to use to extend the type
		 * @return void
		 * 
		 * @throws InvalidArgumentException
		 */
		public function extend(string $abstract, Closure $closure): void {
			$abstract = $this->getAlias($abstract);
			
			if(isset($this->instances[ $abstract ])) {
				$this->instances[ $abstract ] = $closure($this->instances[ $abstract ], $this);
				
				$this->rebound($abstract);
			} else {
				$this->extenders[ $abstract ][] = $closure;
				
				if($this->resolved($abstract)) {
					$this->rebound($abstract);
				}
			}
		}
		
		/**
		 * Register an existing instance as shared in the container
		 * @param string $abstract The abstract type to bind
		 * @param mixed $instance The instance to bind
		 * @return mixed
		 */
		public function instance(string $abstract, mixed $instance): mixed {
			$this->removeAbstractAlias($abstract);
			
			$isBound = $this->bound($abstract);
			
			unset($this->aliases[ $abstract ]);
			
			// if the abstract was already bound, fire rebound callbacks for it
			$this->instances[ $abstract ] = $instance;
			
			if($isBound) {
				$this->rebound($abstract);
			}
			
			return $instance;
		}
		
		/**
		 * Remove an alias from the contextual binding alias cache
		 * @param string $searched The alias to remove
		 * @return void
		 */
		protected function removeAbstractAlias(string $searched): void {
			if(!isset($this->aliases[ $searched ])) {
				return;
			}
			
			foreach($this->abstractAliases as $abstract => $aliases) {
				foreach($aliases as $index => $alias) {
					if($alias == $searched) {
						unset($this->abstractAliases[ $abstract ][ $index ]);
					}
				}
			}
		}
		
		/**
		 * Assign a set of tags to a given binding
		 * @param array|string $abstracts The abstracts to tag
		 * @param mixed ...$tags The tags to assign
		 * @return void
		 */
		public function tag(array|string $abstracts, mixed $tags): void {
			$tags = is_array($tags)?$tags:array_slice(func_get_args(), 1);
			
			foreach($tags as $tag) {
				if(!isset($this->tags[ $tag ])) {
					$this->tags[ $tag ] = [];
				}
				
				foreach((array)$abstracts as $abstract) {
					$this->tags[ $tag ][] = $abstract;
				}
			}
		}
		
		/**
		 * Resolve all of the bindings for a given tag
		 * @param string $tag The tag to resolve
		 * @return iterable An iterable of the resolved bindings
		 */
		public function tagged(string $tag): iterable {
			if(!isset($this->tags[ $tag ])) {
				return [];
			}
			
			return new RewindableGenerator(function () use ($tag) {
				foreach ($this->tags[ $tag ] as $abstract) {
					yield $this->make($abstract);
				}
			}, count($this->tags[ $tag ]));
		}
		
		/**
		 * Alias a type to a different name
		 * @param string $abstract The abstract type to alias
		 * @param string $alias The alias to use
		 * @return void
		 * 
		 * @throws SelfAliasException
		 */
		public function alias(string $abstract, string $alias): void {
			if($alias === $abstract) {
				throw new SelfAliasException('['. $abstract .'] is aliased to itself.');
			}
			
			$this->aliases[ $alias ] = $abstract;
			$this->abstractAliases[ $abstract ][] = $alias;
		}
		
		/**
		 * Bind a new callback to an abstract's rebind event
		 * @param string $abstract The abstract type to bind
		 * @param Closure $callback The callback to bind
		 * @return mixed
		 */
		public function rebinding(string $abstract, Closure $callback): mixed {
			$this->reboundCallbacks[ $abstract = $this->getAlias($abstract) ][] = $callback;
			
			if($this->bound($abstract)) {
				return $this->make($abstract);
			}
		}
		
		/**
		 * Refresh an instance on the given target and method
		 * @param string $abstract The abstract type to refresh
		 * @param mixed $target The target to refresh
		 * @param string $method The method to refresh
		 * @return mixed
		 */
		public function refresh(
			string $abstract,
			mixed $target,
			string $method
		): mixed {
			return $this->rebinding($abstract, function ($app, $instance) use ($target, $method) {
				$target->{$method}($instance);
			});
		}
		
		/**
		 * Fire the rebound callbacks for the given abstract type
		 * @param string $abstract The abstract type to rebound
		 * @return void
		 */
		protected function rebound(string $abstract): void {
			$instance = $this->make($abstract);
			
			foreach($this->getReboundCallbacks($abstract) as $callback) {
				$callback($this, $instance);
			}
		}
		
		/**
		 * Get the rebound callbacks for a given type
		 * @param string $abstract The abstract type to get callbacks for
		 * @return array The callbacks
		 */
		protected function getReboundCallbacks(string $abstract): array {
			return $this->reboundCallbacks[ $abstract ] ?? [];
		}
		
		 /**
		 * Wrap the given closure such that its dependencies will be injected when executed
		 * @param Closure $callback The callback to wrap
		 * @param array $parameters The parameters to inject
		 * @return Closure The wrapped callback
		 */
		public function wrap(Closure $callback, array $parameters=[]): Closure {
			return fn () => $this->call($callback, $parameters);
		}
		
		/**
		 * Call the given Closure / class@method and inject its dependencies
		 * @param callable|array|string $callback The callback to call
		 * @param array<string, mixed> $parameters The parameters to inject
		 * @param string|null $defaultMethod The default method to call
		 * @return mixed The result of the callback
		 *
		 * @throws InvalidArgumentException
		 */
		public function call(
			callable|array|string $callback,
			array $parameters=[],
			string|null $defaultMethod=null
		): mixed {
			$pushedToBuildStack = false;
			
			if(($className = $this->getClassForCallable($callback)) && !in_array($className, $this->buildStack, true)) {
				$this->buildStack[] = $className;
				
				$pushedToBuildStack = true;
			}
			
			$result = BoundMethod::call(
				$this,
				$callback,
				$parameters,
				$defaultMethod
			);
			
			if($pushedToBuildStack) {
				array_pop($this->buildStack);
			}
			
			return $result;
		}
		
		/**
		 * Get the class name for the given callback, if one can be determined
		 * @param callable|array|string $callback The callback to get the class name for
		 * @return string|false The class name, or false if one cannot be determined
		 */
		protected function getClassForCallable(callable|array|string $callback): string|false {
			if(PHP_VERSION_ID >= 80200) {
				if(
					is_callable($callback)
					&& !($reflector = new ReflectionFunction($callback(...)))->isAnonymous()
				) {
					return $reflector->getClosureScopeClass()->name ?? false;
				}
				
				return false;
			}
			
			if(!is_array($callback)) {
				return false;
			}
			
			return is_string($callback[0]) ? $callback[0] : get_class($callback[0]);
		}
		
		/**
		 * Get a closure to resolve the given type from the container
		 * @param string $abstract The abstract type to resolve
		 * @return Closure The closure to resolve the type
		 */
		public function factory(string $abstract): Closure {
			return fn () => $this->make($abstract);
		}
		
		/**
		 * An alias function name for make()
		 * @param string|callable $abstract The abstract type to resolve
		 * @param array $parameters The parameters to inject
		 * @return mixed The resolved instance
		 *
		 * @throws BuildResolutionException
		 */
		public function makeWith(string|callable $abstract, array $parameters=[]): mixed {
			return $this->make($abstract, $parameters);
		}
		
		/**
		 * Resolve the given type from the container
		 * @param string|callable $abstract The abstract type to resolve
		 * @param array $parameters The parameters to inject
		 * @return mixed The resolved instance
		 */
		public function make(string|callable $abstract, array $parameters=[]): mixed {
			return $this->resolve($abstract, $parameters);
		}
		
		/**
		 * {@inheritdoc}
		 */
		public function get(string $id): mixed {
			try {
				return $this->resolve($id);
			} catch(Exception $e) {
				if($this->has($id)) {
					// Exception caught from resolved instance, rethrow
					throw $e;
				}
				
				// trouble calling resolve, throw new exception
				throw new InstanceNotFoundException(
					$e->getMessage(),
					$e->getCode(),
					$e
				);
			}
		}
		
		/**
		 * Resolve the given abstract type to a concrete instance
		 * @param string|callable $abstract The abstract type to resolve
		 * @param array $parameters The parameters to inject
		 * @param bool $raiseEvents Whether to raise events
		 * @return mixed The resolved instance
		 * 
		 * @throws Magnetar\Container\BuildResolutionException
		 */
		protected function resolve(
			string|callable $abstract,
			array $parameters=[],
			bool $raiseEvents=true
		): mixed {
			$abstract = $this->getAlias($abstract);
			
			// First we'll fire any event handlers which handle the 'before' resolving of
			// specific types. This gives some hooks the chance to add various extends
			// calls to change the resolution of objects that they're interested in.
			if($raiseEvents) {
				$this->fireBeforeResolvingCallbacks($abstract, $parameters);
			}
			
			$concrete = $this->getContextualConcrete($abstract);
			
			// if parameters are provided, we need to create a contextualized instance
			$needsContextualBuild = (!empty($parameters) || (null !== $concrete));
			
			// if the instance is already resolved and there's no context, return it
			if(isset($this->instances[ $abstract ]) && !$needsContextualBuild) {
				return $this->instances[ $abstract ];
			}
			
			$this->with[] = $parameters;
			
			if(null === $concrete) {
				$concrete = $this->getConcrete($abstract);
			}
			
			// instantiante an instance of the concrete type
			$object = $this->isBuildable($concrete, $abstract)
				? $this->build($concrete)
				: $this->make($concrete);
			
			// If we defined any extenders for this type, we'll need to spin through them
			// and apply them to the object being built. This allows for the extension
			// of services, such as changing configuration or decorating the object.
			foreach($this->getExtenders($abstract) as $extender) {
				$object = $extender($object, $this);
			}
			
			// if the instance is a singleton, store it in the instances array
			if($this->isShared($abstract) && !$needsContextualBuild) {
				$this->instances[ $abstract ] = $object;
			}
			
			if($raiseEvents) {
				$this->fireResolvingCallbacks($abstract, $object);
			}
			
			$this->resolved[ $abstract ] = true;
			
			array_pop($this->with);
			
			return $object;
		}
		
		/**
		 * Get the concrete type for a given abstract
		 * @param string|callable $abstract The abstract type to get the concrete type for
		 * @return mixed The concrete type
		 */
		protected function getConcrete(string|callable $abstract): mixed {
			if(isset($this->bindings[ $abstract ])) {
				return $this->bindings[ $abstract ]['concrete'];
			}
			
			return $abstract;
		}
		
		/**
		 * Resolve the given abstract type to a concrete instance
		 * @param string|callable $abstract The abstract type to resolve
		 * @return Closure|string|array|null The concrete instance
		 */
		protected function getContextualConcrete(string|callable $abstract): Closure|string|array|null {
			if(null !== ($binding = $this->findInContextualBindings($abstract))) {
				return $binding;
			}
			
			if(empty($this->abstractAliases[ $abstract ])) {
				return null;
			}
			
			foreach($this->abstractAliases[ $abstract ] as $alias) {
				if(null !== ($binding = $this->findInContextualBindings($alias))) {
					return $binding;
				}
			}
			
			return null;
		}
		
		/**
		 * Find the concrete binding for the given abstract
		 * @param string|callable $abstract The abstract type to find the concrete binding for
		 * @return Closure|string|null The concrete binding
		 */
		protected function findInContextualBindings(string|callable $abstract): Closure|string|null {
			return $this->contextual[ end($this->buildStack) ][ $abstract ] ?? null;
		}
		
		/**
		 * Determine if the given concrete is buildable
		 * @param mixed $concrete The concrete to check
		 * @param string $abstract The abstract type to check
		 * @return bool Whether the concrete is buildable
		 */
		protected function isBuildable(mixed $concrete, string $abstract): bool {
			return (($concrete === $abstract) || ($concrete instanceof Closure));
		}
		
		/**
		 * Instantiate a concrete instance of the given type
		 * @param Closure|string $concrete The concrete to instantiate
		 * @param array $parameters The parameters to inject
		 * @return mixed The instantiated concrete
		 */
		public function build(Closure|string $concrete): mixed {
			// If the concrete type is actually a Closure, we will just execute it and
			// hand back the results of the functions, which allows functions to be
			// used as resolvers for more fine-tuned resolution of these objects.
			if($concrete instanceof Closure) {
				return $concrete($this, $this->getLastParameterOverride());
			}
			
			try {
				$reflector = new ReflectionClass($concrete);
			} catch(ReflectionException $e) {
				throw new BuildResolutionException('Target class ['. $concrete .'] does not exist.', 0, $e);
			}
			
			// If the type is not instantiable, the developer is attempting to resolve
			// an abstract type such as an Interface or Abstract Class and there is
			// no binding registered for the abstractions so we need to bail out.
			if(!$reflector->isInstantiable()) {
				return $this->notInstantiable($concrete);
			}
			
			$this->buildStack[] = $concrete;
			
			$constructor = $reflector->getConstructor();
			
			// If there are no constructors, that means there are no dependencies then
			// we can just resolve the instances of the objects right away, without
			// resolving any other types or dependencies out of these containers.
			if(null === $constructor) {
				array_pop($this->buildStack);
				
				return new $concrete;
			}
			
			$dependencies = $constructor->getParameters();
			
			// Once we have all the constructor's parameters we can create each of the
			// dependency instances and then use the reflection instances to make a
			// new instance of this class, injecting the created dependencies in.
			try {
				$instances = $this->resolveDependencies($dependencies);
			} catch(BuildResolutionException $e) {
				array_pop($this->buildStack);
				
				throw $e;
			}
			
			array_pop($this->buildStack);
			
			return $reflector->newInstanceArgs($instances);
		}
		
		/**
		 * Resolve dependencies for the given concrete
		 * @param ReflectionParameter[] $dependencies The dependencies to resolve
		 * @return array The resolved dependencies
		 */
		protected function resolveDependencies(array $dependencies): array {
			$results = [];
			
			foreach($dependencies as $dependency) {
				if($this->hasParameterOverride($dependency)) {
					$results[] = $this->getParameterOverride($dependency);
					
					continue;
				}
				
				$result = (null === Helper::getParameterClassName($dependency))
					? $this->resolvePrimitive($dependency)
					: $this->resolveClass($dependency);
				
				if($dependency->isVariadic()) {
					$results = array_merge($results, $result);
				} else {
					$results[] = $result;
				}
			}
			
			return $results;
		}
		
		/**
		 * Determine if the given dependency has a parameter override
		 * @param ReflectionParameter $dependency The dependency to check
		 * @return bool Whether the dependency has a parameter override
		 */
		protected function hasParameterOverride(ReflectionParameter $dependency): bool {
			return array_key_exists($dependency->name, $this->getLastParameterOverride());
		}
		
		/**
		 * Get the parameter override for the given dependency
		 * @param ReflectionParameter $dependency The dependency to get the parameter override for
		 * @return mixed The parameter override
		 */
		protected function getParameterOverride(ReflectionParameter $dependency): mixed {
			return $this->getLastParameterOverride()[ $dependency->name ];
		}
		
		/**
		 * Get the last parameter override
		 * @return array The last parameter override
		 */
		protected function getLastParameterOverride(): array {
			return count($this->with) ? end($this->with) : [];
		}
		
		/**
		 * Resolve a primitive dependency
		 * @param ReflectionParameter $dependency The dependency to resolve
		 * @return mixed The resolved dependency
		 * 
		 * @throws ResolvingDependenciesException
		 */
		protected function resolvePrimitive(ReflectionParameter $parameter): mixed {
			if(null !== ($concrete = $this->getContextualConcrete('$'. $parameter->getName()))) {
				return Helper::unwrapIfClosure($concrete, $this);
			}
			
			if($parameter->isDefaultValueAvailable()) {
				return $parameter->getDefaultValue();
			}
			
			if($parameter->isVariadic()) {
				return [];
			}
			
			$this->unresolvablePrimitive($parameter);
		}
		
		/**
		 * Resolve a class based dependency from the container
		 * @param ReflectionParameter $parameter The dependency to resolve
		 * @return mixed The resolved dependency
		 *
		 * @throws Magnetar\Container\ResolvingDependenciesException
		 */
		protected function resolveClass(ReflectionParameter $parameter): mixed {
			try {
				return $parameter->isVariadic()
					? $this->resolveVariadicClass($parameter)
					: $this->make(Helper::getParameterClassName($parameter));
			} catch(InstanceNotFoundException $e) {
				if($parameter->isDefaultValueAvailable()) {
					array_pop($this->with);
					
					return $parameter->getDefaultValue();
				}
				
				if($parameter->isVariadic()) {
					array_pop($this->with);
					
					return [];
				}
				
				throw $e;
			}
		}
		
		/**
		 * Resolve a class based variadic dependency from the container
		 * @param ReflectionParameter $parameter The dependency to resolve
		 * @return mixed The resolved dependency
		 */
		protected function resolveVariadicClass(ReflectionParameter $parameter): mixed {
			$className = Helper::getParameterClassName($parameter);
			
			$abstract = $this->getAlias($className);
			
			if(!is_array($concrete = $this->getContextualConcrete($abstract))) {
				return $this->make($className);
			}
			
			return array_map(fn ($abstract) => $this->resolve($abstract), $concrete);
		}
		
		/**
		 * Throw an exception that the concrete is not instantiable
		 * @param string $concrete The concrete that is not instantiable
		 * @return void
		 * 
		 * @throws UninstantiableException
		 */
		protected function notInstantiable(string $concrete): void {
			if(!empty($this->buildStack)) {
				$previous = implode(', ', $this->buildStack);
				
				throw new UninstantiableException('Target ['. $concrete .'] is not instantiable while building ['. $previous .'].');
			}
			
			throw new UninstantiableException('Target ['. $concrete .'] is not instantiable.');
		}
		
		/**
		 * Throw an exception for an unresolvable primitive
		 * @param ReflectionParameter $parameter The parameter that is unresolvable
		 * @return void
		 *
		 * @throws BuildResolutionException
		 */
		protected function unresolvablePrimitive(ReflectionParameter $parameter): void {
			throw new BuildResolutionException('Unresolvable dependency resolving ['. $parameter .'] in class '. $parameter->getDeclaringClass()->getName());
		}
		
		/**
		 * Register a new before resolving callback for all types
		 * @param Closure|string $abstract The abstract type to register the callback for
		 * @param Closure|null $callback The callback to register
		 * @return void
		 */
		public function beforeResolving(
			Closure|string $abstract,
			Closure|null $callback=null
		): void {
			if(is_string($abstract)) {
				$abstract = $this->getAlias($abstract);
			}
			
			if(($abstract instanceof Closure) && (null === $callback)) {
				$this->globalBeforeResolvingCallbacks[] = $abstract;
			} else {
				$this->beforeResolvingCallbacks[ $abstract ][] = $callback;
			}
		}
		
		/**
		 * Register a new resolving callback
		 * @param Closure|string $abstract The abstract type to register the callback for
		 * @param Closure|null $callback The callback to register
		 * @return void
		 */
		public function resolving(
			Closure|string $abstract,
			Closure|null $callback=null
		): void {
			if(is_string($abstract)) {
				$abstract = $this->getAlias($abstract);
			}
			
			if((null === $callback) && ($abstract instanceof Closure)) {
				$this->globalResolvingCallbacks[] = $abstract;
			} else {
				$this->resolvingCallbacks[ $abstract ][] = $callback;
			}
		}
		
		/**
		 * Register a new after resolving callback for all types
		 * @param Closure|string $abstract The abstract type to register the callback for
		 * @param Closure|null $callback The callback to register
		 * @return void
		 */
		public function afterResolving(
			Closure|string $abstract,
			Closure|null $callback=null
		): void {
			if(is_string($abstract)) {
				$abstract = $this->getAlias($abstract);
			}
			
			if(($abstract instanceof Closure) && (null === $callback)) {
				$this->globalAfterResolvingCallbacks[] = $abstract;
			} else {
				$this->afterResolvingCallbacks[ $abstract ][] = $callback;
			}
		}
		
		/**
		 * Fire all of the before resolving callbacks
		 * @param string $abstract The abstract type to fire the callbacks for
		 * @param array $parameters The parameters to inject
		 * @return void
		 */
		protected function fireBeforeResolvingCallbacks(
			string $abstract,
			array $parameters=[]
		): void {
			$this->fireBeforeCallbackArray($abstract, $parameters, $this->globalBeforeResolvingCallbacks);
			
			foreach($this->beforeResolvingCallbacks as $type => $callbacks) {
				if(($type === $abstract) || is_subclass_of($abstract, $type)) {
					$this->fireBeforeCallbackArray($abstract, $parameters, $callbacks);
				}
			}
		}
		
		/**
		 * Fire an array of callbacks with an object
		 * @param string $abstract The abstract type to fire the callbacks for
		 * @param array $parameters The parameters to inject
		 * @param array $callbacks The callbacks to fire
		 * @return void
		 */
		protected function fireBeforeCallbackArray(
			string $abstract,
			array $parameters,
			array $callbacks
		): void {
			foreach($callbacks as $callback) {
				$callback($abstract, $parameters, $this);
			}
		}
		
		/**
		 * Fire all of the resolving callbacks
		 * @param string $abstract The abstract type to fire the callbacks for
		 * @param mixed $object The object to fire the callbacks for
		 * @return void
		 */
		protected function fireResolvingCallbacks(string $abstract, mixed $object): void {
			$this->fireCallbackArray($object, $this->globalResolvingCallbacks);
			
			$this->fireCallbackArray(
				$object,
				$this->getCallbacksForType($abstract, $object, $this->resolvingCallbacks)
			);
			
			$this->fireAfterResolvingCallbacks($abstract, $object);
		}
		
		/**
		 * Fire all of the after resolving callbacks
		 * @param string $abstract The abstract type to fire the callbacks for
		 * @param mixed $object The object to fire the callbacks for
		 * @return void
		 */
		protected function fireAfterResolvingCallbacks(string $abstract, mixed $object): void {
			$this->fireCallbackArray($object, $this->globalAfterResolvingCallbacks);
			
			$this->fireCallbackArray(
				$object,
				$this->getCallbacksForType($abstract, $object, $this->afterResolvingCallbacks)
			);
		}
		
		/**
		 * Get all callbacks for a given type
		 * @param string $abstract The abstract type to get callbacks for
		 * @param mixed $object The object to get callbacks for
		 * @param array $callbacksPerType The callbacks to get
		 * @return array The callbacks
		 */
		protected function getCallbacksForType(
			string $abstract,
			mixed $object,
			array $callbacksPerType
		): array {
			$results = [];
			
			foreach($callbacksPerType as $type => $callbacks) {
				if(($type === $abstract) || ($object instanceof $type)) {
					$results = array_merge($results, $callbacks);
				}
			}
			
			return $results;
		}
		
		/**
		 * Fire an array of callbacks with an object
		 * @param mixed $object The object to fire the callbacks for
		 * @param array $callbacks The callbacks to fire
		 * @return void
		 */
		protected function fireCallbackArray(mixed $object, array $callbacks): void {
			foreach($callbacks as $callback) {
				$callback($object, $this);
			}
		}
		
		/**
		 * Get the container's bindings
		 * @return array The bindings
		 */
		public function getBindings(): array {
			return $this->bindings;
		}
		
		/**
		 * Get the alias for an abstract if available
		 * @param string $abstract The abstract type to get the alias for
		 * @return string The alias
		 */
		public function getAlias(string $abstract): string {
			return isset($this->aliases[ $abstract ])
				? $this->getAlias($this->aliases[ $abstract ])
				: $abstract;
		}
		
		/**
		 * Get the extender callbacks for a given type
		 * @param string $abstract The abstract type to get the extenders for
		 * @return array The extenders
		 */
		protected function getExtenders(string $abstract): array {
			return $this->extenders[ $this->getAlias($abstract) ] ?? [];
		}
		
		/**
		 * Remove all of the extender callbacks for a given type
		 * @param string $abstract The abstract type to remove the extenders for
		 * @return void
		 */
		public function forgetExtenders(string $abstract): void {
			unset($this->extenders[ $this->getAlias($abstract) ]);
		}
		
		/**
		 * Unbind the given abstract type from the container
		 * @param string $abstract The abstract type to unbind
		 * @return void
		 */
		protected function dropStaleInstances(string $abstract): void {
			unset($this->instances[ $abstract ], $this->aliases[ $abstract ]);
		}
		
		/**
		 * Remove a resolved instance from the instance cache
		 * @param string $abstract The abstract type to remove the instance for
		 * @return void
		 */
		public function forgetInstance(string $abstract): void {
			unset($this->instances[ $abstract ]);
		}
		
		/**
		 * Clear all of the instances from the container
		 * @return void
		 */
		public function forgetInstances(): void {
			$this->instances = [];
		}
		
		/**
		 * Clear all of the scoped instances from the container
		 * @return void
		 */
		public function forgetScopedInstances(): void {
			foreach($this->scopedInstances as $scoped) {
				unset($this->instances[ $scoped ]);
			}
		}
		
		/**
		 * Flush the container of all bindings and instances
		 * @return void
		 */
		public function flush(): void {
			$this->aliases = [];
			$this->resolved = [];
			$this->bindings = [];
			$this->instances = [];
			$this->abstractAliases = [];
			$this->scopedInstances = [];
		}
		
		/**
		 * Get the globally available instance of the container
		 * @return static
		 */
		public static function getInstance(): static {
			if(null === static::$instance) {
				static::$instance = new static;
			}
			
			return static::$instance;
		}
		
		/**
		 * Set the shared instance of the container
		 * @param \Magnetar\Container\Container|null $container The container to set as the shared instance
		 * @return \Magnetar\Container\Container|static The shared instance
		 */
		public static function setInstance(Container|null $container = null): Container|static {
			return static::$instance = $container;
		}
		
		/**
		 * Determine if a given offset exists
		 * @param mixed $key The offset to check
		 * @return bool Whether the offset exists
		 */
		public function offsetExists(mixed $key): bool {
			return $this->bound($key);
		}
		
		/**
		 * Get the value at a given offset
		 * @param mixed $key The offset to get
		 * @return mixed The value at the offset
		 */
		public function offsetGet(mixed $key): mixed {
			return $this->make($key);
		}
		
		/**
		 * Set the value at a given offset
		 * @param mixed $key The offset to set
		 * @param mixed $value The value to set
		 * @return void
		 */
		public function offsetSet(mixed $key, mixed $value): void {
			$this->bind(
				$key,
				(($value instanceof Closure) ? $value : fn () => $value)
			);
		}
		
		/**
		 * Unset the value at a given offset
		 * @param string $key The offset to unset
		 * @return void
		 */
		public function offsetUnset(mixed $key): void {
			unset(
				$this->bindings[ $key ],
				$this->instances[ $key ],
				$this->resolved[ $key ]
			);
		}
		
		/**
		 * Dynamically access container services
		 * @param string $key The service to access
		 * @return mixed The service
		 */
		public function __get(string $key): mixed {
			return $this[ $key ];
		}
		
		/**
		 * Dynamically set container services
		 * @param string $key The service to set
		 * @param mixed $value The value to set
		 * @return void
		 */
		public function __set(string $key, mixed $value): void {
			$this[ $key ] = $value;
		}
	}