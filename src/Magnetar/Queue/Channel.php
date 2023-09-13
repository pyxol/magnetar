<?php
	declare(strict_types=1);
	
	namespace Magnetar\Queue;
	
	/**
	 * A class that represents a queue channel
	 * 
	 * @todo heavy cleanup
	 */
	class Channel {
		public function __construct(
			protected string $channelName,
			protected QueueAdapter $adapter
		) {
			
		}
		
		public function getChannelName(): string {
			return $this->channelName;
		}
		
		public function getAdapter(): QueueAdapter {
			return $this->adapter;
		}
		
		public function publish(
			mixed $message,
			string $routingKey = '',
			array $options = []
		): void {
			$this->adapter->publish($message, $routingKey, $options);
		}
		
		public function consume(
			string $consumerTag,
			callable $callback,
			array $options = []
		): void {
			$this->adapter->consume($consumerTag, $callback, $options);
		}
		
		public function ack(
			Message $message,
			bool $multiple = false
		): void {
			$this->adapter->ack($message, $multiple);
		}
		
		public function nack(
			Message $message,
			bool $multiple = false,
			bool $requeue = false
		): void {
			$this->adapter->nack($message, $multiple, $requeue);
		}
		
		public function reject(
			Message $message,
			bool $requeue = false
		): void {
			$this->adapter->reject($message, $requeue);
		}
		
		public function requeue(
			Message $message
		): void {
			$this->adapter->requeue($message);
		}
	}