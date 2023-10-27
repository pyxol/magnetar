<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http;
	
	use Magnetar\Http\Response;
	use Magnetar\Http\Exceptions\InvalidRedirectURLException;
	use Magnetar\Http\Exceptions\InvalidResponseCodeException;
	
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
		 * The HTTP Response Code
		 * @var int
		 */
		protected int $statusCode = 307;
		
		/**
		 * How long to delay the redirect in seconds, showing a "Redirecting..." page during the delay
		 * @var int
		 */
		protected int $delaySeconds = 0;
		
		/**
		 * Set the redirect URL
		 * @param string $url
		 * @return self
		 * 
		 * @throws InvalidRedirectURLException
		 */
		public function setURL(string $url): self {
			if(!filter_var($url, FILTER_VALIDATE_URL)) {
				throw new InvalidRedirectURLException('Invalid redirect URL');
			}
			
			$this->url = $url;
			
			return $this;
		}
		
		/**
		 * Set the response code
		 * @param int $code The HTTP Response Code for the redirect. Only allows 301, 302, and 307. Defaults to 307
		 * @return self
		 * 
		 * @throws InvalidResponseCodeException
		 */
		public function setCode(int $code=307): self {
			if(!in_array($code, [301, 302, 307])) {
				throw new InvalidResponseCodeException('Invalid redirect code');
			}
			
			return $this;
		}
		
		/**
		 * Set the response code to 301 (permanent redirect)
		 * @return self
		 */
		public function permanent(): self {
			$this->statusCode = 301;
			
			return $this;
		}
		
		/**
		 * Set the response code to 307 (temporary redirect)
		 * @return self
		 */
		public function temporary(): self {
			$this->statusCode = 307;
			
			return $this;
		}
		
		/**
		 * Trigger the basic redirect page to delay for a certain number of seconds
		 * @param int $seconds
		 * @return self
		 */
		public function delay(int $seconds=0): self {
			$this->delaySeconds = abs($seconds);
			
			return $this;
		}
		
		/**
		 * Resets the delay to the default of 0 seconds
		 * @return self
		 */
		public function nodelay(): self {
			$this->delaySeconds = 0;
			
			return $this;
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function send(): self {
			// check for valid URL
			if(null === $this->url) {
				throw new \Exception('No URL set');
			}
			
			// send the redirect header if there is no delay
			if(0 === $this->delaySeconds) {
				$this->header('Location', $this->url, true, $this->statusCode);
			}
			
			// set body to a basic redirect page
			$this->setBody(
				$this->generateRedirecPage($this->url)
			);
			
			$this->sendHeaders();
			$this->sendBody();
			
			$this->sent = true;
			
			return $this;
		}
		
		/**
		 * Generate the redirect HTML page
		 * @param string|null $url The URL to redirect to. Defaults to the URL set in the response
		 * @return string
		 */
		protected function generateRedirecPage(string|null $url=null): string {
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
						<meta http-equiv="refresh" content="{$this->delaySeconds};url={$escaped_url}">
						<meta name="robots" content="noindex,follow">
					</head>
					<body>
						<p>Redirecting to <a href="{$escaped_url}">{$url}</a></p>
					</body>
				</html>
			HTML;
		}
	}