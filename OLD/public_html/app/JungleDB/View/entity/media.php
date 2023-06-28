<?php
	api::hook()->add('site_head', function() {
		?>
		<link href="/static/css/jungle.gallery.css" rel="stylesheet" type="text/css" />
		<?php
	});
	
	api::tpl()->view('header');
?>
	
	<?php api::tpl()->view('entity/wrapper_header'); ?>
		
		<h2 class="section_title">Media</h2>

		<?php if(!empty(api::tpl()->media)): ?>
			<div class="entity_media_table">
			<?php
				foreach(api::tpl()->media as $media_id):
					$media_details = api::media( $media_id )->getFileDetails();
					
					if(!empty($media_details)) {
						$media_details = json_decode($media_details, true);
						
						if("image" === api::media( $media_id )->getType()) {
							$media_details['dimensions'] = array(
								'width'		=> $media_details[0],
								'height'	=> $media_details[1],
							);
						}
					}
				?>
				<div id="entity_media_table_item_<?=api::media( $media_id )->getId();?>" class="entity_media_table_item" data-media="<?=api::media( $media_id )->getId();?>">
					<?php if(api::media( $media_id )->getType() == "image"): ?>
						<a href="<?=api::media( $media_id )->getThumb("o");?>" target="_blank" class="entity_media_table_anchor modal_gallery" id="entity_media_table_anchor_<?=api::media( $media_id )->getId();?>" data-entity="<?=api::tpl()->entity->getID();?>" data-media="<?=api::media( $media_id )->getId();?>">
							<img src="<?=api::media( $media_id )->getThumb("n");?>" alt="<?=api::tpl()->entity->getTitle();?>" class="entity_media_table_image" id="entity_media_table_image_<?=api::media( $media_id )->getId();?>" />
							<div class="entity_media_table_settings_icon" data-media="<?=api::media( $media_id )->getId();?>">Settings</div>
						</a>
						
						<div id="entity_media_table_settings_pane_<?=api::media( $media_id )->getId();?>" class="entity_media_table_settings_pane">
							<strong><?=api::media( $media_id )->getFile();?></strong><br /><br />
							<a href="#" class="media_make_primary" data-entity="<?=api::tpl()->entity->getID();?>" data-media="<?=api::media( $media_id )->getId();?>">Make Primary</a><br /><br />
							Filesize: <strong><?=api::media( $media_id )->getFileSize();?></strong><br />
							Metadata: <a href="/dev/media_meta/view.php?id=<?=api::media( $media_id )->getId();?>">View Meta</a><br /><br />
							<a href="#" class="media_detatch" data-entity="<?=api::tpl()->entity->getID();?>" data-media="<?=api::media( $media_id )->getId();?>">Detatch from Entity</a><br /><br />
							Type: <strong><?=api::media( $media_id )->getFileMimeType();?>/<?=api::media( $media_id )->getFileMimeSubtype();?></strong><br />
							<?php if(!empty($media_details['dimensions'])): ?>
								Width: <strong><?=$media_details['dimensions']['width'];?>px</strong><br />
								Height: <strong><?=$media_details['dimensions']['height'];?>px</strong><br /><br />
							<?php endif; ?>
							<a href="#" class="settings_pane_hide" data-media="<?=api::media( $media_id )->getId();?>">Hide Settings</a>
						</div>
					<?php else: ?>
						<div class="entity_media_table_content_type">[<?=api::media( $media_id )->getType();?>]</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
			</div>
			
			<div id="modal_gallery_details" data-entity="<?=api::tpl()->entity->getID();?>" style="display: none;"></div>
			
			<?php api::hook()->add('site_foot', function() {
				?>
				<script type="text/javascript">
					var gallery_details = false;
					var KEYCODE_ESC = 27;
					var KEYCODE_LEFT = 37;
					var KEYCODE_RIGHT = 39;
					var KEYCODE_ENTER = 13;
					
					var user_logged_in = false;
					
					var modal_gallery_resize_callback;
					
					;jQuery(document).ready(function($) {
						$("body").on('click', ".entity_media_table_settings_icon", function(e) {
							e.preventDefault();
							e.stopPropagation();
							
							var media_id = $(this).attr('data-media') || false;
							
							if(!media_id) {
								return;
							}
							
							var image_height = $("#entity_media_table_image_"+ media_id).height();
							var settings_pane_padding = (($("#entity_media_table_settings_pane_"+ media_id).outerHeight() - $("#entity_media_table_settings_pane_"+ media_id).height()) / 2);
							
							$("#entity_media_table_settings_pane_"+ media_id).show().css({
								'opacity': 0,
								'height': (image_height - settings_pane_padding) +"px",
								'margin-top': 0
							}).animate({
								'opacity': 1,
								'margin-top': "-"+ (image_height + settings_pane_padding) +"px"
							}, {'duration': 300});
						});
						
						$("body").on('click', ".settings_pane_hide", function(e) {
							e.preventDefault();
							
							$(this).blur();
							
							var media_id = $(this).attr('data-media') || false;
							
							if(!media_id) {
								return;
							}
							
							var image_height = $("#entity_media_table_image_"+ media_id).height();
							$("#entity_media_table_settings_pane_"+ media_id).show().css({
								'opacity': 1
							}).animate({
								'opacity': 0,
								'margin-top': "0px"
							}, {
								'duration': 300,
								'complete': function() {
									$(this).hide();
								}
							});
						});
						
						$("body").on('click', ".media_make_primary", function(e) {
							e.preventDefault();
							
							var entity_id = $(this).attr('data-entity') || false;
							var media_id = $(this).attr('data-media') || false;
							
							if(!entity_id || !media_id) {
								return;
							}
							
							
							$.ajax({
								'url':		"/ajax/entity_media.php",
								'data':		{
									'do':			"set_primary",
									'media_id':		media_id,
									'entity_id':	entity_id
								},
								'method':	"POST",
								'cache':	false,
								'beforeSend':	function() {
									$("#entity_media_table_settings_pane_"+ media_id).find(".settings_pane_hide").click();
								},
								'success':	function(data) {
									if(!data.status) {
										return;
									}
									
									if(data.status === "error") {
										if(data.status_msg) {
											alert("Error: "+ data.status_msg);
										}
										
										return;
									}
									
									if(data.cargo.entity_id !== undefined && data.cargo.media.m !== undefined) {
										$("#entity_primary_photo_"+ data.cargo.entity_id).attr('src', data.cargo.media.m);
										
										var primary_photo_top = $("#entity_primary_photo_"+ data.cargo.entity_id).offset().top || 0;
										
										$("html, body").animate({
											'scrollTop': Math.max(0, (primary_photo_top - 20))
										}, {'duration': 500});
									}
								}
							});
						});
						
						$("body").on('click', ".modal_gallery", function(e) {
							e.preventDefault();
							
							var entity_id = $(this).attr('data-entity') || false;
							var media_id = $(this).attr('data-media') || false;
							
							if(!entity_id || !media_id) {
								JungleDB.Log.client(".modal_gallery#click entity_id or media_id is empty");
								
								return;
							}
							
							$("#modal_gallery_details").attr('data-media', media_id);
							
							JungleDB.Gallery.Actions.gallery_start();
						});
						
						JungleDB.Gallery.init();
						
					});
				</script>
				<script type="text/javascript" src="/static/js/jungle.gallery.js"></script>
				<?php
				});
			?>
		<?php else: ?>
			<i>This entity has no media attached.</i>
		<?php endif; ?>
		
	<?php api::tpl()->view('entity/wrapper_footer'); ?>

<?php api::tpl()->view('footer'); ?>