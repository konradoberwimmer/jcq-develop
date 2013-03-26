<?php
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');

class JcqModelProjects extends JModel {
	 
	//TODO: secure against insertion
	
	function getProjects()
	{
		$db = $this->getDBO();
		 
		$db->setQuery('SELECT * FROM jcq_project');
		$projects = $db->loadObjectList();

		 
		if ($projects === null) JError::raiseError(500, 'Error reading db');

		return $projects;
	}
	 
	function getProject($ID)
	{
		$query = 'SELECT * FROM jcq_project WHERE id = '.$ID;
		$db = $this->getDBO();
		$db->setQuery($query);
		$project = $db->loadObject();
		 
		if ($project === null) JError::raiseError(500, 'Project with ID: '.$ID.' not found.');
		else return $project;
	}

	function getPageCount($projectID)
	{
		$query = 'SELECT ID FROM jcq_page WHERE projectID = '.$projectID;
		$db = $this->getDBO();
		$db->setQuery($query);
		$pages = $db->loadResultArray();
		 
		if ($pages == null) return 0;
		else return count($pages);
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
	
	function getNewProject()
	{
		$projectTableRow =& $this->getTable('projects');
		$projectTableRow->ID = 0;
		$projectTableRow->name = '';
		return $projectTableRow;
	}
	 
	function saveProject($project)
	{
		$projectTableRow =& $this->getTable();
		 
		// Bind the form fields to the greetings table
		if (!$projectTableRow->bind($project)) JError::raiseError(500, 'Error binding data');

		// Make sure the greetings record is valid
		if (!$projectTableRow->check()) JError::raiseError(500, 'Invalid data');
		 
		if (!$projectTableRow->store())
		{
			$errorMessage = $projectTableRow->getError();
			JError::raiseError(500, 'Error inserting data: '.$errorMessage);
		}
		
		// if the project is new, build the user data table (using once again the trick of updated id after the store operation)
		if ($project['ID']==0)
		{
			$query = "CREATE TABLE jcq_proj".$projectTableRow->ID." (userID VARCHAR(255), sessionID VARCHAR(50) NOT NULL, curpage BIGINT NOT NULL, finished BOOLEAN DEFAULT 0 NOT NULL, timestampBegin BIGINT, PRIMARY KEY (sessionID))";
			$db = $this->getDBO();
			$db->setQuery($query);
			if (!$db->query()){
				$errorMessage = $this->getDBO()->getErrorMsg();
				JError::raiseError(500, 'Error creating user data database: '.$errorMessage);
			}
		}
		return $projectTableRow->ID;
	}

	function deleteProjects($arrayIDs)
	{
		$query = "DELETE FROM jcq_project WHERE ID IN (".implode(',', $arrayIDs).")";
		$db = $this->getDBO();
		$db->setQuery($query);
		if (!$db->query()){
			$errorMessage = $this->getDBO()->getErrorMsg();
			JError::raiseError(500, 'Error deleting projects: '.$errorMessage);
		}
		// delete the answer tables too ...
		// FIXME User should definitely by reminded to store before that ;-)
		foreach ($arrayIDs as $oneID)
		{
			$query = "DROP TABLE jcq_proj".$oneID;
			$db = $this->getDBO();
			$db->setQuery($query);
			if (!$db->query()){
				$errorMessage = $this->getDBO()->getErrorMsg();
				JError::raiseError(500, 'Error deleting projects: '.$errorMessage);
			}
		}
	}

	function getPages($projectID)
	{
		$db = $this->getDBO();
			
		$db->setQuery('SELECT * FROM jcq_page WHERE projectID = '.$projectID.' ORDER BY ord');
		$pages = $db->loadObjectList();
				
		if ($pages === null) JError::raiseError(500, 'Error reading db');
	
		return $pages;
	}
}

