<?php
	declare(strict_types=1);
	
	namespace Magnetar\Container;
	
	use Closure;
	use ReflectionParameter;
	use ReflectionNamedType;
	
	/**
	 * Helper functions for the container
	 */
	class Helper {
		/**
		 * If the given value is not an array and not null, wrap it in one.
		 *
		 * From Arr::wrap() in Illuminate\Support.
		 *
		 * @param mixed $value
		 * @return array
		 */
		public static function arrayWrap(mixed $value) {
			if(null === $value) {
				return [];
			}
			
			return is_array($value)?$value:[ $value ];
		}
		
		/**
		 * Return the default value of a given value
		 * @param mixed $value
		 * @param mixed ...$args
		 * @return mixed
		 */
		public static function unwrapIfClosure(mixed $value, mixed ...$args): mixed {
			return (($value instanceof Closure) ? $value(...$args) : $value);
		}
		
		/**
		 * Get the class name of the parameter's type
		 * @param ReflectionParameter $parameter
		 * @return string|null
		 */
		public static function getParameterClassName(ReflectionParameter $parameter): string|null {
			$type = $parameter->getType();
			
			if(!($type instanceof ReflectionNamedType) || $type->isBuiltIn()) {
				return null;
			}
			
			$name = $type->getName();
			
			if(null !== ($class = $parameter->getDeclaringClass())) {
				if('self' === $name) {
					return $class->getName();
				}
				
				if(('parent' === $name) && ($parent = $class->getParentClass())) {
					return $parent->getName();
				}
			}
			
			return $name;
		}
	}