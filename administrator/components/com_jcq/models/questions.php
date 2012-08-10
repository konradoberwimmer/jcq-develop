<?php
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');

class JcqModelQuestions extends JModel {

	static function getQuestionTypes()
	{
		$questtypes = array();
		$questtypes[111]='111 - Einfachauswahl untereinander';
		$questtypes[141]='141 - Textfeld einzeilig';
		$questtypes[311]='311 - Standard-Matrix 1';
		$questtypes[340]='340 - Semantisches Differential';
		return $questtypes;
	}
	
	static function getMandatoryTypes()
	{
		$mandatorytypes = array();
		$mandatorytypes[0]='No';
		$mandatorytypes[1]='Yes';
		return $mandatorytypes;
	}
	
	static function getDataTypes()
	{
		$datatypes = array();
		$datatypes[1]='Integer';
		$datatypes[2]='Real';
		$datatypes[3]='String';
		return $datatypes;
	}
	
	function getQuestion($ID)
	{
		$query = 'SELECT * FROM jcq_question WHERE ID = '.$ID;
		$db = $this->getDBO();
		$db->setQuery($query);
		$question = $db->loadObject();
			
		if ($question === null) JError::raiseError(500, 'Question with ID: '.$ID.' not found.');
		else return $question;
	}
	
	function getTypeFromQuestion($ID)
	{
		$query = 'SELECT questtype FROM jcq_question WHERE ID = '.$ID;
		$db = $this->getDBO();
		$db->setQuery($query);
		$question = $db->loadObject();
			
		if ($question === null) JError::raiseError(500, 'Question with ID: '.$ID.' not found.');
		else return $question->questtype;
	}

	function getNewQuestion($pageID)
	{
		$questionTableRow =& $this->getTable('questions');
		$questionTableRow->ID = 0;
		$questionTableRow->name = '';
		$questionTableRow->ord = 0; //FIXME should be set to highest value
		$questionTableRow->pageID = $pageID;
		return $questionTableRow;
	}
	
	function saveQuestion($question)
	{
		$questionTableRow =& $this->getTable();
			
		// Bind the form fields to the greetings table
		if (!$questionTableRow->bind($question)) JError::raiseError(500, 'Error binding data');
	
		// Make sure the greetings record is valid
		if (!$questionTableRow->check()) JError::raiseError(500, 'Invalid data');
			
		if (!$questionTableRow->store())
		{
			$errorMessage = $questionTableRow->getError();
			JError::raiseError(500, 'Error inserting data: '.$errorMessage);
		}
	}
	
	function deleteQuestions($arrayIDs)
	{
		$query = "DELETE FROM jcq_question WHERE ID IN (".implode(',', $arrayIDs).")";
		$db = $this->getDBO();
		$db->setQuery($query);
		if (!$db->query()){
			$errorMessage = $this->getDBO()->getErrorMsg();
			JError::raiseError(500, 'Error deleting questions: '.$errorMessage);
		}
	}
	
	function setQuestionOrder(array $questionids,array $questionord)
	{
		for ($i=0;$i<count($questionids);$i++)
		{
			$query = "UPDATE jcq_question SET ord=".$questionord[$i]." WHERE ID=".$questionids[$i];
			$db = $this->getDBO();
			$db->setQuery($query);
			if (!$db->query()){
				$errorMessage = $this->getDBO()->getErrorMsg();
				JError::raiseError(500, 'Error setting question order: '.$errorMessage);
			}
		}
	}

	function getPageFromQuestion($questionID)
	{
		$query = 'SELECT * FROM jcq_question WHERE ID = '.$questionID;
		$db = $this->getDBO();
		$db->setQuery($query);
		$question = $db->loadObject();
			
		if ($question === null) JError::raiseError(500, 'Question with ID: '.$questionID.' not found.');
		else
		{
			$query = 'SELECT * FROM jcq_page WHERE ID = '.$question->pageID;
			$db = $this->getDBO();
			$db->setQuery($query);
			$page = $db->loadObject();
				
			if ($page === null) JError::raiseError(500, 'Page with ID: '.$question->pageID.' not found.');
			else return $page;
		}
	}
	
	function getProjectFromPage($pageID)
	{
		$query = 'SELECT * FROM jcq_page WHERE ID = '.$pageID;
		$db = $this->getDBO();
		$db->setQuery($query);
		$page = $db->loadObject();
			
		if ($page === null) JError::raiseError(500, 'Page with ID: '.$pageID.' not found.');
		else
		{
			$query = 'SELECT * FROM jcq_project WHERE ID = '.$page->projectID;
			$db = $this->getDBO();
			$db->setQuery($query);
			$project = $db->loadObject();
				
			if ($project === null) JError::raiseError(500, 'Project with ID: '.$page->projectID.' not found.');
			else return $project;
		}
	}
}