<?php
	declare(strict_types=1);
	
	namespace Magnetar\Pipeline;
	
	use Closure;
	use Exception;
	use Throwable;
	
	use Magnetar\Container\Container;
	
	/**
	 * A pipeline that passes a thing through a series of pipes
	 */
	class Pipeline {
		/**
		 * The thing that is being passed through the pipeline
		 * @var mixed
		 */
		protected mixed $thing;
		
		/**
		 * An array of callable pipes to pass the thing through
		 * @var array
		 */
		protected array $pipes = [];
		
		/**
		 * The method to call on each pipe
		 * @var string
		 */
		protected string $method = 'handle';
		
		/**
		 * Create a new Pipeline instance
		 * @param Container $container
		 */
		public function __construct(
			protected Container $container
		) {
			
		}
		
		/**
		 * Set the thing to pass through the pipeline
		 * @param mixed $thing
		 * @return $this
		 */
		public function send(mixed $thing): static {
			$this->thing = $thing;
			
			return $this;
		}
		
		/**
		 * Set the pipes to pass the thing through
		 * @param array $pipes
		 * @return $this
		 */
		public function through(array $pipes): static {
			$this->pipes = $pipes;
			
			return $this;
		}
		
		/**
		 * Set the method to call on each pipe
		 * @param string $method
		 * @return $this
		 */
		public function using(string $method): static {
			$this->method = $method;
			
			return $this;
		}
		
		/**
		 * Run through the pipeline with a final destination callback
		 * @param Closure $destination The final destination callback
		 * @return mixed
		 * @throws Exception
		 */
		public function then(Closure $destination): mixed {
			$pipeline = array_reduce(
				// reverse the pipes so they are executed in the correct order
				// eg: from the core of the onion outwards
				array_reverse($this->pipes),
				
				// create a closure that will call the next pipe in the chain
				$this->carry(),
				
				// create a closure that will call the destination callback
				$this->prepDestination($destination)
			);
			
			return $pipeline($this->thing);
		}
		
		/**
		 * Run through the pipline and return the result
		 * @return mixed
		 */
		public function thenReturn(): mixed {
			return $this->then(function($thing) {
				return $thing;
			});
		}
		
		/**
		 * Create a closure for the final pipe
		 * @param Closure $destination
		 * @return Closure
		 */
		protected function prepDestination(Closure $destination): Closure {
			return function($thing) use ($destination) {
				return $destination($thing);
			};
		}
		
		/**
		 * Create a closure for the next pipe in the chain
		 * @return Closure
		 * 
		 * @throws Exception
		 */
		protected function carry(): Closure {
			return function($stack, $pipe) {
				return function($thing) use ($stack, $pipe) {
					try {
						if(is_callable($pipe)) {
							// if the pipe is a callable, call it directly
							return $pipe($thing, $stack);
						} elseif(!is_object($pipe)) {
							// allow the container to handle the instantiation of the pipe
							// by passing the pipe name to the container
							[$name, $parameters] = $this->parsePipeString($pipe);
							
							$pipe = $this->container->make($name);
							
							$parameters = array_merge([$thing, $stack], $parameters);
						} else {
							// if the pipe is an object, just pass it the thing and the stack
							$parameters = [$thing, $stack];
						}
						
						// if the pipe has the pipeline's handle method, call that
						// otherwise call the pipe directly
						$carry = method_exists($pipe, $this->method)
							? $pipe->{$this->method}(...$parameters)
							: $pipe(...$parameters);
						
						return $this->handleCarry($carry, $thing);
					} catch(Throwable $e) {
						$this->handleException($thing, $e);
					}
				};
			};
		}
		
		/**
		 * Parse a pipe string into a name and parameters
		 * @param string $pipe The pipe string
		 * @return array
		 */
		protected function parsePipeString(string $pipe): array {
			[$name, $parameters] = array_pad(explode(':', $pipe, 2), 2, []);
			
			if(is_string($parameters)) {
				$parameters = explode(',', $parameters);
			}
			
			return [$name, $parameters];
		}
		
		/**
		 * Handle the result of a pipe
		 * @param mixed $carry The result of the pipe
		 * @param mixed $thing The input that was passed through the pipe
		 * @return mixed
		 */
		protected function handleCarry(mixed $carry, mixed $thing): mixed {
			return $carry;
		}
		
		/**
		 * Handle an exception that occurs in a pipe
		 * @param mixed $thing
		 * @param Throwable $e
		 * @return void
		 * 
		 * @throws Throwable
		 */
		protected function handleException(mixed $thing, Throwable $e): void {
			throw $e;
		}
	}