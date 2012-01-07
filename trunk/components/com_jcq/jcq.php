<?php
	//pruefe, ob innerhalb von Joomla aufgerufen
	defined('_JEXEC') or die('Restricted Access');
	
	echo("Hallo im Front-end!");
	
	/*
	function get_inner_html($node) {
		$innerHTML= '';
		$children = $node->childNodes;
		foreach ($children as $child) {
			$innerHTML .= $child->ownerDocument->saveXML( $child );
		}
	
		return $innerHTML;
	}
	*/
	
	//wenn bereits eine ID fuer einen Fragebogen vorhanden ist, kann zur helper.php weitergegangen werden
	if(isset($_GET["quest_id"])){
		require_once('modules/mod_questionnaire/helper.php');
	}
	
	//ansonsten ist die Liste verfuegbarer Frageboegen anzuzeigen
	else{
		$debug=true;
		
		//hole verschiedene Frageboegen aus Datenbank
		$db =& JFactory::getDBO();
		$sql_questionnaires = "SELECT *	FROM modquest_fragebogen ORDER BY Ordnung";
		$db->setQuery($sql_questionnaires);
		$db->query();
		$questionnaires = $db->loadAssocList();
		
		//wenn keine Frageboegen in DB oder der DB-Aufbau gedebuggt werden soll
		/*if ($debug==true||count($questionnaires)==0)
		{
			//Datenbank anlegen
			$sql_dropTableFragebogen = "DROP TABLE IF EXISTS modquest_fragebogen";
			$db->setQuery($sql_dropTableFragebogen);
			$db->query();
			$sql_createTableFragebogen = 	
			"CREATE TABLE modquest_fragebogen (
				  FragebogenID int(11) AUTO_INCREMENT NOT NULL,
				  Ordnung int(11) NOT NULL,
				  FragebogenName varchar(255) NOT NULL,
				  Klasse varchar(255) NOT NULL,
				  PhpDatei varchar(255) NOT NULL,
				  Beschreibung text,
				  mehrfach int(1) DEFAULT 0 NOT NULL,
				  PRIMARY KEY  (FragebogenID)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
			$db->setQuery($sql_createTableFragebogen);
			$db->query();
			
			$sql_dropTableSeiten = "DROP TABLE IF EXISTS modquest_seiten";
			$db->setQuery($sql_dropTableSeiten);
			$db->query();
			$sql_createTableSeiten =
			"CREATE TABLE modquest_seiten (
				  FragebogenID int(11) NOT NULL,
				  SeitenID int(11) AUTO_INCREMENT NOT NULL,
				  Ordnung int(11) NOT NULL,
				  PRIMARY KEY  (SeitenID)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
			$db->setQuery($sql_createTableSeiten);
			$db->query();
			
			$sql_dropTableFragen = "DROP TABLE IF EXISTS modquest_fragen";
			$db->setQuery($sql_dropTableFragen);
			$db->query();
			$sql_createTableFragen = 	
			"CREATE TABLE modquest_fragen (
				  SeitenID int(11) NOT NULL,
				  FragenID int(11) AUTO_INCREMENT NOT NULL,
				  Ordnung int(11) NOT NULL,
				  FragenTyp int(11) NOT NULL,
				  SkalaID int(11),
				  SkalaID_2 int(11),
				  Text text,
				  PrePostText text,
				  PrePostText_2 text,
				  Varname varchar(50),
				  SkalaBreite int(11),
				  alternateBackground int(1) DEFAULT 1,
				  DAC int(1) DEFAULT 1,
				  PRIMARY KEY  (FragenID)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
			$db->setQuery($sql_createTableFragen);
			$db->query();
			
			$sql_dropTableSkala = "DROP TABLE IF EXISTS modquest_skalen";
			$db->setQuery($sql_dropTableSkala);
			$db->query();
			$sql_createTableSkala =
			"CREATE TABLE modquest_skalen (
				  SkalaID int(11) AUTO_INCREMENT NOT NULL,
				  Bezeichnung varchar(50) NOT NULL,
				  PRIMARY KEY  (SkalaID)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
			$db->setQuery($sql_createTableSkala);
			$db->query();
			
			$sql_dropTableSkala = "DROP TABLE IF EXISTS modquest_codes";
			$db->setQuery($sql_dropTableSkala);
			$db->query();
			$sql_createTableSkala =
			"CREATE TABLE modquest_codes (
				  SkalaID int(11) NOT NULL,
				  CodeID int(11) AUTO_INCREMENT NOT NULL,
				  Ordnung int(11) NOT NULL,
				  Code int(11) NOT NULL,
				  Label varchar(250),
				  PRIMARY KEY  (CodeID)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
			$db->setQuery($sql_createTableSkala);
			$db->query();
			
			$sql_dropTableItems = "DROP TABLE IF EXISTS modquest_items";
			$db->setQuery($sql_dropTableItems);
			$db->query();
			$sql_createTableItems = 	
			"CREATE TABLE modquest_items (
				  FragenID int(11) NOT NULL,
				  ItemID int(11) AUTO_INCREMENT NOT NULL,
				  Ordnung int(11) NOT NULL,
				  Varname varchar(50) NOT NULL,
				  Varname_2 varchar(50),
				  TextLinks text,
				  TextRechts text,
				  DAC int(1) DEFAULT 1,
				  PRIMARY KEY  (ItemID)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
			$db->setQuery($sql_createTableItems);
			$db->query();

			//dieser Algorithmus ist so allgemein, dass einzig REQUIRED dass alles korrekt im XML formuliert ist!!!
			//BENEFITS: nullable Felder muessen im XML nicht vorkommen, die Reihenfolge der Feldangabe ist egal
			if (file_exists("modules/mod_questionnaire/init.xml"))
			{
				//Init-Datei laden laden
				$DOM = new DOMDocument();
				$DOM->load("modules/mod_questionnaire/init.xml");
				$rootElement = $DOM->getElementsByTagName("mod_questionnaire")->item(0);

				//um nicht beim Initialisieren schon auf die DB lesend zugreifen zu muessen, muss ich mir in Arrays merken, welche Seiten zu welchem Fragebogen gehoeren und welche Frage zu welcher Seite sowie welche Frage welchen Typ hat
				$seiteInFB = array();
				$frageInSeite = array();
				$frageHatTyp = array();
				
				//Enities holen, die eingefuegt werden
				$entities = $rootElement->childNodes;
				$numentities = $entities->length;
				for ($pos=0;$pos<$numentities;$pos++)
				{
					$currententity = $entities->item($pos);
					
					//Felder der Entity holen und in indiziertes Array schreiben
					$element = $currententity->nodeName;
					if (!$currententity->hasChildNodes()) continue; //ueberspringe Entities ohne Inhalt
					$fields = $currententity->childNodes;
					$numfields = $fields->length;
					$fieldarray = array();
					for ($i=0;$i<$numfields;$i++)
					{
						$currentfield = $fields->item($i);
						if (strlen(trim($currentfield->nodeValue))<1) continue; //ueberspringe Felder ohne Werte
						$fieldarray[$currentfield->nodeName] = $currentfield->nodeValue;
					}
					
					//bereite SQL-Insert vor
					$sql_insert = "INSERT INTO modquest_$element (";
					$i=0;
					foreach ($fieldarray as $k => $v)
					{
						if ($i>0) $sql_insert = $sql_insert.",";
						$sql_insert = $sql_insert.$k;
						$i++;
					}
					$sql_insert = $sql_insert.") VALUES (";
					$i=0;
					foreach ($fieldarray as $k => $v)
					{
						if ($i>0) $sql_insert = $sql_insert.",";
						$sql_insert = $sql_insert."'".mysql_real_escape_string($v)."'"; //sichert nur gegen SQL-Insertion, #TODO deutsche Sonderzeichen sind noch unbehandelt
						$i++;
					}
					$sql_insert = $sql_insert.");";
					//fuehre SQL-Insert durch
					$db->setQuery($sql_insert);
					$db->query();
					
					//ergaenze Antworttabelle
					if ($element == "fragebogen")
					{
						#bereite Antworttabelle fuer Fragebogen vor
						$id = $fieldarray["FragebogenID"];
						$sql_dropTableBeantwortungen = "DROP TABLE IF EXISTS modquest_antworten_$id";
						$db->setQuery($sql_dropTableBeantwortungen);
						$db->query();
						$sql_createTableBeantwortungen =
									"CREATE TABLE modquest_antworten_$id (
										  UserID  varchar(50),
										  SessionID varchar(50) NOT NULL,
										  seite int(11) DEFAULT 1 NOT NULL,
										  timestampEnd int(11),
										  PRIMARY KEY  (UserID)
									) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
						$db->setQuery($sql_createTableBeantwortungen);
						$db->query();
					}
					//REQUIRES: Fragebogen in XML vor Seite.
					else if ($element == "seiten")
					{
						$seiteInFB[$fieldarray["SeitenID"]] = $fieldarray["FragebogenID"];
						$fbid = $fieldarray["FragebogenID"];
						$pageID = $fieldarray["SeitenID"];
						$sql_addcolumn = "ALTER TABLE modquest_antworten_$fbid ADD timestampPage_$pageID int(11);";
						$db->setQuery($sql_addcolumn);
						$db->query();
					}
					//REQUIRES: Seite in XML vor Frage.
					else if ($element == "fragen")
					{
						$frageInSeite[$fieldarray["FragenID"]] = $fieldarray["SeitenID"];
						$frageHatTyp[$fieldarray["FragenID"]] = $fieldarray["FragenTyp"];
						$fbid = $seiteInFB[$fieldarray["SeitenID"]];
						if (isset($fieldarray['Varname'])) $varname = $fieldarray['Varname'];
						if ($fieldarray["FragenTyp"]==111) //Ist es ein Fragetyp, der direkt ein numerisches Ergebnis liefert?
						{
							$sql_addcolumn = "ALTER TABLE modquest_antworten_$fbid ADD $varname int(11);";
							$db->setQuery($sql_addcolumn);
							$db->query();
						}
						else if ($fieldarray["FragenTyp"]==141) //Ist es ein Fragetyp, der direkt ein alphanumerisches Ergebnis liefert?
						{
							$sql_addcolumn = "ALTER TABLE modquest_antworten_$fbid ADD $varname text;";
							$db->setQuery($sql_addcolumn);
							$db->query();
						}
					}
					//REQUIRES: Fragen in XML vor Items.
					else if ($element == "items")
					{
						$seiteid = $frageInSeite[$fieldarray["FragenID"]];
						$fbid = $seiteInFB[$seiteid];
						$fragetyp = $frageHatTyp[$fieldarray["FragenID"]];
						$varname = $fieldarray["Varname"];
						if ($fragetyp == 311 || $fragetyp == 361)
						{
							$sql_addcolumn = "ALTER TABLE modquest_antworten_$fbid ADD $varname int(11);";
							$db->setQuery($sql_addcolumn);
							$db->query();
							//extra fuer Typ 361, solange er ungenuegend bestimmt ist
							if (isset($fieldarray["Varname_2"]))
							{
								$varname2 = $fieldarray["Varname_2"];
								$sql_addcolumn = "ALTER TABLE modquest_antworten_$fbid ADD $varname2 int(11);";
								$db->setQuery($sql_addcolumn);
								$db->query();
							}
						}
					}
				}
			}
			//Initialisierungsdatei loeschen
			if (!$debug) unlink('modules/mod_questionnaire/init.xml');
				
			//fetche erneut
			$db->setQuery($sql_questionnaires);
			$questionnaires = $db->loadAssocList();
		}
		*/
		/*
		foreach ($questionnaires as $questionnaire)
		{
			//links zu den Frageboegen mit Selbstaufruf der Seite
			$u =& JURI::getInstance('SERVER');	
			echo '<h1><a href="'.JURI::current().'?'.$u->getQuery().'&quest_id='.$questionnaire['FragebogenID'].'">'.htmlspecialchars($questionnaire['FragebogenName']).'</a></h1><p>'.$questionnaire['Beschreibung'].'</p>';
			echo '<p><a href="'.JURI::current().'?'.$u->getQuery().'&quest_id='.$questionnaire['FragebogenID'].'">(Weiter zu &quot;'.htmlspecialchars($questionnaire['FragebogenName']).'&quot;)</p></h3>';
		}*/
	}
?>