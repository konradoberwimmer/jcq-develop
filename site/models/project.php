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
	
	function getImports($projectID)
	{
		$sqlimports = "SELECT * FROM jcq_import WHERE projectID=$projectID";
		$db = $this->getDBO();
		$db->setQuery($sqlimports);
		return $db->loadObjectList();
	}

}
