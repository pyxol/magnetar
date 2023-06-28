<!DOCTYPE html>
<html xml:lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml">
<head>
	<title><?=api::tpl()->site_title;?></title>
	
	<link rel="shortcut icon" href="/static/etc/favicon.ico" type="image/x-icon" />
	<link href="/static/css/jungle.css" rel="stylesheet" type="text/css" />
	<link href="/static/css/fonts/OpenSans/style.css" rel="stylesheet" type="text/css" />
	
	<meta charset="utf-8" />
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	
	<?php api::hook()->run('site_head'); ?>
</head>

<body>
	<?php api::hook()->run('site_body'); ?>
	
	<div id="header" class="<?=api::tpl()->body_class;?>">
		<h1 id="logo"><a href="/" class="ajax" title="JungleDB - an entity graph of life, knowledge, and connections within">JungleDB</a></h1>
		
		<div id="header_search">
			<form method="get" action="/search.php" id="header_search_form">
				<label for="header_search_query">Search: </label>
				<input type="text" name="query" value="<?=api::request()->query;?>" id="header_search_query" autocomplete="off" />
				<input type="submit" value="Search" />
			</form>
		</div>
		
		<div id="user_account_control">
			<a href="/user/don/" title="My Profile" class="uac_avatar ajax"><img src="/static/images/tmp/uac_avatar.jpg" class="uac_avatar_img" /></a>
			<div class="uac_identity">
				<a href="/user/don/" title="My Profile" class="uac_name ajax">Don Wilson</a> <a href="/account/" title="Manage your session" class="uac_etc">Edit Session</a>
			</div>
		</div>
	</div>
	
	<div id="app_container">