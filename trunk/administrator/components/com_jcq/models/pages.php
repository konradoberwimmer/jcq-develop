<?php
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');

class JcqModelPages extends JModel {
	
	//TODO: secure against insertion
	
	function getPage($ID)
	{
		$query = 'SELECT * FROM jcq_page WHERE ID = '.$ID;
		$db = $this->getDBO();
		$db->setQuery($query);
		$page = $db->loadObject();
			
		if ($page === null) JError::raiseError(500, 'Page with ID: '.$ID.' not found.');
		else return $page;
	}
	
	function getQuestionCount($pageID)
	{
		$query = 'SELECT ID FROM jcq_question WHERE pageID = '.$pageID;
		$db = $this->getDBO();
		$db->setQuery($query);
		$questions = $db->loadResultArray();
			
		if ($questions == null) return 0;
		else return count($questions);
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
	
	function getNewPage($projectID)
	{
		$pageTableRow =& $this->getTable('pages');
		$pageTableRow->ID = 0;
		$pageTableRow->name = '';
		$pageTableRow->ord = 0; //FIXME should be set to highest value
		$pageTableRow->projectID = $projectID;
		return $pageTableRow;
	}
	
	function savePage($page)
	{
		$pageTableRow =& $this->getTable();
			
		// Bind the form fields to the greetings table
		if (!$pageTableRow->bind($page)) JError::raiseError(500, 'Error binding data');
	
		// Make sure the greetings record is valid
		if (!$pageTableRow->check()) JError::raiseError(500, 'Invalid data');
			
		if (!$pageTableRow->store())
		{
			$errorMessage = $pageTableRow->getError();
			JError::raiseError(500, 'Error inserting data: '.$errorMessage);
		}
		
		// if a new page is created, also create the timestamp in the user data table
		if ($page['ID']==0)
		{
			$query = "ALTER TABLE jcq_proj".$pageTableRow->projectID." ADD COLUMN p".$pageTableRow->ID."timestamp BIGINT";
			$db = $this->getDBO();
			$db->setQuery($query);
			if (!$db->query()){
				$errorMessage = $this->getDBO()->getErrorMsg();
				JError::raiseError(500, 'Error altering user data table: '.$errorMessage);
			}
		}
	}
	
	function deletePages($arrayIDs)
	{
		// drop timestamp as well as columns of questions and items of this page in the user data table by naming convention
		// now this is really neat, the database will create the ALTER TABLE statement :-)
		foreach ($arrayIDs as $oneID)
		{
			$project = $this->getProjectFromPage($oneID);
			$statementquery = "SELECT CONCAT('ALTER TABLE jcq_proj".$project->ID." ', GROUP_CONCAT('DROP COLUMN ',column_name)) AS statement FROM information_schema.columns WHERE table_name = 'jcq_proj".$project->ID."' AND column_name LIKE 'p".$oneID."%';";
			$db = $this->getDBO();
			$db->setQuery($statementquery);
			$sqlresult = $db->loadResult();
			if ($sqlresult!=null)
			{
				$db->setQuery($sqlresult);
				if (!$db->query()){
					$errorMessage = $this->getDBO()->getErrorMsg();
					JError::raiseError(500, 'Error altering user data table: '.$errorMessage);
				}
			}
		}
		// delete the page itself		
		$query = "DELETE FROM jcq_page WHERE ID IN (".implode(',', $arrayIDs).")";
		$db = $this->getDBO();
		$db->setQuery($query);
		if (!$db->query()){
			$errorMessage = $this->getDBO()->getErrorMsg();
			JError::raiseError(500, 'Error deleting pages: '.$errorMessage);
		}
		
	}
	
	function setPageOrder(array $pageids,array $pageord)
	{
		for ($i=0;$i<count($pageids);$i++)
		{
			$query = "UPDATE jcq_page SET ord=".$pageord[$i]." WHERE ID=".$pageids[$i];
			$db = $this->getDBO();
			$db->setQuery($query);
			if (!$db->query()){
				$errorMessage = $this->getDBO()->getErrorMsg();
				JError::raiseError(500, 'Error setting page order: '.$errorMessage);
			}
		}
	}
	
	function getQuestions($pageID)
	{
		$db = $this->getDBO();
			
		$db->setQuery('SELECT * FROM jcq_question WHERE pageID = '.$pageID.' ORDER BY ord');
		$questions = $db->loadObjectList();
	
		if ($questions === null) JError::raiseError(500, 'Error reading db');
		else return $questions;
	}
}