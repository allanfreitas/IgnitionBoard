<!DOCTYPE html>
<html lang="en" dir="ltr">
	<head>
		<!-- Stop! Meta-time! -->
		<meta http-equiv="charset" content="utf-8" />
		<meta http-equiv="content-type" content="text/html; charset=utf-8">
		<meta http-equiv="X-UA-Compatible" content="chrome=1" />
		<meta name="author" content="IgniteBB Forum Software" />
		<meta name="language" content="en" />
		<meta http-equiv="Accept-Encoding" content="gzip,deflate" />
		<meta http-equiv="Content-Encoding" content="gzip" />
		<!-- Title time -->
		<title>{BOARD_TITLE} {BOARD_PAGE_NAME}</title>
		<!-- CSS time -->
		<link rel="stylesheet" type="text/css" media="all" href="{BOARD_THEME_CSS}" />
		<!-- JS time -->
		{BOARD_THEME_JS}
		<script type="text/javascript" src="{BOARD_THEME_JS.URL}"></script>
		{/BOARD_THEME_JS}
	</head>
	<body>
		<div id="board-frame">
			<div id="board-header">
				<h1><img src="{BOARD_THEME_IMG}logo/header.png" alt="{BOARD_VENDOR_TITLE}" />{BOARD_TITLE} {BOARD_PAGE_NAME}</h1>
				<div id="board-header-userinfo">
					<img src="{BOARD_THEME_IMG}logo/logo64.png" alt="User Avatar Test" />
					<div>						
						You are logged in as <a href="<?=site_url('user/view/');?>">{USER_NAME}</a>
					</div>
					<div>						
						<a class="emphasis" href="<?=site_url('messages/');?>"><img src="{BOARD_THEME_IMG}icons/16x16/pm.png" />0 Unread PM's</a>
						<a class="emphasis" href="<?=site_url('unread/');?>"><img src="{BOARD_THEME_IMG}icons/16x16/post.png" />0 Unread Posts</a>
					</div>
					<div>						
						{FRIENDS_ONLI} {GROUP_ONLI}
					</div>
				</div>
			</div>
			<div id="board-navigation">
				<ul id="board-navigation-location">
					<li><a href="<?=site_url('home/');?>">Board Index</a></li>
					<li>Category: <a href="<?=site_url('category/4/');?>">Germany</a></li>
					<li>Board: <a href="<?=site_url('board/1/');?>">Lol</a></li>
					<li>Topic: <a href="<?=site_url('topic/106/');?>">Penis</a></li>
				</ul>
				<ul id="board-navigation-pages">
					<li><a href="<?=site_url('home/');?>">Home</a></li>
					<li><a href="<?=site_url('search/');?>">Search</a></li>
					<li><a href="<?=site_url('members/');?>">Members</a></li>
					<li><a href="<?=site_url('rules/');?>">Rules</a></li>
				</ul>
			</div>
			<div id="board-content">
