<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

class JcqViewEditprogramfile extends JView
{
	function display($programfileID)
	{
		JToolBarHelper::title('JCQ: Edit program file', 'generic.png');
		JToolBarHelper::save("saveEditedProgramfile","Save");
		JToolBarHelper::cancel("cancelEditProgramfile","Cancel");
		
		$programfile = $this->getModel()->getProgramfile($programfileID);
		$project = $this->getModel("projects")->getProject($programfile->projectID);
		$this->assignRef('programfile',$programfile);
		$this->assignRef('project',$project);
		$this->assignRef('programfileID',$programfileID);
		parent::display();
	}
}
