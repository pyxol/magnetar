<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http\CookieJar;
	
	class Cookie {
		/**
		 * Whether the cookie has been sent
		 * @var bool
		 */
		protected bool $sent = false;
		
		/**
		 * Cookie constructor
		 */
		public function __construct(
			/**
			 * Cookie name
			 * @var string
			 */
			protected string $name,
			
			/**
			 * Cookie value
			 * @var string
			 */
			protected string $value='',
			
			/**
			 * Expiration in seconds
			 * @var int|null
			 */
			protected int|null $expires_seconds=null,
			
			/**
			 * Path
			 * @var string|null
			 */
			protected string|null $path=null,
			
			/**
			 * Domain
			 * @var string|null
			 */
			protected string|null $domain=null,
			
			/**
			 * Secure flag
			 * @var bool|null
			 */
			protected bool|null $secure=null,
			
			/**
			 * Httponly flag
			 * @var bool|null
			 */
			protected bool|null $httponly=null
		) {
			
		}
		
		/**
		 * Set the cookie using the PHP setcookie() function
		 * @return void
		 */
		public function send(): void {
			if($this->sent) {
				return;
			}
			
			setcookie(
				$this->name,
				$this->value,
				(time() + $this->expires_seconds),
				$this->path,
				$this->domain,
				$this->secure,
				$this->httponly
			);
			
			$this->sent = true;
		}
		
		/**
		 * Get the name of the cookie
		 * @return string
		 */
		public function getName(): string {
			return $this->name;
		}
		
		/**
		 * Set the name of the cookie
		 * @param string $name The cookie name
		 * @return self
		 */
		public function setName(string $name): self {
			$this->name = $name;
			
			return $this;
		}
		
		/**
		 * Get the value of the cookie
		 * @return string
		 */
		public function getValue(): string {
			return $this->value;
		}
		
		/**
		 * Set the value of the cookie
		 * @param string|bool $value The cookie value. If false, the cookie will be deleted
		 * @return self
		 */
		public function setValue(string|bool $value): self {
			$this->value = $value;
			
			return $this;
		}
		
		/**
		 * Get the expiration time of the cookie
		 * @return int|null
		 */
		public function getExpires(): int|null {
			return $this->expires_seconds;
		}
		
		/**
		 * Set the expiration time of the cookie
		 * @param int $expires_seconds The expiration time in seconds. If null, the default will be used
		 * @return self
		 */
		public function setExpires(int $expires_seconds): self {
			$this->expires_seconds = $expires_seconds;
			
			return $this;
		}
		
		/**
		 * Get the expiration time of the cookie
		 * @return string|null
		 */
		public function getPath(): string|null {
			return $this->path;
		}
		
		/**
		 * Set the expiration time of the cookie
		 * @param string|null $path The path. If null, the default will be used
		 * @return self
		 */
		public function setPath(string|null $path): self {
			$this->path = $path;
			
			return $this;
		}
		
		/**
		 * Get the Domain setting
		 * @return string|null
		 */
		public function getDomain(): string|null {
			return $this->domain;
		}
		
		/**
		 * Set the Domain setting
		 * @param string|null $path The path. If null, the default will be used
		 * @return self
		 */
		public function setDomain(string|null $domain): self {
			$this->domain = $domain;
			
			return $this;
		}
		
		/**
		 * Get the Secure setting
		 * @return bool|null
		 */
		public function getSecure(): bool|null {
			return $this->secure;
		}
		
		/**
		 * Set the Secure setting
		 * @param bool|null $secure The Secure setting. If null, the default will be used
		 * @return self
		 */
		public function setSecure(bool|null $secure): self {
			$this->secure = $secure;
			
			return $this;
		}
		
		/**
		 * Get the Http Only setting
		 * @return bool|null
		 */
		public function getHttpOnly(): bool|null {
			return $this->httponly;
		}
		
		/**
		 * Set the Http Only setting
		 * @param bool|null $httponly The Http Only setting. If null, the default will be used
		 * @return self
		 */
		public function setHttpOnly(bool|null $httponly): self {
			$this->httponly = $httponly;
			
			return $this;
		}
	}