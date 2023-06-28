<?php api::tpl()->view('header'); ?>
		
	<?php api::tpl()->view('entity/wrapper_header'); ?>
		
		<?php
			if(is_array(api::tpl()->media) && (count(api::tpl()->media) > 1)):
				$i = 0;
				$max_i = 15;
			?>
				<a href="<?=api::tpl()->entity->getUrl('media');?>" class="entity_media_preview ajax" title="Photos of <?=api::tpl()->entity->getTitle();?>">
					<div class="entity_media_preview_text">View Media</div>
					<div class="entity_media_preview_images">
					<?php
						foreach(api::tpl()->media as $media_id) {
							if(api::tpl()->entity->getPrimaryMedia()->getId() === api::media($media_id)->getId()) { continue; }
							if(++$i > $max_i) { break; }
							?><img src="<?=api::media($media_id)->getThumb("k");?>" class="entity_media_preview_image ajax" alt="<?=trim(api::media($media_id)->getDescription() . " - ". api::tpl()->entity->getTitle(), " -");?>" /><?php
						}
					?>
					</div>
				</a>
		<?php
			endif;
		?>
		
	<?php api::tpl()->view('entity/wrapper_footer'); ?>
	
	<script type="text/javascript">
		;jQuery(document).ready(function(e) {
			$("body").on('click', ".add_entity_meta", function(e) {
				e.preventDefault();
				
				//if(!confirm('Are you sure?')) {
				//	return false;
				//}
				
				var meta_entity_id = $(this).attr('data-entity_id') || false;
				var meta_key = $(this).attr('data-key') || false;
				var meta_value = $(this).attr('data-value') || false;
				
				if(!meta_entity_id || !meta_key || !meta_value) {
					console.log("Link is malformed.");
					
					return;
				}
				
				var action = "insert";
				
				$.ajax({
					'url': "/ajax/entity_meta.php",
					'type': "GET",
					'cache': false,
					'data': {'action': action, 'meta_entity_id': meta_entity_id, 'meta_key': meta_key, 'meta_value': meta_value},
					'success': function(data) {
						if(data.status === "error") {
							alert("Error: "+ data.status_msg);
							
							return;
						}
						
						window.location = window.location;
					}
				});
			});
		});
	</script>
	
<?php api::tpl()->view('footer'); ?>