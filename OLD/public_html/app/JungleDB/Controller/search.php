<?php
	namespace JungleDB;
	
	use \api as api;
	
	class Controller_search extends Abstract_Controlller {
		private $query			= "";
		private $results		= array();
		private $num_results	= 0;
		private $num_pages		= 0;
		private $page			= 1;
		private $per_page		= 18;
		private $time_taken		= 0;
		private $num_pages_max	= 10;
		private $entity_type	= false;
		private $min_search_len	= 3;
		
		public function get($query="") {
			$this->clean_query($query);
			
			$this->min_search_len = Config::get('Search.min_search_len', 3);
			
			if(!empty($_REQUEST['page']) && is_numeric($_REQUEST['page'])) {
				$this->page = (int)$_REQUEST['page'];
			}
			
			if(!empty($_REQUEST['type']) && is_numeric($_REQUEST['type'])) {
				$this->entity_type = (int)$_REQUEST['type'];
			}
			
			
			if(strlen($this->query) < $this->min_search_len) {
				return api::tpl()->set('error', "Please provide a query of at least ". $this->min_search_len ." character". (($this->min_search_len <> 1)?"s":"") ." long.")->view('error/404');
			}
			
			api::request()->setUrl( $this->search_url() );
			
			$this->search( $search['query'] );
			
			api::tpl()->view('search/index');
		}
		
		private function search($query) {
			if(!class_exists("SphinxClient")) {
				api::tpl()->set('error', "The required class 'SphinxClient' doesn't exist.")->view('error/404');
				
				die;
			}
			
			$cl = new SphinxClient();
			
			$cl->setServer(SPHINX_HOST, SPHINX_PORT);
			
			$cl->setConnectTimeout(2);		// no connection attempt longer than X second[s]
			$cl->setMaxQueryTime(1000);		// no query longer than X/1000 second[s]
			
			$cl->setFieldWeights(array(
				'title'		=> 2,
				'excerpt'	=> 1,
			));
			
			if(!empty($search['entity_type'])) {
				// limit search to specific entity type
				$entity_type = getEntityTypeById( $search['entity_type'] );
				
				if(!empty($entity_type)) {
					$entity_type_family_ids = getEntityTypeIdAndChildIds( $entity_type['id'] );
					
					$cl->setFilter("type_id", $entity_type_family_ids, false);
				}
			}
			
			$cl->setLimits((($search['page'] - 1) * $search['per_page']), $search['per_page']);
			
			$cl->setMatchMode(SPH_MATCH_EXTENDED2);
			$cl->setSortMode(SPH_SORT_EXPR, "@weight * entity_weight");
			$sph_result = $cl->Query($search['query'], "index_jungle");
			
			$search['num_results'] = $sph_result['total_found'];
			$search['num_result_start'] = ((($search['page'] - 1) * $search['per_page']) + 1);
			$search['num_result_end'] = min(($search['num_result_start'] + ($search['per_page'] - 1)), $search['num_results']);
			$search['time_taken'] = $sph_result['time'];
			
			if($search['num_results'] > 0) {
				$search['num_pages'] = ceil( @($search['num_results'] / $search['per_page']) );
			}
			
			$search['num_pages'] = min($search['num_pages'], $search['num_pages_max']);
			
			if(!empty($sph_result['matches'])) {
				$search['results'] = $db->get_results("SELECT * FROM `entity` WHERE `id` IN ('". implode("','", array_keys($sph_result['matches'])) ."') ORDER BY FIELD(`id`, '". implode("','", array_keys($sph_result['matches'])) ."') ASC");
			}
		}
		
		private function set_entity_type_counts($query) {
			// try to get counts for entity_type
			$cl2 = new SphinxClient();
			$cl2->setServer("127.0.0.1", 9312);
			$cl2->setMatchMode(SPH_MATCH_ALL);
			$cl2->SetGroupBy("type_id", SPH_GROUPBY_ATTR);
			$sph_result2 = $cl2->Query($query, "index_jungle");
			
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