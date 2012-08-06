<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
class TOOLBAR_jcq {
	function _NEW() {
		JToolBarHelper::save();
		JToolBarHelper::apply();
		JToolBarHelper::cancel();
	}
	function _DEFAULT() {
		JToolBarHelper::title(JText::_('JCQ'), 'generic.png' );
		JToolBarHelper::addNew();
	}
}