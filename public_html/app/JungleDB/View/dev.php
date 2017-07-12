<?php
	prepend_site_title("Welcome to the Jungle");
	
	get_header();
?>
	
	<div id="container">
		<ul class="large_list">
			<li><a href="//admin.jungledb.dev/">admin area</a></li>
			<li><a href="/dev/wiki.php">wikipedia search</a></li>
			<li><a href="/wiki_linker/">wiki linker</a></li>
			<li><a href="/entertainment/movie/2-fast-2-furious/">2 Fast 2 Furious</a> - /entertainment/movie/2-fast-2-furious/</li>
			<li><a href="/person/james-brown/">James Brown</a> - /person/james-brown/</li>
			<li>&nbsp;</li>
			<li><a href="/j/movie/2-fast-2-furious/">2 Fast 2 Furious</a> - /j/movie/2-fast-2-furious/</li>
			<li><a href="/j/person/james-brown/">James Brown</a> - /j/person/james-brown/</li>
		</ul>
	</div>
	
	<style type="text/css">
		.large_list { }
			.large_list li { margin-bottom: 5px; }
				.large_list li, .large_list li a { font: normal normal normal 20px/24px Verdana; color: #505050; }
					.large_list li a { font-weight: bold; color: #3366CC; text-decoration: none; }
						.large_list li a:hover { text-decoration: underline; }
	</style>
	
	<script type="text/javascript"><?php ob_start(); ?>
		;jQuery(document).ready(function($) {
			
			// focus on the search bar first thing
			$(window).load(function() { $("#frontpage_search_query").focus(); });
			
		});
	<?php queue_js_inline(ob_get_contents()); ob_end_clean(); ?></script>
	
<?php
	get_footer();