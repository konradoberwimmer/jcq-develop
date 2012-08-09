<?php
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');

class JcqModelQuestions extends JModel {

	function getQuestion($ID)
	{
		$query = 'SELECT * FROM jcq_question WHERE ID = '.$ID;
		$db = $this->getDBO();
		$db->setQuery($query);
		$question = $db->loadObject();
			
		if ($question === null) JError::raiseError(500, 'Question with ID: '.$ID.' not found.');
		else return $question;
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
}