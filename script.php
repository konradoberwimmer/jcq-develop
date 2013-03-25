<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

class com_jcqInstallerScript
{
	function install($parent)
	{
		
	}

	function uninstall($parent)
	{
		$db = JFactory::getDbo();
		$db->setQuery('SELECT * FROM jcq_project');
		$projects = $db->loadObjectList();
		foreach ($projects as $project)
		{
			$query = "DROP TABLE jcq_proj".$project->ID;
			$db->setQuery($query);
			if (!$db->query()){
				$errorMessage = JFactory::getDbo()->getErrorMsg();
				JError::raiseError(500, 'Error deleting projects: '.$errorMessage);
			}
		}
	}

	
	function update($parent)
	{
	
	}

	function preflight($type, $parent)
	{
	
	}

	function postflight($type, $parent)
	{
	
	}
}