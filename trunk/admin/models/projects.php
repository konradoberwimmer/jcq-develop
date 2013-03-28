<?php
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');

class SPSSVariable
{
	public $intvarname;
	public $extvarname;
	public $datatype;
	public $pageID;
	public $questionID;
	public $itemID = null;
	public $scaleID = null;
}

class JcqModelProjects extends JModel {
	 
	private $db;
	
	function __construct() 
	{
		parent::__construct();
		$this->db = $this->getDBO();
	}
		
	function getProjects()
	{
		$this->db->setQuery('SELECT * FROM jcq_project');
		$projects = $this->db->loadObjectList();
		if ($projects === null) JError::raiseError(500, 'Error reading db');
		return $projects;
	}

	function getProject($ID)
	{
		$query = 'SELECT * FROM jcq_project WHERE id = '.$ID;
		$this->db->setQuery($query);
		$project = $this->db->loadObject();
		if ($project === null) JError::raiseError(500, 'Project with ID: '.$ID.' not found.');
		else return $project;
	}

	function getPageCount($projectID)
	{
		$query = 'SELECT ID FROM jcq_page WHERE projectID = '.$projectID;
		$this->db->setQuery($query);
		$pages = $this->db->loadResultArray();
		if ($pages == null) return 0;
		else return count($pages);
	}
	 
	function getQuestionCount($pageID)
	{
		$query = 'SELECT ID FROM jcq_question WHERE pageID = '.$pageID;
		$this->db->setQuery($query);
		$questions = $this->db->loadResultArray();
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
		//TODO: secure against insertion
		$projectTableRow =& $this->getTable();
		if (!$projectTableRow->bind($project)) JError::raiseError(500, 'Error binding data');
		if (!$projectTableRow->check()) JError::raiseError(500, 'Invalid data');
		if (!$projectTableRow->store())	JError::raiseError(500, 'Error inserting data: '.$projectTableRow->getError());
		
		// if the project is new, build the user data table (using updated id after the store operation)
		if ($project['ID']==0)
		{
			$query = "CREATE TABLE jcq_proj".$projectTableRow->ID." (userID VARCHAR(255), sessionID VARCHAR(50) NOT NULL, curpage BIGINT NOT NULL, finished BOOLEAN DEFAULT 0 NOT NULL, timestampBegin BIGINT, timestampEnd BIGINT, PRIMARY KEY (sessionID))";
			$this->db->setQuery($query);
			if (!$this->db->query()){
				$errorMessage = $this->getDBO()->getErrorMsg();
				JError::raiseError(500, 'Error creating user data database: '.$errorMessage);
			}
		}
		return $projectTableRow->ID;
	}

	function deleteProjects($arrayIDs)
	{
		$query = "DELETE FROM jcq_project WHERE ID IN (".implode(',', $arrayIDs).")";
		$this->db->setQuery($query);
		if (!$this->db->query()){
			$errorMessage = $this->getDBO()->getErrorMsg();
			JError::raiseError(500, 'Error deleting projects: '.$errorMessage);
		}
		// delete the answer tables too ...
		#FIXME User should definitely by reminded to store before that ;-)
		foreach ($arrayIDs as $oneID)
		{
			$query = "DROP TABLE jcq_proj".$oneID;
			$this->db = $this->getDBO();
			$this->db->setQuery($query);
			if (!$this->db->query()){
				$errorMessage = $this->getDBO()->getErrorMsg();
				JError::raiseError(500, 'Error deleting projects: '.$errorMessage);
			}
		}
	}

	function getPages($projectID)
	{
		$this->db->setQuery('SELECT * FROM jcq_page WHERE projectID = '.$projectID.' ORDER BY ord');
		$pages = $this->db->loadObjectList();
		if ($pages === null) JError::raiseError(500, 'Error reading db');
		return $pages;
	}
	
