<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

class JcqViewEditimport extends JView
{
	function display($importID)
	{
		JToolBarHelper::title('JCQ: Edit program file', 'generic.png');
		JToolBarHelper::save("saveEditedImport","Save");
		JToolBarHelper::cancel("cancelEditImport","Cancel");
		
		$import = $this->getModel()->getImport($importID);
		$project = $this->getModel("projects")->getProject($import->projectID);
		$this->assignRef('import',$import);
		$this->assignRef('project',$project);
		$this->assignRef('importID',$importID);
		parent::display();
	}
}
