<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

class JcqViewExportproject extends JView
{
	function display($projectID)
	{
		JToolBarHelper::title('JCQ: Export project', 'generic.png');
		JToolBarHelper::cancel();
		
		$this->assignRef('projectID',$projectID);
		parent::display();
	}
}
