<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

class JcqViewUploadtokensform extends JView
{
	function display($usergrouppost,$sheetData,$filename)
	{
		$this->assignRef('usergroupID', $usergrouppost['ID']);
		$usergroup = $this->getModel()->getUsergroup($this->usergroupID);
		$this->assignRef('usergroup',$usergroup);
		$project = $this->getModel()->getProjectFromUsergroup($this->usergroupID);
		$this->assignRef('project',$project);
		$this->assignRef('usergrouppost', $usergrouppost);
		$this->assignRef('sheetData',$sheetData);
		$this->assignRef('filename', $filename);
		
		JToolBarHelper::title('JCQ: Upload tokens');
		JToolBarHelper::save("insertUploadedTokens","Insert tokens");
		JToolBarHelper::cancel("cancelInsertUploadedTokens","Cancel");
		
		parent::display();
	}
}