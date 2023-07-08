<?php
	declare(strict_types=1);
	
	namespace Magnetar\Container;
	
	use Magnetar\Container\Container;
	use Magnetar\Container\Helper;
	
	class ContextualBindingBuilder {
		/**
		 * The underlying container instance
		 * @var Magnetar\Container\Container
		 */
		protected $container;
		
		/**
		 * The concrete instance
		 * @var string|array
		 */
		protected $concrete;
		
		/**
		 * The abstract target
		 * @var string
		 */
		protected $needs;
		
		/**
		 * Create a new contextual binding builder
		 * @param Magnetar\Container\Container $container
		 * @param string|array $concrete
		 * @return void
		 */
		public function __construct(Container $container, string|array $concrete) {
			$this->concrete = $concrete;
			$this->container = $container;
		}
		
		/**
		 * Define the abstract target that depends on the context
		 * @param string $abstract
		 * @return $this
		 */
		public function needs(string $abstract): self {
			$this->needs = $abstract;
			
			return $this;
		}
		
		/**
		 * Define the implementation for the contextual binding
		 *
		 * @param Closure|string|array $implementation
		 * @return void
		 */
		public function give(Closure|string|array $implementation): void {
			foreach(Helper::arrayWrap($this->concrete) as $concrete) {
				$this->container->addContextualBinding($concrete, $this->needs, $implementation);
			}
		}
		
		/**
		 * Define tagged services to be used as the implementation for the contextual binding
		 * @param string $tag
		 * @return void
		 */
		public function giveTagged(string $tag): void {
			$this->give(function ($container) use ($tag) {
				$taggedServices = $container->tagged($tag);
				
				return is_array($taggedServices) ? $taggedServices : iterator_to_array($taggedServices);
			});
		}
		
		/**
		 * Specify the configuration item to bind as a primitive
		 * @param  string  $key
		 * @param  mixed  $default
		 * @return void
		 */
		public function giveConfig(string $key, mixed $default=null): void {
			$this->give(fn ($container) => $container->get('config')->get($key, $default));
		}
	}
