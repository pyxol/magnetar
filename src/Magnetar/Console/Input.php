<?php
	declare(strict_types=1);
	
	namespace Magnetar\Console;
	
	/**
	 * Command line input
	 */
	class Input {
		/**
		 * Script name
		 * @var string|null
		 */
		protected string|null $script_name = null;
		
		/**
		 * The command line arguments (that aren't flags) that were passed
		 * @var array Simple array of arguments
		 */
		protected array $arguments = [];
		
		/**
		 * The command line flags (eg: -f, --flag)
		 * @var array Assoc array of flag name => value|true
		 */
		protected array $flags = [];
		
		/**
		 * Constructor
		 * @param array|null $arguments Array of arguments from a $argv-like source or null to use the default $_SERVER['argv']
		 */
		public function __construct(array|null $arguments=null) {
			$arguments ??= $_SERVER['argv'] ?? [];
			
			// parse the arguments
			$this->parseArguments($arguments);
		}
		
		/**
		 * Parse the raw arguments array into key/value flags and basic arguments
		 * @param array $args The raw list of arguments from the command line
		 * @return void
		 */
		protected function parseArguments(?array $args): void {
			$this->script_name = array_shift($arguments);
			
			foreach($args as $arg) {
				if('' === ($arg = trim($arg))) {
					continue;
				}
				
				// check if it's a flag or argument
				if(preg_match("#^(\-|\-\-)([A-Za-z0-9\-_]+)#si", $arg, $arg_match)) {
					$flag_name = ltrim($arg_match[2], '-');
					
					if(false !== strpos($flag_name, '=')) {
						// key value pair
						
						// flag example: -f=value
						// long flag example: --flag=value
						
						[ $flag_name, $flag_value ] = explode('=', $flag_name, 2);
						
						$flag_value = $flag_value;
					} else {
						// boolean flag
						
						// flag example: -f
						// long flag example: --flag
						
						$flag_value = true;
					}
					
					$this->flags[ trim($flag_name) ] = $flag_value;
				} else {
					// argument
					$this->arguments[] = trim($arg);
				}
			}
		}
		
		/**
		 * Get the script name from the command line input
		 * @return string|null The script name or null if not set
		 */
		public function getScriptName(): string|null {
			return $this->script_name;
		}
		
		/**
		 * Get the arguments
		 * @return array The command line arguments
		 */
		public function getArguments(): array {
			return $this->arguments;
		}
		
		/**
		 * Get a command line argument by index
		 * @param int $index The index of the argument
		 * @return mixed The argument string or null if not set
		 */
		public function getArgument(int $index): mixed {
			return $this->arguments[ $index ] ?? null;
		}
		
		/**
		 * Determine if an argument exists
		 * @param int $index The index of the argument
		 * @return mixed The value of the argument or null if not set
		 */
		public function hasArgument(string $argument): bool {
			return in_array($argument, $this->arguments);
		}
		
		/**
		 * Get the defined flags
		 * @return array The flags as an assoc array of flag name => value|true
		 */
		public function getFlags(): array {
			return $this->flags;
		}
		
		/**
		 * Get a command line flag's value
		 * @param string $flag_name The value of the flag. eg: f for -f, flag for --flag
		 * @return mixed The value of the flag or null if not set
		 */
		public function getFlag(string $flag_name): mixed {
			return $this->flags[ $flag_name ] ?? null;
		}
	}