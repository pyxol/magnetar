<?php
	declare(strict_types=1);
	
	namespace Magnetar\Container;
	
	/**
	 * Interface for the container
	 */
	interface ContainerInterface {
		/**
		 * Finds an entry of the container by its identifier and returns it.
		 * 
		 * @param string $id
		 */
		public function get(string $id);
		
		/**
		 * Determine if the container can return an entry for the given identifier.
		 * 
		 * @param string $id
		 * @return bool
		 */
		public function has(string $id): bool;
	}