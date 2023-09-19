<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Enums;
	
	/**
	 * An enum for defining static types
	 */
	enum TypedEnum {
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