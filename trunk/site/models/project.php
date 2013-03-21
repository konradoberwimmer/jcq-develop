<?php
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');

class JcqModelProject extends JModel
{
	function getCSSfilename($projectID)
	{
		$sqlproject = "SELECT cssfile FROM jcq_project WHERE ID=$projectID";
		$db = $this->getDBO();
		$db->setQuery($sqlproject);
		return $db->loadObject()->cssfile;
	}
	
	function getClassfilename($projectID)
	{
		$sqlproject = "SELECT classfile FROM jcq_project WHERE ID=$projectID";
		$db = $this->getDBO();
		$db->setQuery($sqlproject);
		return $db->loadObject()->classfile;
	}
	
	function getClassname($projectID)
	{
		$sqlproject = "SELECT classname FROM jcq_project WHERE ID=$projectID";
		$db = $this->getDBO();
		$db->setQuery($sqlproject);
		return $db->loadObject()->classname;
	}
}
