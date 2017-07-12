<?php
	$entity_type_id = (!empty($site['args']['e_type'])?$site['args']['e_type']:false);
	
	if(false === $entity_type_id) {
		// prefer to show 404 instead of redirect
		//redirect( site_url("/") );
		
		print "no entity type specified.";
		
		die;
	}
	
	$entity_type = $db->get_row("SELECT * FROM `entity_type` WHERE `id` = '". $db->escape($entity_type_id) ."'");
	
	if(empty($entity_type['id'])) {
		// prefer to show 404 instead of redirect
		//redirect("/");
		
		print "entity type not found.";
		
		die;
	}
	
	prepend_site_title($entity_type['title']);
	
	get_header();
	
	
	$memcache = new Memcache;
	$memcache->connect("127.0.0.1", 11211);
	
	$content_cache_key = "photo_wall_entity_type_". $entity_type['id'];
	
	if(!empty($_REQUEST['_force'])) {
		$memcache->delete(md5( $content_cache_key ));
	}
	
	$images = $memcache->get(md5( $content_cache_key ));
	
	if(empty($images)) {
		$images = array();
		$num_images = $num_images_needed = 18;
		$img_i = 0;
		
		do {
			// latest media:
			//$db_images = $db->get_results("
			//	SELECT
			//		media.id as `media_id`, media.type as `media_type`, media.server_id as `media_server`, media.folder as `media_folder`, media.file as `media_file`, media.hash as `media_hash`, media.file_details as `media_details`, media.status as `media_status`,
			//		entity.id as `entity_id`, entity.type_id as `entity_type`, entity.title as `entity_title`, entity.excerpt as `entity_excerpt`
			//	FROM
			//		`entity_xref_media`
			//			LEFT JOIN `media` ON media.id = entity_xref_media.media_id
			//			LEFT JOIN `entity` ON entity.id = entity_xref_media.entity_id
			//	WHERE entity.type_id = '15'
			//	GROUP BY entity_xref_media.entity_id
			//	ORDER BY RAND()
			//	LIMIT 0, ". $db->escape($num_images_needed) ."
			//");
			
			$db_images = $db->get_results("
				SELECT
					media.id as `media_id`, media.type as `media_type`, media.server_id as `media_server`, media.folder as `media_folder`, media.file as `media_file`, media.hash as `media_hash`, media.file_details as `media_details`, media.status as `media_status`,
					entity.id as `entity_id`, entity.type_id as `entity_type`, entity.title as `entity_title`, entity.excerpt as `entity_excerpt`
				FROM
					`entity`
						LEFT JOIN `media` ON media.id = entity.media_id
				WHERE
					entity.type_id = '". $entity_type['id'] ."'
					AND entity.media_id IS NOT NULL
				ORDER BY RAND()
				LIMIT 0, ". $db->escape($num_images_needed) ."
			");
			
			if(empty($db_images)) {
				break;
			}
			
			foreach($db_images as $db_image) {
				if($db_image['media_status'] != 2) {
					continue;
				}
				
				if(empty($db_image['media_file'])) {
					continue;
				}
				
				$images[ $img_i++ ] = $db_image;
				$num_images_needed--;
				
				if(count($images) >= $num_images) {
					break 2;
				}
			}
		} while(count($images) < $num_images);
		
		unset($db_images, $img_i, $num_images, $num_images_needed);
		
		$memcache->set(md5( $content_cache_key ), $images, MEMCACHE_COMPRESSED, 900);
	}
?>
	
	<div id="app" class="center-sided">
		<div class="entity_type_header">
			<h1 class="page_title eth_title">Entity Type: <a href="<?=site_url("/type/". $entity_type['id'] ."/");?>" title="<?=esc_attr($entity_type['title']);?>"><?=esc_html($entity_type['title']);?></a></h1>
		</div>
		
		<div id="photo_wall">
			<?php if(!empty($images)): $img = 0; ?>
			<div class="pw_row pw_row_large">
				<div class="pw_large">
					<a href="<?=esc_attr("/j/". $images[$img]['entity_id'] ."/");?>" title="<?=esc_attr($images[$img]['entity_title']);?>" class="pw_anchor ajax">
						<img src="<?=esc_attr(mediaThumbUrl($images[$img]['media_folder'], $images[$img]['media_file'], "660x340"));?>" alt="<?=esc_attr($images[$img]['entity_title']);?>" class="pw_img" />
						<div class="pw_context"><strong><?=esc_html($images[$img]['entity_title']);?></strong></div>
					</a><?php $img++; ?>
				</div>
				<div class="pw_side">
					<a href="<?=esc_attr("/j/". $images[$img]['entity_id'] ."/");?>" title="<?=esc_attr($images[$img]['entity_title']);?>" class="pw_anchor ajax">
						<img src="<?=esc_attr(mediaThumbUrl($images[$img]['media_folder'], $images[$img]['media_file'], "320x150"));?>" alt="<?=esc_attr($images[$img]['entity_title']);?>" class="pw_img" />
						<div class="pw_context"><strong><?=esc_html($images[$img]['entity_title']);?></strong></div>
					</a><?php $img++; ?>
					<a href="<?=esc_attr("/j/". $images[$img]['entity_id'] ."/");?>" title="<?=esc_attr($images[$img]['entity_title']);?>" class="pw_anchor ajax">
						<img src="<?=esc_attr(mediaThumbUrl($images[$img]['media_folder'], $images[$img]['media_file'], "320x150"));?>" alt="<?=esc_attr($images[$img]['entity_title']);?>" class="pw_img" />
						<div class="pw_context"><strong><?=esc_html($images[$img]['entity_title']);?></strong></div>
					</a><?php $img++; ?>
				</div>
			</div>
			<div class="pw_row pw_row_single">
				<a href="<?=esc_attr("/j/". $images[$img]['entity_id'] ."/");?>" title="<?=esc_attr($images[$img]['entity_title']);?>" class="pw_anchor ajax">
					<img src="<?=esc_attr(mediaThumbUrl($images[$img]['media_folder'], $images[$img]['media_file'], "320x150"));?>" alt="<?=esc_attr($images[$img]['entity_title']);?>" class="pw_img" />
					<div class="pw_context"><strong><?=esc_html($images[$img]['entity_title']);?></strong></div>
				</a><?php $img++; ?>
				<a href="<?=esc_attr("/j/". $images[$img]['entity_id'] ."/");?>" title="<?=esc_attr($images[$img]['entity_title']);?>" class="pw_anchor ajax">
					<img src="<?=esc_attr(mediaThumbUrl($images[$img]['media_folder'], $images[$img]['media_file'], "320x150"));?>" alt="<?=esc_attr($images[$img]['entity_title']);?>" class="pw_img" />
					<div class="pw_context"><strong><?=esc_html($images[$img]['entity_title']);?></strong></div>
				</a><?php $img++; ?>
				<a href="<?=esc_attr("/j/". $images[$img]['entity_id'] ."/");?>" title="<?=esc_attr($images[$img]['entity_title']);?>" class="pw_anchor ajax">
					<img src="<?=esc_attr(mediaThumbUrl($images[$img]['media_folder'], $images[$img]['media_file'], "320x150"));?>" alt="<?=esc_attr($images[$img]['entity_title']);?>" class="pw_img" />
					<div class="pw_context"><strong><?=esc_html($images[$img]['entity_title']);?></strong></div>
				</a><?php $img++; ?>
			</div>
			<div class="pw_row pw_row_large pw_row_large_right">
				<div class="pw_large">
					<a href="<?=esc_attr("/j/". $images[$img]['entity_id'] ."/");?>" title="<?=esc_attr($images[$img]['entity_title']);?>" class="pw_anchor ajax">
						<img src="<?=esc_attr(mediaThumbUrl($images[$img]['media_folder'], $images[$img]['media_file'], "660x340"));?>" alt="<?=esc_attr($images[$img]['entity_title']);?>" class="pw_img" />
						<div class="pw_context"><strong><?=esc_html($images[$img]['entity_title']);?></strong></div>
					</a><?php $img++; ?>
				</div>
				<div class="pw_side">
					<a href="<?=esc_attr("/j/". $images[$img]['entity_id'] ."/");?>" title="<?=esc_attr($images[$img]['entity_title']);?>" class="pw_anchor ajax">
						<img src="<?=esc_attr(mediaThumbUrl($images[$img]['media_folder'], $images[$img]['media_file'], "320x150"));?>" alt="<?=esc_attr($images[$img]['entity_title']);?>" class="pw_img" />
						<div class="pw_context"><strong><?=esc_html($images[$img]['entity_title']);?></strong></div>
					</a><?php $img++; ?>
					<a href="<?=esc_attr("/j/". $images[$img]['entity_id'] ."/");?>" title="<?=esc_attr($images[$img]['entity_title']);?>" class="pw_anchor ajax">
						<img src="<?=esc_attr(mediaThumbUrl($images[$img]['media_folder'], $images[$img]['media_file'], "320x150"));?>" alt="<?=esc_attr($images[$img]['entity_title']);?>" class="pw_img" />
						<div class="pw_context"><strong><?=esc_html($images[$img]['entity_title']);?></strong></div>
					</a><?php $img++; ?>
				</div>
			</div>
			<div class="pw_row pw_row_single">
				<a href="<?=esc_attr("/j/". $images[$img]['entity_id'] ."/");?>" title="<?=esc_attr($images[$img]['entity_title']);?>" class="pw_anchor ajax">
					<img src="<?=esc_attr(mediaThumbUrl($images[$img]['media_folder'], $images[$img]['media_file'], "320x150"));?>" alt="<?=esc_attr($images[$img]['entity_title']);?>" class="pw_img" />
					<div class="pw_context"><strong><?=esc_html($images[$img]['entity_title']);?></strong></div>
				</a><?php $img++; ?>
				<a href="<?=esc_attr("/j/". $images[$img]['entity_id'] ."/");?>" title="<?=esc_attr($images[$img]['entity_title']);?>" class="pw_anchor ajax">
					<img src="<?=esc_attr(mediaThumbUrl($images[$img]['media_folder'], $images[$img]['media_file'], "320x150"));?>" alt="<?=esc_attr($images[$img]['entity_title']);?>" class="pw_img" />
					<div class="pw_context"><strong><?=esc_html($images[$img]['entity_title']);?></strong></div>
				</a><?php $img++; ?>
				<a href="<?=esc_attr("/j/". $images[$img]['entity_id'] ."/");?>" title="<?=esc_attr($images[$img]['entity_title']);?>" class="pw_anchor ajax">
					<img src="<?=esc_attr(mediaThumbUrl($images[$img]['media_folder'], $images[$img]['media_file'], "320x150"));?>" alt="<?=esc_attr($images[$img]['entity_title']);?>" class="pw_img" />
					<div class="pw_context"><strong><?=esc_html($images[$img]['entity_title']);?></strong></div>
				</a><?php $img++; ?>
			</div>
			<div class="pw_row pw_row_large">
				<div class="pw_large">
					<a href="<?=esc_attr("/j/". $images[$img]['entity_id'] ."/");?>" title="<?=esc_attr($images[$img]['entity_title']);?>" class="pw_anchor ajax">
						<img src="<?=esc_attr(mediaThumbUrl($images[$img]['media_folder'], $images[$img]['media_file'], "660x340"));?>" alt="<?=esc_attr($images[$img]['entity_title']);?>" class="pw_img" />
						<div class="pw_context"><strong><?=esc_html($images[$img]['entity_title']);?></strong></div>
					</a><?php $img++; ?>
				</div>
				<div class="pw_side">
					<a href="<?=esc_attr("/j/". $images[$img]['entity_id'] ."/");?>" title="<?=esc_attr($images[$img]['entity_title']);?>" class="pw_anchor ajax">
						<img src="<?=esc_attr(mediaThumbUrl($images[$img]['media_folder'], $images[$img]['media_file'], "320x150"));?>" alt="<?=esc_attr($images[$img]['entity_title']);?>" class="pw_img" />
						<div class="pw_context"><strong><?=esc_html($images[$img]['entity_title']);?></strong></div>
					</a><?php $img++; ?>
					<a href="<?=esc_attr("/j/". $images[$img]['entity_id'] ."/");?>" title="<?=esc_attr($images[$img]['entity_title']);?>" class="pw_anchor ajax">
						<img src="<?=esc_attr(mediaThumbUrl($images[$img]['media_folder'], $images[$img]['media_file'], "320x150"));?>" alt="<?=esc_attr($images[$img]['entity_title']);?>" class="pw_img" />
						<div class="pw_context"><strong><?=esc_html($images[$img]['entity_title']);?></strong></div>
					</a><?php $img++; ?>
				</div>
			</div>
			<div class="pw_row pw_row_single">
				<a href="<?=esc_attr("/j/". $images[$img]['entity_id'] ."/");?>" title="<?=esc_attr($images[$img]['entity_title']);?>" class="pw_anchor ajax">
					<img src="<?=esc_attr(mediaThumbUrl($images[$img]['media_folder'], $images[$img]['media_file'], "320x150"));?>" alt="<?=esc_attr($images[$img]['entity_title']);?>" class="pw_img" />
					<div class="pw_context"><strong><?=esc_html($images[$img]['entity_title']);?></strong></div>
				</a><?php $img++; ?>
				<a href="<?=esc_attr("/j/". $images[$img]['entity_id'] ."/");?>" title="<?=esc_attr($images[$img]['entity_title']);?>" class="pw_anchor ajax">
					<img src="<?=esc_attr(mediaThumbUrl($images[$img]['media_folder'], $images[$img]['media_file'], "320x150"));?>" alt="<?=esc_attr($images[$img]['entity_title']);?>" class="pw_img" />
					<div class="pw_context"><strong><?=esc_html($images[$img]['entity_title']);?></strong></div>
				</a><?php $img++; ?>
				<a href="<?=esc_attr("/j/". $images[$img]['entity_id'] ."/");?>" title="<?=esc_attr($images[$img]['entity_title']);?>" class="pw_anchor ajax">
					<img src="<?=esc_attr(mediaThumbUrl($images[$img]['media_folder'], $images[$img]['media_file'], "320x150"));?>" alt="<?=esc_attr($images[$img]['entity_title']);?>" class="pw_img" />
					<div class="pw_context"><strong><?=esc_html($images[$img]['entity_title']);?></strong></div>
				</a><?php $img++; ?>
			</div>
			<?php else: ?>
				No entities to show at this moment...
			<?php endif; ?>
		</div>
	</div>
	
<?php
	get_footer();