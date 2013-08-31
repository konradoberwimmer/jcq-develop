<?php
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');

class JcqModelUsergroups extends JModel {
	 
	private $db;
	
	function __construct() 
	{
		parent::__construct();
		$this->db = $this->getDBO();
	}

	function getUsergroups($projectID)
	{
		$this->db->setQuery("SELECT * FROM jcq_projusergroup$projectID ORDER BY value");
		$results = $this->db->loadObjectList();
		return $results;
	}
	
	function getTokenCount($projectID, $usergroup)
	{
		$this->db->setQuery("SELECT ID FROM jcq_projtoken$projectID WHERE usergroupID=$usergroup");
		$results = $this->db->loadResultArray();
		if ($results==null) return 0;
		else return count($results);
	}
	
	function getParticipantsBegun($projectID,$groupID=null)
	{
		$this->db->setQuery("SELECT sessionID FROM jcq_proj$projectID WHERE preview=0".($groupID!==null?" AND groupID=".$groupID:""));
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
			$this->db->setQuery("SELECT sessionID FROM jcq_proj$projectID WHERE curpage!=$firstpageID AND preview=0".($groupID!==null?" AND groupID=".$groupID:""));
			$results = $this->db->loadResultArray();
			if ($results==null) return 0;
			else return count($results);			
		}
	}
	
	function getParticipantsFinished($projectID,$groupID=null)
	{
		$this->db->setQuery("SELECT sessionID FROM jcq_proj$projectID WHERE finished=1 AND preview=0".($groupID!==null?" AND groupID=".$groupID:""));
		$results = $this->db->loadResultArray();
		if ($results==null) return 0;
		else return count($results);
	}
	
	function getAverageDurationFinished($projectID,$groupID=null)
	{
		$this->db->setQuery("SELECT timestampBegin, timestampEnd FROM jcq_proj$projectID WHERE finished=1 AND preview=0".($groupID!==null?" AND groupID=".$groupID:""));
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
		$this->db->setQuery("SELECT timestampBegin, timestampEnd FROM jcq_proj$projectID WHERE finished=1 AND preview=0".($groupID!==null?" AND groupID=".$groupID:""));
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
		$this->db->setQuery("SELECT timestampEnd FROM jcq_proj$projectID WHERE finished=1 AND preview=0 ".($groupID!==null?" AND groupID=".$groupID:"")." ORDER BY timestampEnd DESC");
		$results = $this->db->loadObjectList();
		if ($results==null) return null;
		else return $results[0]->timestampEnd;
	}
	
	function getLastBegun($projectID,$groupID=null)
	{
		$this->db->setQuery("SELECT timestampBegin FROM jcq_proj$projectID WHERE preview=0 ".($groupID!==null?" AND groupID=".$groupID:"")." ORDER BY timestampBegin DESC");
		$results = $this->db->loadObjectList();
		if ($results==null) return null;
		else return $results[0]->timestampBegin;
	}
}

