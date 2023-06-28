<?php api::tpl()->view('header'); ?>
	
	<?php
		function jungle_frontpageWall_side($entity_id) {
			?>
			<a href="<?=api::entity($entity_id)->getUrl();?>" title="<?=api::entity($entity_id)->getTitle();?>" class="pw_anchor ajax">
				<img src="<?=api::entity($entity_id)->getPrimaryMedia()->getThumb('320x150');?>" alt="<?=api::entity($entity_id)->getTitle();?>" class="pw_img" />
				<div class="pw_context"><strong><?=api::entity($entity_id)->getTitle();?></strong></div>
			</a>
			<?php
		}
		
		function jungle_frontpageWall_large($entity_id) {
			?>
			<a href="<?=api::entity($entity_id)->getUrl();?>" title="<?=api::entity($entity_id)->getTitle();?>" class="pw_anchor ajax">
				<img src="<?=api::entity($entity_id)->getPrimaryMedia()->getThumb('660x340');?>" alt="<?=api::entity($entity_id)->getTitle();?>" class="pw_img" />
				<div class="pw_context"><strong><?=api::entity($entity_id)->getTitle();?></strong></div>
			</a>
			<?php
		}
	?>
	
	<div id="app" class="center-sided">
		<div id="welcome">
			<div class="welcome_logo">JungleDB</div>
			<div class="welcome_suffix">An entity graph of life, knowledge, and connections within</div>
		</div>
		
		<div id="photo_wall">
			<?php if(!empty(api::tpl()->entities)): $i = 0; ?>
			<div class="pw_row pw_row_large">
				<div class="pw_large">
					<?php jungle_frontpageWall_large( api::tpl()->entities[ $i++ ] ); ?>
				</div>
				<div class="pw_side">
					<?php jungle_frontpageWall_side( api::tpl()->entities[ $i++ ] ); ?>
					<?php jungle_frontpageWall_side( api::tpl()->entities[ $i++ ] ); ?>
				</div>
			</div>
			<div class="pw_row pw_row_single">
				<?php jungle_frontpageWall_side( api::tpl()->entities[ $i++ ] ); ?>
				<?php jungle_frontpageWall_side( api::tpl()->entities[ $i++ ] ); ?>
				<?php jungle_frontpageWall_side( api::tpl()->entities[ $i++ ] ); ?>
			</div>
			<div class="pw_row pw_row_large pw_row_large_right">
				<div class="pw_large">
					<?php jungle_frontpageWall_large( api::tpl()->entities[ $i++ ] ); ?>
				</div>
				<div class="pw_side">
					<?php jungle_frontpageWall_side( api::tpl()->entities[ $i++ ] ); ?>
					<?php jungle_frontpageWall_side( api::tpl()->entities[ $i++ ] ); ?>
				</div>
			</div>
			<div class="pw_row pw_row_single">
				<?php jungle_frontpageWall_side( api::tpl()->entities[ $i++ ] ); ?>
				<?php jungle_frontpageWall_side( api::tpl()->entities[ $i++ ] ); ?>
				<?php jungle_frontpageWall_side( api::tpl()->entities[ $i++ ] ); ?>
			</div>
			<div class="pw_row pw_row_large">
				<div class="pw_large">
					<?php jungle_frontpageWall_large( api::tpl()->entities[ $i++ ] ); ?>
				</div>
				<div class="pw_side">
					<?php jungle_frontpageWall_side( api::tpl()->entities[ $i++ ] ); ?>
					<?php jungle_frontpageWall_side( api::tpl()->entities[ $i++ ] ); ?>
				</div>
			</div>
			<div class="pw_row pw_row_single">
				<?php jungle_frontpageWall_side( api::tpl()->entities[ $i++ ] ); ?>
				<?php jungle_frontpageWall_side( api::tpl()->entities[ $i++ ] ); ?>
				<?php jungle_frontpageWall_side( api::tpl()->entities[ $i++ ] ); ?>
			</div>
			<?php else: ?>
				No entities to show at this moment...
			<?php endif; ?>
		</div>
	</div>
	
<?php api::tpl()->view('footer'); ?>