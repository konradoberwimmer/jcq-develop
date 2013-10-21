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
	
	function getProgramfiles($projectID)
	{
		$db = $this->getDBO();
		$db->setQuery("SELECT * FROM jcq_programfile WHERE projectID=$projectID");
		return $db->loadObjectList();
	}

}
