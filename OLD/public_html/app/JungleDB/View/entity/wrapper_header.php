	
	<div id="app" class="three-sided app__entity_view">
		<div id="facade">
			<div class="primary_photo">
				<a href="<?=api::tpl()->entity->getUrl();?>" title="<?=api::tpl()->entity->getTitle();?> [<?=api::tpl()->entity->getType()->getTitle();?>]" class="ajax"><img src="<?=(api::tpl()->entity->getPrimaryMedia()->getId()?api::tpl()->entity->getPrimaryMedia()->getThumb("200x"):"/static/images/no_thumb.png");?>" class="primary_photo" id="entity_primary_photo_<?=api::tpl()->entity->getId();?>" /></a>
			</div>
			
			<ul class="menu entity_section_menu">
				<li class="active"><a href="<?=api::tpl()->entity->getUrl();?>" title="<?=api::tpl()->entity->getTitle();?>">Index</a></li>
				<li><a href="<?=api::tpl()->entity->getUrl('update');?>" title="<?=api::tpl()->entity->getTitle();?>: Update Info">Update Info</a></li>
				<li><a href="<?=api::tpl()->entity->getUrl('media');?>" title="<?=api::tpl()->entity->getTitle();?>: Media">Media</a></li>
				<li><a href="<?=api::tpl()->entity->getUrl('external-ids');?>" title="<?=api::tpl()->entity->getTitle();?>: External Links">External Links</a></li>
				<li><a href="//admin.jungledb.dev/entity_debug.php?id=<?=api::tpl()->entity->getId();?>" title="<?=api::tpl()->entity->getTitle();?>: Debug in Admin" target="_blank">Debug</a></li>
				<li><a href="//admin.jungledb.dev/entity.php?id=<?=api::tpl()->entity->getId();?>" title="<?=api::tpl()->entity->getTitle();?>: View in Admin" target="_blank">Admin Area</a></li>
			</ul>
		</div>
		
		<div id="main">
			<h1 class="page_title"><a href="<?=api::tpl()->entity->getUrl();?>" title="<?=api::tpl()->entity->getTitle();?> [<?=strtolower(api::tpl()->entity->getType()->getTitle());?>]" class="ajax"><?=api::tpl()->entity->getTitle();?></a> <span class="entity_header_details"></span></h1>
			<div class="page_breadcrumb"><?php /*$i = 0; foreach(api::tpl()->entity_type_parents as $ent_typ_par): if($i++ > 0) { print " &rsaquo; "; } ?><a href="/type/<?=$ent_typ_par['id'];?>/" class="ajax"><?=$ent_typ_par['title'];?></a><?php endforeach;*/ ?></div>
			
			<div id="main_wrapper">