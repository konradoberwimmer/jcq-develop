<?php
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');

class JcqModelItems extends JModel {

	//TODO: secure against insertion

	function getItems($questionID)
	{
		$query = 'SELECT * FROM jcq_item WHERE questionID = '.$questionID.' ORDER BY ord';
		$db = $this->getDBO();
		$db->setQuery($query);
		return $db->loadObjectList();
	}

	function saveItem(array $item)
	{
		$itemTableRow =& $this->getTable('items');
		if (!$itemTableRow->bind($item)) JError::raiseError(500, 'Error binding data');
		if (!$itemTableRow->check()) JError::raiseError(500, 'Invalid data');
		if (!$itemTableRow->store())
		{
			$errorMessage = $itemTableRow->getError();
			JError::raiseError(500, 'Error inserting data: '.$errorMessage);
		}
		
		//set default values for new item
		if ($item['ID']==0)
		{
			$itemTableRow->mandatory=true;
			$itemTableRow->varname="question".$itemTableRow->questionID."item".$itemTableRow->ID;
			$itemTableRow->store();
		}
	}
	
	function getQuestionFromItem($itemID)
	{
		$query = 'SELECT * FROM jcq_item WHERE ID = '.$itemID;
		$db = $this->getDBO();
		$db->setQuery($query);
		$item = $db->loadObject();
			
		if ($item === null) JError::raiseError(500, 'Item with ID: '.$itemID.' not found.');
		else
		{
			$query = 'SELECT * FROM jcq_question WHERE ID = '.$item->questionID;
			$db->setQuery($query);
			$question = $db->loadObject();
	
			if ($question === null) JError::raiseError(500, 'Question with ID: '.$itemID->questionID.' not found.');
			else return $question;
		}
	}
	
	function deleteItems($arrayIDs)
	{
		//first remove user data columns!
		$db = $this->getDBO();
		foreach ($arrayIDs as $oneID)
		{
			$questionID = $this->getQuestionFromItem($oneID)->ID;
			//use model questions to get pageID and projectID
			require_once( JPATH_COMPONENT.DS.'models'.DS.'questions.php' );
			$modelquestions = new JcqModelQuestions();
			$pageID = $modelquestions->getPageFromQuestion($questionID)->ID;
			$projectID = $modelquestions->getProjectFromPage($pageID)->ID;
			$query = "ALTER TABLE jcq_proj".$projectID." DROP COLUMN p".$pageID."q".$questionID."i".$oneID;
			if (!$db->query())
			{
				$errorMessage = $this->getDBO()->getErrorMsg();
				JError::raiseError(500, 'Error altering user data table: '.$errorMessage);
			}
		}		
		$query = "DELETE FROM jcq_item WHERE ID IN (".implode(',', $arrayIDs).")";
		$db->setQuery($query);
		if (!$db->query()){
			$errorMessage = $this->getDBO()->getErrorMsg();
			JError::raiseError(500, 'Error deleting items: '.$errorMessage);
		}
	}
	
	function addColumnUserDataINT($pageID,$questionID,$itemID)
	{
		//use model questions to get projectID
		require_once( JPATH_COMPONENT.DS.'models'.DS.'questions.php' );
		$modelquestions = new JcqModelQuestions();
		$project = $modelquestions->getProjectFromPage($pageID);
		$query = "ALTER TABLE jcq_proj".$project->ID." ADD COLUMN p".$pageID."q".$questionID."i".$itemID." INT";
		$db = $this->getDBO();
		$db->setQuery($query);
		if (!$db->query())
		{
			$errorMessage = $this->getDBO()->getErrorMsg();
			JError::raiseError(500, 'Error altering user data table: '.$errorMessage);
		}
	}
}