<?php
	declare(strict_types=1);
	
	namespace Magnetar\Router\Enums;
	
	enum HTTPMethodEnum {
		case GET;
		case POST;
		case PUT;
		case PATCH;
		case DELETE;
		case OPTIONS;
		case HEAD;
		//case TRACE;
		case CONNECT;
	}