<?php
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'view'.DS.'vwadmin.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'view'.DS.'vwadminProjects.php');
require_once(JPATH_COMPONENT_SITE.DS.'model'.DS.'jcqProject.php');

class vwadminProject extends vwadmin
{
	private $id;
	
	function __construct($id) 
	{
		if (isset($id)) $this->id=$id;
	}
		
	public function doTask()
	{
		#TODO doTask in vwadminProject
	}
	
	protected function breadcrumb()
	{
		$parentview = new vwadminProjects(0);
		$project = new JCQProject();
		$project->loadFromDatabase(JFactory::getDBO(),$this->id,false);
		return ($parentview->breadcrumb())." &gt; <a href='".JURI::root()."administrator".DS."index.php?option=com_jcq&view=vwadminProject&id=".$this->id."'>".$project->getName()."</a>";
	}
	
	public function show()
	{
		JToolBarHelper::save();
		$project = new JCQProject();
		$project->loadFromDatabase(JFactory::getDBO(),$this->id,false);
		echo("<h3>Project: ".$project->getName()."</h3>");
		echo("<form>");
		echo("<table class='settings'>");
		$name=$project->getName();
		echo("<tr><td>Name:</td><td><input type='text' value='$name'></td></tr>");
		echo("</table>");
		echo("</form>");
	}
}