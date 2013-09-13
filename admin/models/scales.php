<?php
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');

class JcqModelScales extends JModel {

	private $db;
	
	function __construct()
	{
		parent::__construct();
		$this->db = $this->getDBO();
	}

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
	
	function addAttachedScale($questionID,$scaleID,$ord,$mandatory)
	{
		$query = "INSERT INTO jcq_questionscales (questionID, scaleID, ord, mandatory) VALUES ($questionID,$scaleID,$ord,".($mandatory?"1":"0").")";
		$db = $this->getDBO();
		$db->setQuery($query);
		if (!$db->query()){
			$errorMessage = $this->getDBO()->getErrorMsg();
			JError::raiseError(500, 'Error adding attached scales: '.$errorMessage);
		}
	}
	
	function saveScale($scale)
	{
		
		//TODO: secure against insertion
		
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
		//first delete binded items (if any)
		foreach ($arrayIDs as $oneID)	
		{
			$bindeditems = $this->getCodebindedItems($oneID);
			if ($bindeditems!=null && count($bindeditems)>0)
			{
				foreach ($bindeditems as $bindeditem)
				{
					$this->db->setQuery("SELECT * FROM jcq_question WHERE ID=".$bindeditem->questionID);
					$question = $this->db->loadObject();
					if (!$question) JError::raiseError(500, 'Error getting question: '.$this->db->getErrorMsg());
					$this->db->setQuery("SELECT * FROM jcq_page WHERE ID=".$question->pageID);
					$page = $this->db->loadObject();
					if (!$page) JError::raiseError(500, 'Error getting page: '.$this->db->getErrorMsg());
					$this->db->setQuery("DELETE FROM jcq_item WHERE ID=".$bindeditem->ID);
					if (!$this->db->query()) JError::raiseError(500, 'Error deleting textfield: '.$this->db->getErrorMsg());
					//also delete data column
					$this->db->setQuery("ALTER TABLE jcq_proj".$page->projectID." DROP COLUMN p".$page->ID."q".$question->ID."i".$bindeditem->ID);
					if (!$this->db->query()) JError::raiseError(500, 'Error altering userdata table: '.$this->db->getErrorMsg());
				}
			}
		}
		//then delete the code
		$query = "DELETE FROM jcq_code WHERE ID IN (".implode(',', $arrayIDs).")";
		$db = $this->getDBO();
		$db->setQuery($query);
		if (!$db->query()){
			$errorMessage = $this->getDBO()->getErrorMsg();
			JError::raiseError(500, 'Error deleting codes: '.$errorMessage);
		}
	}
	
	function getCodebindedItems($codeID)
	{
		$this->db->setQuery("SELECT * FROM jcq_item WHERE bindingType='CODE' AND bindingID=".$codeID);
		$sqlresult = $this->db->loadObjectList();
		if ($sqlresult===false) JError::raiseError(500, 'Error fetching textfields: '.$this->getDBO()->getErrorMsg());
		else return $sqlresult;
	}
	
	function addrmTextfields($arrayIDs,$questionid)
	{
		foreach($arrayIDs as $oneID)
		{
			$this->db->setQuery("SELECT * FROM jcq_question WHERE ID=".$questionid);
			$question = $this->db->loadObject();
			if (!$question) JError::raiseError(500, 'Error getting question: '.$this->db->getErrorMsg());
			$this->db->setQuery("SELECT * FROM jcq_page WHERE ID=".$question->pageID);
			$page = $this->db->loadObject();
			if (!$page) JError::raiseError(500, 'Error getting page: '.$this->db->getErrorMsg());
			//Delete if a textfield is already there
			$bindeditems = $this->getCodebindedItems($oneID);
			if ($bindeditems!=null && count($bindeditems)>0)
			{
				foreach ($bindeditems as $bindeditem)
				{
					$this->db->setQuery("DELETE FROM jcq_item WHERE ID=".$bindeditem->ID);
					if (!$this->db->query()) JError::raiseError(500, 'Error deleting textfield: '.$this->db->getErrorMsg());
					//also delete data column
					$this->db->setQuery("ALTER TABLE jcq_proj".$page->projectID." DROP COLUMN p".$page->ID."q".$question->ID."i".$bindeditem->ID);
					if (!$this->db->query()) JError::raiseError(500, 'Error altering userdata table: '.$this->db->getErrorMsg());
				}
			} else
			{
				//if non exists so far, create one
				$itemTableRow =& $this->getTable('items');
				$itemTableRow->ord = 0;
				$itemTableRow->datatype = 3;
				$itemTableRow->prepost = "%s";
				$itemTableRow->varname = "question".$questionid."code".$oneID."text";
				$itemTableRow->mandatory = 0;
				$itemTableRow->questionID = $questionid;
				$itemTableRow->bindingType = "CODE";
				$itemTableRow->bindingID = $oneID;
				if (!$itemTableRow->store()) JError::raiseError(500, 'Error inserting textfield: '.$itemTableRow->getError());
				//also create the userdata table column
				$this->db->setQuery("ALTER TABLE jcq_proj".$page->projectID." ADD COLUMN p".$page->ID."q".$question->ID."i".$itemTableRow->ID." TEXT");
				if (!$this->db->query()) JError::raiseError(500, 'Error altering userdata table: '.$this->db->getErrorMsg());	
			}
		}
	}
}