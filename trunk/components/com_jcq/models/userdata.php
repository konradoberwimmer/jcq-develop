<?php
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');

class JcqModelUserdata extends JModel
{
	private $projectID = null;
	private $sessionID = null;
	
	function loadSession($projectID,$sessionID)
	{
		//this is just for safety: look if session really exists
		$sqlsession = "SELECT * FROM jcq_proj".$projectID." WHERE sessionID='".$sessionID."'";
		$db = $this->getDBO();
		$db->setQuery($sqlsession);
		$session = $db->loadObject();
		if ($session==null) return false; //error because session unknown
		else
		{
			$this->sessionID = $sessionID;
			$this->projectID = $projectID;
			return true;
		}
	}
	
	function createSession($projectID)
	{
		$sqlproject = "SELECT * FROM jcq_project WHERE ID=".$projectID;
		$db = $this->getDBO();
		$db->setQuery($sqlproject);
		$project = $db->loadObject();
		
		if ($project==null) JError::raiseError(500, 'Project with ID '.$projectID.' not found!');
		
		$user =& JFactory::getUser();
		
		//case 1: no user logged in (ID is set to 0)
		if ($user->id==0)
		{
			//case 1a: anonymous answers allowed --> create new ID
			if ($project->anonymous==1)
			{
				$this->sessionID = uniqid('', true);
				$this->projectID = $projectID;
				$sqlnewsession = "INSERT INTO jcq_proj".$projectID." (sessionID, curpage) VALUES ('".$this->sessionID."',0)";
				$db->setQuery($sqlnewsession);
				if (!$db->query())
				{
					$errorMessage = $this->getDBO()->getErrorMsg();
					JError::raiseError(500, 'Error inserting new session: '.$errorMessage);
				}
			}
			//case 1b: anonymous answers not allowed --> return false (error)
			else return false;
		}
		//case 2: a user is logged in
		else
		{
			$sqlsessions = "SELECT * FROM jcq_proj".$projectID." WHERE userID='".$user->username."'";
			$db->setQuery($sqlsessions);
			$sessions = $db->loadObjectList();
			
			//case 2a: no session exists for user or multiple answers are permitted --> create session
			if ($sessions==null || $project->multiple==1)
			{
				$this->sessionID = uniqid('', true);
				$this->projectID = $projectID;
				$sqlnewsession = "INSERT INTO jcq_proj".$projectID." (userID, sessionID, curpage) VALUES ('".$user->username."','".$this->sessionID."',0)";
				$db->setQuery($sqlnewsession);
				if (!$db->query())
				{
					$errorMessage = $this->getDBO()->getErrorMsg();
					JError::raiseError(500, 'Error inserting new session: '.$errorMessage);
				}
			}
			//case 2b: a session exists for user and multiple answers are not permitted --> load old session
			else
			{
				$this->sessionID = $sessions[0]->sessionID;
				$this->projectID = $projectID;
			}
		}
		return true;
	}
	
	//requires a session to be loaded
	function getCurrentPage()
	{
		$sqlsession = "SELECT * FROM jcq_proj".$this->projectID." WHERE sessionID='".$this->sessionID."'";
		$db = $this->getDBO();
		$db->setQuery($sqlsession);
		$session = $db->loadObject();
		
		//case: if at the beginning, set to first page and return that value
		if ($session->curpage==0)
		{
			$sqlpages = "SELECT * FROM jcq_page WHERE projectID=".$this->projectID." ORDER BY ord";
			$db->setQuery($sqlpages);
			$pages = $db->loadObjectList();
			$curpage = $pages[0]->ID;
			$sqlnextpage = "UPDATE jcq_proj".$this->projectID." SET curpage=".$curpage." WHERE sessionID='".$this->sessionID."'";
			$db->setQuery($sqlnextpage);
			if (!$db->query())
			{
				$errorMessage = $this->getDBO()->getErrorMsg();
				JError::raiseError(500, 'Error going next page: '.$errorMessage);
			}
			return $curpage;
		}
		//case: else return current page to display
		else return $session->curpage;
	}
	
	//requires a session to be loaded
	function storeAndContinue()
	{
		$sqlsession = "SELECT * FROM jcq_proj".$this->projectID." WHERE sessionID='".$this->sessionID."'";
		$db = $this->getDBO();
		$db->setQuery($sqlsession);
		$session = $db->loadObject();
		
		//TODO actually store data
		
		//set current page to next page
		$sqlpages = "SELECT * FROM jcq_page WHERE projectID=".$this->projectID." ORDER BY ord";
		$db->setQuery($sqlpages);
		$pages = $db->loadObjectList();
		$foundpage = false;
		for ($i=0;$i<count($pages);$i++)
		{
			if ($pages[$i]->ID==$session->curpage)
			{
				$foundpage = true;
				//next page exists in project
				if ($i<count($pages)-1) $nextpage = $pages[$i+1]->ID;
				//no next page --> user code has to be invoked
				else $nextpage = -1; //TODO set to finished
				$sqlnextpage = "UPDATE jcq_proj".$this->projectID." SET curpage=".$nextpage." WHERE sessionID='".$this->sessionID."'";
				$db->setQuery($sqlnextpage);
				if (!$db->query())
				{
					$errorMessage = $this->getDBO()->getErrorMsg();
					JError::raiseError(500, 'Error going next page: '.$errorMessage);
				}
				break;
			}
		}	
		if (!foundpage) JError::raiseError(500, 'Error: could not find page with ID'.$session->curpage);
		return true;
	}
	
	function getSessionID()
	{
		return $this->sessionID;
	}
}