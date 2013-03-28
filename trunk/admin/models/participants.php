<?php
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');

class JcqModelParticipants extends JModel {
	 
	private $db;
	
	function __construct() 
	{
		parent::__construct();
		$this->db = $this->getDBO();
	}

	function getParticipantsBegun($projectID)
	{
		$this->db->setQuery("SELECT sessionID FROM jcq_proj$projectID");
		$results = $this->db->loadResultArray();
		if ($results==null) return 0;
		else return count($results);
	}

	function getParticipantsFinishedFirst($projectID)
	{
		$this->db->setQuery("SELECT ID FROM jcq_page WHERE projectID=$projectID ORDER BY ord");
		$results = $this->db->loadResultArray();
		if ($results==null) return null;
		else
		{
			$firstpageID = $results[0];
			$this->db->setQuery("SELECT sessionID FROM jcq_proj$projectID WHERE curpage!=$firstpageID");
			$results = $this->db->loadResultArray();
			if ($results==null) return 0;
			else return count($results);			
		}
	}
	
	function getParticipantsFinished($projectID)
	{
		$this->db->setQuery("SELECT sessionID FROM jcq_proj$projectID WHERE finished=1");
		$results = $this->db->loadResultArray();
		if ($results==null) return 0;
		else return count($results);
	}
	
	function getAverageDurationFinished($projectID)
	{
		$this->db->setQuery("SELECT timestampBegin, timestampEnd FROM jcq_proj$projectID WHERE finished=1");
		$results = $this->db->loadObjectList();
		if ($results==null) return 0;
		else
		{
			$sumduration = 0;
			for ($i=0;$i<count($results);$i++) $sumduration += ($results[$i]->timestampEnd - $results[$i]->timestampBegin);
			return floatval($sumduration)/floatval($i);
		}
	}

	function getMediumDurationFinished($projectID)
	{
		$this->db->setQuery("SELECT timestampBegin, timestampEnd FROM jcq_proj$projectID WHERE finished=1");
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
	
	function getLastFinished($projectID)
	{
		$this->db->setQuery("SELECT timestampEnd FROM jcq_proj$projectID WHERE finished=1 ORDER BY timestampEnd DESC");
		$results = $this->db->loadObjectList();
		if ($results==null) return null;
		else return $results[0]->timestampEnd;
	}
	
	function getLastBegun($projectID)
	{
		$this->db->setQuery("SELECT timestampBegin FROM jcq_proj$projectID ORDER BY timestampBegin DESC");
		$results = $this->db->loadObjectList();
		if ($results==null) return null;
		else return $results[0]->timestampBegin;
	}
}

