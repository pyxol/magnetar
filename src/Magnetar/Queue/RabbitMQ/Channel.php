<?php
	declare(strict_types=1);
	
	namespace Magnetar\Queue\RabbitMQ;
	
	use Exception;
	
	use Magnetar\Queue\Channel as BaseChannel;
	use Magnetar\Queue\RabbitMQ\Message;
	
	use PhpAmqpLib\Channel\AMQPChannel;
	
	/**
	 * Channel class for RabbitMQ queues
	 */
	class Channel extends BaseChannel {
		protected AMQPChannel $qch;
		
		/**
		 * {@inheritDoc}
		 */
		public function publish(
			mixed $message,
			string $exchange=''
		): bool {
			if(!($message instanceof Message)) {
				$message = new Message($message);
			}
			
			try {
				// @throws AMQPChannelClosedException
				// @throws AMQPConnectionClosedException
				// @throws AMQPConnectionBlockedException
				$this->qch->basic_publish(
					$message->encode(),
					$exchange,
					$this->getChannelName()
				);
				
				return true;
			} catch(Exception $e) {
				//throw new RuntimeException('Failed to publish message to queue: '. $e->getMessage(), $e->getCode(), $e);
				
				// @todo logger
				
				return false;
			}
		}
		
		/**
		 * {@inheritDoc}
		 * 
		 * @todo process callback is a string/named class
		 * @todo needs testing
		 */
		public function consume(callable|array|string $callback): void {
			if(is_string($callback)) {
				$this->qch->basic_consume(
					$this->getChannelName(),
					'',
					false,
					true,
					false,
					false,
					$this->adapter->getApp()->make($callback)
				);
			} elseif(is_array($callback)) {
				$this->qch->basic_consume(
					$this->getChannelName(),
					'',
					false,
					true,
					false,
					false,
					$callback
				);
			} elseif(is_callable($callback)) {
				$this->qch->basic_consume(
					$this->getChannelName(),
					'',
					false,
					true,
					false,
					false,
					$callback
				);
			} else {
				throw new Exception('Invalid callback type');
			}
		}
	}