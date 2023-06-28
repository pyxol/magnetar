	
	
	;var JungleDB = {};
	
	// Initiate JungleDB JavaScript Engine
	JungleDB.init = function() {
		JungleDB.Utilities.loop_method_functions( JungleDB.Binds );
	};
	
	// site settings from hidden inputs near start of body tag
	JungleDB.settings = {};
	
	// placeholder for Facebook JS SDK's FB variable
	JungleDB.FB = false;
	
	// scopeless variable storage
	JungleDB.pit = {
		'search_ajax_instance': null
	};
	
	
	JungleDB.hashes = {
		// 'key': { type: (success|error|other|...), text: "..." }
		
		// Accounts
		'settings_saved':			"Successfully saved your account settings.",
		'site_logout':				"Sorry to see you go... Come back soon!",
		'signup_success':			"Welcome to JungleDB! Thanks for signing up!",
		'account_finish':			"You'll need to complete the signup process before continuing...",
		'settings_no_account':		"You need an account to do that...",
		
		// Site
		'contact_message_sent':		"Your message has been sent. Thanks!",
		
		// Content
		'content_not_found':		"We couldn't find the submission you were looking for.",
		'content_banned':			"The submission you were trying to view has been removed.",
		'content_random_nothing':	"We couldn't find any submissions to direct you to... Please try again.",
		'content_new_success':		"Success! Share your submission with friends so it can make it to the frontpage.",
		
		// Member Profiles
		'profile_not_specified':	"You were accidentally linked to a non-existant member profile. Sorry!",
		'profile_not_found':		"We couldn't find the profile you were looking for.",
		
		// Twitter
		'twapi_nil':				"",
		'twapi_error_database':		false,
		'twapi_error_missing':		false,
		'twapi_error_cancel':		"You'll need to authorize through Twitter to have an account with our website.",
		'twapi_signup_success':		"Welcome to JungleDB! Thanks for signing up!",
		'twapi_success_login':		"Welcome back! Let's have fun!",
		
		// Facebook
		'fbapi_nil':				"",
		'fbapi_error_database':		false,
		'fbapi_error_missing':		false,
		'fbapi_error_cancel':		"You'll need to authorize through Facebook to have an account with our website.",
		'fbapi_signup_success':		"Welcome to JungleDB! Thanks for signing up!",
		'fbapi_success_login':		"Welcome back! Let's have fun!"
	};
	
	JungleDB.Utilities = {
		'resolveUrl': function(url) {
			var anchor = document.createElement('a');
			anchor.href = url;
			url = anchor.href;
			anchor.href = null;
			
			return url;
		},
		
		// http://stackoverflow.com/a/841121/103337
		'selectRange': function(selector, start, end) {
			return $(selector).each(function() {
				if($(selector).setSelectionRange) {
					$(selector).focus();
					$(selector).setSelectionRange(start, end);
				} else if ($(selector).createTextRange) {
					var range = $(selector).createTextRange();
					
					range.collapse(true);
					range.moveEnd('character', end);
					range.moveStart('character', start);
					range.select();
				}
			});
		},
		
		'loop_method_functions': function( the_object ) {
			var obj;
			
			for(obj in the_object) {
				if(the_object.hasOwnProperty(obj)) {
					if(typeof the_object[ obj ] === "function") {
						the_object[ obj ]();
					}
				}
			}
		},
		
		'popup': function(popup_url, popup_width, popup_height, popup_name) {
			if(popup_url === undefined) {
				return false;
			}
			
			var popup = {
				'url':		popup_url,
				'width':	((popup_width !== undefined)?popup_width:550),
				'height':	((popup_height !== undefined)?popup_height:400),
				'name':		((popup_name !== undefined)?popup_name:"") + "_"+ Math.random().toString().slice(5, 12)
			};
			
			
			var new_window = window.open(
				popup.url,
				popup.name,
				"width="+ popup.width +",height="+ popup.height +",location=yes,menubar=no,status=no,toolbar=no"
			);
			
			if(window.focus) {
				new_window.focus();
			}
		},
		
		'first_key': function(obj) {
			var key;
			
			for(key in obj) {
				if(obj.hasOwnProperty(key)) {
					if(typeof obj[key] !== "function") {
						return key;
					}
				}
			}
		},
		
		'min_value_key': function(obj) {
			var min_key = JungleDB.Utilities.first_key(obj);
			var key;
			
			for(key in obj) {
				if(obj.hasOwnProperty(key)) {
					if(obj[key] < obj[min_key]) {
						min_key = key;
					}
				}
			}
			
			return min_key;
		},
		
		'microtime': function() {
			return (new Date().getTime() / 1000);
		},
		
		'the_time': function() {
			var date = new Date();
			
			return ((date.getHours() < 10)?"0":"") + date.getHours() +":"+ ((date.getMinutes() < 10)?"0":"") + date.getMinutes() +":"+ ((date.getSeconds() < 10)?"0":"") + date.getSeconds();
		}
	};
	
	JungleDB.Log = {
		'server': function(msg) {
			if(msg === undefined || msg === "") {
				return;
			}
			
			$.ajax({
				'url':		"/ajax/debug.console.php",
				'data':		{
					'action':	"site_log",
					'message':	msg
				},
				'type':		"GET",
				'cache':	false,
				'success':	function() {
					JungleDB.Log.client("Sent log to server...");
				}
			});
		},
		
		'client': function(msg) {
			if(msg === undefined || msg === "") {
				return;
			}
			
			if(JungleDB.settings.debug_mode !== undefined) {
				if(JungleDB.settings.debug_mode === false || JungleDB.settings.debug_mode === 0 || JungleDB.settings.debug_mode === "0") {
					return;   // debug mode is off, do not send console logs
				}
			}
			
			var log_date = JungleDB.Utilities.the_time();
			
			if(typeof msg === "array" || typeof msg === "object") {
				console.dir(msg);
			} else {
				console.log("["+ log_date +"] "+ msg);
			}
		}
	};
	
	JungleDB.Actions = {
		'ajax_sideLoad': function(url, skip_push_state) {
			skip_push_state = skip_push_state || false;
			
			JungleDB.Log.client("Sideloading "+ url);
			
			window.scrollTo(0, 0);
			
			$.ajax({
				'url':	url,
				'headers':	{'X-ajax-plz': "1"},
				'success':	function(data) {
					// todo: data.target_elements = {'#app_container': "<div id='app'>...", ...}
					// todo: data.page_title = "..."
					// todo: data.page_description = "..."
					// todo: data.page_keywords = "..."
					
					if(!skip_push_state) {
						window.history.pushState({'ajax': true, 'url': url}, url, url);
					}
					
					
					//$("meta[property='canonical']").attr('content', url);
					
					
					/*
					var cx;
					
					for(cx in data.target_elements) {
						if(data.target_elements.hasOwnProperty(cx)) {
							$( data.target_elements[cx].selector ).empty().append( data.target_elements[cx].cargo );
						}
					}
					*/
					
					
					$("meta[property='og:url']").attr('content', url);
					$("#app_container").empty().append( data );
				},
				'error':	function(jqXHR, textStatus, errorThrown) {
					alert("There was an error... ("+ textStatus +": "+ errorThrown +")");
				},
				'complete':	function() {
					
				}
			});
		},
		
		/* Search: Ajax load on type */
		'search_ajaxResults': function(options) {
			var search = $.extend({}, {
				'query':			"",
				'entity_type':		false,
				'results_element':	"#search_results",
				'message_element':	false,
				'page':				1,
				'per_page':			20,
				'before': 			function(){ },
				'after': 			function(){ }
			}, options);
			
			//search.query = $.trim( search.query.replace(/[\,\.\;\:<>\:\"\'\[\]\!\@\#\$\%\^\&\*\(\)\_]/g, "") );
			search.query = $.trim( search.query );
			
			search.last_query = "";
			
			if(search.results_element) {
				search.last_query = $.trim( $(search.results_element).attr('data-last-query') || "" );
			}
			
			if(search.query === "" || search.query === search.last_query) {
				// query didn't change at all
				return;
			}
			
			JungleDB.Log.client(options);
			JungleDB.Log.client(search);
			JungleDB.Log.client("search.query = "+ search.query +" && options.query = "+ options.query);
			
			if(typeof search.before === "function") {
				(function() { search.before(search.query); })();
			}
			
			if((search.query !== "") && (search.query !== search.last_query)) {
				$.ajax({
					'url':		"/ajax/search.php",
					'method':	"GET",
					'data':		{
						'query':				search.query,
						'entity_type_limit':	search.entity_type
					},
					'beforeSend': function() {
						if(search.results_element) {
							$( search.results_element ).empty();
						}
						
						if(search.message_element) {
							$(search.message_element).empty();
						}
					},
					'success': function(data) {
						if(data.status === undefined) {
							return;
						}
						
						if(data.status === "error") {
							if(data.status_msg !== undefined) {
								if(search.message_element) {
									$(search.message_element).empty().html("<strong>Search Error:</strong> "+ data.status_msg);
								}
							}
							
							return;
						}
						
						if(parseInt(data.cargo.total_results, 10) <= 0) {
							if(search.message_element) {
								$(search.message_element).empty().html("No matches were found for <strong>"+ data.cargo.query +"</strong>! Proceed to adding your entity.");
							}
							
							return;
						}
						
						var key;
						
						for(key in data.cargo.results) {
							if(data.cargo.results.hasOwnProperty(key)) {
								var result = data.cargo.results[key];
								
								$("<div/>", {
									'id': "sr_li_"+ result.id,
									'class': "search_result"
								}).appendTo( search.results_element );
								
								$("<a/>", {
									'id': "sr_li_"+ result.id +"_anchor",
									'href': result.link,
									'title': result.title,
									'target': "_blank",
									'class': "search_result_anchor"
								}).appendTo("#sr_li_"+ result.id);
								
								if(result.media.n !== undefined) {
									$("<div/>", {
										'id': "sr_li_"+ result.id +"_thumb_container",
										'class': "search_result_thumb_container"
									}).appendTo("#sr_li_"+ result.id +"_anchor");
									
									$("<img/>", {
										'id': "sr_li_"+ result.id +"_thumb",
										'alt': result.title,
										'src': result.media.n,
										'class': "search_result_thumb"
									}).appendTo("#sr_li_"+ result.id +"_thumb_container");
								} else {
									$("#sr_li_"+ result.id).addClass("search_result_thumbless");
								}
								
								$("<div/>", {
									'id': "sr_li_"+ result.id +"_text_container",
									'class': "search_result_text_container"
								}).appendTo("#sr_li_"+ result.id +"_anchor");
								
								$("<div/>", {
									'id': "sr_li_"+ result.id +"_title",
									'class': "search_result_title"
								}).html( result.title ).appendTo("#sr_li_"+ result.id +"_text_container");
								
								$("<div/>", {
									'id': "sr_li_"+ result.id +"_url",
									'class': "search_result_url"
								}).html( result.link ).appendTo("#sr_li_"+ result.id +"_text_container");
								
								$("<div/>", {
									'id': "sr_li_"+ result.id +"_excerpt",
									'class': "search_result_description"
								}).html( result.excerpt ).appendTo("#sr_li_"+ result.id +"_text_container");
								
							}
						}
						
						if(search.message_element) {
							$(search.message_element).empty().html("JungleDB found <strong>"+ data.cargo.total_found +"</strong> textually relevant entities");
						}
					},
					'complete': function() {
						$( search.results_element ).attr('data-last-query', search.query);
					}
				});
			} else {
				JungleDB.Log.client("search.query is same as search.last_query");
			}
			
			if(typeof search.after === "function") {
				(function() { search.after(search.query); })();
			}
			
			search.last_query = search.query;
		}
	};
	
	JungleDB.Binds = {
		/*
		'ajax_anchors': function() {
			$("body").on('click', ".ajax", function(e) {
				var url = $(this).attr('href') || false;
				
				if(!url) {
					alert("No url to load via .ajax");
					
					return;
				}
				
				url = JungleDB.Utilities.resolveUrl(url);
				
				e.preventDefault();
				
				JungleDB.Actions.ajax_sideLoad(url);
				
				return;
			});
			
			$(window).bind('popstate', function(e) {
				if(e.originalEvent && e.originalEvent.state && e.originalEvent.state.ajax && e.originalEvent.state.url) {
					JungleDB.Actions.ajax_sideLoad(e.originalEvent.state.url, false);
				}
			});
		},
		*/
		
		'set_site_variables': function() {
			$(".global_site_variable").each(function() {
				JungleDB.settings[ $(this).attr("name") ] = $(this).val();
			});
		},
		
		'disabled_links': function() {
			$("body").on("click", ".disabled", function(e) { e.stopPropagation(); e.preventDefault(); return false; });
		},
		
		'search_on_type':	function() {
			$("body").on('click.search_on_type', "#auto_complete", function(e) {
				e.stopPropagation();
			});
			
			$("body").on('keydown.search_on_type', "#header_search_form", function(e) {
				if(13 === e.which) {
					var stored_entity_id = $("#header_search_query").data('entity-id-selected') || false;
					
					if(stored_entity_id) {
						e.stopPropagation();
						e.preventDefault();
						
						window.location = $("#acr_anchor_"+ stored_entity_id).attr('href');
						
						return false;
					}
				}
			});
			
			$("body").on('keydown.search_on_type', "#header_search_query", function(e) {
				if(40 === e.which) {
					// down key
					e.preventDefault();
					
					// movement down
					var num_results = $("#auto_complete_results li").length || 0;
					
					if(num_results <= 0) {
						$("#header_search_query").removeData('entity-id-selected').focus();
						
						return;
					}
					
					var entity_id_to_select = false;
					
					if(false === entity_id_to_select) {
						if($("#auto_complete_results li.acr_selected").length <= 0) {
							// next one in the list, select it
							var entity_id = $("#auto_complete_results li:first-child").data('entity-id') || false;
							
							if(entity_id) {
								entity_id_to_select = entity_id;
							}
						}
					}
					
					
					if(false === entity_id_to_select) {
						var selected_position = 0;   // default to input box
						var position_i = 0;
						
						$("#auto_complete_results li").each(function() {
							position_i++;
							
							if($(this).hasClass("acr_selected")) {
								selected_position = position_i;
								
								if(selected_position < num_results) {
									$(this).removeClass("acr_selected");
								}
							}
							
							if((0 !== selected_position) && (position_i === (selected_position + 1))) {
								// next one in the list, select it
								var entity_id = $(this).data('entity-id') || false;
								
								if(entity_id) {
									entity_id_to_select = entity_id;
								}
							}
						});
					}
					
					
					if(false !== entity_id_to_select) {
						$("#auto_complete_result_"+ entity_id_to_select).addClass("acr_selected");
						$("#header_search_query").data('entity-id-selected', entity_id_to_select);
					}
					
					return;
				}
				
				// Up key
				if(38 === e.which) {
					e.preventDefault();
					
					if($("#auto_complete_results li").length <= 0) {
						// nothing to do
						return;
					}
					
					// movement up
					var selected_position = 0;   // default to input box
					
					if($("#auto_complete_results li.acr_selected").length > 0) {
						var position_i = 0;
						
						$("#auto_complete_results li").each(function() {
							if($(this).hasClass("acr_selected")) {
								$(this).removeClass("acr_selected");
								
								selected_position = position_i;
							}
							
							if(0 === selected_position) {
								position_i++;
							}
						});
					}
					
					if(selected_position > 0) {
						var entity_id = $("#auto_complete_results li:nth-child("+ selected_position +")").data('entity-id') || false;
						
						if(entity_id) {
							$("#auto_complete_result_"+ entity_id).addClass("acr_selected");
							$("#header_search_query").data('entity-id-selected', entity_id);
						}
					} else {
						$("#header_search_query").removeData('entity-id-selected').focus();
					}
					
					return;
				}
			});
			
			$("body").on('keyup.search_on_type', "#header_search_query", function(e) {
				// Escape key
				if(27 === e.which) {
					if($("#auto_complete").length) {
						$("#header_search").removeClass("header_search_auto_complete");
						$("#auto_complete").empty().remove();
					}
					
					return;
				}
				
				// Stop keys
				if($.inArray(e.which, [9, 13, 16, 17, 18, 19, 20, 27, 33, 34, 35, 36, 37, 38, 39, 40, 45, 93, 91, 112, 113, 114, 115, 116, 117, 118, 119, 120, 121, 122, 123, 144, 145, 182, 183]) !== -1) {
					//JungleDB.Log.client("key ignored - "+ e.which +" ("+ typeof(e.which) +")");
					
					return;
				}
				
				
				// store query
				var q = $(this).val() || "";
				q = q.replace(/^\s{1,}/g, "").replace(/\s{1,}$/g, "").replace(/\s{2,}/g, " ");
				
				if(!q || q.length < 3) {
					return;
				}
				
				var previous_q = $(this).data("previous-query") || false;
				
				if(previous_q && previous_q == q) {
					return;
				}
				
				$(this).data("previous-query", q);
				
				
				// Pull results
				
				if(JungleDB.pit.search_ajax_instance && null !== JungleDB.pit.search_ajax_instance) {
					clearTimeout(JungleDB.pit.search_ajax_instance);
				}
				
				JungleDB.pit.search_ajax_instance = setTimeout(function() {
					$.ajax({
						'url':	"/ajax/auto_complete.php",
						'data':	{
							'query':	q
						},
						'success':	function(data) {
							if(!data.status) {
								JungleDB.Log.client("AJAX response is not properly formatted.");
								
								return;
							}
							
							if(data.status === "error") {
								if(data.status_msg) {
									JungleDB.Log.client("AJAX Error: "+ data.status_msg);
								}
								
								return;
							}
							
							if(!data.cargo) {
								JungleDB.Log.client("AJAX response is not properly formatted for cargo.");
								
								return;
							}
							
							
							// prep auto complete container
							if($("#auto_complete").length) {
								$("#auto_complete").empty().remove();
								$("#header_search").removeClass("header_search_auto_complete");
							}
							
							$("body").one('click', function(e) {
								if($("#auto_complete").length) {
									$("#header_search").removeClass("header_search_auto_complete");
									$("#auto_complete").empty().remove();
								}
							});
							
							$("#header_search").addClass("header_search_auto_complete");
							
							$("<div/>", {
								'id':		"auto_complete"
							}).css({
								'width':	$("#header_search_query").innerWidth(true)
							}).appendTo("#header_search");
							
							$("<ol/>", {
								'id':		"auto_complete_results"
							}).appendTo("#auto_complete");
							
							var acr_default_thumb = "";
							
							for(var i in data.cargo) {
								if(data.cargo.hasOwnProperty(i)) {
									$("<li/>", {
										'id':		"auto_complete_result_"+ data.cargo[i].id,
										'class':	"auto_complete_result_type_"+ data.cargo[i].type_id
									}).data('entity-id', data.cargo[i].id).appendTo("#auto_complete_results");
									
									$("<a/>", {
										'id':		"acr_anchor_"+ data.cargo[i].id,
										'class':	"acr_anchor",
										'href':		"/j/"+ data.cargo[i].id +"/",
										'title':	data.cargo[i].title
									}).appendTo("#auto_complete_result_"+ data.cargo[i].id);
									
									if(data.cargo[i].media_url && data.cargo[i].media_url.s) {
										$("<img/>", {
											'class':	"acr_thumb",
											'alt':		data.cargo[i].title,
											'src':		data.cargo[i].media_url.s
										}).appendTo("#acr_anchor_"+ data.cargo[i].id);
									}
									
									$("<div/>", {
										'class':	"acr_title"
									}).html( data.cargo[i].title ).appendTo("#acr_anchor_"+ data.cargo[i].id);
								}
							}
							
							
							var full_query = $("#header_search_query").data("previous-query") || false;
							
							if(full_query) {
								$("<li/>", {
									'id':		"auto_complete_result_full"
								}).data('entity-id', "full").appendTo("#auto_complete_results");
								
								$("<a/>", {
									'id':		"acr_anchor_full",
									'class':	"acr_anchor_full",
									'href':		"/search.php?query="+ encodeURIComponent(full_query),
									'title':	"Show All Results"
								}).html("Show All Results").appendTo("#auto_complete_result_full");
							}
						},
						'complete':	function() {
							JungleDB.pit.search_ajax_instance = null;
						}
					});
				}, 300);
			});
		}
	};
	
	JungleDB.Tools = {};
	
	
	
	jQuery(document).ready(function($) {
		JungleDB.init();
	});
	
	