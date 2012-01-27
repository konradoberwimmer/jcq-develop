<?php
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'view'.DS.'vwadmin.php');
require_once(JPATH_COMPONENT_SITE.DS.'model'.DS.'jcqProjects.php');
require_once(JPATH_COMPONENT_SITE.DS.'model'.DS.'jcqProject.php');

class vwadminProjects extends vwadmin
{
	private $id;
	
	function __construct($id) 
	{
		if (isset($id)) $this->id=$id;
	}
	
	public function doTask()
	{
		#TODO doTask in vwadminProjects
	}
	
	public function show()
	{
		$projects = JCQProjects::getProjectIDs(JFactory::getDBO());
		if (!isset($projects) || count($projects)==0) echo("<p>Currently there are no projects.</p>");
		else
		{
			echo("<form>");
			echo("<p>The JCQ component contains ".count($projects)." project(s).</p>");
			echo("<table style='border-collapse: collapse;'>");
			echo("<tr style='border-bottom: 1px solid grey;'><th>Select</th><th>Name</th><th>URL</th><th>Class file</th><th>Class name</th><th>Options</th><th>Pages</th></tr>");
			foreach ($projects as $id)
			{
				$id=$id['ID'];
				$project = new JCQProject();
				$project->loadFromDatabase(JFactory::getDBO(),$id,false);
				echo("<tr>");
				echo("<td><input type='checkbox' name='project_$id' value=''/></td>"); //checkbox
				echo("<td>".$project->getName()."</td>"); //name
				echo("<td><a href='".JURI::root()."index.php?option=com_jcq&pjid=$id' target='_blank'>".JURI::root()."index.php?option=com_jcq&pjid=$id</a></td>"); //URL
				echo("<td>".$project->getClassfileStr()."</td>"); //classfile
				echo("</tr>");
			}
			echo("</table>");
			echo("</form>");
		}
	}
}
