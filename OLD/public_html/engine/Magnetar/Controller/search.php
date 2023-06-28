<?php
	namespace Magnetar;
	
	use \api as api;
	
	class Controller_search extends Abstract_Controller {
		private $query			= false;
		private $results		= array();
		private $num_results	= 0;
		private $num_pages		= 0;
		private $page			= 1;
		private $per_page		= 18;
		private $num_result_start	= 0;
		private $num_result_end	= 0;
		private $time_taken		= 0;
		private $num_pages_max	= 10;
		private $entity_type 	= false;
		private $min_search_len	= 3;
		
		public function get($query=false) {
			$this->query = $query;
			
			if((false === $this->query) && isset(api::request()->query)) {
				$this->query = api::request()->query;
			}
			
			if(false === $this->query) {
				//return api::request()->redirect("/");
				return api::tpl()->set('error', "No search query provided...")->view('error/404');
			}
			
			$this->query = $this->clean_query($this->query);
			
			$this->min_search_len = Config::get('Search.min_search_len', $this->min_search_len);
			
			if(!empty($_REQUEST['page']) && is_numeric($_REQUEST['page'])) {
				$this->page = (int)$_REQUEST['page'];
			}
			
			if(!empty($_REQUEST['type']) && is_numeric($_REQUEST['type'])) {
				$this->entity_type = api::entity_type( $_REQUEST['type'] );
			}
			
			
			if(strlen($this->query) < $this->min_search_len) {
				return api::tpl()->set('error', "Please provide a query of at least ". $this->min_search_len ." character". (($this->min_search_len <> 1)?"s":"") ." long.")->view('error/404');
			}
			
			api::request()->setUrl( $this->search_url() );
			
			$this->search( $this->query );
			
			api::tpl()->set(array(
				'query'				=> $this->query,
				'results'			=> $this->results,
				'num_results'		=> $this->num_results,
				'num_pages'			=> $this->num_pages,
				'page'				=> $this->page,
				'per_page'			=> $this->per_page,
				'num_result_start'	=> $this->num_result_start,
				'num_result_end'	=> $this->num_result_end,
				'time_taken'		=> $this->time_taken,
				'num_pages_max'		=> $this->num_pages_max,
				'entity_type'		=> $this->entity_type,
				'min_search_len'	=> $this->min_search_len,
			))->view('search/index');
		}
		
		private function search() {
			$search = $this;
			
			$sph_result = api::cache()->get(md5("new.jungledb.dev:search:". md5($this->query) .":". time()), function() use($search) {
				if(!class_exists("SphinxClient")) {
					api::tpl()->set('error', "The required class 'SphinxClient' doesn't exist.")->view('error/404');
					
					die;
				}
				
				$config = Config::get('SphinxClient');
				
				if(empty($config['host']) || empty($config['port'])) {
					api::tpl()->set('error', "SphinxClient configuration values are setup incorrectly (host and port keys are required).")->view("error/404");
					
					die;
				}
				
				$cl = new \SphinxClient();
				
				$cl->setServer($config['host'], $config['port']);
				
				$cl->setConnectTimeout(2);		// no connection attempt longer than X second[s]
				$cl->setMaxQueryTime(1000);		// no query longer than X/1000 second[s]
				
				$cl->setFieldWeights(array(
					'title'		=> 2,
					'excerpt'	=> 1,
				));
				
				if($search->entity_type) {
					// limit search to specific entity type
					//$entity_type_family_ids = getEntityTypeIdAndChildIds( $entity_type['id'] );
					
					//$cl->setFilter("type_id", $entity_type_family_ids, false);
				}
				
				$cl->setLimits((($search->page - 1) * $search->per_page), $search->per_page);
				
				$cl->setMatchMode(SPH_MATCH_EXTENDED2);
				$cl->setSortMode(SPH_SORT_EXPR, "@weight * entity_weight");
				
				return $cl->Query($search->query, "index_jungle");
			});
			
			if(empty($sph_result)) {
				return;
			}
			
			$this->num_results		= $sph_result['total_found'];
			$this->num_result_start	= ((($this->page - 1) * $this->per_page) + 1);
			$this->num_result_end	= min(($this->num_result_start + ($this->per_page - 1)), $this->num_results);
			$this->time_taken		= $sph_result['time'];
			
			if($this->num_results > 0) {
				$this->num_pages = ceil( @($this->num_results / $this->per_page) );
			}
			
			$this->num_pages = min($this->num_pages, $this->num_pages_max);
			
			if(!empty($sph_result['matches'])) {
				//$this->results = api::db()->get_results("SELECT * FROM `entity` WHERE `id` IN ('". implode("','", array_keys($sph_result['matches'])) ."') ORDER BY FIELD(`id`, '". implode("','", array_keys($sph_result['matches'])) ."') ASC");
				
				foreach(array_keys($sph_result['matches']) as $entity_id) {
					$this->results[] = api::entity( $entity_id );
				}
			}
		}
		
		private function set_entity_type_counts() {
			// try to get counts for entity_type
			$cl2 = new SphinxClient();
			$cl2->setServer("127.0.0.1", 9312);
			$cl2->setMatchMode(SPH_MATCH_ALL);
			$cl2->SetGroupBy("type_id", SPH_GROUPBY_ATTR);
			$sph_result2 = $cl2->Query($this->query, "index_jungle");
			
			$results_by_entity_type = array();
			
			if(!empty($sph_result2['matches'])) {
				foreach($sph_result2['matches'] as $etc_match) {
					$results_by_entity_type[ $etc_match['attrs']['type_id'] ] = $etc_match['attrs']['@count'];
				}
			}
			
			api::tpl()->results_by_entity_type = $results_by_entity_type;
		}
		
		private function clean_query($query) {
			$query = strtolower($query);
			$query = preg_replace("#[^A-Za-z0-9\- ]+#si", "", $query);
			
			return $query;
		}
		
		private function search_url() {
			return "/search.php?query=". urlencode($this->query) . (($this->page > 1)?"&page=". $this->page:"") . (!empty($this->entity_type)?"&type=". $this->entity_type:"");
		}
	}