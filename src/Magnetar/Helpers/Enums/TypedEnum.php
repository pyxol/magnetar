<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Enums;
	
	use Exception;
	
	/**
	 * An enum for defining static types
	 */
	enum Typed {
		case Null;
		case Boolean;
		case Int;
		case Float;
		case String;
		case Array;
		case Object;
		case Callable;
		case Resource;
		case Mixed;
		case Void;
	}