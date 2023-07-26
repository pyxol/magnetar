<?php
	display_tpl('header', [
		'title' => $this->title ?? 'Frontpage',
	]);
?>
	
	<div class='h1'>503 Server Error</div>
	
	<p>We experienced a server error with your request.</p>
	
	<?php if($this->message): ?>
		<p class="text-muted"><?=esc_html($this->message);?></p>
	<?php endif; ?>
	
<?php
	display_tpl('footer');