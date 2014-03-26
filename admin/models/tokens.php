<?php
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');

function RandomString($length)
{
	$characters = '0123456789abcdefghijklmnopqrstuvwxyz';
	$randstring = '';
	for ($i = 0; $i < $length; $i++) {
		$randstring .= $characters[rand(0, strlen($characters))];
	}
	return $randstring;
}

class JcqModelTokens extends JModel {

	private $db;

	function __construct()
	{
		parent::__construct();
		$this->db = $this->getDBO();
	}

	function getToken($ID)
	{
		$query = 'SELECT * FROM jcq_token WHERE ID = '.$ID;
		$this->db->setQuery($query);
		$token = $this->db->loadObject();
			
		if ($token === null) JError::raiseError(500, 'Token with ID: '.$ID.' not found.');
		else return $token;
	}
	
	function removeTokens($tokenIDs,$delAnswers=false)
	{
		// beforehand delete the answers (if any)
		if ($delAnswers)
		{
			foreach ($tokenIDs as $oneID)
			{
				$token = $this->getToken($oneID);
				$usergroup = $this->getUsergroupFromToken($oneID);
				$project = $this->getProjectFromUsergroup($usergroup->ID);
			
				$this->db->setQuery("DELETE FROM jcq_proj".$project->ID." WHERE tokenID=".$token->ID);
				if (!$this->db->query()) JError::raiseError(500, 'Error deleting answers: '.$this->db->getErrorMsg());
			}
		}
		
		$this->db->setQuery("DELETE FROM jcq_token WHERE ID IN (".implode(',', $tokenIDs).")");
		if (!$this->db->query()) JError::raiseError(500, 'Error deleting tokens: '.$this->db->getErrorMsg());
	}
	
	function getUsergroupFromToken($tokenID)
	{
		$query = 'SELECT * FROM jcq_token WHERE ID = '.$tokenID;
		$this->db->setQuery($query);
		$token = $this->db->loadObject();
			
		if ($token === null) JError::raiseError(500, 'Token with ID: '.$tokenID.' not found.');
		else
		{
			$query = 'SELECT * FROM jcq_usergroup WHERE ID = '.$token->usergroupID;
			$this->db->setQuery($query);
			$usergroup = $this->db->loadObject();
	
			if ($usergroup === null) JError::raiseError(500, 'User group with ID: '.$token->usergroupID.' not found.');
			else return $usergroup;
		}
	}
		
	function getProjectFromUsergroup($usergroupID)
	{
		$query = 'SELECT * FROM jcq_usergroup WHERE ID = '.$usergroupID;
		$this->db->setQuery($query);
		$usergroup = $this->db->loadObject();
			
		if ($usergroup === null) JError::raiseError(500, 'User group with ID: '.$usergroupID.' not found.');
		else
		{
			$query = 'SELECT * FROM jcq_project WHERE ID = '.$usergroup->projectID;
			$this->db->setQuery($query);
			$project = $this->db->loadObject();
	
			if ($project === null) JError::raiseError(500, 'Project with ID: '.$usergroup->projectID.' not found.');
			else return $project;
		}
	}

	
	function getNewToken($usergroupID)
	{
		$tokenTableRow = $this->getTable();
		$tokenTableRow->ID = 0;
		$tokenTableRow->token = RandomString(8);
		$tokenTableRow->usergroupID = $usergroupID;
		return $tokenTableRow;
	}

	function saveToken($token)
	{
		$tokenTableRow = $this->getTable();
		
		if (!$tokenTableRow->bind($token)) JError::raiseError(500, 'Error binding data');
		if (!$tokenTableRow->check()) JError::raiseError(500, 'Invalid data');
		if (!$tokenTableRow->store()) JError::raiseError(500, 'Error inserting data: '.$tokenTableRow->getError());
		
		return $tokenTableRow->ID;
	}

	function getTokens($usergroupID)
	{
		$this->db->setQuery("SELECT * FROM jcq_token WHERE usergroupID=$usergroupID");
		$results = $this->db->loadObjectList();
		return $results;
	}
	
	function getTokenIDs($usergroupID)
	{
		$this->db->setQuery("SELECT ID FROM jcq_token WHERE usergroupID=$usergroupID");
		$results = $this->db->loadColumn();
		return $results;
	}
	
}

