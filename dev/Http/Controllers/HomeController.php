<?php
	declare(strict_types=1);
	
	use Magnetar\Http\Controller\Controller;
	use Magnetar\Helpers\Facades\Config;
	use Magnetar\Helpers\Facades\DB;
	use Magnetar\Helpers\Facades\Response;
	use Magnetar\Helpers\Facades\Cache;
	use Magnetar\Helpers\Facades\Log;
	
	class HomeController extends Controller {
		public function index(): void {
			Response::send(
				tpl('frontpage')
			);
		}
		
		public function db(): void {
			// list tables
			if(Config::get('database.default') === 'sqlite') {
				$tables = DB::get_rows("
					SELECT
						name
					FROM
						sqlite_schema
					WHERE
						type = 'table' AND
						name NOT LIKE 'sqlite_%'
				");
			} else {
				$tables = DB::get_rows("
					SHOW TABLES
				");
			}
			
			Response::send(
				tpl('database/tables', [
					'tables' => $tables,
				])
			);
		}
		
		public function set_cache(): void {
			$cached_val = date('r');
			$cache_set = Cache::set('cached_val', $cached_val, 15);
			
			Response::send(
				tpl('cache/set', [
					'cached_val' => $cached_val,
					'cache_set' => $cache_set,
					'log' => Log::dump(0, true),
				])
			);
		}
		
		public function get_cache(): void {
			Response::send(
				tpl('cache/get', [
					'cached_val' => Cache::get('cached_val') ?? 'NOT SET',
					'log' => Log::dump(0, true),
				])
			);
		}
	}