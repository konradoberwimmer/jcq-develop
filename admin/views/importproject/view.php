<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

class JcqViewImportproject extends JView
{
	function display()
	{
		JToolBarHelper::title('JCQ: Import project', 'generic.png');
		JToolBarHelper::cancel();

		parent::display();
	}
}
