<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

class JcqViewProjectform extends JView
{
	function displayEdit($projectID)
	{
		$model = $this->getModel();
		$project = $model->getProject($projectID);
		$this->assignRef('project', $project);
		
		JToolBarHelper::title('JCQ: Edit project');
		JToolBarHelper::save("saveProject","Save");
		JToolBarHelper::cancel();
		parent::display();
	}
		 
	function displayAdd(){
		$model = $this->getModel();
		$project = $model->getNewProject();
		$this->assignRef('project', $project);

		JToolBarHelper::title('JCQ: New Project');
		JToolBarHelper::save("saveProject","Save");
		JToolBarHelper::cancel();
		parent::display();
	}
}

