<?php
	declare(strict_types=1);
	
	namespace Magnetar\Auth;
	
	use Magnetar\Application;
	use Magnetar\Model\Model;
	use Magnetar\Auth\Exceptions\AuthorizationException;
	use Magnetar\Http\Request;
	use Magnetar\Utilities\Cryptography\Encryption;
	use Magnetar\Utilities\Str;
	
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
		 * Status of whether the request has been checked for authentication
		 * @var bool
		 */
		protected bool $requestChecked = false;
		
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
			$this->user_model = $this->app['config']['auth.model.class'] ?? null;
		}
		
		/**
		 * Attempt to authenticate a user. The $credentials array should specify the columns to validate against and their values
		 * @param Request|array|null $credentials The object to authenticate with. Can be a Request object or an assoc array
		 * @return bool
		 */
		public function attempt(
			Request|array|null $credentials=null
		): bool {
			if(null === $credentials) {
				$credentials = $this->app->request();
			}
			
			// @TODO
			if($credentials instanceof Request) {
				// use cookie to remember user
				$cookies = $credentials->cookies();
				
				if(isset($cookies[ $this->rememberCookieName() ])) {
					
				}
			} else if(is_array($credentials)) {
				
			}
			
			return false;
		}
		
		/**
		 * Act as a specific user
		 * @param Model $user The user to act as
		 * @return void
		 * 
		 * @throws \Magnetar\Auth\Exceptions\AuthorizationException
		 */
		public function actAs(Model $user): void {
			if(!($user instanceof $this->user_model)) {
				throw new AuthorizationException('Invalid user model');
			}
			
			$this->user = $user;
		}
		
		/**
		 * Check if a user is authenticated
		 * @return bool
		 */
		public function check(): bool {
			return (null !== $this->user);
		}
		
		/**
		 * Get the currently authenticated user
		 * @return User|null
		 */
		public function user(): User|null {
			return $this->user;
		}
		
		/**
		 * Get the ID (key) of the currently authenticated user. Returns 0 if no user is authenticated
		 * @return int|string
		 */
		public function id(): int|string {
			return $this->user?->getKey() ?? 0;
		}
		
		/**
		 * Log the user out
		 * @return void
		 */
		public function logout(): void {
			$this->invalidateSession();
			$this->invalidateRememberCookie();
		}
		
		/**
		 * Remember the user by looking up the 'remember me' cookie
		 * @return bool
		 * 
		 * @throws \Magnetar\Auth\Exceptions\AuthorizationException
		 */
		public function remember(): bool {
			if(null !== $this->user) {
				return true;
			}
			
			// get cookie
			if(null === ($raw_cookie = $this->getRememberCookie())) {
				return false;
			}
			
			// decode and decrypt cookie
			$cookie = (new Encryption(
				$this->app['config']['app.key'],
				null,//$this->app['config']['app.digest'],
				$this->app['config']['app.cipher']
			))->decrypt($raw_cookie);
			
			// validate cookie
			if(!isset($cookie['id']) || !isset($cookie['token'])) {
				$this->invalidateRememberCookie();
				
				return false;
			}
			
			// validate token
			if(!hash_equals($cookie['token'], hash_hmac('sha256', $cookie['id'], $this->app['config']['app.key']))) {
				$this->invalidateRememberCookie();
				
				return false;
			}
			
			// get user
			$this->user = $this->newUserModel()->findOrNull(
				$cookie
			);
			
			return (null !== $this->user);
		}
		
		/**
		 * Invalidate the current session
		 * @return void
		 */
		protected function invalidateSession(): void {
			if(null !== $this->user) {
				$this->user = null;
			}
		}
		
		/**
		 * Invalidate the existing 'remember me' cookie
		 * @return void
		 */
		protected function invalidateRememberCookie(): void {
			// @TODO check if works properly
			$this->app['cookie']->remove($this->rememberCookieName());
		}
		
		/**
		 * Get the value of the 'remember me' cookie
		 * @return array|null
		 */
		protected function getRememberCookie(): array|null {
			return $this->app['request']->cookie(
				$this->rememberCookieName(),
				null
			);
		}
		
		/**
		 * Get the name of the cookie used to remember the user
		 * @return string
		 */
		protected function rememberCookieName(): string {
			return Str::snake_case($this->app['config']['app.name'] ?? 'magnetar') .'_auth'. ((null !== $this->user_model)?'_'. substr(md5($this->user_model), 0, 10):'');
		}
		
		/**
		 * Get the user model
		 * @return \Magnetar\Model\Model
		 * 
		 * @throws \Magnetar\Auth\Exceptions\AuthorizationException
		 */
		protected function newUserModel(): Model {
			if(null === $this->user_model) {
				throw new AuthorizationException('Model class for authentication is not specified');
			}
			
			return new $this->user_model;
		}
	}