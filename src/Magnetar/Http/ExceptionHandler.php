<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http;
	
	use Throwable;
	
	use Magnetar\Application;
	use Magnetar\Http\Request;
	use Magnetar\Http\Response;
	use Magnetar\Router\Exceptions\RouteUnassignedException;
	
	class ExceptionHandler {
		protected Throwable $caughtException;
		
		public function __construct(
			/**
			 * The application instance
			 * @var Application
			 */
			protected Application $app
		) {
			
		}
		
		/**
		 * Record the exception
		 * @param Throwable $e
		 * @return void
		 */
		public function record(Throwable $e): void {
			$this->caughtException = $e;
		}
		
		/**
		 * Render the exception
		 * @param Request $request
		 * @param Throwable $e
		 * @return Response
		 */
		public function render(Request $request, Throwable $e): Response {
			$this->record($e);
			
			$theme_file = 'errors/503';
			$response_status = 503;
			
			if($e instanceof RouteUnassignedException) {
				$theme_file = 'errors/404';
				$response_status = 404;
			}
			
			return (new Response())->responseCode($response_status)->body(
				$this->app->make('theme')->tpl($theme_file, [
					'request' => $request,
					'message' => $e->getMessage(),
					'file' => $e->getFile(),
					'line' => $e->getLine(),
					'trace' => $e->getTraceAsString()
				])
			);
		}
	}