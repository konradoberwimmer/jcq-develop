<?php
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');

class JcqModelPage extends JModel
{
	private $pageID = null;
	
	function setPage($pageID)
	{
		$this->pageID = $pageID;
	}
	
	//requires the pageID to be set
	function getQuestions()
	{
		$sqlquestions = "SELECT * FROM jcq_question WHERE pageID=".$this->pageID." ORDER BY ord";
		$db = $this->getDBO();
		$db->setQuery($sqlquestions);
		return $db->loadObjectList();
	}
	
	function getScaleToQuestion($questionID)
	{
		$query = 'SELECT * FROM jcq_questionscales WHERE questionID = '.$questionID;
		$db = $this->getDBO();
		$db->setQuery($query);
		$scale = $db->loadObjectList();
		if ($scale==null) return null;
		
		$scaleID = $scale[0]->scaleID;
		$query = 'SELECT * FROM jcq_code  WHERE scaleID = '.$scaleID.' ORDER BY ord';
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	function getScalesToQuestion($questionID)
	{
		$query = 'SELECT * FROM jcq_scale, jcq_questionscales WHERE jcq_scale.ID = jcq_questionscales.scaleID AND jcq_questionscales.questionID = '.$questionID.' ORDER BY ord';
		$db = $this->getDBO();
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	function getCodesToScale($scaleID)
	{
		$query = 'SELECT * FROM jcq_code WHERE scaleID = '.$scaleID.' ORDER BY ord';
		$db = $this->getDBO();
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	function getItemsToQuestion($questionID)
	{
		$db = $this->getDBO();
		$query = 'SELECT * FROM jcq_item  WHERE questionID = '.$questionID.' ORDER BY ord';
		$db->setQuery($query);
		return $db->loadObjectList();
	}
}