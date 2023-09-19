<?php
	declare(strict_types=1);
	
	namespace Magnetar\Router;
	
	use Exception;
	
	use Magnetar\Router\Router;
	use Magnetar\Container\Container;
	use Magnetar\Router\RouteCollection;
	use Magnetar\Helpers\TypedEnumHelper;
	use Magnetar\Helpers\Enums\TypedEnum;
	use Magnetar\Router\Enums\HTTPMethodEnum;
	use Magnetar\Router\Helpers\HTTPMethodEnumResolver;
	
	/**
	 * A defined route
	 * 
	 * @todo handle method matching
	 * @todo handle multiple methods + matching
	 */
	class Route {
		/**
		 * The route name
		 * @var string|null The route name
		 */
		protected string|null $name = null;
		
		/**
		 * The route's regex pattern (converted from this->pattern_basic)
		 * @var string
		 */
		protected string $pattern_regex;
		
		/**
		 * An array of named parameters and their associated Typed value
		 * @var array<string, Typed>
		 */
		protected array $var_types = [];
		
		
		protected Router $router;
		
		protected Container $container;
		
		/**
		 * Constructor
		 * @param RouteCollection $routeCollection The route collection this route belongs to
		 * @param string $name The route name
		 * @param HTTPMethodEnum|string $method The HTTP request method (GET, POST, etc)
		 * @param string $pattern_basic The pattern to match against
		 */
		public function __construct(
			/**
			 * The route collection this route belongs to
			 * @var RouteCollection
			 */
			protected RouteCollection $routeCollection,
			
			/**
			 * The HTTP request method (GET, POST, etc)
			 * @var string
			 */
			protected HTTPMethodEnum|array|string|null $method,
			
			/**
			 * The pattern to match against
			 * @var string|null
			 */
			protected string|null $pattern_basic
		) {
			// properly type the method
			if(is_string($method)) {
				$this->method = HTTPMethodEnumResolver::resolve(strtoupper($method));
			}
			
			// parse pattern
			$this->pattern_regex = $this->parsePattern($this->pattern_basic);
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
		 * Parse the convenient pattern into a regex pattern
		 * @return void
		 * 
		 * @throws Exception If the pattern uses an invalid type name
		 */
		protected function parsePattern(string $pattern): string {
			// parse typed patterns
			// example:
			//    /{id:int}/{name:string}/{age:int}/
			//      => /(?<id>[0-9]+)/(?<name>[^/]+)/(?<age>[0-9]+)/
			$pattern = preg_replace_callback(
				// match any named parameters
				"#\{([a-zA-Z0-9_]+)\:([A-Za-z]+)\}#si",
				// replace with named capture group
				function(array $matches): string {
					$type = TypedEnumHelper::getType($matches['type'], TypedEnum::String);
					
					$this->var_types[ $matches['name'] ] = $type;
					
					// use the Typed value to generate a match pattern
					return '(?<' . $matches['name'] . '>'. $this->regexMatchByTyped($type) .')';
				},
				$pattern
			);
			
			// parse basic patterns (defaults to strings)
			$pattern = preg_replace_callback(
				// match any named parameters
				"#\{(?<name>[a-zA-Z0-9_]+)\}#si",
				// replace with named capture group
				function(array $matches): string {
					$this->var_types[ $matches['name'] ] = TypedEnum::String;
					
					return '(?<' . $matches['name'] . '>'. $this->regexMatchByTyped(TypedEnum::String) .')';
				},
				$pattern
			);
			
			return $pattern;
		}
		
		/**
		 * Generate a regex variable match pattern for a given type
		 * @param Typed $type The type to generate a match pattern for
		 * @return string The regex variable match pattern
		 */
		protected function regexMatchByTyped(TypedEnum $type): string {
			return match($type) {
				TypedEnum::Boolean => '[01]',
				TypedEnum::Int => '[0-9]+',
				TypedEnum::Float => '[0-9]+(?:\.[0-9]+)?',
				TypedEnum::String => '[^/]+',
				
				// other Typed values aren't supported, so we'll
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
		
		
	}