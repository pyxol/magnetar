<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers;
	
	use Exception;
	
	use Magnetar\Helpers\Enums\Typed;
	
	/**
	 * Helper static class to assist with typed values
	 */
	class TypedHelper {
		/**
		 * Get the type of a value
		 * @param mixed $value The value to get the type of
		 * @param Typed|null $default The default value to return if the type is unknown. If null, an exception will be thrown
		 * @return Typed The type of the value
		 * 
		 * @throws Exception If the type is unknown and no default value is provided
		 */
		public static function getType(mixed $value, Typed|null $default=null): Typed {
			// get the type of the value
			$type = gettype($value);
			
			// return the type
			return match($type) {
				'boolean' => Typed::Boolean,
				'integer' => Typed::Int,
				'double' => Typed::Float,
				'string' => Typed::String,
				'array' => Typed::Array,
				'object' => Typed::Object,
				'resource' => Typed::Resource,
				'resource (closed)' => Typed::Resource,
				'NULL' => Typed::Null,
				//'callable' => Typed::Callable,
				//'mixed' => Typed::Mixed,
				//'void' => Typed::Void,
				default => $default ?? throw new Exception('Unknown type: '. $type)
			};
		}
		
		/**
		 * Get a type by it's name. This accepts loosely typed names, such as 'int' or 'integer' for Int, or 'bool' or 'boolean' for Boolean
		 * @param string $named_type The name of the type to get
		 * @param mixed $default The default value to return if the type is unknown. If null, an exception will be thrown
		 * @return Typed The type
		 * 
		 * @throws Exception If the type is unknown and no default value is provided
		 * 
		 * @see Magnetar\Helpers\Enums\Typed
		 */
		public static function typeByName(string $named_type, mixed $default=null): Typed {
			return match($named_type) {
				// booleans
				'bool' => Typed::Boolean,
				'boolean' => Typed::Boolean,
				
				// integers
				'int' => Typed::Int,
				'integer' => Typed::Int,
				
				// floats
				'float' => Typed::Float,
				'double' => Typed::Float,
				
				// strings
				'string' => Typed::String,
				
				// arrays
				'array' => Typed::Array,
				
				// objects
				'object' => Typed::Object,
				
				// callables
				'callable' => Typed::Callable,
				
				// resources
				'resource' => Typed::Resource,
				
				// mixed
				'mixed' => Typed::Mixed,
				
				// null
				'null' => Typed::Null,
				
				// void
				'void' => Typed::Void,
				default => $default ?? throw new Exception('Unknown type: '. $named_type)
			};
		}
		
		/**
		 * Get an array of type names, not including loosely typed names such as 'int' or 'integer' and 'bool' or 'boolean'
		 * @return array
		 */
		public static function names(): array {
			return [
				'boolean',
				'integer',
				'float',
				'double',
				'string',
				'array',
				'object',
				'callable',
				'resource',
				'mixed',
				'null',
				'void',
			];
		}
		
		/**
		 * Get an array of all the type names, including loosely typed names such as 'int' or 'integer' and 'bool' or 'boolean'
		 * @return array
		 */
		public static function allNames(): array {
			return [
				'bool',
				'boolean',
				'int',
				'integer',
				'float',
				'double',
				'string',
				'array',
				'object',
				'callable',
				'resource',
				'mixed',
				'null',
				'void',
			];
		}
	}