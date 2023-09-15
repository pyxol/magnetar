<?php
	declare(strict_types=1);
	
	namespace Magnetar\Router\Enums;
	
	enum HTTPMethod {
		case GET;
		case POST;
		case PUT;
		case PATCH;
		case DELETE;
		case OPTIONS;
		case HEAD;
		case TRACE;
		case CONNECT;
		
		/**
		 * Convert this HTTPMethod enum to a string
		 * @return string
		 */
		public function __toString(): string {
			return match($this) {
				self::GET => 'GET',
				self::POST => 'POST',
				self::PUT => 'PUT',
				self::PATCH => 'PATCH',
				self::DELETE => 'DELETE',
				self::OPTIONS => 'OPTIONS',
				self::HEAD => 'HEAD',
				self::TRACE => 'TRACE',
				self::CONNECT => 'CONNECT',
			};
		}
	}