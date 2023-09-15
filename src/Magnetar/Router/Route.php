<?php
	declare(strict_types=1);
	
	namespace Magnetar\Router;

	use Magnetar\Helpers\TypedHelper;
	use Magnetar\Helpers\Enums\Typed;
	use Magnetar\Router\Enums\HTTPMethod;
	use Magnetar\Router\Helpers\HTTPMethodEnumResolver;

	/**
	 * A defined route
	 */
	class Route {
		protected string $pattern_regex;
		
		/**
		 * An array of named parameters and their associated Typed value
		 * @var array<string, Typed>
		 */
		protected array $var_types = [];
		
		public function __construct(
			/**
			 * The route collection this route belongs to
			 * @var RouteCollection
			 */
			protected RouteCollection $routeCollection,
			
			/**
			 * The route name
			 * @var string
			 */
			protected string $name,
			
			/**
			 * The HTTP request method (GET, POST, etc)
			 * @var string
			 */
			protected HTTPMethod|string $method,
			
			/**
			 * The pattern to match against
			 * @var string
			 */
			protected string $pattern_basic
		) {
			// properly type the method
			if(is_string($method)) {
				$this->method = HTTPMethodEnumResolver::resolve($method);
			}
			
			// parse pattern
			$this->pattern_regex = $this->parsePattern($this->pattern_basic);
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
					$type = TypedHelper::getType($matches['type'], Typed::String);
					
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
					$this->var_types[ $matches['name'] ] = Typed::String;
					
					return '(?<' . $matches['name'] . '>'. $this->regexMatchByTyped(Typed::String) .')';
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
		protected function regexMatchByTyped(Typed $type): string {
			return match($type) {
				Typed::Boolean => '[01]',
				Typed::Int => '[0-9]+',
				Typed::Float => '[0-9]+(?:\.[0-9]+)?',
				Typed::String => '[^/]+',
				
				// other Typed values aren't supported, so we'll
				// default to anything that isn't a slash
				default => '[^/]+'
			};
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