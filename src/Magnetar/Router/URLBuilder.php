<?php
	declare(strict_types=1);
	
	namespace Magnetar\Router;
	
	use Magnetar\Application;
	
	class URLBuilder {
		/**
		 * Schema (http, https, ftp, s3, etc)
		 * @var string
		 */
		protected string $schema = 'https';
		
		/**
		 * Hostname (example.com, www.example.com, etc)
		 * @var string|null
		 */
		protected string|null $hostname = null;
		
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
		 * Named parameters (?param=value&param2=value2)
		 * @var array
		 */
		protected array $params = [];
		
		/**
		 * Fragment (#...)
		 * @var string|null
		 */
		protected string|null $fragment = null;
		
		/**
		 * Constructor method
		 * @param Application $app
		 */
		public function __construct(
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
			
			// set the schema
			if(isset($parts['scheme'])) {
				$builder->schema($parts['scheme']);
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
		 * Set the schema
		 * @param string $schema The schema to use. Example: http, https, ftp, s3, etc
		 * @return self
		 */
		public function schema(string $schema): self {
			$this->schema = strtolower($schema);
			
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
		 * Build the URL
		 * @return string
		 */
		public function build(): string {
			// start with the schema
			$url = $this->schema . '://';
			
			// add the hostname
			if(null === $this->hostname) {
				$this->hostname($this->app['config']->get('app.hostname') ?? $_SERVER['HTTP_HOST']);
			}
			
			$url .= $this->hostname;
			
			// add the port (if necessary)
			if(null !== $this->port) {
				if(80 === $this->port) {
					if('http' !== $this->schema) {
						$url .= ':'. $this->port;
					}
				} elseif(443 === $this->port) {
					if('https' !== $this->schema) {
						$url .= ':'. $this->port;
					}
				} else {
					$url .= ':'. $this->port;
				}
			}
			
			// add a trailing slash
			$url .= '/';
			
			// add the path
			if('' !== $this->path) {
				$url .= ltrim($this->path, '/');
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