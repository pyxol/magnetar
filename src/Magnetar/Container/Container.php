<?php
	declare(strict_types=1);
	
	namespace Magnetar\Container;
	
	use ArrayAccess;
	use Closure;
	use Exception;
	use TypeError;
	use ReflectionClass;
	use ReflectionException;
	use ReflectionParameter;
	
	use Magnetar\Container\ContainerInterface;
	use Magnetar\Container\Helper;
	use Magnetar\Container\BuildResolutionException;
	use Magnetar\Container\InstanceNotFoundException;
	use Magnetar\Container\ResolvingDependenciesException;
	use Magnetar\Container\UninstantiableException;
	
	class Container implements ArrayAccess, ContainerInterface {
		/**
		 * Currently available global container instance (if any)
		 * @var static
		 */
		protected static $instance;
		
		/**
		 * The container's bindings
		 * @var array
		 */
		protected array $bindings = [];
		
		/**
		 * Storage for the container's instances
		 * @var array
		 */
		protected array $instances = [];
		
		/**
		 * The container's build stack
		 * @var array
		 */
		protected array $buildStack = [];
		
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
		 * An array of the types that have been resolved
		 * @var array
		 */
		protected array $resolved = [];
		
		
		protected array $globalBeforeResolvingCallbacks = [];
		protected array $globalAfterResolvingCallbacks = [];
		protected array $beforeResolvingCallbacks = [];
		protected array $globalResolvingCallbacks = [];
		protected array $resolvingCallbacks = [];
		protected array $afterResolvingCallbacks = [];
		
		/**
		 * Register a new before resolving callback for all types.
		 *
		 * @param Closure|string $abstract
		 * @param Closure|null $callback
		 * @return void
		 */
		public function beforeResolving(Closure|string $abstract, Closure|null $callback=null): void {
			if(is_string($abstract)) {
				$abstract = $this->getAlias($abstract);
			}
			
			if(is_null($callback) && ($abstract instanceof Closure)) {
				$this->globalBeforeResolvingCallbacks[] = $abstract;
			} else {
				$this->beforeResolvingCallbacks[ $abstract ][] = $callback;
			}
		}
		
		/**
		 * Register a new resolving callback.
		 *
		 * @param Closure|string $abstract
		 * @param Closure|null $callback
		 * @return void
		 */
		public function resolving(Closure|string $abstract, Closure|null $callback=null): void {
			if(is_string($abstract)) {
				$abstract = $this->getAlias($abstract);
			}
			
			if(is_null($callback) && ($abstract instanceof Closure)) {
				$this->globalResolvingCallbacks[] = $abstract;
			} else {
				$this->resolvingCallbacks[ $abstract ][] = $callback;
			}
		}
		
		/**
		 * Register a new after resolving callback for all types.
		 *
		 * @param Closure|string $abstract
		 * @param Closure|null $callback
		 * @return void
		 */
		public function afterResolving(Closure|string $abstract, Closure|null $callback=null): void {
			if(is_string($abstract)) {
				$abstract = $this->getAlias($abstract);
			}
			
			if(is_null($callback) && ($abstract instanceof Closure)) {
				$this->globalAfterResolvingCallbacks[] = $abstract;
			} else {
				$this->afterResolvingCallbacks[ $abstract ][] = $callback;
			}
		}
		
		/**
		 * Fire all of the before resolving callbacks.
		 *
		 * @param string $abstract
		 * @param array $parameters
		 * @return void
		 */
		protected function fireBeforeResolvingCallbacks(string $abstract, array $parameters=[]): void {
			$this->fireBeforeCallbackArray($abstract, $parameters, $this->globalBeforeResolvingCallbacks);
			
			foreach($this->beforeResolvingCallbacks as $type => $callbacks) {
				if(($type === $abstract) || is_subclass_of($abstract, $type)) {
					$this->fireBeforeCallbackArray($abstract, $parameters, $callbacks);
				}
			}
		}
		
		/**
		 * Fire an array of callbacks with an object
		 *
		 * @param string $abstract
		 * @param array $parameters
		 * @param array $callbacks
		 * @return void
		 */
		protected function fireBeforeCallbackArray(string $abstract, array $parameters, array $callbacks): void {
			foreach ($callbacks as $callback) {
				$callback($abstract, $parameters, $this);
			}
		}
		
		/**
		 * Fire all of the resolving callbacks
		 *
		 * @param string $abstract
		 * @param mixed $object
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
		 *
		 * @param string $abstract
		 * @param mixed $object
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
		 * Get all callbacks for a given type.
		 *
		 * @param string $abstract
		 * @param mixed $object
		 * @param array $callbacksPerType
		 * @return array
		 */
		protected function getCallbacksForType(string $abstract, mixed $object, array $callbacksPerType): array {
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
		 *
		 * @param mixed $object
		 * @param array $callbacks
		 * @return void
		 */
		protected function fireCallbackArray(mixed $object, array $callbacks): void {
			foreach($callbacks as $callback) {
				$callback($object, $this);
			}
		}
		
		/**
		 * Determine if the given abstract type has been bound
		 * @param string $abstract
		 * @return bool
		 */
		public function bound(string $abstract): bool {
			return isset($this->bindings[ $abstract ]);
		}
		
		/**
		 * Get the Closure to be used when building a type.
		 *
		 * @param string $abstract
		 * @param string $concrete
		 * @return Closure
		 */
		protected function getClosure(string $abstract, string $concrete): Closure {
			return function (Container $container, array $parameters=[]) use ($abstract, $concrete): mixed {
				if($abstract == $concrete) {
					return $container->build($concrete);
				}
				
				return $container->resolve($concrete, $parameters);
			};
		}
		
		/**
		 * Bind a new instance to the container
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
			
			if(is_null($concrete)) {
				$concrete = $abstract;
			}
			
			if(!$concrete instanceof Closure) {
				if(!is_string($concrete)) {
					throw new TypeError(self::class .'::bind(): Argument #2 ($concrete) must be of type Closure|string|null');
				}
				
				$concrete = $this->getClosure($abstract, $concrete);
			}
			
			// add new reference
			$this->bindings[ $abstract ] = compact('concrete', 'shared');
		}
		
		/**
		 * Get the concrete instance of the given abstract type
		 * @param string $abstract
		 * @return bool
		 */
		public function resolved(string $abstract): bool {
			return isset($this->instances[ $abstract ]);
		}
		
		/**
		 * {@inheritdoc}
		 * 
		 * @return mixed
		 * 
		 * @throws InstanceNotFoundException
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
		 * {@inheritdoc}
		 * 
		 * @return bool
		 */
		public function has(string $id): bool {
			return $this->bound($id);
		}
		
		/**
		 * Register a shared binding in the container
		 *
		 * @param string $abstract
		 * @param Closure|string|null $concrete
		 * @return void
		 */
		public function singleton(string $abstract, Closure|string|null $concrete=null): void {
			$this->bind($abstract, $concrete, true);
		}
		
		/**
		 * Register a shared binding if it hasn't already been registered
		 *
		 * @param string $abstract
		 * @param Closure|string|null $concrete
		 * @return void
		 */
		public function singletonIf(string $abstract, Closure|string|null $concrete=null): void {
			if(!$this->bound($abstract)) {
				$this->singleton($abstract, $concrete);
			}
		}
		
		/**
		 * Alias a type to a different name.
		 *
		 * @param string $abstract
		 * @param string $alias
		 * @return void
		 *
		 * @throws SelfAliasException
		 */
		public function alias(string $abstract, string $alias): void {
			if($alias === $abstract) {
				throw new SelfAliasException("[". $abstract ."] is aliased to itself.");
			}
			
			$this->aliases[ $alias ] = $abstract;
			$this->abstractAliases[ $abstract ][] = $alias;
		}
		
		/**
		 * Bind a new callback to an abstract's rebind event.
		 *
		 * @param string $abstract
		 * @param Closure $callback
		 * @return mixed
		 */
		public function rebinding(string $abstract, Closure $callback): mixed {
			$this->reboundCallbacks[ $abstract = $this->getAlias($abstract) ][] = $callback;
			
			if($this->bound($abstract)) {
				return $this->make($abstract);
			}
		}
		
		/**
		 * Refresh an instance on the given target and method.
		 *
		 * @param  string  $abstract
		 * @param  mixed  $target
		 * @param  string  $method
		 * @return mixed
		 */
		public function refresh($abstract, $target, $method)
		{
			return $this->rebinding($abstract, function ($app, $instance) use ($target, $method) {
				$target->{$method}($instance);
			});
		}
		
		/**
		 * Fire the "rebound" callbacks for the given abstract type.
		 *
		 * @param string $abstract
		 * @return void
		 */
		protected function rebound(string $abstract): void {
			$instance = $this->make($abstract);
			
			foreach($this->getReboundCallbacks($abstract) as $callback) {
				$callback($this, $instance);
			}
		}
		
		/**
		 * Get the rebound callbacks for a given type.
		 *
		 * @param string $abstract
		 * @return array
		 */
		protected function getReboundCallbacks(string $abstract): array {
			return $this->reboundCallbacks[$abstract] ?? [];
		}
		
		/**
		 * Register an existing instance as shared in the container.
		 *
		 * @param  string  $abstract
		 * @param  mixed  $instance
		 * @return mixed
		 */
		public function instance($abstract, $instance)
		{
			$this->removeAbstractAlias($abstract);
			
			$isBound = $this->bound($abstract);
			
			unset($this->aliases[$abstract]);
			
			// We'll check to determine if this type has been bound before, and if it has
			// we will fire the rebound callbacks registered with the container and it
			// can be updated with consuming classes that have gotten resolved here.
			$this->instances[$abstract] = $instance;
			
			if($isBound) {
				$this->rebound($abstract);
			}
			
			return $instance;
		}
		
		/**
		 * Determine if the given abstract type is shared
		 * @param string $abstract
		 * @return bool
		 */
		public function isShared(string $abstract): bool {
			if(isset($this->instances[ $abstract ])) {
				return true;
			}
			
			return (isset($this->bindings[ $abstract ]['shared']) && $this->bindings[ $abstract ]['shared']);
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
		 * Get the concrete type for a given abstract
		 * @param string|callable $abstract
		 * @return mixed
		 */
		protected function getConcrete(string|callable $abstract): mixed {
			if(!isset($this->bindings[ $abstract ])) {
				return $abstract;
			}
			
			return $this->bindings[ $abstract ]['concrete'];
		}
		
		/**
		 * Determine if the given concrete is buildable
		 * @param mixed $concrete
		 * @param string $abstract
		 * @return bool
		 */
		protected function isBuildable(mixed $concrete, string $abstract): bool {
			return (($concrete === $abstract) || ($concrete instanceof Closure));
		}
		
		/**
		 * Get the last parameter override
		 * @return array
		 */
		protected function getLastParameterOverride(): array {
			return count($this->with) ? end($this->with) : [];
		}
		
		/**
		 * Determine if the given dependency has a parameter override
		 * @param ReflectionParameter $dependency
		 * @return bool
		 */
		protected function hasParameterOverride(ReflectionParameter $dependency): bool {
			return array_key_exists($dependency->name, $this->getLastParameterOverride());
		}
		
		/**
		 * Get the parameter override for the given dependency
		 * @param ReflectionParameter $dependency
		 * @return mixed
		 */
		protected function getParameterOverride(ReflectionParameter $dependency): mixed {
			return $this->getLastParameterOverride()[ $dependency->name ];
		}
		
		/**
		 * Find the concrete binding for the given abstract
		 * @param string|callable $abstract
		 * @return Closure|string|null
		 */
		protected function findInContextualBindings(string|callable $abstract): Closure|string|null {
			return $this->contextual[ end($this->buildStack) ][ $abstract ] ?? null;
		}
		
		/**
		 * Resolve the given abstract type to a concrete instance
		 * @param string|callable $abstract
		 * @return Closure|string|array|null
		 */
		protected function getContextualConcrete(string|callable $abstract): Closure|string|array|null {
			if(!is_null($binding = $this->findInContextualBindings($abstract))) {
				return $binding;
			}
			
			if(empty($this->abstractAliases[ $abstract ])) {
				return null;
			}
			
			foreach($this->abstractAliases[ $abstract ] as $alias) {
				if(!is_null($binding = $this->findInContextualBindings($alias))) {
					return $binding;
				}
			}
		}
		
		/**
		 * Resolve a primitive dependency
		 * @param ReflectionParameter $dependency
		 * @return mixed
		 * 
		 * @throws ResolvingDependenciesException
		 */
		protected function resolvePrimitive(ReflectionParameter $parameter): mixed {
			if(!is_null($concrete = $this->getContextualConcrete('$'. $parameter->getName()))) {
				return Helper::unwrapIfClosure($concrete, $this);
			}
			
			if($parameter->isDefaultValueAvailable()) {
				return $parameter->getDefaultValue();
			}
			
			if($parameter->isVariadic()) {
				return [];
			}
			
			throw new ResolvingDependenciesException(
				'Unresolvable dependency resolving [' . $parameter->getType() . '] in class ' . $parameter->getDeclaringClass()->getName()
			);
		}
		
		
		protected function resolveClass(ReflectionParameter $dependency): mixed {
			try {
				return $this->get($dependency->getType()->getName());
			} catch(InstanceNotFoundException $e) {
				if($dependency->isOptional()) {
					return $dependency->getDefaultValue();
				}
				
				throw new ResolvingDependenciesException(
					'Unresolvable dependency resolving [' . $dependency->getType() . '] in class ' . $dependency->getDeclaringClass()->getName()
				);
			}
		}
		
		/**
		 * Resolve dependencies for the given concrete
		 * @param ReflectionParameter[] $dependencies
		 * @return array
		 */
		protected function resolveDependencies(array $dependencies): array {
			$results = [];
			
			foreach($dependencies as $dependency) {
				if ($this->hasParameterOverride($dependency)) {
					$results[] = $this->getParameterOverride($dependency);
					
					continue;
				}
				
				$result = is_null(Helper::getParameterClassName($dependency))
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
		 * Get the container's bindings.
		 *
		 * @return array
		 */
		public function getBindings(): array {
			return $this->bindings;
		}
		
		/**
		 * Get the alias for an abstract if available
		 * @param string $abstract
		 * @return string
		 */
		public function getAlias(string $abstract): string {
			if(isset($this->aliases[ $abstract ])) {
				return $this->getAlias($this->aliases[ $abstract ]);
			}
			
			return $abstract;
		}
		
		/**
		 * Get the extender callbacks for a given type.
		 *
		 * @param string $abstract
		 * @return array
		 */
		protected function getExtenders(string $abstract): array {
			return $this->extenders[ $this->getAlias($abstract) ] ?? [];
		}
		
		/**
		 * Remove all of the extender callbacks for a given type.
		 *
		 * @param string $abstract
		 * @return void
		 */
		public function forgetExtenders(string $abstract): void {
			unset($this->extenders[ $this->getAlias($abstract) ]);
		}
		
		/**
		 * Throw an exception that the concrete is not instantiable
		 * @param string $concrete
		 * @return void
		 * 
		 * @throws UninstantiableException
		 */
		protected function notInstantiable(string $concrete): void {
			if(!empty($this->buildStack)) {
				$previous = implode(', ', $this->buildStack);
				
				$message = "Target [$concrete] is not instantiable while building [$previous].";
			} else {
				$message = "Target [$concrete] is not instantiable.";
			}
			
			throw new UninstantiableException($message);
		}
		
		/**
		 * Instantiate a concrete instance of the given type
		 * @param Closure|string $concrete
		 * @param array $parameters
		 * @return mixed
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
				throw new BuildResolutionException("Target class [$concrete] does not exist.", 0, $e);
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
			if(is_null($constructor)) {
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
		 * Resolve the given type from the container
		 * @param string|callable $abstract
		 * @param array $parameters
		 * @return mixed
		 */
		public function make(string|callable $abstract, array $parameters=[]): mixed {
			return $this->resolve($abstract, $parameters);
		}
		
		/**
		 * Resolve the given abstract type to a concrete instance
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
			
			// First we'll fire any event handlers which handle the "before" resolving of
			// specific types. This gives some hooks the chance to add various extends
			// calls to change the resolution of objects that they're interested in.
			if($raiseEvents) {
				$this->fireBeforeResolvingCallbacks($abstract, $parameters);
			}
			
			$concrete = $this->getContextualConcrete($abstract);
			
			// if parameters are provided, we need to create a contextualized instance
			$needsContext = !empty($parameters);
			
			// if the instance is already resolved and there's no context, return it
			if(isset($this->instances[ $abstract ]) && !$needsContext) {
				return $this->instances[ $abstract ];
			}
			
			$this->with[] = $parameters;
			
			if(is_null($concrete)) {
				$concrete = $this->getConcrete($abstract);
			}
			
			// instantiante an instance of the concrete type
			$object = $this->isBuildable($concrete, $abstract) ? $this->build($concrete, $parameters) : $this->make($concrete);
			
			// If we defined any extenders for this type, we'll need to spin through them
			// and apply them to the object being built. This allows for the extension
			// of services, such as changing configuration or decorating the object.
			foreach($this->getExtenders($abstract) as $extender) {
				$object = $extender($object, $this);
			}
			
			// if the instance is a singleton, store it in the instances array
			if($this->isShared($abstract) && !$needsContext) {
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
		 * Unbind the given abstract type from the container
		 * @param string $abstract
		 * @return void
		 */
		protected function dropStaleInstances(string $abstract): void {
			unset($this->instances[ $abstract ], $this->aliases[ $abstract ]);
		}
		
		/**
		 * Remove a resolved instance from the instance cache
		 *
		 * @param string $abstract
		 * @return void
		 */
		public function forgetInstance(string $abstract): void {
			unset($this->instances[ $abstract ]);
		}
		
		/**
		 * Clear all of the instances from the container
		 *
		 * @return void
		 */
		public function forgetInstances(): void {
			$this->instances = [];
		}
		
		/**
		 * Remove an alias from the contextual binding alias cache
		 *
		 * @param string $searched
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
		 * Get the globally available instance of the container.
		 *
		 * @return static
		 */
		public static function getInstance(): static {
			if(is_null(static::$instance)) {
				static::$instance = new static;
			}
			
			return static::$instance;
		}
		
		/**
		 * Set the shared instance of the container.
		 *
		 * @param Container|null $container
		 * @return Container|static
		 */
		public static function setInstance(Container|null $container = null): Container|static {
			return static::$instance = $container;
		}
		
		/**
		 * Determine if a given offset exists.
		 *
		 * @param string $key
		 * @return bool
		 */
		public function offsetExists($key): bool {
			return $this->bound($key);
		}
		
		/**
		 * Get the value at a given offset.
		 *
		 * @param string $key
		 * @return mixed
		 */
		public function offsetGet($key): mixed {
			return $this->make($key);
		}
		
		/**
		 * Set the value at a given offset.
		 *
		 * @param string $key
		 * @param mixed $value
		 * @return void
		 */
		public function offsetSet($key, $value): void {
			$this->bind($key, $value instanceof Closure ? $value : fn () => $value);
		}
		
		/**
		 * Unset the value at a given offset.
		 *
		 * @param string $key
		 * @return void
		 */
		public function offsetUnset($key): void {
			unset($this->bindings[$key], $this->instances[$key], $this->resolved[$key]);
		}
		
		/**
		 * Dynamically access container services.
		 *
		 * @param string $key
		 * @return mixed
		 */
		public function __get(string $key): mixed {
			return $this[ $key ];
		}
		
		/**
		 * Dynamically set container services.
		 *
		 * @param string $key
		 * @param mixed $value
		 * @return void
		 */
		public function __set(string $key, mixed $value): void {
			$this[ $key ] = $value;
		}
	}