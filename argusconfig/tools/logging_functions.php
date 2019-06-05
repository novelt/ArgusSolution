<?php

    /**
     * Log message in a specific sub folder
     *
     * @param $prefix
     * @param $message
     */
    function LogGatewayMessage ($prefix, $message) {
        writeLog ($prefix, $message, "gateways", false, false);
    }

	// Append message to log file (choosen by prefix)
	function LogMessage	($prefix, $message)	{
        writeLog ($prefix, $message, null, true, true);
	}

	function writeLog ($prefix, $message, $path=null, $dateTime=true, $returnChars=true) {
        global $config;
        $filename=$config["path_logs"];

        if (!empty ($path)) {
            $filename.= $path.DIRECTORY_SEPARATOR ;
        }

        $filename.= $prefix.'-'.date("Y-m-d").'.txt';
        $fh=fopen($filename, 'a') or die(sprintf(_("Impossible to write log in file [%s]"), $filename));

        ($dateTime == true ? $logEntry = date("Y-m-d H:i:s")." " : $logEntry = "");
        $logEntry .= $message;
        ($returnChars == true ? $logEntry.= "\r\n" : null);

        fwrite($fh, $logEntry);
        fclose($fh);
    }
	
	// Log an error and die (end of process)
	function LogErrorAndDie($Message,$Function,$Line,$File, $die=true) {
		global $config;
		// formatting
		$MSG="";
		$MSG.=_("Fatal error from the application")." (".$config["ses_version"].")\r\n";
		$MSG.="Function : ".$Function."\r\n";
		$MSG.="Line     : ".$Line."\r\n";
		$MSG.="File     : ".$File."\r\n";
		$MSG.="Message  : ".$Message;
		// Ecriture fichier
		LogMessage('errors',$MSG);
		// Web display and die
        if ($die) {
            die("<div style=\"border:5px solid red;padding:5px;color:#800000;font-family:monospace;\">" . str_replace("\r\n", "<br>", $MSG) . "</div>");
        } else {
            throw new Exception($Message);
        }
	}
	
	// Show a message in HTML using PRE
	function DebugHTML($Message) {
		echo('<pre class="debug_html">'.htmlspecialchars($Message).'</pre>');
	}
	// Show a dump of the PHP variable
	function DebugDump($Variable) {
		DebugHTML(var_export($Variable, TRUE));
	}

?>