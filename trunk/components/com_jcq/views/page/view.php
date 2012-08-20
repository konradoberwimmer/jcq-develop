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
		
		$modelpage = $this->getModel();
		$modelpage->setPage($pageID);
		$this->assignRef('page', $modelpage);
		$modeluserdata = $this->getModel('userdata');
		$this->assignRef('userdata', $modeluserdata);
		
		$this->assignRef('markmissing',$markmissing);
		$this->assignRef('pageID',$pageID);
		
		if ($this->markmissing)
		{
			echo '<div class="questionalertmissing">Bitte beantworten Sie noch die rot markierten Fragen!</div>';
		}
		parent::display();
	}
}