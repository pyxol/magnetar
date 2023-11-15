<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http\CookieJar;
	
	use Magnetar\Http\Request;
	
	class CookieJar {
		/**
		 * Assoc array of cookies
		 * @var array
		 */
		protected array $cookies = [];
		
		/**
		 * Assoc array of queued cookies to be sent with the response
		 * @var array
		 */
		protected array $cookie_queue = [];
		
		/**
		 * Status of whether the cookies have been imported from a request
		 * @var bool
		 */
		protected bool $imported = false;
		
		/**
		 * Status of whether the default cookie settings have been set
		 * @var bool
		 */
		protected bool $defaults_set = false;
		
		/**
		 * Default expiration time in seconds
		 * @var int
		 */
		protected int $default_expires_seconds = 3600;
		
		/**
		 * Default path
		 * @var string
		 */
		protected string $default_path = '';
		
		/**
		 * Default domain
		 * @var string
		 */
		protected string $default_domain = '';
		
		/**
		 * Default secure flag
		 * @var bool
		 */
		protected bool $default_secure = false;
		
		/**
		 * Default httponly flag
		 * @var bool
		 */
		protected bool $default_httponly = false;
		
		/**
		 * CookieJar constructor
		 */
		public function __construct() {
			
		}
		
		/**
		 * Import cookies from a request
		 * @param Request $request The request instance
		 * @return void
		 */
		public function importCookiesFromRequest(Request $request): void {
			if($this->imported) {
				return;
			}
			
			$cookies = $request->cookies();
			
			foreach($cookies as $name => $value) {
				$this->cookies[ $name ] = new Cookie($name, $value);
			}
			
			$this->imported = true;
		}
		
		/**
		 * Get all cookies
		 * @return array
		 */
		public function getCookies(): array {
			return $this->cookies;
		}
		
		/**
		 * Get all queued cookies
		 * @return array
		 */
		public function getQueuedCookies(): array {
			return $this->cookie_queue;
		}
		
		/**
		 * Get a specific cookie
		 * @param string $name The cookie name
		 * @return Cookie|null
		 */
		public function get(string $name): ?Cookie {
			return $this->cookie_queue[ $name ] ?? $this->cookies[ $name ] ?? null;
		}
		
		/**
		 * Get a cookie value
		 * @param string $name The cookie name
		 * @return string|null
		 */
		public function getValue(string $name): string|null {
			if(null === ($cookie = $this->get($name))) {
				return null;
			}
			
			return $cookie->getValue();
		}
		
		/**
		 * Set a cookie using a Cookie instance
		 * @param string $name The cookie name
		 * @param string $value The cookie value
		 * @param int|null $expires_seconds The expiration time in seconds
		 * @return self
		 */
		public function set(
			string $name,
			string $value,
			int|null $expires_seconds=null
		): self {
			$this->cookie_queue[ $name ] = new Cookie(
				$name,
				$value,
				$expires_seconds ?? $this->default_expires_seconds
			);
			
			return $this;
		}
		
		/**
		 * Set a cookie using a Cookie instance
		 * @param Cookie $cookie The cookie instance
		 * @return self
		 */
		public function setCookie(Cookie $cookie): self {
			$this->cookie_queue[ $cookie->getName() ] = $cookie;
			
			return $this;
		}
		
		/**
		 * Remove a cookie
		 * @param string $name The cookie name
		 * @return self
		 */
		public function remove(string $name): self {
			unset($this->cookie_queue[ $name ]);
			
			return $this;
		}
		
		
		public function getDefaultExpiresSeconds(): int {
			return $this->default_expires_seconds;
		}
		
		/**
		 * Set the default cookie settings
		 * @param int $expires_seconds The default expiration time in seconds
		 * @param string $path The default path
		 * @param string $domain The default domain
		 * @return self
		 */
		public function setDefaults(
			int|null $expires_seconds=null,
			string|null $path=null,
			string|null $domain=null,
			bool|null $secure=null,
			bool|null $httponly=null
		): self {
			if($this->defaults_set) {
				return $this;
			}
			
			if(null !== $expires_seconds) {
				$this->default_expires_seconds = $expires_seconds;
			}
			
			if(null !== $path) {
				$this->default_path = $path;
			}
			
			if(null !== $domain) {
				$this->default_domain = $domain;
			}
			
			if(null !== $secure) {
				$this->default_secure = $secure;
			}
			
			if(null !== $httponly) {
				$this->default_httponly = $httponly;
			}
			
			// toggle the status of the defaults
			$this->defaults_set = true;
			
			return $this;
		}
		
		/**
		 * Get the default cookie path
		 * @return string The default cookie path
		 */
		public function getDefaultPath(): string {
			return $this->default_path;
		}
		
		/**
		 * Get the default cookie domain
		 * @return string The default cookie domain
		 */
		public function getDefaultDomain(): string {
			return $this->default_domain;
		}
		
		/**
		 * Get the default cookie secure flag
		 * @return bool The default cookie secure flag
		 */
		public function getDefaultSecure(): bool {
			return $this->default_secure;
		}
		
		/**
		 * Get the default cookie httponly flag
		 * @return bool The default cookie httponly flag
		 */
		public function getDefaultHttpOnly(): bool {
			return $this->default_httponly;
		}
	}