<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http;
	
	use Magnetar\Http\Response;
	use Magnetar\Http\Exceptions\InvalidRedirectURLException;
	use Magnetar\Http\Exceptions\InvalidResponseCodeException;
	use Magnetar\Helpers\Facades\URL;
	
	/**
	 * An HTTP response that redirects to a URL
	 */
	class RedirectResponse extends Response {
		/**
		 * The URL to redirect to
		 * @var string|null
		 */
		protected string|null $url = null;
		
		/**
		 * How many seconds to delay the redirect, showing a 'Redirecting...' page during instead of an instant redirect
		 * @var int
		 */
		protected int $delay_seconds = 0;
		
		/**
		 * Simplified alias for setURL()
		 * @param string $url The URL to redirect to
		 * @return self
		 */
		public function to(string $url): self {
			if(!preg_match('/^https?:\/\//', $url)) {
				$url = URL::to($url);
			}
			
			if(!filter_var($url, FILTER_VALIDATE_URL)) {
				throw new InvalidRedirectURLException('Invalid redirect URL');
			}
			
			$this->url = $url;
			
			return $this;
		}
		
		/**
		 * Set the redirect URL
		 * @param string $url The URL to redirect to. If it does not start with http:// or https://, it will be prepended with the current domain
		 * @return self
		 * 
		 * @throws InvalidRedirectURLException
		 */
		public function url(string $url): self {
			return $this->to($url);
		}
		
		/**
		 * {@inheritDoc}
		 * 
		 * @throws InvalidResponseCodeException
		 */
		public function responseCode(int $response_code=307): self {
			// sanitize response code
			if(!in_array($response_code, [300, 301, 302, 303, 304, 307, 308])) {
				throw new InvalidResponseCodeException('Invalid redirect code');
			}
			
			return parent::responseCode($response_code);
		}
		
		/**
		 * Set the response code to 301 (permanent redirect)
		 * @return self
		 */
		public function permanent(): self {
			return $this->responseCode(301);
		}
		
		/**
		 * Set the response code to 307 (temporary redirect)
		 * @return self
		 */
		public function temporary(): self {
			return $this->responseCode(307);
		}
		
		/**
		 * Trigger the basic redirect page to delay for a certain number of seconds
		 * @param int $seconds
		 * @return self
		 */
		public function delay(int $seconds=0): self {
			$this->delay_seconds = abs($seconds);
			
			return $this;
		}
		
		/**
		 * Resets the delay to the default of 0 seconds
		 * @return self
		 */
		public function noDelay(): self {
			$this->delay_seconds = 0;
			
			return $this;
		}
		
		/**
		 * {@inheritDoc}
		 * 
		 * @throws InvalidRedirectURLException
		 */
		public function send(): self {
			// check for valid URL
			if(null === $this->url) {
				throw new InvalidRedirectURLException('No URL set');
			}
			
			// send the redirect header if there is no delay
			if(0 === $this->delay_seconds) {
				$this->header(
					'Location',
					$this->url,
					true,
					$this->response_code
				);
			}
			
			// set body to a basic redirect page
			$this->body( $this->generateRedirecHTMLPage($this->url) );
			
			// send the response
			return parent::send();
		}
		
		/**
		 * Generate the redirect HTML page
		 * @param string|null $url The URL to redirect to. Defaults to the URL set in the response
		 * @return string
		 */
		protected function generateRedirecHTMLPage(string|null $url=null): string {
			// sanitize url
			$url ??= $this->url;
			
			// escape the URL for HTML
			$escaped_url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
			
			// generate the redirect page
			return <<<HTML
				<!DOCTYPE html>
				<html>
					<head>
						<title>Redirecting...</title>
						<meta http-equiv="refresh" content="{$this->delay_seconds};url={$escaped_url}">
						<meta name="robots" content="noindex,follow">
					</head>
					<body>
						<p>Redirecting to <a href="{$escaped_url}">{$url}</a></p>
					</body>
				</html>
			HTML;
		}
	}