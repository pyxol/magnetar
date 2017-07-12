		</div>
		</div>
		
		<div id="notes">
			<?php /*if(!empty($notes_entity_sections)): foreach($notes_entity_sections as $group_key => $group): ?>
			<?php if(!empty($group['title'])): ?><h3 class="notes_title"><?=esc_html($group['title']);?></h3><?php endif; ?>
			<ul class="menu entity_notes_menu">
				<?php foreach($group['sections'] as $es_key => $es_value): ?>
				<li<?=(($es_key === api::tpl()->entity_section)?" class=\"active\"":"");?>><a href="<?=esc_attr($es_value['url']);?>" title="<?=esc_attr(api::tpl()->entity->getTitle());?>: <?=esc_attr($es_value['title']);?>"<?=(!empty($es_value['target'])?" target=\"". esc_attr($es_value['target']) ."\"":"");?> class="ajax"><?=esc_html($es_value['title']);?></a></li>
				<?php endforeach; ?>
			</ul>
			<?php endforeach; endif;*/ ?>
		</div>
	</div>