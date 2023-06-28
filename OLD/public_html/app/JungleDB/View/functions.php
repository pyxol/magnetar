<?php
	// dependencies
	require_once($site['dir']['template_includes'] ."utilities.php");
	require_once($site['dir']['template_includes'] ."utilities-entities.php");
	require_once($site['dir']['template_includes'] ."utilities-wikipedia.php");
	
	// modules
	require_once($site['dir']['template_includes'] ."rewrite.php");
	
	// CSS + JS Queues //
	
		queue_css('jungle',	get_template_path() ."static/css/jungle.css");
		queue_css('jungle-mobile', get_template_path() ."static/css/jungle.mobile.css", false, "handheld, screen and (max-device-width: 480px)");
		
		queue_js('jquery',	"//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js");
		queue_js('jungle',	get_template_path() ."static/js/jungle.js");
	
	
	// Layout Debug bar //
	
		if(!empty($site['layout_debug'])) {
			function layoutDebugBar() {
				?><div style="width: 100%; height: 1px; background-color: #f00; position: fixed; top: 670px;"></div><?php
			}	add_hook('body', "layoutDebugBar");
		}
	
	
	// User actions //
	
		function jungle_id_to_admin() {
			if(preg_match("#^\/?j\/([0-9]+)\/#i", getenv("REQUEST_URI"), $match)) {
				redirect("http://admin.jungledb.dev/entity.php?id=". trim($match[1], "/"));
			}
			
			redirect("/");
			
			die;
		}
		
		function resolve_wiki_hash($hash) {
			global $db;
			
			if(!preg_match("#^[A-Fa-f0-9]{32}$#si", $hash)) {
				return false;
			}
			
			$wikipedia = $db->get_row("SELECT * FROM `articles` WHERE `title_hash` = '". $db->escape($hash) ."'");
			
			if(!empty($wikipedia['redirect_to'])) {
				$wikipedia = resolve_wiki_hash($wikipedia['redirect_to']);
			}
			
			return $wikipedia;
		}
		
		function wiki_hash_jumper() {
			global $site;
			global $db;
			
			if(empty($site['args']['jump_hash']) || !preg_match("#^[A-Fa-f0-9]{32}$#si", $site['args']['jump_hash'])) {
				die("wiki hash value missing from url or incorrect format");
			}
			
			$db->select_db(WIKIPEDIA_DATABASE);
			$wikipedia = resolve_wiki_hash($site['args']['jump_hash']);
			$db->select_db(DB_NAME);
			
			if(empty($wikipedia)) {
				die("Could not find wiki hash in database");
			}
			
			$entity_meta_id = $db->get_var("SELECT `id` FROM `meta` WHERE `key` = 'external_id:wikipedia' LIMIT 1");
			
			if(!empty($entity_meta_id)) {
				$entity_id = $db->get_var("SELECT `entity_id` FROM `entity_meta` WHERE `meta_id` = '". $db->escape($entity_meta_id) ."' AND `value` = '". $db->escape($wikipedia['wiki_id']) ."'");
			}
			
			if(!empty($entity_id)) {
				redirect("/j/". $entity_id ."/");
				
				die;
				
				//$entity = $db->get_row("SELECT * FROM `entity` WHERE `id` = '". $db->escape($entity_id) ."'");
				//
				//if(!empty($entity)) {
				//	$entity_type = $db->get_row("SELECT * FROM `entity_type` WHERE `id` = '". $db->escape($entity['type_id']) ."'");
				//	
				//	if(!empty($entity_type)) {
				//		redirect("/j/". $entity_type['seoid'] ."/". $entity['seoid'] ."/");
				//		
				//		die;
				//	}
				//}
			}
			
			if(!empty($wikipedia['id'])) {
				
				$entity_meta_id = $db->get_var("SELECT `id` FROM `meta` WHERE `key` = 'external_id:wikipedia' LIMIT 1");
				
				if(!empty($entity_meta_id)) {
					$entity_id = $db->get_var("SELECT `entity_id` FROM `entity_meta` WHERE `meta_id` = '". $db->escape($entity_meta_id) ."' AND `value` = '". $db->escape($wikipedia['id']) ."'");
				}
				
				if(!empty($entity_id)) {
					$entity = $db->get_row("SELECT * FROM `entity` WHERE `id` = '". $db->escape($entity_id) ."'");
					$entity_type = $db->get_row("SELECT * FROM `entity_type` WHERE `id` = '". $db->escape($entity['type_id']) ."'");
					
					//redirect($site['uri']['base'] ."j/". $entity_type['seoid'] ."/". $entity['seoid'] ."/");
					redirect("/j/". $entity['id'] ."/");
				} else {
					redirect("http://jungledb.dev/dev/wiki.php?e=". $wikipedia['wiki_id']);
				}
				
				die;
			}
			
			die("Something should've caught already");
		}
		
		function allmovie_jumper() {
			global $site;
			global $db;
			
			if(empty($site['args']['jump_id'])) {
				die("allmovie value missing from url or incorrect format");
			}
			
			$meta_id = $db->get_var("SELECT `id` FROM `meta` WHERE `key` = 'external_id:allrovi' LIMIT 1");
			
			if(empty($meta_id)) {
				$entity_id = $db->get_var("SELECT `entity_id` FROM `entity_meta` WHERE `meta_id` = '". $db->escape($meta_id) ."' AND `value` = '". $db->escape($site['args']['jump_id']) ."'");
			}
			
			
			if(!empty($entity_id)) {
				redirect( site_url("/j/". $entity_id ."/") );
				
				die;
			}
			
			die("allmovie id not found");
		}
		
		//function jungle_id_jumper() {
		//	global $site;
		//	global $db;
		//	
		//	print "<pre>". print_r($site['args'],1) ."</pre>\n";
		//	
		//	if(!isset($site['args']['jump_type']) || !isset($site['args']['jump_id'])) {
		//		redirect("/?jump_undefined_args");
		//		
		//		die;
		//	}
		//	
		//	$jump_type = strtolower(trim($site['args']['jump_type']));
		//	$jump_id = $site['args']['jump_id'];
		//	$jump_table = false;
		//	$jump_id_column = false;
		//	$jump_url = false;   // entertainment/movie/%%SEOID%%/
		//	
		//	switch($jump_type) {
		//		case 'm':
		//			$jump_table = "entertainment_movie";
		//			$jump_id_column = "id";
		//			$jump_seoid_column = "seoid";
		//			$jump_url = "/entertainment/movie/%%SEOID%%/";
		//			break;
		//		
		//		case 'p':
		//			$jump_table = "person";
		//			$jump_id_column = "id";
		//			$jump_seoid_column = "seoid";
		//			$jump_url = "/person/%%SEOID%%/";
		//			break;
		//		
		//		// add more...
		//	}
		//	
		//	if($jump_table === false || $jump_id_column === false || $jump_seoid_column === false) {
		//		redirect("/?jump_type_undefined");
		//		
		//		die;
		//	}
		//	
		//	$seoid = $db->get_var("SELECT `". $db->escape($jump_seoid_column) ."` FROM `". $db->escape($jump_table) ."` WHERE `". $db->escape($jump_id_column) ."` = '". $db->escape($jump_id) ."'");
		//	
		//	if($seoid) {
		//		redirect(str_replace("%%SEOID%%", $seoid, $jump_url));
		//		
		//		die;
		//	}
		//	
		//	redirect("/?jump_no_logic");
		//	
		//	die;
		//}
	
	
	//// Sessions //
	//
	//	function redirectFinishSignupIfNeeded() {
	//		global $site;
	//		
	//		if(userNeedsToFinishSignup()) {
	//			if(!in_array($site['template_primary'], $site['fbsync_allowed_templates'])) {
	//				//redirect("/account/finishfbsync/#=account_finish=");
	//				redirect("/account/finishfbsync/");
	//				
	//				die;
	//			}
	//		}
	//	}	add_hook('templates_prepend', "redirectFinishSignupIfNeeded");
	
		
	// Hooks //
	
		// site detail elements
		function site_detail_elements() {
			global $site;
			
			// prepend initial site url if necessary
			if(!empty($site['fb_connect_callback_url'])) {
				if(!preg_match("#^http#si", $site['fb_connect_callback_url'])) {
					$site['fb_connect_callback_url'] = $site['uri']['base'] . ltrim($site['fb_connect_callback_url'], "/");
				}
			}
			
			$elements = array(
				// should be in distynct skeleton --v
				'debug_mode'		=> (($site['debug'] === true)?"1":"0"),
				'site_domain'		=> $site['uri_domain'],
				'site_uri_base'		=> $site['uri']['base'],
				'logged_in'			=> false,   //(isLoggedIn()?"1":"0")
				'finish_signup'		=> false,   //(userNeedsToFinishSignup()?"1":"0")
				
				// should be in template's functions.php --v
				'fb_appId'					=> $site['openGraph']['fb:app_id'],
				'fb_channel_url'			=> $site['fb_channel_url'],
				'fb_perms'					=> $site['fb_perms'],
				'fb_connect_callback_url'	=> $site['fb_connect_callback_url'],
				'fb_page_url'				=> $site['fb_page_url'],
			);
			
			if(!empty($elements)) {
				foreach($elements as $key => $value) {
					//print "<input type=\"hidden\" id=\"". $key ."\" value=\"". $value ."\" />\n";
					form_hidden($key, $value, array('class' => "global_site_variable"));
				}
			}
		}	add_hook('body', "site_detail_elements");
		
		// facebook opengraph meta tags
		function attach_opengraph() {
			global $site;
			
			$openGraph = apply_filters('openGraph', $site['openGraph']);
			
			foreach($openGraph as $key => $value) {
				if(empty($value)) {
					continue;
				}
				
				if($key == "fb:admins") {
					if(is_array($value)) {
						$value = implode(",", $value);
					}
				}
				
				if(is_array($value)) {
					// strip duplicate values
					$cleaned_values = @array_unique($value);
					
					foreach($cleaned_values as $each_value) {
						print "<meta property=\"". $key ."\" content=\"". htmlentities($each_value) ."\" />\n";
					}
				} else {
					print "<meta property=\"". $key ."\" content=\"". htmlentities($value) ."\" />\n";
				}
			}
		}	add_hook('head', "attach_opengraph");
		
		
		// facebook plugin script
		function social_script_facebook() {
			global $site;
			
			?>
				<div id="fb-root"></div>
				<script>
					window.fbAsyncInit = function() {
						FB.init({
							'appId':		"<?=esc_attr($site['fb_app_id']);?>",
							'cookie':		true,
							'logging':		true,
							'status':		false,
							'xfbml':		true,
							'channelUrl':	"<?=esc_attr($site['fb_channel_url']);?>"
						});
						
						window.FB = FB;
					};
					
					(function(d){
						var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
						if (d.getElementById(id)) {return;}
						js = d.createElement('script'); js.id = id; js.async = true;
						js.src = "//connect.facebook.net/en_US/all.js";
						ref.parentNode.insertBefore(js, ref);
					}(document));
				</script>
			<?php
		}	//add_hook('body', "social_script_facebook");
		
		
		// twitter plugin script
		function social_script_twitter() {
			?><script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script><?php
		}	//add_hook('foot', "social_script_twitter");
	
	
	// Analytics //
	
		function google_analytics_javscript() {
			?>
				<script type="text/javascript">
					var _gaq = _gaq || [];
					_gaq.push(['_setAccount', 'UA-30030268-1']);
					_gaq.push(['_trackPageview']);
					
					(function() {
						var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
						ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
						var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
					})();
				</script>
			<?php
		}	//add_hook('foot', "google_analytics_javscript");
	
	
	// Pagination //
	
		function paginate($url="/", $num_pages=1, $page=1, $ignore_home=false) {
			$newer_link = $url . (($page > 2)?"page/". ($page - 1) ."/":"");
			$older_link = $url . (($num_pages > 1)?"page/". (($page < $num_pages)?($page + 1):$num_pages) ."/":"");
			?>
			<div class="paginate">
				<span class="paginate_details">Page <?=esc_html($page);?></span>
				<a href="<?=esc_attr($older_link);?>" class="paginate_older <?php if($page >= $num_pages): ?> paginate_disabled disabled <?php endif; ?>">Older</a>
				<a href="<?=esc_attr($newer_link);?>" class="paginate_newer <?php if($page < 2): ?> paginate_disabled disabled <?php endif; ?>">Newer</a>
			</div>
			<?php
		}
	
	
	// Social //
	
		// Facebook
		
			// like button
			function get_social_fb_like($url=false) {
				if(empty($url)) {
					return false;
				}
				
				return "<fb:like href=\"". esc_attr($url) ."\" send=\"false\" layout=\"button_count\" show_faces=\"false\" font=\"\" label=\"Post\" class=\"fb_edge_widget_with_comment fb_iframe_widget\"></fb:like>";
			}	function social_fb_like($url=false) { print get_social_fb_like($url); }
			
			// 60x68 like badge
			function get_social_fb_like_badge($url=false) {
				if(empty($url)) {
					return false;
				}
				
				return "<fb:like href=\"". esc_attr($url) ."\" send=\"false\" layout=\"box_count\" width=\"60\" show_faces=\"false\"></fb:like>";
			}	function social_fb_like_badge($url=false) { print get_social_fb_like_badge($url); }
			
			// like box
			function get_social_fb_like_box($url=false, $width=300) {
				if(empty($url)) {
					return false;
				}
				
				return "<fb:like-box href=\"". esc_attr($url) ."\" width=\"". esc_attr($width) ."\" show_faces=\"true\" stream=\"false\" header=\"false\"></fb:like-box>";
			}	function social_fb_like_box($url=false) { print get_social_fb_like_box($url); }
			
			// comment count
			function get_social_fb_comment_count($url=false) {
				if(empty($url)) {
					return false;
				}
				
				return "<fb:comments-count href=\"". esc_attr($url) ."\"></fb:comments-count>";
			}	function social_fb_comment_count($url=false) { print get_social_fb_comment_count($url); }
			
			// comment box
			function get_social_fb_comments($url=false, $width=false, $num_posts=false) {
				if(empty($url)) {
					return false;
				}
				
				$num_posts = ((!empty($num_posts))?$num_posts:25);
				$width = ((!empty($width))?$width:570);
				
				return "<fb:comments href=\"". esc_attr($url) ."\" num_posts=\"". esc_attr($num_posts) ."\" width=\"". esc_attr($width) ."\"></fb:comments>";
			}	function social_fb_comments($url=false, $width=false, $num_posts=false) { print get_social_fb_comments($url, $width, $num_posts); }
			
			// share button
			function get_social_fb_share($url=false, $title=false, $description=false, $image=false, $attributes=false) {
				if(empty($url)) {
					return false;
				}
				
				$fb_sharer_url = "https://www.facebook.com/sharer/sharer.php?u=". urlencode($url) . ((!empty($title))?"&t=". urlencode($title):"");
				
				$return = "<a href=\"". esc_attr($fb_sharer_url) ."\" class=\"fb_share\" data-href=\"". esc_attr($url) ."\" ";
				
				if(!empty($title)) {
					$return .= " data-title=\"". esc_attr($title) ."\"";
				}
				
				if(!empty($description)) {
					$return .= " data-description=\"". esc_attr($description) ."\"";
				}
				
				if(!empty($image)) {
					$return .= " data-thumbnail=\"". esc_attr($image) ."\"";
				}
				
				$return .= _form_assign_attributes($attributes);
				$return .= "><span>Share</span></a>";
				
				return $return;
			}	function social_fb_share($url=false, $title=false, $description=false, $image=false, $attributes=false) { print get_social_fb_share($url, $title, $description, $image, $attributes); }
		
		
		// Twitter
		
			// tweet button
			function get_social_tw_tweet($url=false, $data_text=false, $data_via=false, $attributes=false) {
				global $site;
				
				if(empty($url)) {
					return false;
				}
				
				$data_text = (($data_text !== false)?$data_text:"I just laughed at this!");   // !== false instead of !empty() because users can provide "" for blank data_text
				$data_via = (($data_via !== false)?$data_via:"junglecom");   // !== false instead of !empty() because users can provide "" for blank data_via
				
				$tweet_url_params = array(
					'original_referer'	=> $site['uri']['current'],
					'source'			=> "tweetbutton",
					'text'				=> $data_text,
					'url'				=> $url,
					'via'				=> $data_via,
				);
				
				$tweet_url = "https://twitter.com/intent/tweet?". http_build_query($tweet_url_params);
				
				$return = "<a href=\"". esc_attr($tweet_url) ."\" class=\"tw_tweet\" ";
				
				$return .= " data-url=\"". esc_attr($url) ."\" ";
				
				if(!empty($data_text)) {
					$return .= " data-text=\"". esc_attr($data_text) ."\" ";
				}
				
				if(!empty($data_via)) {
					$return .= " data-via=\"". esc_attr($data_via) ."\" ";
				}
				
				$return .= _form_assign_attributes($attributes);
				$return .= "><span>Tweet</span></a>";
				
				return $return;
			}	function social_tw_tweet($url=false, $data_text=false, $data_via=false, $attributes=false) { print get_social_tw_tweet($url, $data_text, $data_via, $attributes); }
			
			// Inline tweet button
			function get_social_tw_tweet_line($url=false, $data_text=false, $data_via=false) {
				if(empty($url)) {
					return false;
				}
				
				$data_text = (($data_text !== false)?$data_text:"I just laughed at this!");   // !== false instead of !empty() because users can provide "" for blank data_text
				$data_via = (($data_via !== false)?$data_via:"junglecom");   // !== false instead of !empty() because users can provide "" for blank data_via
				
				$return = "<a href=\"https://twitter.com/share\" class=\"twitter-share-button\" data-url=\"". esc_attr($url) ."\"";
				
				if(!empty($data_text)) {
					$return .= " data-text=\"". esc_attr($data_text) ."\" ";
				}
				
				if(!empty($data_via)) {
					$return .= " data-via=\"". esc_attr($data_via) ."\" ";
				}
				
				$return .= ">Tweet</a>";
				
				return $return;
			}	function social_tw_tweet_line($url=false, $data_text=false, $data_via=false) { print get_social_tw_tweet_line($url, $data_text, $data_via); }
			
			// Tweet badge
			function get_social_tw_tweet_badge($url=false, $data_text=false, $data_via=false) {
				if(empty($url)) {
					return false;
				}
				
				$data_text = (($data_text !== false)?$data_text:"I just laughed at this!");   // !== false instead of !empty() because users can provide "" for blank data_text
				$data_via = (($data_via !== false)?$data_via:"junglecom");   // !== false instead of !empty() because users can provide "" for blank data_via
				
				$return = "<a href=\"https://twitter.com/share\" class=\"twitter-share-button\" data-count=\"vertical\" data-url=\"". esc_attr($url) ."\"";
				
				if(!empty($data_text)) {
					$return .= " data-text=\"". esc_attr($data_text) ."\" ";
				}
				
				if(!empty($data_via)) {
					$return .= " data-via=\"". esc_attr($data_via) ."\" ";
				}
				
				$return .= ">Tweet</a>";
				
				return $return;
			}	function social_tw_tweet_badge($url=false, $data_text=false, $data_via=false) { print get_social_tw_tweet_badge($url, $data_text, $data_via); }
	
	