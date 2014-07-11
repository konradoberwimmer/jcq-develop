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
		//delete the old versions of questionformlayout
		for ($i=1;$i<=7;$i++)
		{
			$filename = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_jcq'.DS.'views'.DS.'questionform'.DS.'tmpl'.DS.'questionformlayout'.$i.'.php';
			if (file_exists($filename)) unlink($filename);
		}
	}

	function preflight($type, $parent)
	{
	
	}

	function postflight($type, $parent)
	{
	
	}
}