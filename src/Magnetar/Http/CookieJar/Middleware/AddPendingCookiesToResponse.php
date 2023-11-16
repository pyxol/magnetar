<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http\CookieJar\Middleware;
	
	use Closure;
	
	use Magnetar\Http\CookieJar\CookieJar;
	use Magnetar\Http\Request;
	use Magnetar\Http\Response;
	
	class AddPendingCookiesToResponse {
		/**
		 * Constructor
		 */
		public function __construct(
			/**
			 * CookieJar instance
			 * @var CookieJar
			 */
			protected CookieJar $cookieJar
		) {
			$this->cookieJar = $cookieJar;
		}
		
		/**
		 * Handle the request
		 * @param Request $request
		 */
		public function handle(Request $request, Closure $next): Response {
			$response = $next($request);
			
			//print('<pre>'. print_r([
			//	'called_from' => 'AddPendingCookiesToResponse::handle()',
			//	//'response' => $response,
			//	'pending_cookies' => $this->cookieJar->getQueuedCookies(),
			//	'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),
			//], true) .'</pre>');
			
			foreach($this->cookieJar->getQueuedCookies() as $cookie) {
				// @TODO
				$response->setCookie(
					$cookie,
					$this->cookieJar->getDefaultExpiresSeconds(),
					$this->cookieJar->getDefaultPath(),
					$this->cookieJar->getDefaultDomain(),
					$this->cookieJar->getDefaultSecure(),
					$this->cookieJar->getDefaultHttpOnly()
				);
			}
			
			return $response;
		}
	}