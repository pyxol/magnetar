<?php
	declare(strict_types=1);
	
	namespace Magnetar\Router;
	
	use Magnetar\Http\Request\Request;
	
	class Route {
		/**
		 * The request object
		 * @var Request
		 */
		protected Request $request;
		
		/**
		 * The pattern to match against
		 * @var string
		 */
		protected string $pattern = '';
		
		public function __construct(string $pattern, array $raw_matches, Request $request) {
			// assign pattern
			$this->pattern = $pattern;
			
			// assign request
			$this->request = $request;
			
			// parse pattern
			$this->parsePathMatches($raw_matches);
		}
		
		/**
		 * Get the request object
		 * @return Request
		 */
		public function getPattern(): string {
			return $this->pattern;
		}
		
		/**
		 * Get the request object
		 * @return Request
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