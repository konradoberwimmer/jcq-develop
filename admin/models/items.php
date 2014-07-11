<?php
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');
require_once(JPATH_COMPONENT.DS.'models'.DS.'questions.php');

class JcqModelItems extends JModel {

	//TODO: secure against insertion

	private $db;
	
	function __construct()
	{
		parent::__construct();
		$this->db = $this->getDBO();
	}

	function getItem($itemID)
	{
		$query = 'SELECT * FROM jcq_item WHERE ID='.$itemID;
		$this->db->setQuery($query);
		return $this->db->loadObject();
	}

	function getItembindedItems($itemID)
	{
		$this->db->setQuery("SELECT * FROM jcq_item WHERE bindingType='ITEM' AND bindingID=".$itemID);
		$sqlresult = $this->db->loadObjectList();
		if ($sqlresult===false) JError::raiseError(500, 'Error fetching textfields: '.$this->getDBO()->getErrorMsg());
		else return $sqlresult;
	}

	function buildNewItem($questionID,$datatype,$scales=null)
	{
		$itemTableRow = $this->getTable();
		$itemTableRow->questionID = $questionID;
		$itemTableRow->datatype = $datatype;
		$this->db->setQuery("SELECT ord FROM jcq_item WHERE questionID=$questionID ORDER BY ord DESC");
		$items = $this->db->loadObjectList();
		if ($items!==null) $itemTableRow->ord = $items[0]->ord + 1;
		else $itemTableRow->ord = 1;
		if ($itemTableRow->store())
		{
			if ($scales===null) $this->addUserDataColumn($datatype, $itemTableRow->ID);
			else foreach ($scales as $scale) $this->addUserDataColumn($datatype, $itemTableRow->ID, $scale->ID);
		}
		else JError::raiseError(500, 'FATAL: '.$itemTableRow->getError());
		return $itemTableRow;
	}

	function saveItem(array $item, array $scales=null)
	{
		if ($item['ID']<0) $item['ID']=0;
		if (!isset($item['mandatory'])) $item['mandatory']=0;
		$itemTableRow = $this->getTable('items');
		if (!$itemTableRow->bind($item)) JError::raiseError(500, 'Error binding data');
		if (!$itemTableRow->check()) JError::raiseError(500, 'Invalid data');
		if (!$itemTableRow->store())
		{
			$errorMessage = $itemTableRow->getError();
			JError::raiseError(500, 'Error inserting data: '.$errorMessage);
		}
	
		//add user data column(s)
		if ($item['ID']==0)
		{
			if ($scales===null) $this->addUserDataColumn($itemTableRow->datatype, $itemTableRow->ID);
			else foreach ($scales as $scale) $this->addUserDataColumn($itemTableRow->datatype, $itemTableRow->ID, $scale->ID);
		}
	}

	function deleteItem($ID)
	{
		//first remove user data column(s)
		$model_questions = new JcqModelQuestions();
		$item = $this->getItem($ID);
		if ($item===null) return false;
		$questionID = $this->getItem($ID)->questionID;
		$pageID = $model_questions->getPageFromQuestion($questionID)->ID;
		$projectID = $model_questions->getProjectFromPage($pageID)->ID;
		$this->db->setQuery("SELECT CONCAT('ALTER TABLE jcq_proj$projectID ', GROUP_CONCAT('DROP COLUMN ',column_name)) AS statement FROM information_schema.columns WHERE table_name = 'jcq_proj$projectID' AND column_name LIKE 'i".$ID."_%';");
		$sqlresult = $this->db->loadResult();
		if ($sqlresult!=null)
		{
			$this->db->setQuery($sqlresult);
			if (!$this->db->query()) JError::raiseError(500, 'FATAL: '.$this->db->getErrorMsg());
		}
		//then remove item binded items (if any)
		$bindeditems = $this->getItembindedItems($ID);
		if ($bindeditems!==null) foreach ($bindeditems as $bindeditem) $this->deleteItem($bindeditem->ID);
		//then delete the item itself
		$this->db->setQuery("DELETE FROM jcq_item WHERE ID = $ID");
		if (!$this->db->query()) JError::raiseError(500, 'FATAL: '.$this->db->getErrorMsg());
	}

	function addUserDataColumn($datatype, $itemID, $scaleID=null)
	{
		$model_questions = new JcqModelQuestions();
		$questionID = $this->getItem($itemID)->questionID;
		$pageID = $model_questions->getPageFromQuestion($questionID)->ID;
		$projectID = $model_questions->getProjectFromPage($pageID)->ID;
		//expect field type INT as default
		$fieldtype = "INT";
		if ($datatype!=1) $fieldtype = "TEXT";
		if ($scaleID===null) $query = "ALTER TABLE jcq_proj$projectID ADD COLUMN i".$itemID."_ $fieldtype";
		else $query = "ALTER TABLE jcq_proj$projectID ADD COLUMN i".$itemID."_s".$scaleID."_ INT";
		$this->db->setQuery($query);
		if (!$this->db->query()) JError::raiseError(500, 'FATAL: '.$this->db->getErrorMsg());
	}

	function addrmTextfields($itemID,$questionID)
	{
		$bindeditems = $this->getItembindedItems($itemID);
		//Delete if a textfield is already there
		if ($bindeditems!==null && count($bindeditems)>0)
		{
			foreach ($bindeditems as $bindeditem) $this->deleteItem($bindeditem->ID);
		}
		else //insert a textfield
		{
			$newitem = $this->buildNewItem($questionID, 3);
			$newitem->mandatory = 0;
			$newitem->bindingType = "ITEM";
			$newitem->bindingID = $itemID;
			if (!$newitem->store()) JError::raiseError(500, 'FATAL: '.$newitem->getError());
		}
	}
}