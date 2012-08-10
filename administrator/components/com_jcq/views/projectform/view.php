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
		$pages = $model->getPages($projectID);
		$this->assignRef('pages', $pages);
		
		$questioncounts = array();
		$i=0;
		foreach ($pages as $page)
		{
			$questioncount = $model->getQuestionCount($page->ID);
			$questioncounts[$i] = $questioncount;
			$i++;
		}
		$this->assignRef('questioncounts', $questioncounts);
		
		JToolBarHelper::title('JCQ: Edit project');
		if ($model->getPageCount($projectID) > 0) JToolBarHelper::deleteList("Do you really want to delete the selected pages?",'removePage','Remove page(s)');
		if ($model->getPageCount($projectID) > 0) JToolBarHelper::editList('editPage','Edit page');
		JToolBarHelper::addNewX('addPage','New page');
		JToolBarHelper::divider();
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

