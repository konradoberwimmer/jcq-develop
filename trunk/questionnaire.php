<?php
defined( '_JEXEC' ) or die( 'Restricted Access' );

//kann derzeit die Fragtypen
// 111 - Einfachauswahl untereinander
// 141 - Textfeld einzeilig
// 311 - Einfachauswahlmatrix
// 361 - Auswahlboxmatrix - #FIXME Typ 361 generell noch ungenuegend offen definiert, es sind bloss zwei nebenenander stehende Drop-Boxes moeglich, noch dazu aufgrund von Feldern in der Fragen-Entitaet anstatt einer 1:n-Verknuepfung von Frage und Skala
// 998 - Text und HTML-Code

abstract class Questionnaire
{
	
	private function printPage ($fbid, $currentpage, $sessionid, $markMissing, $nextpage)
	{
		if($markMissing)
		{
			echo "<div style='font-weight:bold; color: red; margin-bottom: 10px;'>Die Fragen sind unvollst&auml;ndig beantwortet.<BR>";
			echo "Bitte beantworten Sie die rot markierten Fragen!</div>";
		}
		
		$u =& JURI::getInstance('SERVER');
		
		$db =& JFactory::getDBO();
		//hole bisherige Antworten
		$sql_answers = "SELECT * FROM modquest_antworten_$fbid WHERE SessionID='$sessionid';";
		$db->setQuery($sql_answers);
		$answers=$db->loadAssoc();
		//pruefe, ob Session gesetzt
		if (count($answers)==0) die("Unknown session");
		
		//stelle die Seite dar
		$params = $u->getQuery(true);
		$params['quest_id']=$fbid;
		$params['page']=$nextpage;
		$params['session_id']=$sessionid;
		$url = JURI::current();
		//$url = str_replace('http','https',$url);
		echo '<form action="'.$url.'?'.$u->buildQuery($params).'" method="post">';
		
		$seitenid = $currentpage['SeitenID'];
		$sql_fragen = "SELECT * FROM modquest_fragen WHERE SeitenID=$seitenid ORDER BY Ordnung";
		$db->SetQuery($sql_fragen);
		$fragen=$db->loadAssocList();
		
		foreach($fragen as $frage)
		{

			$fragenid = $frage['FragenID'];
			$fragentyp = $frage['FragenTyp'];
			$fragentext = $frage['Text'];
			$varname = $frage['Varname'];
			#FIXME überführen in Objektorientierung: dynamisch Funktion laden (hook-object)
			switch($fragentyp)
			{
				case 111:
					{
						echo("<div style='margin-bottom: 10px;'>");
						//hole Skala
						$skalaid = $frage['SkalaID'];
						$sql_skala = "SELECT * FROM modquest_codes JOIN modquest_skalen ON (modquest_codes.SkalaID=modquest_skalen.SkalaID) WHERE modquest_skalen.SkalaID=$skalaid ORDER BY modquest_codes.Ordnung";
						$db->SetQuery($sql_skala);
						$codes=$db->loadAssocList();
						//stelle Einfachauswahl dar
						if ($markMissing && $frage['DAC']==1 && !is_numeric($answers[$varname])) $style="font-weight:bold; color: red;";
						else $style="font-weight:bold;";
						echo("<p style='$style'>$fragentext</p>");
						for ($i=0;$i<count($codes);$i++)
						{
							$code=$codes[$i];
							$codenum=$code['Code'];
							$codetext=$code['Label'];
							if ($code['Code']==$answers[$varname]) $checked="checked";
							else $checked="";
							echo("<input type='radio' name='$varname' value='$codenum' $checked>$codetext</input><br>");
						}		
						echo("</div>");
						break;
					}
				case 141:
					{
						echo("<div style='margin-bottom: 10px;'>");
						//stelle Textfeld (einzeilig) dar
						if ($markMissing && $frage['DAC']==1 && strlen($answers[$varname])==0) $style="font-weight:bold; color: red;";
						else $style="font-weight:bold;";
						$value=$answers[$varname];
						$width=$frage['SkalaBreite'];
						if (!is_numeric($width)) $width=30;
						echo("<p style='$style'>$fragentext</p>");
						$prepost=explode("%s",$frage['PrePostText']);
						echo("$prepost[0]<input type='text' name='$varname' size='$width' value='$value'/>$prepost[1]");
						echo("</div>");
						break;
					}
				case 311:
					{
						echo("<div style='margin-bottom: 10px;'>");
						//hole Items
						$sql_items = "SELECT * FROM modquest_items WHERE FragenID=$fragenid ORDER BY Ordnung";
						$db->SetQuery($sql_items);
						$items=$db->loadAssocList();
						//hole Skala
						$skalaid = $frage['SkalaID'];
						$sql_skala = "SELECT * FROM modquest_codes JOIN modquest_skalen ON (modquest_codes.SkalaID=modquest_skalen.SkalaID) WHERE modquest_skalen.SkalaID=$skalaid ORDER BY modquest_codes.Ordnung";
						$db->SetQuery($sql_skala);
						$codes=$db->loadAssocList();
						//Stelle Einfachauswahlmatrix dar
						echo("<p style='font-weight:bold;margin-bottom: 10px;'>$fragentext</p>");
						echo("<table style='border-collapse: collapse;'><tr><th style='border-bottom: 1px solid grey;'>&nbsp;</th>");
						for ($i=0;$i<count($codes);$i++)
						{
							$code=$codes[$i];
							$label=$code['Label'];
							echo("<th style='padding-left: 10px; padding-right: 10px; border-bottom: 1px solid grey;'>$label</th>");
						}
						echo("</tr>");
						for ($i=0;$i<count($items);$i++)
						{
							$item=$items[$i];
							$varname=$item['Varname'];
							$itemtext=$item['TextLinks'];
							if ($markMissing && $item['DAC']==1 && !is_numeric($answers[$varname])) $style="color: red;";
							else $style="";
							if ($i % 2 == 1 && isset($frage['alternateBackground']) && $frage['alternateBackground']==1) $style=$style." background-color:#DDDDDD;";
							echo("<tr><td style='$style border-bottom: 1px solid grey;'>$itemtext</td>");
							for ($j=0;$j<count($codes);$j++)
							{
								$code=$codes[$j];
								$codenum=$code['Code'];
								if ($codenum==$answers[$varname]) $checked="checked";
								else $checked="";
								if ($i % 2 == 1 && isset($frage['alternateBackground']) && $frage['alternateBackground']==1) $style="background-color:#DDDDDD;";
								else $style="";
								echo("<td style='$style text-align:center; border-bottom: 1px solid grey;'><input type='radio' name='$varname' value='$codenum' $checked/></td>");
							}
							echo("</tr>");
						}
						echo("</table>");
						echo("</div>");
						break;
					}
				case 361:
					{
						echo("<div style='margin-bottom: 10px;'>");
						//hole Items
						$sql_items = "SELECT * FROM modquest_items WHERE FragenID=$fragenid ORDER BY Ordnung";
						$db->SetQuery($sql_items);
						$items=$db->loadAssocList();
						//hole Skala (oder Skalen)
						$skalaid = $frage['SkalaID'];
						$sql_skala = "SELECT * FROM modquest_codes JOIN modquest_skalen ON (modquest_codes.SkalaID=modquest_skalen.SkalaID) WHERE modquest_skalen.SkalaID=$skalaid ORDER BY modquest_codes.Ordnung";
						$db->SetQuery($sql_skala);
						$codes=$db->loadAssocList();
						if (isset($frage['SkalaID_2']))
						{
							$skalaid2 = $frage['SkalaID_2'];
							$sql_skala2 = "SELECT * FROM modquest_codes JOIN modquest_skalen ON (modquest_codes.SkalaID=modquest_skalen.SkalaID) WHERE modquest_skalen.SkalaID=$skalaid2 ORDER BY modquest_codes.Ordnung";
							$db->SetQuery($sql_skala2);
							$codes2=$db->loadAssocList();
						}
						$width=$frage['SkalaBreite'];
						//Stelle Select-Matrix dar
						echo("<p style='font-weight:bold; margin-left:20px; margin-bottom: 10px;'>$fragentext</p>");
						echo("<table style='border-collapse:collapse; margin-right: 20px; margin-left: 20px;'>");
						for ($i=0;$i<count($items);$i++)
						{
							$item=$items[$i];
							$varname=$item['Varname'];
							$varname2=$item['Varname_2'];
							$itemtext=$item['TextLinks'];
							if ($markMissing && $item['DAC']==1 && !is_numeric($answers[$varname])) $style="color: red;";
							else if (isset($item['Varname_2']) && $markMissing && $item['DAC']==1 && !is_numeric($answers[$varname2])) $style="color: red;";
							else $style="";
							echo("<tr><td style='border-bottom: 1px solid grey; padding-right: 10px; $style'>$itemtext</td>");
							$prepost=explode("%s",$frage['PrePostText']);
							echo("<td width='$width' style='border-bottom: 1px solid grey;'>$prepost[0]<select name='$varname'>");
							#TODO Leereintrag weg, wenn Default-Eingaben moeglich
							echo("<option></option>");
							for ($j=0;$j<count($codes);$j++)
							{
								$code=$codes[$j];
								$codenum=$code['Code'];
								$codelabel=$code['Label'];
								if ($codenum==$answers[$varname]) $selected="selected";
								else $selected="";
								echo("<option value='$codenum' $selected>$codelabel</option>");
							}
							echo("</select>$prepost[1]</td>");
							if (isset($frage['SkalaID_2']))
							{
								$prepost=explode("%s",$frage['PrePostText_2']);
								echo("<td width='$width' style='border-bottom: 1px solid grey;'>$prepost[0]<select name='$varname2'>");
								for ($j=0;$j<count($codes2);$j++)
								{
									$code2=$codes2[$j];
									$codenum2=$code2['Code'];
									$codelabel2=$code2['Label'];
									if ($codenum2==$answers[$varname2]) $selected="selected";
									else $selected="";
									echo("<option value='$codenum2' $selected>$codelabel2</option>");
								}
								echo("</select>$prepost[1]</td>");
							}
							echo("</tr>");
						}
						echo("</table>");
						echo("</div>");
						break;
					}
				case 998:
					{
						echo($fragentext);
						break;
					}
			}

		}
		
		if($nextpage > 0) echo '<input type="submit" value="Weiter" />';
		else echo '<input type="submit" value="Zum Ergebnis" />';
		echo '</form>';
	}
	
