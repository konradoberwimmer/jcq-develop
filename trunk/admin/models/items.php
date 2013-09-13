<?php
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');

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
	
	function getItems($questionID)
	{
		$query = 'SELECT * FROM jcq_item WHERE questionID = '.$questionID.' ORDER BY ord';
		$this->db->setQuery($query);
		return $this->db->loadObjectList();
	}
	
	function getItembindedItems($itemID)
	{
		$this->db->setQuery("SELECT * FROM jcq_item WHERE bindingType='ITEM' AND bindingID=".$itemID);
		$sqlresult = $this->db->loadObjectList();
		if ($sqlresult===false) JError::raiseError(500, 'Error fetching textfields: '.$this->getDBO()->getErrorMsg());
		else return $sqlresult;
	}

	function saveItem(array $item, array $scales=null)
	{
				//uses model questions to get pageID
		require_once( JPATH_COMPONENT.DS.'models'.DS.'questions.php' );
		$modelquestions = new JcqModelQuestions();
		
		$itemTableRow =& $this->getTable('items');
		if (!$itemTableRow->bind($item)) JError::raiseError(500, 'Error binding data');
		if (!$itemTableRow->check()) JError::raiseError(500, 'Invalid data');
		if (!$itemTableRow->store())
		{
			$errorMessage = $itemTableRow->getError();
			JError::raiseError(500, 'Error inserting data: '.$errorMessage);
		}
		
		$pageid = $modelquestions->getPageFromQuestion($itemTableRow->questionID)->ID;
		$projectid = $modelquestions->getProjectFromPage($pageid)->ID;
		
		//set default values for new item and add user data column;
		if ($item['ID']==0)
		{
			$itemTableRow->mandatory=true;
			$itemTableRow->varname="question".$itemTableRow->questionID."item".$itemTableRow->ID;
			$itemTableRow->store();
			if ($scales===null) $this->addColumnUserDataINT($pageid, $itemTableRow->questionID, $itemTableRow->ID);
			else foreach ($scales as $scale) $this->addColumnUserDataINT($pageid, $itemTableRow->questionID, $itemTableRow->ID, $scale->ID);
		} else if ($scales!==null)
		{
			//stupidly try to add userdata columns if there are scales (question type MULTISCALE)
			//just ignore the errors
			foreach ($scales as $scale)
			{
				$query = "ALTER TABLE jcq_proj$projectid ADD COLUMN p".$pageid."q".$itemTableRow->questionID."i".$itemTableRow->ID."s".$scale->ID." INT";
				$this->db->setQuery($query);
				$this->db->query();
			}
		}
	}
	
	function getQuestionFromItem($itemID)
	{
		$query = 'SELECT * FROM jcq_item WHERE ID = '.$itemID;
		$this->db->setQuery($query);
		$item = $this->db->loadObject();
			
		if ($item === null) JError::raiseError(500, 'Item with ID: '.$itemID.' not found.');
		else
		{
			$query = 'SELECT * FROM jcq_question WHERE ID = '.$item->questionID;
			$this->db->setQuery($query);
			$question = $this->db->loadObject();
	
			if ($question === null) JError::raiseError(500, 'Question with ID: '.$itemID->questionID.' not found.');
			else return $question;
		}
	}
	
	function deleteItems($arrayIDs)
	{
		//first remove user data columns!
		foreach ($arrayIDs as $oneID)
		{
			$questionID = $this->getQuestionFromItem($oneID)->ID;
			//use model questions to get pageID and projectID
			require_once( JPATH_COMPONENT.DS.'models'.DS.'questions.php' );
			$modelquestions = new JcqModelQuestions();
			$pageID = $modelquestions->getPageFromQuestion($questionID)->ID;
			$projectID = $modelquestions->getProjectFromPage($pageID)->ID;
			$statementquery = "SELECT CONCAT('ALTER TABLE jcq_proj$projectID ', GROUP_CONCAT('DROP COLUMN ',column_name)) AS statement FROM information_schema.columns WHERE table_name = 'jcq_proj$projectID' AND column_name LIKE 'p".$pageID."q".$questionID."i".$oneID."%';";
			$this->db->setQuery($statementquery);
			$sqlresult = $this->db->loadResult();
			if ($sqlresult!=null)
			{
				$this->db->setQuery($sqlresult);
				if (!$this->db->query()){
					$errorMessage = $this->getDBO()->getErrorMsg();
					JError::raiseError(500, 'Error altering user data table: '.$errorMessage);
				}
			}
		}		
		$query = "DELETE FROM jcq_item WHERE ID IN (".implode(',', $arrayIDs).")";
		$this->db->setQuery($query);
		if (!$this->db->query()){
			$errorMessage = $this->getDBO()->getErrorMsg();
			JError::raiseError(500, 'Error deleting items: '.$errorMessage);
		}
	}
	
	function addColumnUserDataINT($pageID,$questionID,$itemID,$scaleID=null)
	{
		//use model questions to get projectID
		require_once( JPATH_COMPONENT.DS.'models'.DS.'questions.php' );
		$modelquestions = new JcqModelQuestions();
		$project = $modelquestions->getProjectFromPage($pageID);
		if ($scaleID===null) $query = "ALTER TABLE jcq_proj".$project->ID." ADD COLUMN p".$pageID."q".$questionID."i".$itemID." INT";
		else $query = "ALTER TABLE jcq_proj".$project->ID." ADD COLUMN p".$pageID."q".$questionID."i".$itemID."s".$scaleID." INT";
		$this->db->setQuery($query);
		if (!$this->db->query())
		{
			$errorMessage = $this->getDBO()->getErrorMsg();
			JError::raiseError(500, 'Error altering user data table: '.$errorMessage);
		}
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
					$this->db->setQuery("ALTER TABLE jcq_proj".$page->projectID." DROP COLUMN p".$page->ID."q".$question->ID."i".$bindeditem->ID);
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
				$this->db->setQuery("ALTER TABLE jcq_proj".$page->projectID." ADD COLUMN p".$page->ID."q".$question->ID."i".$itemTableRow->ID." TEXT");
				if (!$this->db->query()) JError::raiseError(500, 'Error altering userdata table: '.$this->db->getErrorMsg());
			}
		}
	}
}