<?php api::tpl()->view('header'); ?>
	
	<div id="error_page">
		<div class="header">404</div>
		
		<div class="message">
			<?php if(api::tpl()->error): ?>
				<?=api::tpl()->error;?> <a href="/" title="JungleDB - Knowledge Graph">Click here</a> to return home.
			<?php else: ?>
				We couldn't find the page you were looking for, so <a href="/" title="JungleDB - Knowledge Graph">click here</a> to return home.
			<?php endif; ?>
		</div>
	</div>
	
<?php api::tpl()->view('footer'); ?>