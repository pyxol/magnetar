<?php
	declare(strict_types=1);
	
	namespace Magnetar\Router;
	
	use ReflectionMethod;
	use ReflectionFunction;
	use ReflectionParameter;
	use InvalidArgumentException;
	
	use Magnetar\Container\Container;
	use Magnetar\Router\Exceptions\UnresolvableRouteParameterException;
	
	/**
	 * Resolves dependencies for a route's callback
	 */
	class RouteDependencyResolver {
		/**
		 * Constructor
		 * @param Container $container The container to use to resolve dependencies
		 */
		public function __construct(
			protected Container $container
		) {
			
		}
		
		/**
		 * Resolve parameters for a callback
		 * @param callable|array|string|null $callback The callback to resolve parameters for
		 * @return array The resolved parameters
		 * 
		 * @throws InvalidArgumentException If the callback is invalid
		 */
		public function resolveParameters(
			callable|array|string|null $callback=null
		): array {
			// null value, no params
			if(null === $callback) {
				return [];
			}
			
			// string
			// ex: Path\To\Class::methodName
			// ex: ClassName::methodName
			// ex: ClassName@methodName
			if(is_string($callback)) {
				// convert string to class array
				if(null === ($callback = $this->convertStringToClassArray($callback))) {
					// invalid string
					throw new InvalidArgumentException('Invalid callback string');
				}
			}
			
			// array
			// ex: [ClassName::class, 'methodName']
			if(is_array($callback)) {
				return $this->resolveClassMethodParameters($callback[0], $callback[1]);
			}
			
			// callable
			// ex: function() {}
			// ex: fn() => {}
			return $this->resolveCallableParameters($callback);
		}
		
		/**
		 * Convert a class reference string to a class+method array
		 * @param string $string The string to convert. Ex: Path\To\Class::methodName or ClassName::methodName or ClassName@methodName
		 * @return array|null
		 */
		protected function convertStringToClassArray(
			string $string
		): array|null {
			if(!preg_match("#^\s*([a-zA-Z0-9_\\\\]+)(?:\:\:|@)([a-zA-Z0-9_]+)\s*$#", $string, $matches)) {
				return null;
			}
			
			return [
				ltrim($matches[1], '\\'),
				$matches[2]
			];
		}
		
		/**
		 * Resolve a reflection parameter dependency
		 * @param ReflectionParameter $parameter The parameter to resolve
		 * @return mixed The resolved value
		 * 
		 * @throws UnresolvableRouteParameterException If the parameter cannot be resolved
		 */
		protected function resolveParameterDependency(
			ReflectionParameter $parameter
		): mixed {
			if($parameter->hasType()) {
				if(!$parameter->getType()->isBuiltin()) {
					return $this->container->make(
						$parameter->getType()->getName()
					);
				}
				
				if($parameter->isOptional() && $parameter->isDefaultValueAvailable()) {
					return $parameter->getDefaultValue();
				}
				
				if($parameter->getType()->allowsNull()) {
					return null;
				}
				
				// @TODO needs further testing
				throw new UnresolvableRouteParameterException("Unable to resolve parameter: ". $parameter->getName());
			}
			
			// resolve as default value
			if($parameter->isOptional()) {
				return $parameter->getDefaultValue();
			}
			
			// @TODO needs further testing
			
			throw new UnresolvableRouteParameterException("Unable to resolve parameter: ". $parameter->getName());
		}
		
		/**
		 * Resolve parameters for a class method
		 * @param string $class The class name
		 * @param string $method The method name
		 * @return array The resolved parameters for the method
		 */
		protected function resolveClassMethodParameters(
			string $class,
			string $method
		): array {
			// cycle through each of the class method's parameter and resolve it
			return array_map(
				fn(ReflectionParameter $parameter) => $this->resolveParameterDependency($parameter),
				(new ReflectionMethod($class, $method))->getParameters()
			);
		}
		
		protected function resolveCallableParameters(
			callable $callable
		): array {
			// cycle through each of the callable's parameter and resolve it
			return array_map(
				fn(ReflectionParameter $parameter) => $this->resolveParameterDependency($parameter),
				(new ReflectionFunction($callable))->getParameters()
			);
		}
	}