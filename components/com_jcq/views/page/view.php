<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

class JcqViewPage extends JView
{
	function displayPage($pageID,$markmissing)
	{
		//add javascript functionality for inputForm
		$path = 'components/com_jcq/js/';
		$filename = 'submitinputform.js';
		JHTML::script($filename, $path, true);
		
		$modeluserdata = $this->getModel('userdata');
		$this->assignRef('userdata', $modeluserdata);
		
		$this->assignRef('pageID',$pageID);
		parent::display();
	}
}