<?php
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');
require_once(JPATH_COMPONENT.DS.'models'.DS.'questions.php');

class JcqModelPages extends JModel {

	//TODO: secure against insertion
	
	private $db;
	
	function __construct()
	{
		parent::__construct();
		$this->db = $this->getDBO();
	}
	
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
		$this->db->setQuery("SELECT ord FROM jcq_page WHERE projectID=$projectID AND isFinal=0 ORDER BY ord DESC");
		$pages = $this->db->loadObjectList();
		if ($pages!==null) $pageTableRow->ord = $pages[0]->ord + 1;
		else $pageTableRow->ord = 1;
		$pageTableRow->projectID = $projectID;
		$pageTableRow->isFinal = 0;
		return $pageTableRow;
	}

	function savePage($page)
	{
		$pageTableRow =& $this->getTable();
			
		// Bind the form fields to the greetings table
		if (!$pageTableRow->bind($page)) JError::raiseError(500, 'Error binding data');

		if ($page['ID']>0) //handle filter information
		{
			$filter="";
			for ($i=0; $i<$page['cntdisjunctions']; $i++)
			{
				if ($i>0) $filter .= "|";
				$filter .= "(";
				for ($j=0; $j<$page['cntconjugations'.$i]; $j++)
				{
					if ($j>0) $filter .= "&";
					$filter .= ("$".$page['variable'.$i.'_'.$j]."$");
					$op="==";
					if ($page['operator'.$i.'_'.$j]==2) $op="!=";
					if ($page['operator'.$i.'_'.$j]==3) $op="<";
					if ($page['operator'.$i.'_'.$j]==4) $op="<=";
					if ($page['operator'.$i.'_'.$j]==5) $op=">=";
					if ($page['operator'.$i.'_'.$j]==6) $op=">";
					$filter .= $op;
					$filter .= $page['val'.$i.'_'.$j];
				}
				$filter .= ")";
			}
			$pageTableRow->filter = $filter;
		}

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
			$query = "ALTER TABLE jcq_proj".$pageTableRow->projectID." ADD COLUMN p".$pageTableRow->ID."_timestamp BIGINT";
			$db = $this->getDBO();
			$db->setQuery($query);
			if (!$db->query()){
				$errorMessage = $this->getDBO()->getErrorMsg();
				JError::raiseError(500, 'Error altering user data table: '.$errorMessage);
			}
		}

		return $pageTableRow->ID;
	}

	function deletePage($ID)
	{
		//remove the timestamp variable from the user data table
		$page = $this->getPage($ID);
		$this->db->setQuery("ALTER TABLE jcq_proj".$page->projectID." DROP COLUMN p".$page->ID."_timestamp");
		//first delete all the questions belonging to the page
		$model_questions = new JcqModelQuestions();
		$this->db->setQuery("SELECT ID FROM jcq_question WHERE pageID = $ID");
		$questions = $this->db->loadObjectList();
		if ($questions!==null) foreach ($questions as $question) $model_questions->deleteQuestion($question->ID);
		//delete the page itself
		$this->db->setQuery("DELETE FROM jcq_page WHERE ID = $ID");
		if (!$this->db->query()) JError::raiseError(500, 'FATAL: '.$this->db->getErrorMsg());
	}

	function setPageOrder(array $pageids,array $pageord)
	{
		for ($i=0;$i<count($pageord);$i++)
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