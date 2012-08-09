<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

class JcqViewPageform extends JView
{
	function displayAdd($projectID){
		$model = $this->getModel();
		$page = $model->getNewPage($projectID);
		$this->assignRef('page', $page);
	
		JToolBarHelper::title('JCQ: New Page');
		JToolBarHelper::save("savePage","Save");
		JToolBarHelper::cancel("cancelAddPage","Cancel");
		parent::display();
	}
}