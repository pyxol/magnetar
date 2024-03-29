<?php
	declare(strict_types=1);
	
	namespace Magnetar\Router;
	
	use Exception;
	
	use Magnetar\Router\Router;
	use Magnetar\Container\Container;
	use Magnetar\Router\RouteCollection;
	use Magnetar\Helpers\Enums\TypedEnum;
	use Magnetar\Helpers\TypedEnumHelper;
	use Magnetar\Router\Enums\HTTPMethodEnum;
	use Magnetar\Router\Helpers\HTTPMethodEnumResolver;
	use Magnetar\Router\Exceptions\InvalidMethodException;
	
	/**
	 * A route that can be defined and matched against a request by the router
	 * using request methods and paths
	 */
	class Route {
		/**
		 * Unique identifier for this route
		 * @var string
		 */
		protected string|null $uniqueID = null;
		
		/**
		 * The route name
		 * @var string|null The route name
		 */
		protected string|null $name = null;
		
		/**
		 * An array of HTTP request methods (GET, POST, etc) this route responds to,
		 * stored as HTTPMethodEnum values. Null values match any method.
		 * @var array|null
		 */
		protected array|null $methods = [];
		
		/**
		 * The basic form of the route's path (before regex conversion)
		 * @var string
		 */
		protected string $path_basic = '';
		
		/**
		 * The route's path in regex format (converted version of this->path_basic)
		 * @var string
		 */
		protected string $path_regex = '';
		
		/**
		 * If the path is a regex pattern, this is true
		 * @var bool
		 */
		protected bool $path_is_regex = false;
		
		/**
		 * An array of named parameters and their associated TypedEnum value
		 * @var array<string, TypedEnum>
		 */
		protected array $var_types = [];
		
		/**
		 * An array of parameters that were matched in the request.
		 * Key is the parameter name, value is a typed variable of the matched value
		 * @var array|null
		 * 
		 * @TODO upon route match, generate a 'MatchedRoute' object that extends this route and stores the matched parameters (among other things)
		 */
		protected array|null $parameters = null;
		
		/**
		 * The middleware to run for this route
		 * @var array
		 */
		protected array $middleware = [];
		
		/**
		 * The router instance
		 * @var Router
		 */
		protected ?Router $router = null;
		
		/**
		 * The container instance
		 * @var Container
		 */
		protected ?Container $container = null;
		
		/**
		 * Constructor
		 * @param RouteCollection $routeCollection The route collection this route belongs to
		 * @param HTTPMethodEnum|array|string|null $method The HTTP request method(s) (GET, POST, etc) to respond to. Null value means any
		 * @param string $path The path to match against
		 * @return self
		 * 
		 * @throws InvalidMethodException If the method(s) is an invalid type
		 */
		public function __construct(
			protected RouteCollection $routeCollection,
			HTTPMethodEnum|array|string|null $method,
			string $path
		) {
			// parse method(s)
			$this->parseMethod($method);
			
			// parse path
			$this->parsePath($path);
		}
		
		/**
		 * Set the router instance
		 * @param Router $router The router instance
		 * @return self
		 */
		public function setRouter(Router $router): self {
			$this->router = $router;
			
			return $this;
		}
		
		/**
		 * Set the container instance
		 * @param Container $container The container instance
		 * @return self
		 */
		public function setContainer(Container $container): self {
			$this->container = $container;
			
			return $this;
		}
		
		/**
		 * Get the route's unique identifier
		 * @return string
		 */
		public function getUniqueID(): string {
			return $this->uniqueID ??= $this->generateUniqueID();
		}
		
		/**
		 * Generate a unique identifier for this route
		 * @return void
		 */
		protected function generateUniqueID(): string {
			// specific method(s) - use pipe-delimited list of methods as a prefix
			return md5(implode('|', array_map(
				fn(HTTPMethodEnum $method): string => HTTPMethodEnumResolver::resolveToString($method),
				$this->methods
			)) .':'. $this->path_basic);
		}
		
		/**
		 * Parse the constructor method
		 * @param HTTPMethodEnum|array|string|null $method
		 * @return void
		 * 
		 * @throws InvalidMethodException If the method is an invalid type
		 */
		protected function parseMethod(
			HTTPMethodEnum|array|string|null $method
		): void {
			// properly type the method
			if(null === $method) {
				// any method is valid
				$this->methods = null;
				
				return;
			} elseif($method instanceof HTTPMethodEnum) {
				// single HTTPMethodEnum, easy
				$this->methods = [
					$method
				];
				
				return;
			} elseif(is_string($method)) {
				// convert method string to HTTPMethodEnum
				$this->methods = [
					HTTPMethodEnumResolver::resolve(strtoupper($method))
				];
				
				return;
			}
			
			// making it this far means it's an array
			foreach($method as $method_item) {
				if($method_item instanceof HTTPMethodEnum) {
					$this->methods[] = $method_item;
				} elseif(is_string($method_item)) {
					$this->methods[] = HTTPMethodEnumResolver::resolve(strtoupper($method_item));
				} else {
					throw new InvalidMethodException('Invalid HTTP method type');
				}
			}
		}
		
		/**
		 * Parse the constructor path pattern
		 * @return void
		 * 
		 * @throws Exception If the pattern uses an invalid type name
		 */
		protected function parsePath(string $path): void {
			$path = $this->routeCollection->formatPathWithPrefix($path);
			
			$this->path_basic = $path;
			
			// delimiter
			$delimiter = '#';
			
			// init pattern
			$regex_pattern = $path;
			
			
			// @TODO maybe combine these into one preg_replace_callback() call? using (?:\:(?<type>...))
			
			// parse type-specific patterns
			// example:
			//    /{city:string}/{last_name}/{id:int}/
			//      => /(?<city>[^/]+)/(?<last_name>[^/]+)/(?<id>[0-9]+)/
			$regex_pattern = preg_replace_callback(
				// match any named parameters
				"#\{(?<name>[a-zA-Z0-9_]+)\:(?<type>[A-Za-z]+)\}#si",
				// replace with named capture group
				function(array $matches) use ($delimiter): string {
					$type = TypedEnumHelper::typeByName($matches['type'], TypedEnum::String);
					
					$this->var_types[ $matches['name'] ] = $type;
					
					// use the TypedEnum value to generate a match pattern
					return '(?<' . $matches['name'] . '>'. $this->regexMatchByTypedEnum($type) .')';
				},
				$regex_pattern,
				-1,
				$count1
			);
			
			// parse basic patterns (defaults to strings)
			$regex_pattern = preg_replace_callback(
				// match any named parameters
				"#\{(?<name>[a-zA-Z0-9_]+)\}#si",
				// replace with named capture group
				function(array $matches) use ($delimiter): string {
					$this->var_types[ $matches['name'] ] = TypedEnum::String;
					
					return '(?<' . $matches['name'] . '>'. $this->regexMatchByTypedEnum(TypedEnum::String) .')';
				},
				$regex_pattern,
				-1,
				$count2
			);
			
			// check if any parameters were found
			if($count1 || $count2) {
				// path is regex
				$this->path_is_regex = true;
			}
			
			// trailing slash optional?
			if((null !== $this->router) && $this->router->isTrailingSlashOptional()) {
				$regex_pattern = preg_replace('#/$#si', '/?', $regex_pattern);
			}
			
			// add delimiters and end-of-string match
			$this->path_regex = $delimiter .'^'. $regex_pattern .'$'. $delimiter .'si';
		}
		
		/**
		 * Generate a regex variable match pattern for a given type
		 * @param TypedEnum $type The type to generate a match pattern for
		 * @return string The regex variable match pattern
		 */
		protected function regexMatchByTypedEnum(TypedEnum $type): string {
			return match($type) {
				TypedEnum::Boolean => '[01]',
				TypedEnum::Int => '[0-9]+',
				TypedEnum::Float => '[0-9]+(?:\.[0-9]+)?',
				TypedEnum::String => '[^/]+',
				
				// other TypedEnum values aren't supported, so we'll
				// default to anything that isn't a slash
				default => '[^/]+'
			};
		}
		
		/**
		 * Set the name for this route
		 * @param string|null $name The name for this route. Prefixed with parent route collection's name. If null, the name is reset.
		 * @return Route
		 */
		public function name(string|null $name=null): Route {
			if(null !== $name) {
				$this->name = $this->routeCollection->formatNameWithPrefix($name);
			} else {
				$this->name = null;
			}
			
			return $this;
		}
		
		/**
		 * Returns the name of the path, or the route's unique identifier (md5 hash) if the name is not set
		 * @return string
		 */
		public function getName(): string {
			return $this->name ?? $this->getUniqueID();
		}
		
		/**
		 * Get the route's methods
		 * @return array The route's methods as HTTPMethodEnum values
		 */
		public function getMethods(): array {
			return $this->methods ?? [];
		}
		
		/**
		 * Get the route's path
		 * @return string The route's path
		 */
		public function getPath(): string {
			return $this->path_basic;
		}
		
		/**
		 * Get the route's path regex
		 * @return string The route's path regex
		 */
		public function getPathRegex(): string {
			return $this->path_regex;
		}
		
		/**
		 * Determine if the route's path is a regex pattern
		 * @return bool
		 */
		public function isPathRegex(): bool {
			return $this->path_is_regex;
		}
		
		/**
		 * Get the route's assoc array of variables defined in the path.
		 * Key is the variable name, value is the TypedEnum
		 * @return array
		 */
		public function getPathVariableTypes(): array {
			return $this->var_types;
		}
		
		///**
		// * Parse the pattern, setting any parameters in the request
		// * @param array $raw_matches The raw matches from the matched Router pattern
		// * @return void
		// */
		//protected function parsePathMatches(array $raw_matches): void {
		//	if(empty($raw_matches)) {
		//		return;
		//	}
		//	
		//	// assign matched path parameters to request
		//	$this->request->assignOverrideParameters(
		//		// filter out numeric keys and override params
		//		array_filter($raw_matches, 'is_string', ARRAY_FILTER_USE_KEY)
		//	);
		//}
		
		/**
		 * Determine if the provided method and path match this route
		 * @param HTTPMethodEnum $method The HTTP method to match against
		 * @param string $path The path to match against
		 * @return bool
		 */
		public function matches(
			HTTPMethodEnum $method,
			string $path
		): bool {
			// check method
			// @TODO maybe something more performant like a bitwise match instead of in_array()
			if((null !== $this->methods) && !in_array($method, $this->methods)) {
				// request method isn't the same
				return false;
			}
			
			// check path
			if($this->path_is_regex) {
				// regex path match?
				if(!preg_match($this->path_regex, $path, $matches)) {
					return false;
				}
				
				// parse path matches
				$this->parseMatchedPathVariables($matches);
				
				return true;
			} else {
				// basic path match?
				if($path !== $this->path_basic) {
					return false;
				}
				
				return true;
			}
		}
		
		/**
		 * Processes the provided array of matches from the path regex
		 * into a typed array of parameters
		 * @param array $matches The raw preg_match matches from the path's regex pattern
		 * @return void
		 */
		protected function parseMatchedPathVariables(array $matches): void {
			// assign matched path parameters to request
			$params = [];
			
			foreach($this->var_types as $var_name => $var_typed) {
				$params[ $var_name ] = TypedEnumHelper::castTypedVariable(
					// the TypedEnum value for the variable
					$var_typed,
					
					// get the matched value from the regex matches
					// @todo handle missing matches (by throwing exception?)
					$matches[ $var_name ] ?? null
				);
			}
			
			$this->parameters = $params;
		}
		
		/**
		 * Get the route's matched, typed parameters
		 * @return array
		 */
		public function parameters(): array {
			return $this->parameters ?? [];
		}
		
		/**
		 * Set the route's middleware
		 * @param string|array $middleware The middleware to set
		 * @return Route
		 */
		public function middleware(string|array $middleware): Route {
			$this->middleware = array_unique(array_merge($this->middleware, (array) $middleware));
			
			return $this;
		}
		
		/**
		 * Reset the route's middleware
		 * @return Route
		 */
		public function resetMiddleware(): Route {
			$this->middleware = [];
			
			return $this;
		}
		
		/**
		 * Get the route's middleware
		 * @return array
		 */
		public function getMiddleware(): array {
			return $this->middleware;
		}
		
		/**
		 * Export the route's data
		 * @return array
		 */
		public function export(): array {
			return [
				'unique_id' => $this->getUniqueID(),
				'name' => $this->getName(),
				'methods' => $this->getMethods(),
				'path_basic' => $this->getPath(),
				'path_regex' => $this->getPathRegex(),
				'path_is_regex' => $this->isPathRegex(),
				'var_types' => $this->getPathVariableTypes(),
			];
		}
		
		/**
		 * Import cached route data
		 * @param array $data The cached route data
		 * @return void
		 * 
		 * @TODO instead of modifying this route, return a new route with the cached data (or use a Route factory)
		 */
		public function importCachedRoute(array $data): void {
			$this->uniqueID = $data['unique_id'] ?? null;
			$this->name = $data['name'] ?? null;
			$this->methods = $data['methods'] ?? [];
			$this->path_basic = $data['path_basic'] ?? '';
			$this->path_regex = $data['path_regex'] ?? '';
			$this->path_is_regex = $data['path_is_regex'] ?? false;
			$this->var_types = $data['var_types'] ?? [];
		}
	}