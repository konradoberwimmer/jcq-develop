<?php
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');

class JcqModelScales extends JModel {

	//TODO: secure against insertion

	function getScale($scaleID)
	{
		$query = 'SELECT * FROM jcq_scale WHERE jcq_scale.ID = '.$scaleID;
		$db = $this->getDBO();
		$db->setQuery($query);
		return $db->loadObject();
	}
	
	function getScales($questionID)
	{
		$query = 'SELECT * FROM jcq_scale, jcq_questionscales WHERE jcq_scale.ID = jcq_questionscales.scaleID AND jcq_questionscales.questionID = '.$questionID.' ORDER BY ord';
		$db = $this->getDBO();
		$db->setQuery($query);
		return $db->loadObjectList();
	}

	function getPredefinedScales()
	{
		$query = 'SELECT * FROM jcq_scale WHERE jcq_scale.predefined = 1';
		$db = $this->getDBO();
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	function getNewPredefinedScale()
	{
		$scaleTableRow =& $this->getTable('scales');
		$scaleTableRow->ID = 0;
		$scaleTableRow->name = '';
		$scaleTableRow->prepost = '%i';
		$scaleTableRow->predefined = 1;
		return $scaleTableRow;
	}
	
	function getCodes($scaleID)
	{
		$query = 'SELECT * FROM jcq_code WHERE scaleID = '.$scaleID.' ORDER BY ord';
		$db = $this->getDBO();
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	function getCodeCount($scaleID)
	{
		$query = 'SELECT * FROM jcq_code WHERE scaleID = '.$scaleID.' ORDER BY ord';
		$db = $this->getDBO();
		$db->setQuery($query);
		$codes = $db->loadResultArray();
		 
		if ($codes == null) return 0;
		else return count($codes);
	}
	
	function addAttachedScale($questionID,$scaleID,$ord)
	{
		$query = "INSERT INTO jcq_questionscales (questionID, scaleID, ord) VALUES ($questionID,$scaleID,$ord)";
		$db = $this->getDBO();
		$db->setQuery($query);
		if (!$db->query()){
			$errorMessage = $this->getDBO()->getErrorMsg();
			JError::raiseError(500, 'Error adding attached scales: '.$errorMessage);
		}
	}
	
	function saveScale($scale)
	{
		$scaleTableRow =& $this->getTable();
			
		// Bind the form fields to the greetings table
		if (!$scaleTableRow->bind($scale)) JError::raiseError(500, 'Error binding data');
	
		// Make sure the greetings record is valid
		if (!$scaleTableRow->check()) JError::raiseError(500, 'Invalid data');
			
		if (!$scaleTableRow->store())
		{
			$errorMessage = $scaleTableRow->getError();
			JError::raiseError(500, 'Error inserting data: '.$errorMessage);
		}
	
		// if a new page is created, create 5 codes
		if ($scale['ID']==0)
		{
			for ($i=1;$i<=5;$i++)
			{
				$newcode =& $this->getTable('codes');
				$newcode->scaleID = $scaleTableRow->ID;
				$newcode->ord = $i;
				$newcode->code = $i;
				if (!$newcode->store())
				{
					$errorMessage = $newcode->getError();
					JError::raiseError(500, 'Error inserting data: '.$errorMessage);
				}
			}
		}
		
		return $scaleTableRow->ID;
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
	
	function clearAttachedScales($questionID)
	{
		$query = "DELETE FROM jcq_questionscales WHERE questionID = ".$questionID;
		$db = $this->getDBO();
		$db->setQuery($query);
		if (!$db->query()){
			$errorMessage = $this->getDBO()->getErrorMsg();
			JError::raiseError(500, 'Error deleting attached scales: '.$errorMessage);
		}
	}
	
	function deleteScales($scaleIDs)
	{
		$query = "DELETE FROM jcq_scale WHERE ID IN (".implode(',', $scaleIDs).")";
		$db = $this->getDBO();
		$db->setQuery($query);
		if (!$db->query()){
			$errorMessage = $this->getDBO()->getErrorMsg();
			JError::raiseError(500, 'Error deleting scales: '.$errorMessage);
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