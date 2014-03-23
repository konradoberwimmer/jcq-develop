<?php
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');
require_once(JPATH_COMPONENT.DS.'models'.DS.'tokens.php');

class JcqModelUsergroups extends JModel {

	private $db;

	function __construct()
	{
		parent::__construct();
		$this->db = $this->getDBO();
	}

	function getUsergroup($ID)
	{
		$query = 'SELECT * FROM jcq_usergroup WHERE ID = '.$ID;
		$this->db->setQuery($query);
		$usergroup = $this->db->loadObject();
			
		if ($usergroup === null) JError::raiseError(500, 'User group with ID: '.$ID.' not found.');
		else return $usergroup;
	}

	function getUsergroups($projectID)
	{
		$this->db->setQuery("SELECT * FROM jcq_usergroup WHERE projectID=$projectID ORDER BY val");
		$results = $this->db->loadObjectList();
		return $results;
	}

	function getAllUsergroupsList()
	{
		$this->db->setQuery("SELECT jcq_usergroup.ID AS ug_ID, jcq_usergroup.name AS ug_name, jcq_project.name AS proj_name FROM jcq_usergroup JOIN jcq_project ON jcq_project.ID=jcq_usergroup.projectID ORDER BY jcq_project.name, jcq_usergroup.val");
		$results = $this->db->loadObjectList();
		return $results;
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

	function getNewUsergroup($projectID)
	{
		$usergroupTableRow =& $this->getTable('usergroups');
		$usergroupTableRow->ID = 0;
		$usergroupTableRow->name = '';
		$usergroupTableRow->val = 1; //FIXME should be set to highest value for this project
		$usergroupTableRow->projectID = $projectID;
		return $usergroupTableRow;
	}

	function copyUsergroup($projectID,$usergroupID)
	{
		$ugtocopy = $this->getUsergroup($usergroupID);
		$usergroupTableRow =& $this->getTable('usergroups');
		$usergroupTableRow->ID = 0;
		$usergroupTableRow->name = $ugtocopy->name;
		$usergroupTableRow->val = $ugtocopy->val;
		$usergroupTableRow->projectID = $projectID;
		if (!$usergroupTableRow->check()) JError::raiseError(500, 'Invalid data');
		if (!$usergroupTableRow->store()) JError::raiseError(500, 'Error inserting data: '.$usergroupTableRow->getError());
		//now copy all the tokens;
		$tokenstocopy = $this->getTokens($usergroupID);
		if ($tokenstocopy!==null)
		{
			foreach ($tokenstocopy as $onetoken)
			{
				$tokenTableRow =& $this->getTable('tokens');
				if (!$tokenTableRow->bind($onetoken)) JError::raiseError(500, 'Error binding data');
				$tokenTableRow->ID=0;
				$tokenTableRow->usergroupID=$usergroupTableRow->ID;
				if (!$tokenTableRow->check()) JError::raiseError(500, 'Invalid data');
				if (!$tokenTableRow->store())	JError::raiseError(500, 'Error inserting data: '.$tokenTableRow->getError());
			}
		}
	}

	function saveUsergroup($usergoup)
	{
		$usergroupTableRow =& $this->getTable();

		if (!$usergroupTableRow->bind($usergoup)) JError::raiseError(500, 'Error binding data');
		if (!$usergroupTableRow->check()) JError::raiseError(500, 'Invalid data');
		if (!$usergroupTableRow->store()) JError::raiseError(500, 'Error inserting data: '.$usergroupTableRow->getError());

		return $usergroupTableRow->ID;
	}

	function removeUsergroups($ugIDs,$delAnswers=false)
	{
		// beforehand delete tokens (if any) because answers might be deleted (otherwise the CASCADE would suffice)
		require_once(JPATH_COMPONENT.DS.'models'.DS.'tokens.php');
		$modeltokens = new JcqModelTokens();
		foreach ($ugIDs as $oneugID)
		{
			$tokens = $this->getTokens($oneugID);
			if ($tokens!=null && count($tokens)>0)
			{
				$arraytokenIDs = array();
				foreach ($tokens as $onetoken) array_push($arraytokenIDs, $onetoken->ID);
				$modeltokens->removeTokens($arraytokenIDs,$delAnswers);
			}
		}

		$this->db->setQuery("DELETE FROM jcq_usergroup WHERE ID IN (".implode(',', $ugIDs).")");
		if (!$this->db->query()) JError::raiseError(500, 'Error deleting usergroups: '.$this->db->getErrorMsg());
	}

	function getTokenCount($usergroupID)
	{
		$this->db->setQuery("SELECT ID FROM jcq_token WHERE usergroupID=$usergroupID");
		$results = $this->db->loadResultArray();
		if ($results==null) return 0;
		else return count($results);
	}

	function getTokens($usergroupID)
	{
		$this->db->setQuery("SELECT * FROM jcq_token WHERE usergroupID=$usergroupID");
		$results = $this->db->loadObjectList();
		return $results;
	}

	function addToken($usergroupID,$token)
	{
		require_once(JPATH_COMPONENT.DS.'models'.DS.'tokens.php');
		$modeltokens = new JcqModelTokens();

		$tokenTableRow =& $this->getTable('tokens');
		if (!$tokenTableRow->bind($token)) JError::raiseError(500, 'Error binding data');
		if ($tokenTableRow->token==null || strlen($tokenTableRow->token)==0) $tokenTableRow->token = RandomString(8);
		$tokenTableRow->usergroupID = $usergroupID;
		if (!$tokenTableRow->check()) JError::raiseError(500, 'Invalid data');
		if (!$tokenTableRow->store())	JError::raiseError(500, 'Error inserting data: '.$tokenTableRow->getError());
	}

	function addTokens($usergoup)
	{
		require_once(JPATH_COMPONENT.DS.'models'.DS.'tokens.php');
		$modeltokens = new JcqModelTokens();

		$numTokens = $usergoup['numTokens'];
		$usergroupID = $usergoup['ID'];
		if (!is_numeric($numTokens)) JError::raiseError(500, 'Invalid number of Tokens: '.$numTokens);
		#FIXME do it better with one access to DB and check if token is created multiple times
		for ($i=0; $i<$numTokens; $i++)
		{
			$tokenTableRow = $modeltokens->getNewToken($usergroupID);
			if (!$tokenTableRow->store())	JError::raiseError(500, 'Error inserting data: '.$tokenTableRow->getError());
		}
	}

	function getParticipantsBegun($projectID,$groupID=null)
	{
		if ($groupID===null) $this->db->setQuery("SELECT sessionID FROM jcq_proj$projectID WHERE preview=0");
		else $this->db->setQuery("SELECT sessionID FROM jcq_proj$projectID WHERE preview=0 AND groupID=$groupID");
		$results = $this->db->loadResultArray();
		if ($results==null) return 0;
		else return count($results);
	}

	function getParticipantsFinishedFirst($projectID,$groupID=null)
	{
		$this->db->setQuery("SELECT ID FROM jcq_page WHERE projectID=$projectID ORDER BY isFinal, ord");
		$results = $this->db->loadResultArray();
		if ($results==null) return null;
		else
		{
			$firstpageID = $results[0];
			if ($groupID===null) $this->db->setQuery("SELECT sessionID FROM jcq_proj$projectID WHERE curpage!=$firstpageID AND preview=0");
			else $this->db->setQuery("SELECT sessionID FROM jcq_proj$projectID WHERE curpage!=$firstpageID AND preview=0 AND groupID=$groupID");
			$results = $this->db->loadResultArray();
			if ($results==null) return 0;
			else return count($results);
		}
	}

	function getParticipantsFinished($projectID,$groupID=null)
	{
		if ($groupID===null) $this->db->setQuery("SELECT sessionID FROM jcq_proj$projectID WHERE finished=1 AND preview=0");
		else $this->db->setQuery("SELECT sessionID FROM jcq_proj$projectID WHERE finished=1 AND preview=0 AND groupID=$groupID");
		$results = $this->db->loadResultArray();
		if ($results==null) return 0;
		else return count($results);
	}

	function getAverageDurationFinished($projectID,$groupID=null)
	{
		if ($groupID===null) $this->db->setQuery("SELECT timestampBegin, timestampEnd FROM jcq_proj$projectID WHERE finished=1 AND preview=0");
		else $this->db->setQuery("SELECT timestampBegin, timestampEnd FROM jcq_proj$projectID WHERE finished=1 AND preview=0 AND groupID=$groupID");
		$results = $this->db->loadObjectList();
		if ($results==null) return 0;
		else
		{
			$sumduration = 0;
			for ($i=0;$i<count($results);$i++) $sumduration += ($results[$i]->timestampEnd - $results[$i]->timestampBegin);
			return floatval($sumduration)/floatval($i);
		}
	}

	function getMediumDurationFinished($projectID,$groupID=null)
	{
		if ($groupID===null) $this->db->setQuery("SELECT timestampBegin, timestampEnd FROM jcq_proj$projectID WHERE finished=1 AND preview=0");
		else $this->db->setQuery("SELECT timestampBegin, timestampEnd FROM jcq_proj$projectID WHERE finished=1 AND preview=0 AND groupID=$groupID");
		$results = $this->db->loadObjectList();
		if ($results==null) return 0;
		else
		{
			$durations = array();
			for ($i=0;$i<count($results);$i++) $durations[$i] = ($results[$i]->timestampEnd - $results[$i]->timestampBegin);
			sort($durations);
			return $durations[floor($i/2.0)];
		}
	}

	function getLastFinished($projectID,$groupID=null)
	{
		if ($groupID===null) $this->db->setQuery("SELECT timestampEnd FROM jcq_proj$projectID WHERE finished=1 AND preview=0 ORDER BY timestampEnd DESC");
		else $this->db->setQuery("SELECT timestampEnd FROM jcq_proj$projectID WHERE finished=1 AND preview=0 AND groupID=$groupID ORDER BY timestampEnd DESC");
		$results = $this->db->loadObjectList();
		if ($results==null) return null;
		else return $results[0]->timestampEnd;
	}

	function getLastBegun($projectID,$groupID=null)
	{
		if ($groupID===null) $this->db->setQuery("SELECT timestampBegin FROM jcq_proj$projectID WHERE finished=1 AND preview=0 ORDER BY timestampBegin DESC");
		else $this->db->setQuery("SELECT timestampBegin FROM jcq_proj$projectID WHERE finished=1 AND preview=0 AND groupID=$groupID ORDER BY timestampBegin DESC");
		$results = $this->db->loadObjectList();
		if ($results==null) return null;
		else return $results[0]->timestampBegin;
	}
}

