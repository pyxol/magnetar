<?php
	declare(strict_types=1);
	
	namespace Magnetar\Queue\Enums;
	
	enum AcknowledgementEnum: string {
		case ACK = 'ack';
		case NACK = 'nack';
		case REJECT = 'reject';
		case REQUEUE = 'requeue';
	}