<?php
	declare(strict_types=1);
	
	namespace Magnetar\Kernel;
	
	use Exception;
	
	use Magnetar\Kernel\KernelPanicException;
	
	abstract class AbstractKernel {
		abstract protected function preprocess(): void;
		abstract protected function postprocess(): void;
		
		abstract protected function handlePanic(KernelPanicException $e): void;
		
		/**
		 * Process the main executable. First parameter is a callback, any other parameters are sent to the callback
		 * @return void
		 */
		protected function execute(): void {
			try {
				// run preprocess routine
				$this->preprocess();
				
				// at least one arg is required
				if(func_num_args() < 1) {
					throw new KernelPanicException('Unable to process callback');
				}
				
				// pull function args
				$params = func_get_args();
				
				// first arg is expected to be callable
				$callback = array_shift($params);
				
				if(is_array($callback)) {
					// class reference and method
					list($class, $method) = $callback;
					
					$instance = new ($class)($this);
					//$instance->$method($this->request, $this->response);
					
					// call instance method and reference params
					call_user_func_array([$instance, $method], $params);
				} elseif(is_callable($callback)) {
					// closure
					call_user_func_array($callback, $params);
				} else {
					// unknown callback method
					throw new KernelPanicException('Kernel execution was provided an unprocessable callback');
				}
			} catch(KernelPanicException $e) {
				// handle kernel panic
				$this->handlePanic($e);
			} catch(Exception $e) {
				print 'Unhandled kernel exception: '. $e->getMessage();
			}
			
			// run postprocess routine
			$this->postprocess();
			
			$this->terminate();
		}
		
		/**
		 * End script execution
		 * @return void
		 */
		protected function terminate(): void {
			// end app
			die;
		}
	}