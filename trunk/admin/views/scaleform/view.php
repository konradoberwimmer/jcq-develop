<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

class JcqViewScaleform extends JView
{
	function displayEdit($scaleID)
	{
		$model = $this->getModel();
		$scale = $model->getScale($scaleID);
		$this->assignRef('scale', $scale);
		$codes = $model->getCodes($scaleID);
		$this->assignRef('codes', $codes);
		
		JToolBarHelper::title('JCQ: Edit scale');
		JToolBarHelper::save("saveScale","Save");
		JToolBarHelper::cancel("cancelAddScale","Cancel");
		
		//add javascript functionality
		$path = 'administrator/components/com_jcq/js/';
		$filename = 'addcodes.js';
		JHTML::script($path.$filename, true);
		
		parent::display();
	}
	
	function displayAdd()
	{
		$model = $this->getModel();
		$scale = $model->getNewPredefinedScale();
		$this->assignRef('scale', $scale);

		JToolBarHelper::title('JCQ: New scale');
		JToolBarHelper::save("saveScale","Save");
		JToolBarHelper::cancel("cancelAddScale","Cancel");
		parent::display();
	}
}