<?php
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');

class JcqModelPage extends JModel
{
	private $pageID = null;
	private $db;
	
	function __construct()
	{
		parent::__construct();
		$this->db = $this->getDBO();
	}
	
	function setPage($pageID)
	{
		$this->pageID = $pageID;
	}
	
	function getPage()
	{
		$this->db->setQuery("SELECT * FROM jcq_page WHERE ID=".$this->pageID);
		return $this->db->loadObject();
	}
	
	function getPagePosition()
	{
		$this->db->setQuery("SELECT * FROM jcq_page WHERE ID=".$this->pageID);
		$page = $this->db->loadObject();
		$this->db->setQuery("SELECT * FROM jcq_page WHERE projectID=".$page->projectID." ORDER BY isFinal, ord");
		$pages = $this->db->loadObjectList();
		for ($i=0;$i<count($pages);$i++) if ($pages[$i]->ID == $this->pageID) break;
		return (float)$i/((float)(count($pages)-1));
	}
	
	function getProjectName()
	{
		$this->db->setQuery("SELECT * FROM jcq_page WHERE ID=".$this->pageID);
		$page = $this->db->loadObject();
		$this->db->setQuery("SELECT * FROM jcq_project WHERE ID=".$page->projectID);
		$project = $this->db->loadObject();
		return $project->name;
	}
	
	//requires the pageID to be set
	function getQuestions()
	{
		$sqlquestions = "SELECT * FROM jcq_question WHERE pageID=".$this->pageID." ORDER BY ord";
		$this->db->setQuery($sqlquestions);
		return $this->db->loadObjectList();
	}
	
	function getScaleToQuestion($questionID)
	{
		$query = 'SELECT * FROM jcq_questionscales WHERE questionID = '.$questionID;
		$this->db->setQuery($query);
		$scale = $this->db->loadObjectList();
		if ($scale==null) return null;
		
		$scaleID = $scale[0]->scaleID;
		$query = 'SELECT * FROM jcq_code  WHERE scaleID = '.$scaleID.' ORDER BY ord';
		$this->db->setQuery($query);
		return $this->db->loadObjectList();
	}
	
	function getScalesToQuestion($questionID)
	{
		$query = 'SELECT * FROM jcq_scale, jcq_questionscales WHERE jcq_scale.ID = jcq_questionscales.scaleID AND jcq_questionscales.questionID = '.$questionID.' ORDER BY ord';
		$this->db->setQuery($query);
		return $this->db->loadObjectList();
	}
	
	function getCodesToScale($scaleID)
	{
		$query = 'SELECT * FROM jcq_code WHERE scaleID = '.$scaleID.' ORDER BY ord';
		$this->db->setQuery($query);
		return $this->db->loadObjectList();
	}
	
	function getItemsToQuestion($questionID)
	{
		$query = 'SELECT * FROM jcq_item  WHERE questionID = '.$questionID.' ORDER BY ord';
		$this->db->setQuery($query);
		return $this->db->loadObjectList();
	}
}