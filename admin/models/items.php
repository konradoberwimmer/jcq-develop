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
		$itemTableRow =& $this->getTable();
		$itemTableRow->questionID = $questionID;
		$itemTableRow->datatype = $datatype;
		$this->db->setQuery("SELECT ord FROM jcq_item WHERE questionID=$questionID ORDER BY ord DESC");
		$items = $this->db->loadObjectList();
		if ($items!==null) $itemTableRow->ord = $items[0]->ord + 1;
		else $itemTableRow->ord = 1;
		if ($itemTableRow->store())
		{
			//add user data column
			if ($scales===null) $this->addUserDataColumn($datatype, $itemTableRow->ID);
			else foreach ($scales as $scale) $this->addUserDataColumn($datatype, $itemTableRow->ID, $scale->ID);
		}
		else JError::raiseError(500, 'FATAL: '.$itemTableRow->getError());
		return $itemTableRow;
	}

	function saveItem(array $item, array $scales=null)
	{
		$itemTableRow =& $this->getTable('items');
		if (!$itemTableRow->bind($item)) JError::raiseError(500, 'Error binding data');
		if (!$itemTableRow->check()) JError::raiseError(500, 'Invalid data');
		if (!$itemTableRow->store())
		{
			$errorMessage = $itemTableRow->getError();
			JError::raiseError(500, 'Error inserting data: '.$errorMessage);
		}
	
		//uses model questions to get pageID
		$modelquestions = new JcqModelQuestions();
		$pageid = $modelquestions->getPageFromQuestion($itemTableRow->questionID)->ID;
		$projectid = $modelquestions->getProjectFromPage($pageid)->ID;

		//set default values for new item and add user data column;
		if ($item['ID']==0)
		{
			if ($scales===null) $this->addUserDataColumn($itemTableRow->datatype, $itemTableRow->ID);
			else foreach ($scales as $scale) $this->addUserDataColumn($itemTableRow->datatype, $scale->ID);
		} else if ($scales!==null)
		{
			//stupidly try to add userdata columns if there are scales (question type MULTISCALE)
			#FIXME just ignore the errors
			foreach ($scales as $scale)
			{
				$query = "ALTER TABLE jcq_proj$projectid ADD COLUMN i".$itemTableRow->ID."_s".$scale->ID."_ INT";
				$this->db->setQuery($query);
				$this->db->query();
			}
		}
	}

	function deleteItem($ID)
	{
		//first remove user data column(s)
		$model_questions = new JcqModelQuestions();
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
			$bindeditems = $this->getItembindedItems($oneID);
			if ($bindeditems!=null && count($bindeditems)>0)
			{
				foreach ($bindeditems as $bindeditem)
				{
					$this->db->setQuery("DELETE FROM jcq_item WHERE ID=".$bindeditem->ID);
					if (!$this->db->query()) JError::raiseError(500, 'Error deleting textfield: '.$this->db->getErrorMsg());
					//also delete data column
					$this->db->setQuery("ALTER TABLE jcq_proj".$page->projectID." DROP COLUMN p".$page->ID."_q".$question->ID."_i".$bindeditem->ID."_");
					if (!$this->db->query()) JError::raiseError(500, 'Error altering userdata table: '.$this->db->getErrorMsg());
				}
			} else
			{
				//if non exists so far, create one
				$itemTableRow =& $this->getTable('items');
				$itemTableRow->ord = 0;
				$itemTableRow->datatype = 3;
				$itemTableRow->varname = "question".$questionid."item".$oneID."text";
				$itemTableRow->mandatory = 0;
				$itemTableRow->prepost = "%s";
				$itemTableRow->questionID = $questionid;
				$itemTableRow->bindingType = "ITEM";
				$itemTableRow->bindingID = $oneID;
				if (!$itemTableRow->store()) JError::raiseError(500, 'Error inserting textfield: '.$itemTableRow->getError());
				//also create the userdata table column
				$this->db->setQuery("ALTER TABLE jcq_proj".$page->projectID." ADD COLUMN p".$page->ID."_q".$question->ID."_i".$itemTableRow->ID."_ TEXT");
				if (!$this->db->query()) JError::raiseError(500, 'Error altering userdata table: '.$this->db->getErrorMsg());
			}
		}
	}
}