	function saveData($projectID)
	{
		#FIXME just for now: create a file to write to
		$file = fopen(JPATH_COMPONENT.DS."data_proj$projectID"."_".time().".sps","w") or JError::raiseError(500, 'Error creating file');
		
		//prepare a storage for the variables to be downloaded
		$variables = array();
		$varcnt = 0;
	
		$this->db->setQuery('SELECT * FROM jcq_page WHERE projectID = '.$projectID.' ORDER BY ord');
		$pages = $this->db->loadObjectList();
		if ($pages!=null)
		{
			for ($i=0;$i<count($pages);$i++)
			{
				$page=$pages[$i];
				$this->db->setQuery('SELECT * FROM jcq_question WHERE pageID = '.$page->ID.' ORDER BY ord');
				$questions = $this->db->loadObjectList();
				if ($questions!=null)
				{
					for ($j=0;$j<count($questions);$j++)
					{
						$question=$questions[$j];
						switch ($question->questtype)
						{
							case 111:
								{
									$newvar = new SPSSVariable();
									$newvar->datatype = 1;
									$newvar->extvarname = $question->varname;
									$newvar->intvarname = "p".$page->ID."q".$question->ID;
									$newvar->pageID = $page->ID;
									$newvar->questionID = $question->ID;
									$this->db->setQuery('SELECT * FROM jcq_questionscales WHERE questionID = '.$question->ID);
									$scales = $this->db->loadObjectList();
									$newvar->scaleID = $scales[0]->scaleID;
									$variables[$varcnt++]=$newvar;
									break;
								}
							case 141:
								{
									$newvar = new SPSSVariable();
									$newvar->datatype = $question->datatype;
									$newvar->extvarname = $question->varname;
									$newvar->intvarname = "p".$page->ID."q".$question->ID;
									$newvar->pageID = $page->ID;
									$newvar->questionID = $question->ID;
									$variables[$varcnt++]=$newvar;
									break;									
								}
							case 311: case 340:
								{
									$this->db->setQuery('SELECT * FROM jcq_questionscales WHERE questionID = '.$question->ID);
									$scales = $this->db->loadObjectList();
									$this->db->setQuery('SELECT * FROM jcq_item WHERE questionID = '.$question->ID.' ORDER BY ord');
									$items = $this->db->loadObjectList();
									for ($k=0;$k<count($items);$k++)
									{
										$item=$items[$k];
										$newvar = new SPSSVariable();
										$newvar->datatype = 1;
										$newvar->extvarname = $item->varname;
										$newvar->intvarname = "p".$page->ID."q".$question->ID."i".$item->ID;
										$newvar->pageID = $page->ID;
										$newvar->questionID = $question->ID;
										$newvar->itemID = $item->ID;
										$newvar->scaleID = $scales[0]->scaleID;
										$variables[$varcnt++]=$newvar;
									}
									break;
								}
							case 361:
								{
									$this->db->setQuery('SELECT * FROM jcq_questionscales WHERE questionID = '.$question->ID.' ORDER BY ord');
									$scales = $this->db->loadObjectList();
									$this->db->setQuery('SELECT * FROM jcq_item WHERE questionID = '.$question->ID.' ORDER BY ord');
									$items = $this->db->loadObjectList();
									for ($k=0;$k<count($items);$k++)
									{
										$item=$items[$k];
										for ($l=0;$l<count($scales);$l++)
										{
											$scale=$scales[$l];
											$newvar = new SPSSVariable();
											$newvar->datatype = 1;
											#TODO give external varname postfix to predefined scales
											$newvar->extvarname = $item->varname."_s".$scale->ID;
											$newvar->intvarname = "p".$page->ID."q".$question->ID."i".$item->ID."s".$scale->ID;
											$newvar->pageID = $page->ID;
											$newvar->questionID = $question->ID;
											$newvar->itemID = $item->ID;
											$newvar->scaleID = $scale->ID;
											$variables[$varcnt++]=$newvar;
										}
									}
									break;
								}							
							case 998: break;
							default: JError::raiseError(500, 'FATAL: Code for saving data from question of type '.$question->questtype.' is missing!!!');
						}
					}
				}
			}
		}
		
		#FIXME hier weiter
		
		fclose($file);
	}
}

