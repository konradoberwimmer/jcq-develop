<?php
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');

class JcqModelPages extends JModel {
	
	function getPage($ID)
	{
		$query = 'SELECT * FROM jcq_page WHERE ID = '.$ID;
		$db = $this->getDBO();
		$db->setQuery($query);
		$page = $db->loadObject();
			
		if ($page === null) JError::raiseError(500, 'Page with ID: '.$ID.' not found.');
		else return $page;
	}
	
	function getNewPage($projectID)
	{
		$pageTableRow =& $this->getTable('pages');
		$pageTableRow->ID = 0;
		$pageTableRow->name = '';
		$pageTableRow->ord = 0; //FIXME should be set to highest value
		$pageTableRow->projectID = $projectID;
		return $pageTableRow;
	}
}