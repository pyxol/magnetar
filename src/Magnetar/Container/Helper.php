<?php
	declare(strict_types=1);
	
	namespace Magnetar\Container;
	
	use Closure;
	use ReflectionParameter;
	use ReflectionNamedType;
	
	class Helper {
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
		public static function getParameterClassName($parameter): string|null {
			$type = $parameter->getType();
			
			if(!($type instanceof ReflectionNamedType) || $type->isBuiltIn()) {
				return null;
			}
			
			$name = $type->getName();
			
			if(!is_null($class = $parameter->getDeclaringClass())) {
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