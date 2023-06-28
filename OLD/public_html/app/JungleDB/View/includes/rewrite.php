<?php
	// Rewrite settings
	function jungle_rewriteRules($rewrite) {
		// sorted by what's caught first, ends if url matches regex
		// starts from beginning of url, no starting slash
		// value for regex array key can be:
		//		- a string (template name)
		//		- array(template="template name", matches=array(match1[, match2]))
		//		- array(ajax=true, matches=array(match1))
		//		- array(function=raw_function_name)
		
		$new_rules = array(
			// stand-alone pages
			'about/terms/?'										=> array('template' => "about_terms"),
			'about/privacy/?'									=> array('template' => "about_privacy"),
			'about/rules/?'										=> array('template' => "about_rules"),
			'about/contact/?'									=> array('template' => "about_contact"),
			'about/?'											=> array('template' => "about"),
			
			// ajax
			'ajax/([A-Za-z0-9\-\_\.]+).php'						=> array('ajax' => true, 'matches' => array("ajax_file")),
			
			// search
			'search\.php'										=> "search",
			's/([A-Za-z0-9\-]+)/?'								=> array('template' => "search", 'matches' => array("query")),
			
			// random page
			'random/?'											=> array('function' => "random_content_redirect"),
			
			// dev/debug
			'dev\.php'											=> array('template' => "dev"),
			'wiki_linker/?'										=> array('template' => "wiki_linker"),
			
			// submit content
			'add/([A-Za-z0-9\-\_]+)/?'							=> array('template' => "add", 'matches' => array("action")),
			'add/?'												=> array('template' => "add"),
			
			// jungle jump page
			'jungle_id/([A-Za-z0-9\-]+?)/([0-9]+?)/?'			=> array('function' => "jungle_id_jumper", 'matches' => array("jump_type", "jump_id")),
			'wiki_hash/([a-f0-9]{32})/?'						=> array('function' => "wiki_hash_jumper", 'matches' => array("jump_hash")),
			'allmovie_id/([0-9]+)/?'							=> array('function' => "allmovie_jumper", 'matches' => array("jump_id")),
			
			// entity view page
			'j/([0-9]+)/admin\.html'							=> array('function' => "jungle_id_to_admin"),
			'j/([0-9]+)/([A-Za-z0-9\-]+)\.html'					=> array('template' => "entity",		'matches' => array("e_id", "e_section")),
			'j/([0-9]+)/?'										=> array('template' => "entity",		'matches' => array("e_id")),
			'type/([0-9]+)/?'									=> array('template' => "entity_type",	'matches' => array("e_type")),
			
			// account pages
			'account/?'											=> array('template' => "account"),
			'account/settings/?'								=> array('template' => "account_settings"),
			'account/finishsync/?'								=> array('template' => "account_finishsync"),
			'account/logout/?'									=> array('function' => "accountLogout"),
			
			// user pages
			'profile/([A-Za-z0-9\-]+)/?'						=> array('template' => "member_profile", 'matches' => array("username")),
		);
		
		$rewrite = $rewrite + $new_rules;
		
		return $rewrite;
	}	add_filter('rewrite_rules', "jungle_rewriteRules");