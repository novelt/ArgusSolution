<?php

	// Set the asked language
	// If langauge is empty, use the defaults messages
	function language_set($language) {
		global $config;
		// Domain to use (name for .po/.mo files)
		$GetTextDomain='messages';
		// Répertoire racine contenant les traductions (relatif)
		$GetTextLocalePath=$config['path_locale'];
		// Chosse the language
		putenv("LANGUAGE=".$language);
		// Set the (absolute or relative) path for the domain
		// Only the .mo compiled files are used (and cached)
		bindtextdomain($GetTextDomain, $GetTextLocalePath);
		// Message encoding in UTF-8
		bind_textdomain_codeset($GetTextDomain, "utf8");
		// Set the domain name to use
		textdomain($GetTextDomain);
	}

?>