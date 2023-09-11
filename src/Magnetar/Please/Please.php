<?php
	declare(strict_types=1);
	
	namespace Magnetar\Please;
	
	use Exception;
	
	use Magnetar\Application;
	use Magnetar\Console\Input;
	use Magnetar\Console\Output;
	use Magnetar\Console\ErrorOutput;
	use Magnetar\Please\DefaultCommandRegistry;
	
	/**
	 * Please is a command line tool for PHP to help with common tasks.
	 * Ask PHP nicely and it will do anything you want
	 */
	class Please {
		/**
		 * The default command registry
		 * @var DefaultCommandRegistry
		 */
		protected DefaultCommandRegistry $registry;
		
		/**
		 * The named command provided by the input
		 * @var string
		 */
		protected ?string $command;
		
		/**
		 * Constructor
		 * @param Application $app The application instance
		 * @param Input $input The input object
		 * @param DefaultCommandRegistry|null $commands Override the default command registery
		 * 
		 * @throws Exception If unable to determine the command
		 * 
		 * @TODO add middleware
		 */
		public function __construct(
			protected Application $app,
			protected Input $input,
			?DefaultCommandRegistry $registry=null
		) {
			$this->registry = $registry ?? new DefaultCommandRegistry();
			
			// @TODO add middleware
			
			$this->determineCommand();
		}
		
		/**
		 * Get the application instance
		 * @return Application The application instance
		 */
		public function getApp(): Application {
			return $this->app;
		}
		
		/**
		 * Get the input object
		 * @return Input The input object
		 */
		public function getInput(): Input {
			return $this->input;
		}
		
		/**
		 * Using the input, determine the command to run
		 * @return void
		 * 
		 * @throws Exception If unable to determine the command
		 * 
		 * @todo identify the requested named command
		 */
		protected function determineCommand(): void {
			// identify the requested named command
			$this->command = $this->input->getArgument(0) ?? throw new Exception('Unable to determine command');
		}
		
		/**
		 * Run the command
		 * @return Output
		 * 
		 * @TODO parse the input
		 * @TODO define known commands
		 * @TODO route requested named command to the correct action
		 * @TODO process the action and return the result
		 */
		public function run(): Output {
			try {
				// route requested named command to the correct action
				$action_class = $this->registry->getCommandClass($this->command) ?? throw new Exception('Unknown command');
				
				// start up the action instance
				$action = (new $action_class)();
				
				// handle the action request and return the output
				return $action->handle($this);
			} catch(Exception $e) {
				return new ErrorOutput($e->getMessage());
			}
		}
	}