	private function decoratePageHeader($questname, $pagenum, $pagecnt)
	{
		#TODO massiv erweitern
		echo("<table style='width:100%'><tr><td style='text-align:right; border-bottom: 1px solid grey;'>$questname - Seite $pagenum von $pagecnt</td></tr></table>");
	}
	
	private function saveAnswers($fbid, $lastpage, $sessionid)
	{
		//hole Fragen zur Seite
		$seitenid = $lastpage['SeitenID'];
		$db =& JFactory::getDBO();
		$sql_fragen = "SELECT * FROM modquest_fragen WHERE SeitenID=$seitenid";
		$db->SetQuery($sql_fragen);
		$fragen=$db->loadAssocList();
		
		//hole bisherige Antworten
		$sql_answers = "SELECT * FROM modquest_antworten_$fbid WHERE SessionID='$sessionid';";
		$db->setQuery($sql_answers);
		$answers=$db->loadAssoc();
		//pruefe, ob Session gesetzt
		if (count($answers)==0) die("Unknown session");
		
		$missings = false;
		//gehe alle fragen durch, hole gegebenenfalls die Items nach
		foreach ($fragen as $frage)
		{
			$fragenid = $frage['FragenID'];
			$fragentyp = $frage['FragenTyp'];
			$varname = $frage['Varname'];
			$dac = $frage['DAC'];
			$sql_insert = "";
			switch ($fragentyp)
			{
				case 111:
					{
						if (isset($_POST[$varname])&&is_numeric($_POST[$varname]))
						{
							$value = $_POST[$varname];
							$sql_insert = "UPDATE modquest_antworten_$fbid SET $varname=$value WHERE SessionID='$sessionid'";
						}
						else if ($dac==1 && !is_numeric($answers[$varname])) $missings=true;
						break;
					}
				case 141:
					{
						if (isset($_POST[$varname])&&strlen($_POST[$varname])>0)
						{
							$value = mysql_real_escape_string($_POST[$varname]); //verhindere SQL-Insertion
							$sql_insert = "UPDATE modquest_antworten_$fbid SET $varname='$value' WHERE SessionID='$sessionid'";
						}
						else if ($dac==1 && strlen($answers[$varname])==0) $missings=true;
						break;
					}
				case 311: case 361:
					{
						$sql_items = "SELECT * FROM modquest_items WHERE FragenID=$fragenid";
						$db->SetQuery($sql_items);
						$items=$db->loadAssocList();
						foreach ($items as $item)
						{
							$varname = $item['Varname'];
							$dac = $item['DAC'];
							if (isset($_POST[$varname])&&is_numeric($_POST[$varname]))
							{
								$value = $_POST[$varname];
								$sql_insert = "UPDATE modquest_antworten_$fbid SET $varname=$value WHERE SessionID='$sessionid'";
								$db->SetQuery($sql_insert); //fuehre hier Update gleich aus
								$seiten=$db->query();
							}
							else if ($dac==1 && !is_numeric($answers[$varname])) $missings=true;
							//Typ 361 braucht evt. zweite Eingabe
							if (isset($item['Varname_2']))
							{
								$varname2 = $item['Varname_2'];
								if (isset($_POST[$varname2])&&is_numeric($_POST[$varname2]))
								{
									$value2 = $_POST[$varname2];
									$sql_insert = "UPDATE modquest_antworten_$fbid SET $varname2=$value2 WHERE SessionID='$sessionid'";
									$db->SetQuery($sql_insert); //fuehre hier Update gleich aus
									$seiten=$db->query();
								}
								else if ($dac==1 && !is_numeric($answers[$varname2])) $missings=true;
							}
						}
						$sql_insert = "";
						break;
					}
			}
			#FIXME Liste von Update-Befehlen sollte gemeinsam durchgegeben werden
			//fuehre Update-Befehl aus
			if (strlen($sql_insert)>0)
			{
				$db->SetQuery($sql_insert);
				$seiten=$db->query();
			}
		}
		
		return $missings;
	}
	
