<?php
	require_once ("./tools/web_template.php");
	WebHeader(_("Manual run of the scheduled tasks"));
	echo('<p>'._("Output of runned tasks:").'</p>');
	echo('<pre class="raw_output">');
	require_once ("action_run_tasks.php");
	echo('</pre>');
	WebFooter();
?>