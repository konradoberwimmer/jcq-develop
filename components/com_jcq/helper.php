<?php
	//pruefe, ob innerhalb von Joomla
	defined('_JEXEC') or die('Restricted Access');

	//wenn ohne quest_id aufgerufen wird, passiert nichts
	if (isset($_GET["quest_id"])&&is_numeric($_GET["quest_id"]))
	{
		//hole entsprechenden Fragebogen aus Datenbank
		$fbid = $_GET["quest_id"];
		$db =& JFactory::getDBO();
		$sql_questionnaires = "SELECT *	FROM modquest_fragebogen WHERE FragebogenID=$fbid";
		$db->setQuery($sql_questionnaires);
		$questionnaires = $db->loadAssocList();
		//unterbreche, wenn kein FB gefunden
		if (count($questionnaires)==0) die('Undefined questionnaire');
		$questionnaire = $questionnaires[0];

		//hole Userdaten aus der joomla-Datenbank
		$us =& JFactory::getUser();
		$user = $us->get('username');	
		
		//TODO anonyme Befragung
		
		//wenn schon eine SessionID vorhanden, dann arbeite weiter		
		if (isset($_GET["session_id"])&&isset($_GET["page"]))
		{
			$sessionid = $_GET["session_id"];
			$sql_sessions = "SELECT * FROM modquest_antworten_$fbid WHERE SessionID='$sessionid'";
			$db->setQuery($sql_sessions);
			$sessions = $db->loadAssocList();
			//unterbreche, wenn kein FB gefunden
			if (count($questionnaires)==0) die('Unknown session');
			$session = $sessions[0];
			
			$obj_helper = null;
			require_once($questionnaire['PhpDatei']);
			$obj_helper = new $questionnaire['Klasse']();
			
			$obj_helper->getQuestionnaire($fbid, $_GET["page"], $sessionid);
		}
		//ansonsten pruefe, ob User schon angelegt ist
		else
		{
			$newsession = false;
			$sql_user = "SELECT * FROM modquest_antworten_$fbid WHERE UserID='$user'";
			$db->setQuery($sql_user);
			$users = $db->loadAssocList();
			//wenn ja, pruefe, ob mehrfache Beantwortung moeglich
			if(count($users)>0)
			{
				$userdata = $users[0];
				//wenn nein, hole alte SessionID und fahre fort
				if ($questionnaire['mehrfach']==0)
				{
					$obj_helper = null;
					require_once($questionnaire['PhpDatei']);
					$obj_helper = new $questionnaire['Klasse']();
					$obj_helper->getQuestionnaire($fbid, $userdata['seite'], $userdata['SessionID']);
				}
				//wenn ja, beginne neue Beantwortung
				else $newsession=true;
			}
			//wenn nein, beginne neue Beantwortung
			else $newsession=true;
			
			//vergib neue SessionID und beginne Befragung
			if ($newsession == true)
			{
				$sessionid = uniqid('', true); 
				$query = "INSERT INTO modquest_antworten_$fbid (UserID,SessionID) VALUES ('$user','$sessionid')";
				$db->setQuery($query);
				$db->query();
				$obj_helper = null;
				require_once($questionnaire['PhpDatei']);
				$obj_helper = new $questionnaire['Klasse']();
				$obj_helper->getQuestionnaire($fbid, 1, $sessionid);
			}	
		}
	}
	else die('Undefined questionnaire');
?>