	public function getQuestionnaire($fbid, $seite, $sessionid)
	{
		//klaere aktuelle, vorherige und nächste Seite
		$db =& JFactory::getDBO();
		$sql_questionnaire = "SELECT * FROM modquest_fragebogen WHERE FragebogenID=$fbid";
		$db->SetQuery($sql_questionnaire);
		$questionnaire=$db->loadAssocList();
		$questionnaire=$questionnaire[0];
		$sql_seiten = "SELECT * FROM modquest_seiten WHERE FragebogenID=$fbid ORDER BY Ordnung";
		$db->SetQuery($sql_seiten);
		$seiten=$db->loadAssocList();
		//brich ab, wenn Seitenzahl zu niedrig
		if (count($seiten)<$seite) die('Internal error: page not available');
		
		#TODO ordentliche Seitenreihenfolge, kein plumpes Verlassen auf die Ordnung
		
		if ($seite>1) $lastpage = $seiten[$seite-2]; //mitten im Fragebogen gibt es eine vorherige Seite
		else if ($seite<1) $lastpage = $seiten[count($seiten)-1]; //am Ende muss die letzte Seite ueberprueft werden
		else $lastpage = null; //zu Beginn gibt es keine vorherige Seite
		
		if ($seite>=1) $currentpage = $seiten[$seite-1]; //wenn noch im Fragebogen, dann aktuelle Seite
		else $currentpage = null; //ansonsten wird das Ergebnis aufgerufen
		
		if ($seite<1) $nextpage = null; //wenn beendet, dann gibt es keine nächste Seite
		else if ($seite<count($seiten)) $nextpage=$seite+1; //wenn noch nicht auf letzter Seite, dann eine weiter
		else $nextpage=-1; //wenn auf letzter Seite, dann geht es weiter zum Ergebnis
		
		//speichere gepostete Antworten und ueberpruefe fehlende Eingaben
		$markMissing=false;
		if ($lastpage!=null)
		{
			$markMissing = $this->saveAnswers($fbid, $lastpage, $sessionid);
			//gehe Seite zurueck, wenn Eingaben fehlen
			if ($markMissing == true)
			{
				$currentpage = $lastpage;
				$nextpage = $seite;
				if ($seite==-1) $seite = count($seiten);
				else $seite--;
			}
		}

			//setze seitencounter und stelle Seite dar
		$sql_updateseitencounter = "UPDATE modquest_antworten_$fbid SET seite=$seite WHERE SessionID='$sessionid'";
		$db->SetQuery($sql_updateseitencounter);
		$db->query();
		$sql_answers = "SELECT * FROM modquest_antworten_$fbid WHERE SessionID='$sessionid';";
		$db->setQuery($sql_answers);
		$answers=$db->loadAssoc();
		if ($seite==-1)
		{
			if (!isset($answers['timestampEnd']))
			{
				$current = time();
				$sql_insert = "UPDATE modquest_antworten_$fbid SET timestampEnd=$current WHERE SessionID='$sessionid'";
				$db->SetQuery($sql_insert);
				$db->query();
			}
			$this->printResults($sessionid);
		}
		else
		{
			$pageID = $seiten[$seite-1]['SeitenID'];
			if (!isset($answers['timestampPage_'.$pageID]))
			{
				$current = time();
				$sql_insert = "UPDATE modquest_antworten_$fbid SET timestampPage_$pageID=$current WHERE SessionID='$sessionid'";
				$db->SetQuery($sql_insert);
				$db->query();
			}
			$this->decoratePageHeader($questionnaire['FragebogenName'], $seite, count($seiten));
			$this->printPage($fbid, $currentpage, $sessionid, $markMissing, $nextpage);
		}
	}
	
	#TODO absichern durch Kombination aus User und Session
	public abstract function printResults($sessionid);
}
?>