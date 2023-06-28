<?php
	namespace Magnetar;
	
	// Database Engine
	/////////////////////
	
	/*
		Notes:
		
		Prepended underscore on variables/functions denote private resources
		Any '$num' variable should always default to 0
	*/
	
	class Database {
		private $_log = array();			// array for storing a log of class events
		private $_log_enabled = false;		// Toggle log functionality
		private $_server = array();			// array for storing server connection details
		private $_link = false;				// link_identifier [defined by mysql_connect, used as tailing argument for most raw mysql_() functions
		private $_num = 0;					// doppleganger for $this->num_queries;
		private $_result = false;			// resource link for the most recently used mysql_query()
		private $_record_queries = false;	// record queries into private instance variable
		private $_queries = array();		// storing all query related stuff inside
		private $_show_errors = false;		// toggle for showing errors
		private $_error_msg = false;		// variable for storing the string returned by mysql_error
		private $_error_code = false;		// variable for storing the string returned by mysql_errno
		private $_timer = 0;				// variable for storing when the timer started
		
		
		public $current_db = false;			// name of the currently selected database [via $this->_server['name']
		public $num_queries = 0;			// counter increment for storing previous queries
		public $last_query = false;			// the most recently processed query (string, not 
		public $num_cols = 0;				// number of rows found using mysql_num_rows() on previous query
		public $num_rows = 0;				// number of rows found using mysql_num_rows() on previous query
		public $num_affected_rows = 0;		// the number of affected rows from the most recently processed query
		public $insert_id = false;			// the inserted ID column value (if available) from the most recently processed query
		public $cols = array();				// storage for columns of requested query
		public $rows = array();				// storage for rows of requested query
		
		
		// class initializer/finalizer
		
		public function __construct($db_host = false, $db_user = false, $db_pass = false, $db_name = false, $persistent=false, $db_port=false) {
			$this->_server['db_host']		= ((false !== $db_host)?	$db_host	:Config::get('Database.host'));
			$this->_server['db_user']		= ((false !== $db_user)?	$db_user	:Config::get('Database.user'));
			$this->_server['db_pass']		= ((false !== $db_pass)?	$db_pass	:Config::get('Database.pass'));
			$this->_server['db_name']		= ((false !== $db_name)?	$db_name	:Config::get('Database.name'));
			$this->_server['persistent']	= ((false !== $persistent)?	$persistent	:Config::get('Database.persistent'));
			$this->_server['db_port']		= ((false !== $db_port)?	$db_port	:Config::get('Database.port'));
		}
		
		public function __destruct() {
			if($this->server_connected()) {
				$this->_disconnect();
			}
		}
		
		
		// dev utility
		
		public function output_queries() {
			print_pre($this->_queries);
		}
		
		
		
		// query sql function helper
		
		public function sql_function($function = false) {
			if($function === false || empty($function)) {
				return "";
			}
			
			$function = strtolower(trim($function));
			
			switch($function) {
				case 'localtime':
				case 'localtimestamp':
				case 'current_timestamp':
				case 'sysdate':
				case 'now':
					return date("Y-m-d H:i:s");
				break;
				
				case 'current_time':
				case 'time':
				case 'curtime':
					return date("H:i:s");
				break;
				
				case 'current_date':
				case 'curdate':
				case 'date':
					return date("Y-m-d");
				break;
				
				case 'unix_timestamp':
					return time();
				break;
			}
			
			return "";
		}
		
		
		// database management
		
		public function select_db($db_name=false) {
			if(empty($db_name)) {
				$this->_log("Could not select a database because no database was given.");	
				
				return false;
			}
			
			$this->current_db = $db_name;
			
			//if(!$this->server_connected()) {
			//	$this->_log("Could not select database '". $db_name ."' because the server is not connected.");
			//	
			//	return false;
			//}
			
			if(!$this->server_connected()) {
				$this->_connect();
			}
			
			if(mysql_select_db($db_name, $this->_link)) {
				$this->_log("Switched to database '". $db_name ."'");
				
				return true;
			} else {
				$this->_record_error();
				
				return false;
			}
		}
		
		public function set_charset($charset=false) {
			if(empty($charset) || !is_string($charset)) {
				return false;
			}
			
			$this->_server['charset_pending'] = strtolower(trim($charset));
			
			return true;
		}
		
		
		// public query functions
		
		public function query($sql=false) {
			return $this->_query($sql);
		}
		
		public function get_results($sql=false, $column_to_key=false) {
			$return = false;
			
			$this->_query($sql);
			
			if(($column_to_key !== false) && (!empty($this->rows))) {
				$tmp = current($this->rows);
				
				if(isset($tmp[$column_to_key])) {
					$tmp = array();
					
					foreach(array_keys($this->rows) as $row_key) {
						$tmp[ $this->rows[ $row_key ][ $column_to_key ] ] = $this->rows[ $row_key ];
					}
					
					return $tmp;
				} else {
					$this->_log("get_result() was unable to find the column '". $column_to_key ."' to assign as column_to_key.");
				}
			}
			
			return $this->rows;
		}
		
		public function get_row($sql=false, $row_offset=0) {
			if(!empty($sql)) {
				$this->_query($sql);
			}
			
			if(!empty($this->rows)) {
				if(array_key_exists($row_offset, $this->rows)) {
					return $this->rows[ $row_offset ];
				}
			}
			
			return false;
		}
		
		public function get_var($sql=false, $col_offset=0, $row_offset=0) {
			if(!empty($sql)) {
				$this->_query($sql);
			}
			
			if(empty($this->rows) || empty($this->cols)) {
				return false;
			}
			
			if(!isset($this->cols[ $col_offset ])) {
				$this->_log("The column with offset of '". $col_offset ."' could not be found.");
				
				return false;
			}
			
			if(!isset($this->rows[ $row_offset ])) {
				$this->_log("The row with offset of '". $row_offset ."' could not be found.");
				
				return false;
			}
			
			$row = $this->rows[ $row_offset ];
			$col = $this->cols[ $col_offset ];
			
			return $row[ $col ];
		}
		
		public function get_col($sql=false, $col_offset=0) {
			if(!empty($sql)) {
				$this->_query($sql);
			}
			
			if(empty($this->rows) || empty($this->cols)) {
				return false;
			}
			
			if(!isset($this->cols[ $col_offset ])) {
				$this->_log("The column with offset of '". $col_offset ."' could not be found.");
				
				return false;
			}
			
			$_rows = array();
			$col = $this->cols[ $col_offset ];
			
			foreach($this->rows as $row) {
				$_rows[] = $row[$col];
			}
			
			return $_rows;
		}
		
		public function insert($table=false, $columns=false) {
			if(empty($table) || empty($columns)) {
				$this->_log("Your requested update query was missing important information.");
				
				return false;
			}
			
			$sql  = "INSERT INTO `". $this->escape($table) ."` SET ";
			$sql .= $this->_combine_together($columns, ", ");
			
			return $this->_query($sql);
		}
		
		public function update($table=false, $columns=false, $where=false) {
			if(empty($table) || empty($columns) || empty($where)) {
				$this->_log("Your requested update query was missing important information.");
				
				return false;
			}
			
			$sql  = "UPDATE `". $this->escape($table) ."` SET ";
			$sql .= $this->_combine_together($columns, ", ");
			$sql .= " WHERE ";
			$sql .= $this->_combine_together($where, " AND ");
			
			return $this->_query($sql);
		}
		
		public function fetch() {
			return;   // todo
		}
		
		
		// utility functions
		
		public function escape($string) {
			if($this->server_connected()) {
				return mysql_real_escape_string($string, $this->_link);
			} else {
				return addslashes($string);
			}
		}
		
		private function _combine_together($var, $divider=" AND ") {
			if(is_array($var)) {
				$vars = array();
				
				foreach($var as $v_col => $v_val) {
					$column_line = " `". $this->escape($v_col) ."` = ";
					
					if($v_val === null) {
						$column_line .= "NULL ";
					} else {
						$column_line .= "'". $this->escape($v_val) ."' ";
					}
					
					$vars[] = $column_line;
					
					$column_line = null;
					unset($column_line);
					
					//$vars[] = " `". $this->escape($v_col) ."` = '". $this->escape($v_val) ."' ";
				}
				
				if(!empty($vars)) {
					return implode($divider, $vars);
				}
			} else {
				return $var;
			}
		}
		
		
		// incrementation
		
		private function _new_query() {
			// increment
			$this->_num++;
			$this->num_queries = $this->_num;
			
			// flush instance variables
			$this->last_query = false;
			$this->num_cols = 0;
			$this->num_rows = 0;
			$this->num_affected_rows = 0;
			$this->insert_id = false;
			$this->cols = array();
			$this->rows = array();
			
			$this->_result = false;
			$this->_error_msg = false;
			$this->_error_code = false;
			$this->_timer = 0;
		}
		
		
		// logging
		
		private function _log($log_msg=false) {
			if($this->_log_enabled !== true) {
				return;
			}
			
			if(empty($log_msg)) {
				return;
			}
			
			if(!is_array($log_msg)) {
				$log_msg = array($log_msg);
			}
			
			foreach($log_msg as $log_msg_item) {
				$this->_log[] = array(
					 'time'			=> time()
					,'microtime'	=> microtime(true)
					,'message'		=> $log_msg_item
				);
			}
		}
		
		public function show_log($limit_last=false) {
			if(empty($this->_log)) {
				return;
			}
			
			// limit=show # last log entries
			$i = 1;
			$num_entries = count($this->_log);
			$limit = ((!empty($limit_last))?( $num_entries - ($limit_last - 1) ):false);
			
			$line_separator = "<br /><br />". PHP_EOL;
			
			if(php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])) {
				$line_separator = PHP_EOL . PHP_EOL;
			}
			
			print $line_separator ."[db.debug] Showing log...". $line_separator;
			
			print str_repeat("=", 30) . $line_separator;
			
			foreach($this->_log as $log_entry) {
				if($limit !== false) {
					if($i < $limit) {
						continue;
					}
				}
				
				print "Log [#". $i .", ". $log_entry['microtime'] ."]: ". $log_entry['message'] . $line_separator;
				
				$i++;
			}
			
			print str_repeat("=", 30) . $line_separator;
		}
		
		public function flush_log() {
			$this->_log = null;
			
			$this->_log = array();
		}
		
		public function start_log() {
			$this->_log_enabled = true;
		}
		
		public function stop_log() {
			$this->flush_log();
			
			$this->_log_enabled = false;
		}
		
		
		// error management
		
		public function show_errors() {
			$this->_show_errors = true;
		}
		
		public function hide_errors() {
			$this->_show_errors = false;
		}
		
		private function _record_error() {
			$error_msg = $this->_error_msg();
			$error_code = $this->_error_code();
			
			if(!empty($error_msg)) {
				$log_msg = "MySQL Error: [#". $error_code ."] ". $error_msg;
				
				$this->_log($log_msg);
				
				if($this->_show_errors === true) {
					print $log_msg ."<br /><br />\n";
				}
			}
		}
		
		public function get_last_error() {
			return $this->_error_msg();
		}
		
		public function get_last_errno() {
			return $this->_error_code();
		}
		
		
		// query recorder
		
		public function flush_record() {
			$this->_record_queries = null;
			
			$this->_record_queries = array();
		}
		
		public function start_record() {
			$this->_record_queries = true;
		}
		
		public function stop_record() {
			$this->flush_record();
			
			$this->_record_queries = false;
		}
		
		
		// database information
		
		public function server_connected() {
			return ($this->_link !== false);
		}
		
		public function server_version() {
			if(!$this->server_connected()) {
				$this->_connect();
			}
			
			if(empty($this->_server['version'])) {
				//$this->_server['version'] = preg_replace("#[^0-9.].*#si", "", mysql_get_server_info($this->_link)); // remove -alpha
				$this->_server['version'] = mysql_get_server_info($this->_link);
			}
			
			return $this->_server['version'];
		}
		
		public function session_charset() {
			if(!$this->server_connected()) {
				return false;
			}
			
			return mysql_client_encoding($this->_link);
		}
		
		public function server_info() {
			if(!$this->server_connected()) {
				$this->_connect();
			}
			
			if(empty($this->_server['stat'])) {
				$this->_server['stat'] = mysql_stat($this->_link);
			}
			
			return $this->_server['stat'];
		}
		
		
		// raw functionality
		
		private function _connect() {
			if(empty($this->_server['db_host'])) {
				$this->_log("Could not connect to host '". $this->_server['db_host'] ."' with user '". $this->_server['db_user'] ."' (with password: ". ((!empty($this->_server['db_pass']))?"YES":"NO") .")");
				
				return false;
			}
			
			// defaults
			if(!empty($this->_server['db_port'])) {
				$this->_server['db_host'] = $this->_server['db_host'] .":". $this->_server['db_port'];
			}
			
			$this->_log("Connecting to database server...");
			
			if($this->_server['persistent'] === true) {
				$this->_link = mysql_pconnect($this->_server['db_host'], $this->_server['db_user'], $this->_server['db_pass']) or $this->_record_error();
			} else {
				$this->_link = mysql_connect($this->_server['db_host'], $this->_server['db_user'], $this->_server['db_pass']) or $this->_record_error();
			}
			
			if($this->_link) {
				$this->select_db($this->_server['db_name']);
			}
			
			// get charset
			$this->_server['charset'] = $this->session_charset();
		}
		
		private function _disconnect() {
			if($this->server_connected()) {
				$this->_log("Disconnecting from database server...");
				
				mysql_close($this->_link) or $this->_record_error();
				
				$this->_link = false;
			}
		}
		
		private function _query($sql) {
			if(empty($sql)) {
				return false;
			}
			
			if(!$this->server_connected()) {
				$this->_connect();
			}
			
			$this->_check_charset();
			
			$this->_new_query();
			
			// trim the given query
			$sql = trim($sql);
			
			$this->_log("Sending query: ". $sql);
			
			// process query
			$this->_timer_start();
			$this->_result = mysql_query($sql, $this->_link);
			$query_time = $this->_timer_stop();
			
			$this->last_query = $sql;
			
			if(false === $this->_result) {
				$this->_record_error();
				
				return false;
			}
			
			
			$return = true;
			
			if(preg_match("#^\s*(insert|delete|update|replace)#si", $this->last_query)) {
				$this->num_affected_rows = mysql_affected_rows($this->_link);
				
				$return = $this->num_affected_rows;
			}
			
			if(preg_match("#^\s*(insert|replace)#si", $this->last_query)) {
				$this->insert_id = mysql_insert_id($this->_link);
				
				$return = $this->insert_id;
			}
			
			if(preg_match("#^\s*(select|show|explain|describe)#si", $this->last_query)) {
				$num_cols = 0;
				
				while($num_cols < mysql_num_fields($this->_result)) {
					$col = (array)mysql_fetch_field($this->_result);
					
					$this->cols[ $num_cols++ ] = $col['name'];
				}
				
				$num_rows = 0;
				
				while($row = mysql_fetch_assoc($this->_result)) {
					$this->rows[ $num_rows++ ] = $row;
				}
				
				$this->num_cols = $num_cols;
				$this->num_rows = $num_rows;
				
				mysql_free_result($this->_result);
			}
			
			// record query information
			if($this->_record_queries === true) {
				$this->_queries[ $this->_num ] = array(
					 'query'				=> $this->last_query
					,'time'					=> $query_time
					,'num_cols'				=> $this->num_cols
					,'num_rows'				=> $this->num_rows
					,'num_affected_rows'	=> $this->num_affected_rows
					,'insert_id'			=> $this->insert_id
					,'cols'					=> $this->cols
					,'rows'					=> $this->rows
				);
			}
			
			if(!empty($return)) {
				return $return;
			}
		}
		
		private function _error_msg() {
			return mysql_error($this->_link);
		}
		
		private function _error_code() {
			return mysql_errno($this->_link);
		}
		
		private function _num_rows() {
			return;
		}
		
		private function _insert_id() {
			return;
		}
		
		private function _check_charset() {
			if(empty($this->_server['charset_pending'])) {
				return false;
			}
			
			if(!$this->server_connected()) {
				$this->_log("Unable to check charset because MySQL is not connected.");
				return false;
			}
			
			if(strcmp($this->_server['charset'], $this->_server['charset_pending']) !== 0) {
				$charset_from = $this->_server['charset'];
				
				$set_charset_return = mysql_set_charset($this->_server['charset_pending'], $this->_link);
				
				$this->_server['charset'] = mysql_client_encoding($this->_link);
				
				$this->_log(array("Switched MySQL charset from '". $charset_from ."' to '". $this->_server['charset_pending'] ."'", "MySQL now returns charset '". $this->_server['charset'] ."'"));
			}
			
			$this->_server['charset_pending'] = false;
			
			return true;
		}
		
		
		// timer for measuring query length
		
		private function _timer_start() {
			$mtime = explode(" ", microtime());
			
			$this->_timer = ($mtime[1] + $mtime[0]);
		}
		
		private function _timer_stop() {
			$mtime = explode(" ", microtime());
			
			return (($mtime[1] + $mtime[0]) - $this->_timer);
		}
		
		
		// debug stuff
		public function getNumQueries() {
			return $this->num_queries;
		}
	}