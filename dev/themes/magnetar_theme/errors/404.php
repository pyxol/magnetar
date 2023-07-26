<?php
	display_tpl('header', [
		'title' => $this->title ?? 'Frontpage',
	]);
?>
	
	<div class='h1'>404 Page Not Found</div>
	
	<p>The requested page could not be found.</p>
	
	<?php if($this->message): ?>
		<p class="text-muted"><?=esc_html($this->message);?></p>
	<?php endif; ?>
	
<?php
	display_tpl('footer');