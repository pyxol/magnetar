<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http\Middleware;
	
	use Closure;
	
	use Magnetar\Http\Request;
	use Magnetar\Http\Response;
	
	class ZeroLengthParametersToNull {
		/**
		 * Handle the request
		 * @param Request $request
		 */
		public function handle(Request $request, Closure $next): Response {
			$parameters = $request->parameters();
			
			foreach($parameters as $key => $value) {
				if(is_string($value) && 0 === strlen($value)) {
					$request->setParameter($key, null);
				}
			}
			
			return $next($request);
		}
	}