<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

class JcqViewEditCSS extends JView
{
	function display($projectID)
	{
		JToolBarHelper::title('JCQ: Edit CSS file', 'generic.png');
		JToolBarHelper::save("saveEditedCSS","Save");
		JToolBarHelper::cancel("cancelEditCSS","Cancel");
		
		$project = $this->getModel("projects")->getProject($projectID);
		$this->assignRef('project',$project);
		parent::display();
	}
}
