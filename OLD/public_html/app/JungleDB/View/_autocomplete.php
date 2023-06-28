	
	<div id="frontpage_wrapper">
		<div id="frontpage">
			
			<h1 class="frontpage_logo"><a href="/" title="Jungle: Organized Information"><span>J</span>ungle</a></h1>
			
			<form method="get" action="/search.php" class="frontpage_search">
				<label for="frontpage_search_query">Search: </label><input type="text" name="query" value="" placeholder="Search..." id="frontpage_search_query" autocomplete="off" /><input type="submit" value="Search" />
				<div id="frontpage_search_results"></div>
			</form>
			
		</div>
	</div>
	
	<style type="text/css">
		#frontpage_search_results { display: none; width: 800px; margin-top: -2px; margin-left: 19px; text-align: left; position: absolute; border: 1px solid #606060; background-color: #fff; }
			#frontpage_search_results .result_item { display: block; padding: 5px 10px; font: normal normal normal 12px/12px Verdana; color: #303030; text-decoration: none; }
				#frontpage_search_results .result_item:hover, #frontpage_search_results .result_item_hover { color: #105ca5; background-color: #f8f8f8; font-weight: bold; }
	</style>
	
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			
			// session variables
			var last_search_query = false;
			
			
			// actions
			
			// call ajax on search box input
			var ajax_keyup_search = function() {
				var search_for = $("#frontpage_search_query").val().replace(/^\s\s*/, '').replace(/\s\s*$/, '').replace(/\s{2,}/, ' ');
				
				if((search_for.length < 3) || (last_search_query === search_for)) {
					return;
				}
				
				last_search_query = search_for;
				
				console.log("Searching for '"+ search_for +"'");
				
				$.get("/ajax/search.php", {'q': search_for}, function(data) {
					
					if(data.status !== "success") {
						console.log("Something happened... MSG: "+ data.status_msg);
						
						return;
					}
					
					// wipe out current ajax tray
					ajax_clear_autocomplete(true);
					
					
					// did we find anything?
					if(data.cargo.num_found < 0) {
						// found nothing
						console.log("Found no results...");
						
						return;
					}
					
					// parse search results
					var search_results = data.cargo.results;
					
					for(var i in search_results) {
						console.log("Appending '"+ search_results[i].title +"'");
						
						$("<a/>", {
							 'class': "result_item"
							,'href': search_results[i].url
							,'title': search_results[i].title
						}).html(search_results[i].title).appendTo("#frontpage_search_results");
					}
					
					ajax_show_autocomplete();
					
				});
			};
		
		
		// utilities
		
			// clear out the autocomplete search tray
			var ajax_clear_autocomplete = function(empty_it) {
				empty_it = empty_it || false;
				
				$("#frontpage_search_results").hide();
				
				if(empty_it) {
					$("#frontpage_search_results").empty();
				}
			};
			
			var ajax_show_autocomplete = function() {
				show_results = ($("#frontpage_search_results .result_item").length > 0?true:false);
				
				if(show_results === true) {
					$("#frontpage_search_results").attr("data-selected", "0").hide().slideDown();
				}
			};
			
			
			// events
			
			// call search ajax function for autocomplete
			$("#frontpage_search_query").keyup(function(e) {
				var key_pressed = e.which;
				
				console.log("Key pressed: "+ key_pressed);
				
				var ignore_keys = [39, 37, 16, 32, 17, 20, 18];
				
				for(var i in ignore_keys) {
					if(i == e) {
						return;
					}
				}
				
				
				clearTimeout($.data(this, 'timer'));
				
				
				// enter -> selected item -> load
				if(key_pressed == 13) {
					console.log("ENTER KEY PRESSED!");
					
					e.preventDefault();
					
					$("#frontpage_search_results .result_item_hover").trigger("click");
					
					return;
				}
				
				
				// if up/down, move active row in results (if any)
				
				// up=38
				// down=40
				
				if(key_pressed == 38 || key_pressed == 40) {
					var num_results = $("#frontpage_search_results .result_item").length;
					
					if(num_results < 1) {
						return;
					}
					
					
					if(!$("#frontpage_search_results").is(":visible")) {
						ajax_show_autocomplete();
					}
					
					
					
					$("#frontpage_search_results .result_item_hover").removeClass("result_item_hover");
					
					var current_selected_result = $("#frontpage_search_results").attr("data-selected") || "0";
					
					if(current_selected_result == "0") {
						if(key_pressed == 38) {
							console.log("go to last item");
							$("#frontpage_search_results .result_item").last().addClass("result_item_hover");
							
							$("#frontpage_search_results").attr("data-selected", num_results.toString());
						} else {
							console.log("go to first item");
							$("#frontpage_search_results .result_item").first().addClass("result_item_hover");
							
							$("#frontpage_search_results").attr("data-selected", "1");
						}
						
						return;
					}
					
					
					var selected_result_item = (parseInt(current_selected_result) + 1);
					
					// key=up
					if(key_pressed == 38) {
						selected_result_item = selected_result_item - 2;
					}
					
					
					if((selected_result_item < 1) || (selected_result_item > num_results)) {
						// deselect completely
						
						$("#frontpage_search_query").removeClass("user_moved_focus").focus().val($("#frontpage_search_query").data('query'));
						$("#frontpage_search_results").attr('data-selected', "0");
						
						return;
					}
					
					
					// apply hover state
					var apply_hover_to = $("#frontpage_search_results .result_item").eq( (selected_result_item - 1) );
					$(apply_hover_to).addClass("result_item_hover");
					$("#frontpage_search_results").attr('data-selected', selected_result_item);
					$("#frontpage_search_query").addClass("user_moved_focus").focus().val($(apply_hover_to).attr('title'));
					
					return;
				}
				
				
				if($(this).hasClass("user_moved_focus")) {
					return;
				}
				
				$(this).data('query', $(this).val());
				var wait = setTimeout(ajax_keyup_search, 300);
				$(this).data('timer', wait);
			});
			
			// hide search results [if any] on blur
			$("#frontpage_search_query").blur(function(e) {
				setTimeout(ajax_clear_autocomplete, 1000);
			});
			
			// show search results [if any] on focus
			$("#frontpage_search_query").focus(function(e) {
				ajax_show_autocomplete();
			});
			
		});
	</script>