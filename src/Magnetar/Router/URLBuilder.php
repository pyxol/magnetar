<?php
	declare(strict_types=1);
	
	namespace Magnetar\Router;
	
	use Magnetar\Application;
	
	/**
	 * URL Builder
	 */
	class URLBuilder {
		/**
		 * Scheme (http, https, ftp, s3, etc)
		 * @var string|null
		 */
		protected string|null $scheme = null;
		
		/**
		 * Cached scheme pulled from app.url that is used when no scheme is set (http, https)
		 * @var string|null
		 */
		protected static string|null $cachedScheme = null;
		
		/**
		 * Hostname (example.com, www.example.com, etc)
		 * @var string|null
		 */
		protected string|null $hostname = null;
		
		/**
		 * Cached hostname pulled from app.url that is used when no hostname is set (eg: example.com))
		 * @var string|null
		 */
		protected static string|null $cachedHostname = null;
		
		/**
		 * Port (80, 443, 21, etc)
		 * @var int|null
		 */
		protected int|null $port = null;
		
		/**
		 * Path (/path/to/file)
		 * @var string
		 */
		protected string $path = '';
		
		/**
		 * Path prefix
		 * @var string|null
		 */
		protected string|null $pathPrefix = null;
		
		/**
		 * Path suffix
		 * @var string|null
		 */
		protected string|null $pathSuffix = null;
		
		/**
		 * Named parameters (?param=value&param2=value2)
		 * @var array
		 */
		protected array $params = [];
		
		/**
		 * Fragment or hash (#...)
		 * @var string|null
		 */
		protected string|null $fragment = null;
		
		/**
		 * Constructor method
		 * @param Application $app
		 */
		public function __construct(
			/**
			 * The application instance
			 * @var Application
			 */
			protected Application $app
		) {
			
		}
		
		/**
		 * Factory method to create a new URLBuilder instance from a URL
		 * @param string $url The URL to use
		 * @return URLBuilder
		 */
		public function make(string $url): URLBuilder {
			$builder = new static($this->app);
			
			// parse the URL
			$parts = parse_url($url);
			
			// set the scheme
			if(isset($parts['scheme'])) {
				$builder->scheme($parts['scheme']);
			}
			
			// set the hostname
			if(isset($parts['host'])) {
				$builder->hostname($parts['host']);
			}
			
			// set the port
			if(isset($parts['port'])) {
				$builder->port((int)$parts['port']);
			}
			
			// set the path
			if(isset($parts['path'])) {
				$builder->path($parts['path']);
			}
			
			// set the parameters
			if(isset($parts['query'])) {
				parse_str($parts['query'], $params);
				$builder->params($params);
			}
			
			// set the fragment
			if(isset($parts['fragment'])) {
				$builder->fragment($parts['fragment']);
			}
			
			return $builder;
		}
		
		/**
		 * Set the hostname
		 * @param string $hostname The hostname to use. Example: example.com, www.example.com, etc
		 * @return self
		 */
		public function hostname(string|null $hostname=null): self {
			$this->hostname = $hostname;
			
			return $this;
		}
		
		/**
		 * Set the scheme
		 * @param string $scheme The scheme to use. Example: http, https, ftp, s3, etc
		 * @return self
		 */
		public function scheme(string $scheme): self {
			$this->scheme = strtolower($scheme);
			
			return $this;
		}
		
		/**
		 * Set the port
		 * @param int $port The port to use. Example: 80, 443, 21, etc
		 * @return self
		 */
		public function port(int $port): self {
			$this->port = $port;
			
			return $this;
		}
		
		/**
		 * Set the path
		 * @param string $path The path to use. Example: path/to/file/
		 * @return self
		 */
		public function path(string $path): self {
			$this->path = $path;
			
			return $this;
		}
		
		/**
		 * Prefix the path
		 * @param string $path The path to prefix with
		 * @return self
		 */
		public function prefixPath(string|null $path=null): self {
			$this->pathPrefix = $path;
			
			return $this;
		}
		
		/**
		 * Suffix the path
		 * @param string $path The path to suffix with
		 * @return self
		 */
		public function suffixPath(string|null $path=null): self {
			$this->pathSuffix = $path;
			
			return $this;
		}
		
		/**
		 * Store an array of named parameters
		 * @param array $params Associative array of parameters
		 * @return self
		 */
		public function params(array $params): self {
			$this->params = $params;
			
			return $this;
		}
		
		/**
		 * Set a parameter
		 * @param string $param The parameter to set
		 * @param mixed $value The value to set the parameter to
		 * @return self
		 * 
		 * @see \http_build_query()
		 */
		public function param(string $param, mixed $value=null): self {
			if(null === $value) {
				unset($this->params[ $param ]);
			} else {
				$this->params[ $param ] = $value;
			}
			
			return $this;
		}
		
		/**
		 * Remove a parameter
		 * @param string $param The parameter to remove
		 * @return self
		 */
		public function removeParam(string $param): self {
			unset($this->params[ $param ]);
			
			return $this;
		}
		
		/**
		 * Set the fragment
		 * @param string $fragment The fragment to use. Example: 'abc' for #abc
		 * @return self
		 */
		public function fragment(string|null $fragment): self {
			$this->fragment = $fragment;
			
			return $this;
		}
		
		/**
		 * Get the cached scheme
		 * @return string
		 */
		public function getCachedScheme(): string {
			if(null === static::$cachedScheme) {
				if(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && (('https' === $_SERVER['HTTP_X_FORWARDED_PROTO']) || ('http' === $_SERVER['HTTP_X_FORWARDED_PROTO']))) {
					$scheme = strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']);
				} else {
					//$scheme = parse_url($this->app['config']->get('app.url', ''), PHP_URL_SCHEME);
					//
					//if((false === $scheme) || (null === $scheme)) {
						// default to http if we can't get the scheme from the environment
						$scheme = $_SERVER['REQUEST_SCHEME'] ?? $_SERVER['SERVER_PROTOCOL'] ?? 'http';
					//}
				}
				
				static::$cachedScheme = $scheme;
			}
			
			return static::$cachedScheme;
		}
		
		/**
		 * Get the cached hostname
		 * @return string
		 */
		public function getCachedHostname(): string {
			if(null === static::$cachedHostname) {
				$hostname = parse_url($this->app['config']->get('app.url', ''), PHP_URL_HOST);
				
				if((false === $hostname) || (null === $hostname)) {
					// default to http
					$hostname = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
				}
				
				static::$cachedHostname = $hostname;
			}
			
			return static::$cachedHostname;
		}
		
		/**
		 * Build the URL
		 * @return string
		 */
		public function build(): string {
			$url = '';
			
			// start with the scheme
			$url .= $this->scheme ?? $this->getCachedScheme();
			$url .= '://';
			
			// add the hostname
			$url .= $this->hostname ?? $this->getCachedHostname();
			
			// add the port (if necessary)
			if(null !== $this->port) {
				if(80 === $this->port) {
					if('http' !== $this->scheme) {
						$url .= ':'. $this->port;
					}
				} elseif(443 === $this->port) {
					if('https' !== $this->scheme) {
						$url .= ':'. $this->port;
					}
				} else {
					$url .= ':'. $this->port;
				}
			}
			
			// add a trailing slash
			$url .= '/';
			
			// attach the path prefix
			if(null !== $this->pathPrefix) {
				$url .= ltrim($this->pathPrefix, '/');
			}
			
			// add the path
			if('' !== $this->path) {
				$url .= ltrim($this->path, '/');
			}
			
			// attach the path suffix
			if(null !== $this->pathSuffix) {
				$url .= $this->pathSuffix;
			}
			
			// add params
			if(!empty($this->params)) {
				if(false !== strpos($this->path, '?')) {
					$url .= '&';
				} else {
					$url .= '?';
				}
				
				$url .= http_build_query($this->params);
			}
			
			// add fragment
			if(null !== $this->fragment) {
				$url .= '#'. $this->fragment;
			}
			
			// done
			return $url;
		}
		
		/**
		 * Generate the URL when the object is used as a string
		 * @return string
		 */
		public function __toString(): string {
			return $this->build();
		}
	}