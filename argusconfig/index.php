<?php
	require_once ("./tools/web_template.php");
	HttpHeader();
	$URL=GetStringParameter("load_url","page_welcome.php");
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"
   "http://www.w3.org/TR/html4/frameset.dtd">
<html>
	<head><title><?php echo(_("WHO/SES - Web administration interface")); ?></title></head>
	<frameset cols="250px,*">
		<frame src="menu.php" name="menu" scrolling="auto" frameborder="1" marginwidth="0" marginheight="0">
		<frame src="<?php echo($URL); ?>" name="contents" scrolling="auto" frameborder="1" marginwidth="0" marginheight="0">
	</frameset>
</html>
