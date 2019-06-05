<?php

	require_once("common.php");

	// Get an integer parameter
	function GetIntegerParameter($Name, $NotFoundValue)
	{
		$retour=intval($NotFoundValue);
		if (isset($_GET[$Name]) && is_numeric($_GET[$Name])) $retour=intval($_GET[$Name]);
		elseif (isset($_POST[$Name]) && is_numeric($_POST[$Name])) $retour=intval($_POST[$Name]);
		return($retour);
	}

	// Get a string parameter
	function GetStringParameter($Name, $NotFoundValue)
	{
		$retour=strval($NotFoundValue);
		if (isset($_GET[$Name])) $retour=trim(strval($_GET[$Name]));
		elseif (isset($_POST[$Name])) $retour=trim(strval($_POST[$Name]));
		return($retour);
	}
	
	// Add "RadioButton"
	function ElementRadioButton($Nom,$Valeurs,$Selection,$Vertical=false)
	{
		$HTML='';
		$HTML.='<span>';
		$nb=0;
		foreach($Valeurs as $libelle=>$valeur)
		{
			$checked='';
			if ($Selection!==FALSE && $valeur==$Selection) $checked='checked';
			if ($nb>0 && $Vertical==true) $HTML.='<br>';
			if ($nb>0 && $Vertical==false) $HTML.=' ';
			$HTML.='<label class="radiobutton" for="id_'.$Nom.'_'.$nb.'"><input type="radio" '.$checked.' name="'.$Nom.'" id="id_'.$Nom.'_'.$nb.'" value="'.$valeur.'" />'.htmlspecialchars($libelle).'</label>';
			$nb=$nb+1;
		}
		$HTML.='</span>';
		return($HTML);
	}

	// Add "Select"
	function ElementSelect($Nom,$Valeurs,$Selection)
	{
		$HTML='<select name="'.$Nom.'">';
		$OptionTrouvee=false;
		foreach($Valeurs as $libelle=>$valeur)
		{
			$selected='';
			if ($Selection!==FALSE && $valeur==$Selection)
			{	
				$selected='selected';
				$OptionTrouvee=true;
			}
			$HTML.='<option value="'.$valeur.'" '.$selected.'>'.htmlspecialchars($libelle).'</option>';
		}
		if ($OptionTrouvee==false && $Selection!==FALSE)
		{
			$HTML.='<option value="'.$Selection.'" selected>'.htmlspecialchars('Unknown value ['.$Selection.']').'</option>';
		}
		$HTML.='</select>';
		return($HTML);
	}

	// Add "Checkbox"
	function ElementCheckbox($Nom,$Checked=false)
	{
		$CheckHTML='';
		if ($Checked) $CheckHTML='checked';
		$HTML='';
		$HTML.='<span>';
		$HTML.='<input type="checkbox" '.$CheckHTML.' name="'.$Nom.'">';
		$HTML.='</span>';
		return($HTML);	
	}

	// Add input text
	function ElementText($Nom,$Valeur,$Taille=10)
	{
		$HTML='<input type="text" size="'.$Taille.'" name="'.$Nom.'" value="'.htmlspecialchars($Valeur).'">';
		return($HTML);	
	}

	// Add textarea
	function ElementTextArea($Nom,$Valeur,$Largeur=200,$Hauteur=75)
	{
		$HTML='<textarea style="width:'.$Largeur.'px;height:'.$Hauteur.'px;" name="'.$Nom.'">'.htmlspecialchars($Valeur).'</textarea>';
		return($HTML);	
	}

	// Display a formatted HTML table from an array
	function HTMLTable($rows,$table_class='',$table_id='',$linesnumber=false,$addheader=false)
	{
		if (count($rows)>0)
		{
			// Formattage général de la table
			echo('<table ');
			if ($table_class!='') echo(' class="'.$table_class.'" ');
			if ($table_id!='') echo(' id="'.$table_id.'" ');
			echo('>');
			// Affichage des lignes
			$tbody_open=false;
			$nb=0;
			for ($i=0;$i<count($rows);$i++)
			{
				// Type d'element
				$Tag='td';
			
				// Test si on a un theader
				if ($i==0 && $addheader)
				{
					echo('<thead>');
					$Tag='th';
				}
				else
				{
					$nb++;
					if (!$tbody_open)
					{
						echo('<tbody>');
						$tbody_open=true;
					}
				}

				// Début de ligne
				if ($nb % 2)
					echo('<tr>');
				else
					echo('<tr class="alternate">');
				
				// Test si l'on doit ajouter les numéros de lignes
				if ($linesnumber)
				{
					if ($i==0 && $addheader)
						echo('<th>&nbsp;</th>');
					else
						echo('<th>'.$nb.'</th>');
				}
				
				// Ajout élément
				foreach($rows[$i] as $key=>$value)
				{
					$tokens=explode("~",$value);
					if (count($tokens)==2)
						echo('<'.$Tag.' class="'.$tokens[0].'">'.$tokens[1].'</'.$Tag.'>');
					else
						echo('<'.$Tag.'>'.$value.'</'.$Tag.'>');
				}

				// Fin de ligne
				echo('</tr>');
					
				// Test si on a un theader
				if ($i==0 && $addheader)
				{
					echo('</thead>');
				}
				// Vidage du buffer
				ob_flush();
				flush();
			}
			// Fermeture TBODY si nécessaire
			if ($tbody_open) echo('</tbody>');

			// Fin de la table
			echo('</table>');
		}
	}
	
	// Send HTTP headers
	function HttpHeader() {
		// Encoding and cache control
		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");   // Past date
		header('Content-type: text/html; charset=UTF-8');   // HTML in UTF-8	
	}
	
	// Start of content page
	function WebHeader($Title, $Notification="", $NotificationType="INFO") {
		global $config;
		HttpHeader();
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title><?php echo(htmlspecialchars($Title)); ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link type="text/css" href="./ressources/style.css" rel="stylesheet">
		<script language="javascript">
			var Notification="<?php echo(str_replace('"','\"',$Notification)); ?>";
			var NotificationType="<?php echo(str_replace('"','\"',$NotificationType)); ?>";
			function run() {
				if (Notification.length>0) {
					document.getElementById("notification").innerHTML=Notification;
					document.getElementById("notification").style.display="block";
					document.getElementById("notification").className="notification_type_"+NotificationType;
					setTimeout("closeNotification()", <?php echo($config["html_notif_duration"]); ?>);
				}
			}
			function closeNotification() {
				document.getElementById("notification").style.display="none";
			}
		</script>
	</head>
	<body class="contents" onload="javascript:run();">
		<div id="notification"></div>
		<div class="contents">
			<div class="contents_title"><?php echo(htmlspecialchars($Title)); ?></div>
<?php
	}
	
	// End of content page
	function WebFooter() {
?>
		</div>
	</body>
</html>
<?php
	}

?>