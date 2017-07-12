	
	
	// Gallery Modal Window
	
	var KEYCODE_ESC		= 27;
	var KEYCODE_LEFT	= 37;
	var KEYCODE_RIGHT	= 39;
	
	JungleDB.Gallery = {};
	
	JungleDB.Gallery.init = function() {
		JungleDB.Utilities.loop_method_functions( JungleDB.Gallery.Binds );
	};
	
	JungleDB.Gallery.Utilities = {
		'gallery_exists': function() {
			return $("#gallery_modal").length;
		},
		
		'getScrollbarWidth': function() {
			if(JungleDB.pit.gallery_scrollbarWidth === undefined) {
			//	if ( $.browser.msie ) {
			//		var $textarea1 = $('<textarea cols="10" rows="2"></textarea>').css({ position: 'absolute', top: -1000, left: -1000 }).appendTo('body');
			//		var $textarea2 = $('<textarea cols="10" rows="2" style="overflow: hidden;"></textarea>').css({ position: 'absolute', top: -1000, left: -1000 }).appendTo('body');
			//		JungleDB.pit.gallery_scrollbarWidth = $textarea1.width() - $textarea2.width();
			//		$textarea1.add($textarea2).remove();
			//	} else {
			//		var $div = $('<div />').css({ width: 100, height: 100, overflow: 'auto', position: 'absolute', top: -1000, left: -1000 }).prependTo('body').append('<div />').find('div').css({ width: '100%', height: 200 });
			//		JungleDB.pit.gallery_scrollbarWidth = 100 - $div.width();
			//		$div.parent().remove();
			//	}
				JungleDB.pit.gallery_scrollbarWidth = 16;
			}
			
			return JungleDB.pit.gallery_scrollbarWidth;
		}
	};
	
	JungleDB.Gallery.Actions = {
		'gallery_start': function(callPushState) {
			var call_pushState = (callPushState === true) || false;
			var gallery_details_elem = $("#modal_gallery_details") || false;
			
			if(!gallery_details_elem) {
				JungleDB.Log.client("gallery_details_elem doesnt exist");
				
				return false;
			}
			
			var gallery_id = $(gallery_details_elem).attr('data-entity') || false;
			
			if(!gallery_id) {
				return false;
			}
			
			if(JungleDB.pit.gallery_details === undefined) {
				JungleDB.pit.gallery_details = {};
			}
			
			var gallery_details = JungleDB.pit.gallery_details[ gallery_id ];
			
			if(!gallery_details) {
				$.ajax({
					'url':		"/ajax/entity_media.php",
					'data':		{'do': "gallery_details", 'entity_id': gallery_id},
					'method':	"GET",
					'cache':	true,
					'success': function(data, textStatus, jqXHR) {
						JungleDB.pit.gallery_details[ gallery_id ] = data;
						
						JungleDB.Gallery.Actions.gallery_start(call_pushState);
					}
				});
				
				return;
			}
			
			var current_media_id = $(gallery_details_elem).attr('data-media') || false;
			
			if(!current_media_id) {
				current_media_id = JungleDB.Utilities.first_key(gallery_details.media);
			}
			
			if(!current_media_id) {
				JungleDB.Log.client("current_media_id is empty");
				return;
			}
			
			var image_details = gallery_details.media[current_media_id] || false;
			
			if(!image_details) {
				JungleDB.Gallery.Actions.gallery_close();
				return false;
			}
			
			
			var prev_image_details = gallery_details.media[ image_details.prev_id ];
			var next_image_details = gallery_details.media[ image_details.next_id ];
			
			
			if(!JungleDB.Gallery.Utilities.gallery_exists()) {
				if(!$("#modal_gallery_photo_loader").length) {
					$("<div/>", {
						 'id': "modal_gallery_photo_loader"
					}).hide().appendTo("body");
				}
				
				$("<div/>", {
					 'id': "gallery_modal"
				}).hide().appendTo("body");
				
				$("<div/>", {
					 'class': "gallery_modal_inset"
				}).append(""+
				"	<div class='image'>"+
				"		<img src='' class='image_element' />"+
				"		<div class='navigate'>"+
				"			<a href='#' class='prev modal_gallery_prev'>Previous</a>"+
				"			<a href='#' class='next modal_gallery_next'>Next</a>"+
				"		</div>"+
				"	</div>"+
				"	<div class='etc'>"+
				"		<div class='gallery_modal_header'>"+
				"			<a href='"+ gallery_details.url +"' class='gallery_header_title'>"+ gallery_details.title +"</a>"+
				"			<div class='gallery_header_iteration'>Viewing <span class='gallery_iteration'>0</span> of <span class='gallery_count'>"+ gallery_details.num_media +"</span></div>"+
				"			<a href='#' class='gallery_modal_button_close'>x</a>"+
				"			<a href='#' class='gallery_modal_button_flag'>Flag</a>"+
				"		</div>"+
				"		<div class='gallery_modal_content'>"+
				"			<strong>"+ image_details.file +"</strong><br /><br />"+
				"			<a href='#' class='media_make_primary' data-entity='"+ gallery_details.id +"' data-media='"+ image_details.id +"'>Make Primary</a><br /><br />"+
				"			Metadata: <a href='http://darwin.cdn/dev/jungledb/media_examine.php?id="+ image_details.id +"' target='_blank'>View Meta</a><br /><br />"+
				//"			<a href='#' class='media_detatch' data-entity='"+ image_details.id +"' data-media='"+ gallery_details.id +"'>Detatch from Entity</a><br /><br />"+
				"			Type: <strong>"+ image_details.type +"</strong><br />"+
				"			Width: <strong>"+ ((image_details.dimensions && image_details.dimensions.width)?image_details.dimensions.width +"px":"") +"</strong><br />"+
				"			Height: <strong>"+ ((image_details.dimensions && image_details.dimensions.width)?image_details.dimensions.height +"px":"") +"</strong>"+
				
				
				
				"		</div>"+
				"	</div>"+
				"").appendTo("#gallery_modal");
			}
			
			$("#gallery_modal .image_element").attr({'src': image_details.media_url.o, 'data-width': image_details.dimensions.width, 'data-height': image_details.dimensions.height})
			$("#gallery_modal .modal_gallery_prev").attr({'href': prev_image_details.url});
			$("#gallery_modal .modal_gallery_next").attr({'href': next_image_details.url});
			$("#gallery_modal .gallery_iteration").html(""+ image_details.position);
			
			JungleDB.Gallery.Actions.gallery_resize();
			
			// get next photo and put it into loading
			try {
				$("#modal_gallery_photo_loader").empty();
				
				$("<img/>", {
					 'src': next_image_details.media_url.o
				}).css({'width': "1px", 'height': "1px"}).appendTo("#modal_gallery_photo_loader");
				
				$("<img/>", {
					 'src': prev_image_details.media_url.o
				}).css({'width': "1px", 'height': "1px"}).appendTo("#modal_gallery_photo_loader");
			} catch(err) { /* u mad? */ }
			
			
			
			if(!$("body").hasClass("body_gallery_modal")) {
				var scrollbar_width = JungleDB.Gallery.Utilities.getScrollbarWidth();
				
				$("body").addClass("body_gallery_modal");
				
				if(scrollbar_width > 0) {
					$("body").css({'margin-right': "+="+ scrollbar_width +"px"});
				}
			}
			
			// update the value of the window's title
			var document_title = gallery_details.title +" #"+ image_details.position +" - JungleDB";
			document.title = document_title;
			
			if(call_pushState) {
				// change the address bar if possible
				try {
					window.history.pushState({
						'image_details':	image_details,
						'pageTitle':		document_title,
					}, "", image_details.url);
					
					$("body").addClass("JS_historyPushedState");
				} catch(err) { console.log("Err'd: "+ err) }
			}
			
			
			$("#gallery_modal").fadeIn(500);
		},
		
		'gallery_next': function() {
			if(JungleDB.Gallery.Utilities.gallery_exists()) {
				var gallery_id = $("#modal_gallery_details").attr('data-entity') || false;
				
				if(JungleDB.pit.gallery_details[ gallery_id ] !== undefined) {
					var gallery_details = JungleDB.pit.gallery_details[ gallery_id ] || false;
					
					if(gallery_details) {
						var current_media_id = $("#modal_gallery_details").attr('data-media') || false;
						
						if(!current_media_id) {
							current_media_id = JungleDB.Utilities.first_key(gallery_details.media);
						}
						
						if(current_media_id) {
							var updated_media_id = gallery_details.media[current_media_id].next_id || false;
							
							if(updated_media_id) {
								$("#modal_gallery_details").attr('data-media', updated_media_id);
								
								JungleDB.Gallery.Actions.gallery_start(true);
								
								return;
							}
						}
					}
				}
			}
		},
		
		'gallery_prev': function() {
			
			if(JungleDB.Gallery.Utilities.gallery_exists()) {
				var gallery_id = $("#modal_gallery_details").attr('data-entity') || false;
				
				if(JungleDB.pit.gallery_details[ gallery_id ] !== undefined) {
					var gallery_details = JungleDB.pit.gallery_details[ gallery_id ] || false;
					
					if(gallery_details) {
						var current_media_id = $("#modal_gallery_details").attr('data-media') || false;
						
						if(!current_media_id) {
							current_media_id = JungleDB.Utilities.first_key(gallery_details.media);
						}
						
						if(current_media_id) {
							var updated_media_id = gallery_details.media[current_media_id].prev_id || false;
							
							if(updated_media_id) {
								$("#modal_gallery_details").attr('data-media', updated_media_id);
								
								JungleDB.Gallery.Actions.gallery_start(true);
								
								return;
							}
						}
					}
				}
			}
		},
		
		'gallery_resize': function() {
			if(!JungleDB.Gallery.Utilities.gallery_exists()) {
				return;
			}
			
			// quick settings
			var margins_x = 50;   // require X pixels on each side of modalbox for margin
			var margins_y = 75;
			
			var window_width = $(window).width();
			var window_height = $(window).height();
			
			// element variables
			var gallery_element = $("#gallery_modal .gallery_modal_inset");
			var image_element = $(gallery_element).find(".image_element");
			
			var etc_element = $(gallery_element).find(".etc");
			var etc_width = $(etc_element).width();   // static
			
			var etc_header_element = $(etc_element).find(".gallery_modal_header");
			var etc_header_height = $(etc_header_element).outerHeight(true);
			
			var image_original_width = $(image_element).attr('data-width');
			var image_original_height = $(image_element).attr('data-height');
			
			
			// calculations
			//var image_width_multiplier	= Math.min(1, Math.max(0, (image_original_width  / image_original_height)));
			//var image_height_multiplier	= Math.min(1, Math.max(0, (image_original_height / image_original_width)));
			
			var image_width_multiplier	= (image_original_width  / image_original_height);
			var image_height_multiplier	= (image_original_height / image_original_width);
			
			var image_max_width = (window_width - margins_x - etc_width - margins_x);
			var gallery_max_height = (window_height - margins_y - margins_y);
			
			
			var image_width = image_max_width;
			var gallery_height = gallery_max_height;
			
			var resized_image_width_multiplier	= (image_width  / gallery_height);
			var resized_image_height_multiplier	= (gallery_height / image_width);
			
			if(resized_image_width_multiplier != image_width_multiplier) {
				gallery_height = (image_height_multiplier * (gallery_height * (1/resized_image_height_multiplier)));
			} else if(resized_image_height_multiplier != image_height_multiplier) {
				image_width = (image_width_multiplier * (image_width * (1/resized_image_width_multiplier)));
			}
			
			
			if(gallery_height > gallery_max_height) {
				gallery_height = gallery_max_height;
				image_width = (gallery_height * (1/image_height_multiplier));
			}
			
			
			var final_image_width = Math.round(image_width);
			var final_gallery_height = Math.round(gallery_height);
			
			
			
			
			
			//var final_margins_x = margins_x;   // autocentered by css
			var final_margins_y = margins_y;
			
			if(final_gallery_height < gallery_max_height) {
				final_margins_y = final_margins_y + Math.round( ((gallery_max_height - final_gallery_height) / 2) );
			}
			
			
			//console.log("===============");
			//console.log("window_width = "+ window_width);
			//console.log("window_height = "+ window_height);
			//console.log("etc_width = "+ etc_width);
			//console.log("image_original_width = "+ image_original_width);
			//console.log("image_original_height = "+ image_original_height);
			//console.log("image_width_multiplier = "+ image_width_multiplier);
			//console.log("image_height_multiplier = "+ image_height_multiplier);
			//console.log("gallery_max_height = "+ gallery_max_height);
			//console.log("image_max_width = "+ image_max_width);
			//console.log("gallery_height = "+ gallery_height);
			//console.log("image_width = "+ image_width);
			//console.log("final_gallery_height = "+ final_gallery_height);
			//console.log("final_image_width = "+ final_image_width);
			//console.log("final_margins_y = "+ final_margins_y);
			
			
			// apply it
			
			var resize_animation_options = {
				'duration':	75,   // milliseconds
				'queue':	false,
			};
			
			$(image_element).animate({'width': final_image_width +"px", 'height': final_gallery_height +"px"}, resize_animation_options);
			
			$(gallery_element).animate({'height': final_gallery_height +"px"}, resize_animation_options);
			
			$(etc_element).animate({'height': final_gallery_height +"px"}, resize_animation_options);
			
			$(gallery_element).animate({'width': (final_image_width + etc_width) +"px"}, resize_animation_options);
			
			$(gallery_element).animate({'margin-top': final_margins_y +"px"}, resize_animation_options);
		},
		
		'gallery_close': function(speed) {
			if(!JungleDB.Gallery.Utilities.gallery_exists()) {
				return false;
			}
			
			if(!speed) {
				speed = 400;
			}
			
			$("#gallery_modal").fadeOut(speed, function() {
				$(this).remove();
				
				if($("body").hasClass("body_gallery_modal")) {
					var scrollbar_width = JungleDB.Gallery.Utilities.getScrollbarWidth();
					
					$("body").removeClass("body_gallery_modal");
					
					if(scrollbar_width > 0) {
						$("body").css({'margin-right': "-="+ scrollbar_width +"px"});
					}
				}
			});
		}
	};
	
	
	JungleDB.Gallery.Binds = [
		// Catch URL changes and load media accordingly
		function() {
			$(window).bind('popstate', function(e) {
				if($("body").hasClass("JS_historyPushedState")) {
					var gallery_details_elem = $("#modal_gallery_details");
					
					try {
						var state = e.originalEvent.state;
						var image_details = state.image_details;
						
						if(!$(gallery_details_elem).length) {
							return false;
						}
						
						var current_media_id = $(gallery_details_elem).attr('data-media', ""+ image_details.id);
						
						JungleDB.Gallery.Actions.gallery_start();
						
						return;
					} catch(err) { console.log("thrown: "+ err); }
					
					if(JungleDB.Gallery.Utilities.gallery_exists()) {
						JungleDB.Gallery.Actions.gallery_close();
					}
				}
			});
		},
		
		// Start gallery event
		function() {
			$("body").on('click', ".gallery_start", function(e) {
				e.preventDefault();
				
				var change_current_media_id = $(this).attr('data-media') || false;
				
				if(change_current_media_id.length > 0) {
					$("#modal_gallery_details").attr('data-media', change_current_media_id);
				}
				
				JungleDB.Gallery.Actions.gallery_start(true);
			});
		},
		
		// window resize event
		function() {
			$(window).on('resize', function() {
				clearTimeout(JungleDB.pit.gallery_callback_resize);
				
				JungleDB.pit.gallery_callback_resize = setTimeout(JungleDB.Gallery.Actions.gallery_resize, 150);
			});
		},
		
		// Gallery close button click event
		function() {
			$("body").on('click', ".gallery_modal_button_close", function(e) {
				e.preventDefault();
				
				JungleDB.Gallery.Actions.gallery_close();
			});
		},
		
		/*
		// Flag media click event
		function() {
			$("body").on('click', ".gallery_modal_button_flag", function(e) {
				e.preventDefault();
				
				if(confirm("Are you sure you want to report this gallery to an administrator?")) {
					$.ajax({
						 'url': "/ajax/gallery_flag.php"
						,'type': "POST"
						,'data': {'id': gallery_details.id}
						,'complete': function() {
							alert("Thanks for reporting this!");
							
							window.location = "/";
						}
					});
				}
			});
		},
		*/
		
		// Close gallery modal if click event is outside of modal
		function() {
			$("body").on('click', "#gallery_modal", function(e) {
				if(e.target !== this) {
					return;
				}
				
				e.preventDefault();
				
				JungleDB.Gallery.Actions.gallery_close();
			});
		},
		
		// previous media click event
		function() {
			$("body").on('click', ".modal_gallery_next", function(e) {
				e.preventDefault();
				
				JungleDB.Gallery.Actions.gallery_next();
			});
		},
		
		// next media click event
		function() {
			$("body").on('click', ".modal_gallery_prev", function(e) {
				e.preventDefault();
				
				JungleDB.Gallery.Actions.gallery_prev();
			});
		},
		
		// ESC/LEFT/RIGHT keyup events
		function() {
			$(document).keyup(function(e) {
				if(JungleDB.Gallery.Utilities.gallery_exists()) {
					if(e.which === KEYCODE_ESC) {
						e.preventDefault();
						
						JungleDB.Gallery.Actions.gallery_close();
						
						return;
					} else if(e.which === KEYCODE_LEFT) {
						e.preventDefault();
						
						JungleDB.Gallery.Actions.gallery_prev();
						
						return;
					} else if(e.which === KEYCODE_RIGHT) {
						e.preventDefault();
						
						JungleDB.Gallery.Actions.gallery_next();
						
						return;
					}
				}
			});
		}
	];
	
	