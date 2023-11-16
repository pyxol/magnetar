<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method static \Magnetar\Queue\QueueAdapter connection(?string $connection_name)
	 * @method static ?string getDefaultConnectionName()
	 * @method static array getConnected()
	 * @method static \Magnetar\Queue\QueueAdapter adapter(string $connection_name)
	 * @method static \Magnetar\Application getApp()
	 * @method static string getAdapterName()
	 * @method static string getConnectionName()
	 * @method static array getConnectionConfig()
	 * @method static \Magnetar\Queue\Connection getConnection()
	 * @method static \Magnetar\Queue\Message makeMessage(mixed $body)
	 * @method static \Magnetar\Queue\Channel channel(string $channelName)
	 * @method static bool publish(string $channel, mixed $message, string $exchange)
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