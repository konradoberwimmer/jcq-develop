<?php
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');
require_once(JPATH_COMPONENT.DS.'models'.DS.'items.php');

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
		if ($code['ID']<0) $code['ID']=0;
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
	
	function deleteScale($ID)
	{
		//first delete all the codes from scale
		$codes = $this->getCodes($ID);
		if ($codes!==null) foreach ($codes as $code) $this->deleteCode($code->ID);
		//then delete the scale itself
		$this->db->setQuery("DELETE FROM jcq_scale WHERE ID = $ID");
		if (!$this->db->query()) JError::raiseError(500, 'FATAL: '.$this->db->getErrorMsg());
	}
	
	function deleteCode($ID)
	{
		//first delete binded items (if any)
		$model_items = new JcqModelItems();
		$bindeditems = $this->getCodebindedItems($ID);
		if ($bindeditems!==null) foreach ($bindeditems as $bindeditem) $model_items->deleteItem($bindeditem->ID);
		//then delete the code itself
		$this->db->setQuery("DELETE FROM jcq_code WHERE ID = $ID");
		if (!$this->db->query()) JError::raiseError(500, 'FATAL: '.$this->db->getErrorMsg());
	}
	
	function getCodebindedItems($codeID)
	{
		$this->db->setQuery("SELECT * FROM jcq_item WHERE bindingType='CODE' AND bindingID=".$codeID);
		$sqlresult = $this->db->loadObjectList();
		if ($sqlresult===false) JError::raiseError(500, 'Error fetching textfields: '.$this->getDBO()->getErrorMsg());
		else return $sqlresult;
	}
	
	function addrmTextfields($codeID,$questionID)
	{
		$model_items = new JcqModelItems();
		$bindeditems = $this->getCodebindedItems($codeID);
		//Delete if a textfield is already there
		if ($bindeditems!==null && count($bindeditems)>0)
		{
			foreach ($bindeditems as $bindeditem) $model_items->deleteItem($bindeditem->ID);
		}
		else //insert a textfield
		{
			$newitem = $model_items->buildNewItem($questionID, 3);
			$newitem->mandatory = 0;
			$newitem->bindingType = "CODE";
			$newitem->bindingID = $codeID;
			if (!$newitem->store()) JError::raiseError(500, 'FATAL: '.$newitem->getError());
		}
	}
}