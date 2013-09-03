<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

class JcqViewUsergroupform extends JView
{
	function displayEdit($usergroupID){
		$model = $this->getModel();
		$usergroup = $model->getUsergroup($usergroupID);
		$project = $model->getProjectFromUsergroup($usergroupID);
		$tokens = $model->getTokens($usergroupID);
		$this->assignRef('usergroup', $usergroup);
		$this->assignRef('project', $project);
		$this->assignRef('tokens', $tokens);
		
		$path = 'administrator/components/com_jcq/js/';
		$filename = 'overridesubmit.js';
		JHTML::script($path.$filename, true);
		
		JToolBarHelper::title('JCQ: Edit user group');
		JToolBarHelper::save("saveUsergroup","Save");
		JToolBarHelper::cancel("cancelAddUsergroup","Cancel");
		parent::display();
	}

	function displayAdd($projectID){
		$model = $this->getModel();
		$usergroup = $model->getNewUsergroup($projectID);
		$this->assignRef('usergroup', $usergroup);

		JToolBarHelper::title('JCQ: New user group');
		JToolBarHelper::save("saveUsergroup","Save");
		JToolBarHelper::cancel("cancelAddUsergroup","Cancel");
		parent::display();
	}
}