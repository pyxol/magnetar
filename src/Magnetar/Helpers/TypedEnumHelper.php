<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers;
	
	use Exception;
	
	use Magnetar\Helpers\Enums\TypedEnum;
	
	/**
	 * Helper static class to assist with typed values
	 */
	class TypedEnumHelper {
		/**
		 * Get the type of a value
		 * @param mixed $value The value to get the type of
		 * @param TypedEnum|null $default The default value to return if the type is unknown. If null, an exception will be thrown
		 * @return TypedEnum The type of the value
		 * 
		 * @throws Exception If the type is unknown and no default value is provided
		 */
		public static function getType(mixed $value, TypedEnum|null $default=null): TypedEnum {
			// get the type of the value
			$type = gettype($value);
			
			// return the type
			return match($type) {
				'boolean' => TypedEnum::Boolean,
				'integer' => TypedEnum::Int,
				'double' => TypedEnum::Float,
				'string' => TypedEnum::String,
				'array' => TypedEnum::Array,
				'object' => TypedEnum::Object,
				'resource' => TypedEnum::Resource,
				'resource (closed)' => TypedEnum::Resource,
				'NULL' => TypedEnum::Null,
				//'callable' => TypedEnum::Callable,
				//'mixed' => TypedEnum::Mixed,
				//'void' => TypedEnum::Void,
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
		public static function typeByName(string $named_type, mixed $default=null): TypedEnum {
			return match($named_type) {
				// booleans
				'bool' => TypedEnum::Boolean,
				'boolean' => TypedEnum::Boolean,
				
				// integers
				'int' => TypedEnum::Int,
				'integer' => TypedEnum::Int,
				
				// floats
				'float' => TypedEnum::Float,
				'double' => TypedEnum::Float,
				
				// strings
				'string' => TypedEnum::String,
				
				// arrays
				'array' => TypedEnum::Array,
				
				// objects
				'object' => TypedEnum::Object,
				
				// callables
				'callable' => TypedEnum::Callable,
				
				// resources
				'resource' => TypedEnum::Resource,
				
				// mixed
				'mixed' => TypedEnum::Mixed,
				
				// null
				'null' => TypedEnum::Null,
				
				// void
				'void' => TypedEnum::Void,
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