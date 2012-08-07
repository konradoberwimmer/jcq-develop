<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

class JcqViewProjectlist extends JView
{

	function display()
	{
       $model = $this->getModel();
       $projects = $model->getProjects();
       $this->assignRef('projects', $projects); 
       
       JToolBarHelper::title('JCQ: Projects', 'generic.png');
       if ($projects != null) JToolBarHelper::deleteList("Do you really want to delete the selected projects?",'removeProject','Remove');
       if ($projects != null) JToolBarHelper::editListX('editProject','Edit');
       JToolBarHelper::addNewX('addProject','New project');
       parent::display();
	}
}