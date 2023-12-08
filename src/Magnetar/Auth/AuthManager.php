<?php
	declare(strict_types=1);
	
	namespace Magnetar\Auth;
	
	use Magnetar\Application;
	use Magnetar\Model\Model;
	use Magnetar\Auth\Exceptions\AuthorizationException;
	
	/**
	 * Authentication manager
	 */
	class AuthManager {
		/**
		 * The currently authenticated user
		 * @var ?\Magnetar\Model\Model
		 */
		protected ?Model $user=null;
		
		/**
		 * The full class name for model to use for authentication. Must extend Magnetar\Model\Model
		 * @var string
		 */
		protected ?string $user_model=null;
		
		/**
		 * Constructor
		 */
		public function __construct(
			/**
			 * The application instance
			 * @var Application
			 */
			protected Application $app
		) {
			$this->setDefaultUserModel();
		}
		
		/**
		 * Set the default user model
		 * @return void
		 */
		protected function setDefaultUserModel(): void {
			$this->user_model = $this->app->config('auth.model.class') ?? null;
		}
		
		/**
		 * Get the user model
		 * @return \Magnetar\Model\Model
		 * 
		 * @throws \Magnetar\Auth\Exceptions\AuthorizationException
		 */
		protected function getNewModel(): Model {
			if(null === $this->user_model) {
				throw new AuthorizationException('Model class for authentication is not specified');
			}
			
			return new $this->user_model;
		}
		
		/**
		 * Attempt to authenticate a user. The $credentials array should specify the columns to validate against and their values
		 * @param mixed $credentials The object to authenticate with. Can be a Request object or an assoc array
		 * @param bool $remember Whether to remember the user. If true, a cookie will be set
		 * @return bool
		 */
		public function attempt(mixed $credentials, bool $remember=false): bool {
			// @TODO
			if($credentials instanceof Request) {
				// @TODO
			} else if(is_array($credentials)) {
				// @TODO
			}
			
			return false;
		}
		
		/**
		 * Check if a user is authenticated
		 * @return bool
		 */
		public function check(): bool {
			// @TODO
			return false;
		}
		
		/**
		 * Get the currently authenticated user
		 * @return User
		 */
		public function user(): User {
			// @TODO
			
			return new User();
		}
		
		
		public function id(): int {
			// @TODO
			
			return 0;
		}
		
		/**
		 * Log the user out
		 * @return void
		 */
		public function logout(): void {
			// @TODO
		}
		
		
	}