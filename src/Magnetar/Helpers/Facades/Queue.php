<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method connection(?string $connection_name=null): Magnetar\Queue\QueueAdapter
	 * @method getDefaultConnectionName(): ?string
	 * @method getConnected(): array
	 * @method adapter(string $connection_name): Magnetar\Queue\QueueAdapter
	 * @method getApp(): Magnetar\Application
	 * @method getAdapterName(): string
	 * @method getConnectionName(): string
	 * @method getConnectionConfig(): array
	 * @method getConnection(): Magnetar\Queue\Connection
	 * @method makeMessage(mixed $body): Magnetar\Queue\Message
	 * @method channel(string $channelName): Magnetar\Queue\Channel
	 * @method publish(string $channel, mixed $message, string $exchange=''): bool
	 * 
	 * @see \Magnetar\Queue\QueueManager
	 * @see \Magnetar\Queue\QueueAdapter
	 */
	class Queue extends Facade {
		/**
		 * Get the named key that this facade represents
		 * @return string
		 */
		protected static function getFacadeKey(): string {
			return 'queue';
		}
	}