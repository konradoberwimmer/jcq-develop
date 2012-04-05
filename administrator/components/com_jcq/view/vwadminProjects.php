<?php
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'view'.DS.'vwadmin.php');
require_once(JPATH_COMPONENT_SITE.DS.'model'.DS.'jcqProjects.php');
require_once(JPATH_COMPONENT_SITE.DS.'model'.DS.'jcqProject.php');

class vwadminProjects extends vwadmin
{
	function __construct($id) { } //for this view, id is not used
	
	public function doTask()
	{
		#TODO doTask in vwadminProjects
	}
	
	protected function breadcrumb()
	{
		return "<a href='".JURI::root()."administrator".DS."index.php?option=com_jcq'>JCQ</a>";
	}
	
	public function show()
	{
		$projects = JCQProjects::getProjectIDs(JFactory::getDBO());
		if (!isset($projects) || count($projects)==0) echo("<p>Currently there are no projects.</p>");
		else
		{
			echo("<h3>Joomla Complex Questionnaires</h3>");
			echo("<form>");
			echo("<p>The JCQ component contains ".count($projects)." project(s).</p>");
			echo("<table class='list'>");
			echo("<tr style='border-bottom: 1px solid grey;'><th>Select</th><th>Name</th><th>URL</th><th>Class file</th><th>Class name</th><th>Options</th><th>Pages</th></tr>");
			foreach ($projects as $id)
			{
				$id=$id['ID'];
				$project = new JCQProject();
				$project->loadFromDatabase(JFactory::getDBO(),$id,false);
				echo("<tr>");
				echo("<td><input type='checkbox' name='project_$id' value=''/></td>"); //checkbox
				echo("<td><a href='".JURI::root()."administrator".DS."index.php?option=com_jcq&view=vwadminProject&id=$id'>".$project->getName()."</a></td>"); //name
				echo("<td><a href='".JURI::root()."index.php?option=com_jcq&pjid=$id' target='_blank'>".JURI::root()."index.php?option=com_jcq&pjid=$id</a></td>"); //URL
				echo("<td>".$project->getClassfileStr()."</td>"); //classfile
				echo("<td>".$project->getClassname()."</td>"); //class name
				echo("<td>".$project->getOptionsStr()."</td>"); //options as string
				echo("<td>".$project->numPages()."</td>"); //number of pages
				echo("</tr>");
			}
			echo("</table>");
			echo("</form>");
		}
	}
}
