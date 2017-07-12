	
	
	/* Search Autocomplete */
	JungleDB.Tools.AutoComplete = {
		'timeout':	"",
		'q':		"",
		'minLen':	2,
		'maxLen':	20,
		
		'HideSuggestions':	function() {
			$("#search_box_suggestions").stop(true, false).fadeOut("fast", function() { $(this).remove(); });
		};
		
		'buildQuerySlug':	function (q) {
			return q.replace("'", "").replace(" ", "-").replace(/[^A-Za-z0-9]/g, "-").replace(/\-{2,}/g, "-").replace(/^\-{1,}/g, "").replace(/\-{1,}$/g, "").replace("-", " ");
		};
		
		'didQueryChange':	function(q) {
			q = this.buildQuerySlug(q);
			var old_q = this.q;
			
			this.q = q;
			
			if(old_q !== q) {
				return true;
			}
		};
		
		'redirectUser':		function(q) {
			window.location = "/dl/"+ buildQuerySlug(q) +"/";
		};
		
		
		// Auto close search box suggestions on clicking away
		// http://stackoverflow.com/a/1423722/103337
		$('body').click(function(event) {
			if(!$(event.target).closest('#search_box_suggestions').length) {
				HideSuggestions();
			}
		});
		
		
		$(".cnwAutoComplete").on('keydown', function(e) {
			if(e.which === 9) {
				// tab key caught
				e.preventDefault();
			}
		});
		
		$(".cnwAutoComplete").attr('autocomplete', "off").on('keyup', function(e) {
			$(this).css({'outline': "none"});
			
			inputElementID = $(this).attr('id') || "";
			
			if(inputElementID.length < 1) {
				var iii;
				
				for(iii=0; iii<16; iii++) {
					var rnum = Math.floor(Math.random() * "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz".length);
					inputElementID += "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz".substring(rnum, (rnum + 1));
				}
				
				$(this).attr('id', inputElementID);
			}
			
			// ESC key -- close autosuggest
			if(e.which === 27) {
				HideSuggestions();
				
				return;
			}
			
			// up/down arrows -- move across list
			if(e.which === 38 || e.which === 40) {
				if(!$("#search_box_suggestions").length) {
					return;
				}
				
				var num_positions = $("#search_box_suggestions li").length || 0;
				
				if(num_positions === 0) {
					// no where to navigate to
					return;
				}
				
				var typed_query = $(this).attr('data-typed-query') || "";
				
				if(typed_query.length > 0) {
					typed_query = $.trim( typed_query ).toLowerCase();
				}
				
				var query_text = typed_query;
				var position = 0;
				
				// where is the cursor
				if($("#search_box_suggestions li.active").length) {
					// we're in the suggestion list!
					$("#search_box_suggestions li").each(function(i, v) {
						if($(this).hasClass("active")) {
							position = (i + 1);
							
							$(this).removeClass("active");
						}
					});
					
				}
				
				if(e.which === 38) {
					// pressed up
					if(position === 0) {
						// in the search box, assign last item in suggestions classname
						$("#search_box_suggestions li:nth-child("+ num_positions +")").addClass("active");
						query_text = $("#search_box_suggestions li:nth-child("+ num_positions +")").attr('data-query');
					} else if(position !== 1) {
						// somewhere in the list, not the first position (active already removed)
						$("#search_box_suggestions li:nth-child("+ (position - 1) +")").addClass("active");
						query_text = $("#search_box_suggestions li:nth-child("+ (position - 1) +")").attr('data-query');
					}
				} else {
					// pressed down
					if(position !== num_positions) {
						// somewhere in the middle
						$("#search_box_suggestions li:nth-child("+ (position + 1) +")").addClass("active");
						
						query_text = $("#search_box_suggestions li:nth-child("+ (position + 1) +")").attr('data-query');
					}
				}
				
				
				// apply search query value to input box, then select the additional text automatically
				// $(this).selectRange()
				
				e.preventDefault();
				
				// move cursor to end of line by default
				JungleDB.Utilities.selectRange(this, $(this).val().length, $(this).val().length);
				
				$(this).val( query_text );
				
				if(typed_query !== query_text) {
					
					$(this).selectRange( query_text.length, query_text.length );
					
					if(typed_query.substr(0, typed_query.length) === query_text.substr(0, typed_query.length)) {
						$(this).selectRange( typed_query.length, query_text.length );
					}
					
				}
				
				
				return;
			}
			
			// enter key + tab key -- select active suggestion to input text if possible
			if(e.which === 13 || e.which === 9) {
				if($("#search_box_suggestions li.active").length) {
					$(this).val( $("#search_box_suggestions li.active").attr('data-query') );
				}
				
				// save query to data attribute
				$(this).attr('data-typed-query', $.trim( $(this).val() ).toLowerCase());
			}
			
			// enter key -- send user to search results based on query
			if(e.which === 13) {
				redirectUser( $(this).val() );
				
				return;
			}
			
			// ignore key inputs from arrows, spaces, etc.
			if(e.which.toString().match(/^(9|13|16|17|18|19|20|27|33|34|35|36|37|38|39|40|45|91|92|93|144|145|186|187|188|189|190|191|192|219|220|221|222|106|107|109|110|111|112|113|114|115|116|117|118|119|120|121|122|123)$/g)) {
				return;
			}
			
			// set the input attribute for typed query here
			
			$(this).attr('data-typed-query', $.trim( $(this).val() ).toLowerCase());
			
			clearTimeout(JungleDB.Tools.AutoComplete.timeout);
			
			// load ajax after 300 milliseconds so calls don't double up on themselves on active typing
			JungleDB.Tools.AutoComplete.timeout = setTimeout(function() {
				try {
					JungleDB.Tools.AutoComplete.didQueryChange( $("#"+ inputElementID).val() );
					
					if((q.length < minLen) || (q.length > maxLen)) {
						return;
					}
					
					$.ajax({
						'type': "GET",
						'url': "/cnw_ajax_autocomplete.php",
						'data': {'query_prefix': q},
						'cache': true,
						'context': $("#"+ inputElementID),
						'success': function(data, textStatus, jqXHR) {
							if(data.status === "error") {
								return;
							}
							
							if(!data.cargo) {
								HideSuggestions();
								
								return;
							}
							
							if(data.cargo.length < 1) {
								HideSuggestions();
								
								return;
							}
							
							if(!$("#search_box_suggestions").length) {
								$(this).after(
									$("<ul/>", {
										'id': "search_box_suggestions"
									}).hide()
								);
							}
							
							$("#search_box_suggestions").stop(true, true).empty().hide();
							
							if($(this).hasClass("fullSearchBox")) {
								$("#search_box_suggestions").removeClass("sidebarSearchBox").addClass("fullSearchBox").css({
									'border-top': "1px solid #f0f0f0",
									'margin-top': ($(this).outerHeight() - 6)
								});
							} else if($(this).hasClass("sidebar")) {
								$("#search_box_suggestions").removeClass("fullSearchBox").addClass("sidebarSearchBox").css({
									'border': "1px solid #000000",
									'border-top': "0",
									'border-radius': "0",
									'width': ($(this).outerWidth() - 2),
									'margin-top': ($(this).outerHeight() - 2)
								});
							} else {
								$("#search_box_suggestions").removeClass("fullSearchBox sidebarSearchBox").css({
									'width': $(this).outerWidth(),
									'margin-top': ($(this).outerHeight() - 2)
								});
							}
							
							var input_text = buildQuerySlug( $(this).val() );
							var k;
							
							function search_box_suggestion_click_event(e) {
								redirectUser(e.data.qq);
							}
							
							for(k in data.cargo) {
								if(data.cargo.hasOwnProperty(k)) {
									if(!data.cargo.hasOwnProperty(k)) {
										continue;
									}
									
									$("#search_box_suggestions").append(
										$("<li/>").attr({
											'data-query': data.cargo[k],
											'data-parent-element-id': inputElementID
										}).html( data.cargo[k].replace(input_text, "<b>"+ input_text +"</b>")).on('click', {'qq': data.cargo[k]}, search_box_suggestion_click_event)
									);
								}
							}
							
							$("#search_box_suggestions").stop(true, false).show();
						}
					});
				} catch(err) {
					console.log("err == "+ err);
				}
			}, 300);
		});
	};
	
	