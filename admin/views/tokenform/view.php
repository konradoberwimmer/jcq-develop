<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

class JcqViewTokenform extends JView
{
	function displayEdit($tokenID)
	{
		$model = $this->getModel();
		$token = $model->getToken($tokenID);
		$this->assignRef('token',$token);
		$usergroup = $model->getUsergroupFromToken($tokenID);
		$this->assignRef('usergroup',$usergroup);
		$project = $model->getProjectFromUsergroup($usergroup->ID);
		$this->assignRef('project',$project);
				
		JToolBarHelper::title('JCQ: Edit token');
		JToolBarHelper::save("saveToken","Save");
		JToolBarHelper::cancel("cancelAddToken","Cancel");
		
		parent::display();
	}
	
	function displayAdd($usergroupID)
	{
		$model = $this->getModel();
		$token = $model->getNewToken($usergroupID);
		$this->assignRef('token', $token);

		JToolBarHelper::title('JCQ: New token');
		JToolBarHelper::save("saveToken","Save");
		JToolBarHelper::cancel("cancelAddToken","Cancel");
		parent::display();
	}
}