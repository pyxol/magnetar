<?php
	declare(strict_types=1);
	
	namespace Magnetar\Router;
	
	use Magnetar\Http\Request;
	
	/**
	 * A matched route
	 */
	class Route {
		public function __construct(
			/**
			 * The pattern to match against
			 * @var string
			 */
			protected string $pattern='',
			
			/**
			 * The raw matches from the matched Router pattern
			 * @var array
			 */
			array $raw_matches,
			
			/**
			 * The request object
			 * @var Request
			 */
			protected Request $request
		) {
			// parse pattern
			$this->parsePathMatches($raw_matches);
		}
		
		/**
		 * Get the matched request pattern
		 * @return string The matched request pattern
		 */
		public function getPattern(): string {
			return $this->pattern;
		}
		
		/**
		 * Get the request object
		 * @return Request The request object
		 */
		public function getRequest(): Request {
			return $this->request;
		}
		
		/**
		 * Parse the pattern, setting any parameters in the request
		 * @param array $raw_matches The raw matches from the matched Router pattern
		 * @return void
		 */
		protected function parsePathMatches(array $raw_matches): void {
			if(empty($raw_matches)) {
				return;
			}
			
			// assign matched path parameters to request
			$this->request->assignOverrideParameters(
				// filter out numeric keys and override params
				array_filter($raw_matches, 'is_string', ARRAY_FILTER_USE_KEY)
			);
		}
	}