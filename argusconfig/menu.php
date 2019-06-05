<?php
	require_once ("./tools/web_template.php");
	HttpHeader();
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>Administration menu</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link type="text/css" href="./ressources/style.css" rel="stylesheet">
	</head>
	<body class="menu">
		<div class="menu">
			<img class="logo" src="./ressources/who.png">
		<?php
			require_once ("./config/globals.php");
			require_once ("./config/webpages.php");
			echo('<div class="version">'._("Web Administration Tool").'<br>'.$config["ses_version"].'</div>');
			foreach($web_pages as $category=>$pages) {
				echo('<div class="menu_category">'.htmlspecialchars($category).'</div>');
				foreach($pages as $id=>$page) {
					echo('<div class="menu_title"><a href="'.$page['URL'].'" target="contents">'.htmlspecialchars($page['Title']).'</a></div>');
				}
			}
		?>
		</div>
	</body>
</html>