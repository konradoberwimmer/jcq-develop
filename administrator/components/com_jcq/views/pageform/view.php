<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

class JcqViewPageform extends JView
{
	function displayEdit($pageID){
		$model = $this->getModel();
		$page = $model->getPage($pageID);
		$project = $model->getProjectFromPage($pageID);
		$this->assignRef('page', $page);
		$this->assignRef('project', $project);
	
		JToolBarHelper::title('JCQ: Edit page');
		JToolBarHelper::save("savePage","Save");
		JToolBarHelper::cancel("cancelAddPage","Cancel");
		parent::display();
	}
	
	function displayAdd($projectID){
		$model = $this->getModel();
		$page = $model->getNewPage($projectID);
		$this->assignRef('page', $page);
	
		JToolBarHelper::title('JCQ: New page');
		JToolBarHelper::save("savePage","Save");
		JToolBarHelper::cancel("cancelAddPage","Cancel");
		parent::display();
	}
}