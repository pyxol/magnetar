<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http\CookieJar;
	
	class Cookie {
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
			 * Expiration time in seconds. Used for newly created cookies
			 * @var int|null
			 */
			protected int|null $expires_seconds=null
		) {
			
		}
		
		/**
		 * Get the name of the cookie
		 */
		public function getName(): string {
			return $this->name;
		}
		
		/**
		 * Get the value of the cookie
		 */
		public function getValue(): string {
			return $this->value;
		}
		
		/**
		 * Get the expiration time of the cookie
		 */
		public function getExpires(): int|null {
			return $this->expires_seconds;
		}
		
		/**
		 * Set the value of the cookie
		 */
		public function setValue(string $value): void {
			$this->value = $value;
		}
		
		/**
		 * Set the expiration time of the cookie
		 */
		public function setExpires(int $expires_seconds): void {
			$this->expires_seconds = $expires_seconds;
		}
		
		/**
		 * Get the cookie as a string
		 */
		public function __toString(): string {
			return $this->name .'='. $this->value;
		}
		
		/**
		 * Create a new cookie
		 */
		public static function create(string $name, string $value='', int|null $expires=null): Cookie {
			return new Cookie($name, $value, $expires);
		}
		
		/**
		 * Create a new cookie from a string
		 */
		public static function fromString(string $cookie): Cookie {
			$parts = explode('=', $cookie, 2);
			
			return new Cookie($parts[0], $parts[1]);
		}
		
		/**
		 * Create a new cookie from an array
		 */
		public static function fromArray(array $cookie): Cookie {
			return new Cookie($cookie['name'], $cookie['value'], $cookie['expires']);
		}
		
		/**
		 * Create a new cookie from a cookie object
		 */
		public static function fromCookie(Cookie $cookie): Cookie {
			return new Cookie($cookie->getName(), $cookie->getValue(), $cookie->getExpires());
		}
	}