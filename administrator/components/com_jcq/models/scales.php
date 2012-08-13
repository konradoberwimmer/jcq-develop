<?php
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');

class JcqModelScales extends JModel {

	//TODO: secure against insertion

	function getScales($questionID)
	{
		$query = 'SELECT * FROM jcq_scale, jcq_questionscales WHERE jcq_scale.ID = jcq_questionscales.scaleID AND jcq_questionscales.questionID = '.$questionID;
		$db = $this->getDBO();
		$db->setQuery($query);
		return $db->loadObjectList();
	}

	function getCodes($scaleID)
	{
		$query = 'SELECT * FROM jcq_code WHERE scaleID = '.$scaleID.' ORDER BY ord';
		$db = $this->getDBO();
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	function saveCode(array $code)
	{
		$codeTableRow =& $this->getTable('codes');
		if (!$codeTableRow->bind($code)) JError::raiseError(500, 'Error binding data');
		if (!$codeTableRow->check()) JError::raiseError(500, 'Invalid data');
		if (!$codeTableRow->store())
		{
			$errorMessage = $codeTableRow->getError();
			JError::raiseError(500, 'Error inserting data: '.$errorMessage);
		}
	}
	
	function deleteCodes($arrayIDs)
	{
		$query = "DELETE FROM jcq_code WHERE ID IN (".implode(',', $arrayIDs).")";
		$db = $this->getDBO();
		$db->setQuery($query);
		if (!$db->query()){
			$errorMessage = $this->getDBO()->getErrorMsg();
			JError::raiseError(500, 'Error deleting codes: '.$errorMessage);
		}
	}
}