<?php
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');

class SPSSVariable
{
	public $intvarname;
	public $extvarname;
	public $datatype;
	public $varlabel;
	public $valuelabels;
	public $codes = null;
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
		#FIXME write in western european collation
		$filename = "data_proj$projectID"."_".time().".sps";
		$file = fopen(JPATH_COMPONENT.DS."userdata".DS.$filename,"w") or JError::raiseError(500, 'Error creating file');
		$project = $this->getProject($projectID);
	
		//prepare a storage for the variables to be downloaded
		$variables = $this->getVariableList($projectID);
		$varcnt = count($variables);
		
		fwrite($file,"*** DATA FROM PROJECT '".$project->name."' at time ".strftime("%d.%m.%Y, %H:%M:%S",time())." ***.\n\n");
	
		//Define Data.
		#TODO add sessionID, duration etc.
		fwrite($file,"DATA LIST LIST (\";\") / ");
		for ($i=0;$i<$varcnt;$i++)
		{
			fwrite($file,$variables[$i]->extvarname." ");
			switch ($variables[$i]->datatype)
			{
				case 1:
					{
						fwrite($file,"(F8.0) ");
						break;
					}
				case 2:
					{
						fwrite($file,"(F8.2) ");
						break;
					}
				case 3:
					{
						fwrite($file,"(A) ");
						break;
					}
				default: JError::raiseError(500,"FATAL: code for saving data of type ".$variables[$i]->datatype." is missing!!!");
			}
		}
		fwrite($file,".\n");
	
		//Get Data.
		fwrite($file,"BEGIN DATA\n");
		$this->db->setQuery("SELECT * FROM jcq_proj$projectID ORDER BY timestampBegin");
		#FIXME perhaps this is not the most memory efficient procedure
		$data = $this->db->loadAssocList();
		#TODO set user-missings if value is missing
		foreach ($data as $row)
		{
			for ($i=0;$i<$varcnt;$i++)
			{
				if ($i>0) fwrite($file,";");
				switch ($variables[$i]->datatype)
				{
					case 1: case 2:
						{
							fwrite($file,$row[$variables[$i]->intvarname]);
							break;
						}
					case 3:
						{
							#TODO secure against irregular text
							fwrite($file,"\"".$row[$variables[$i]->intvarname]."\"");
						break;
						}
					default: JError::raiseError(500,"FATAL: code for saving data of type ".$variables[$i]->datatype." is missing!!!");
				}
			}
			fwrite($file,"\n");
		}
		fwrite($file,"END DATA.\n\n");
	
		//Set Variable Labels.
		fwrite($file,"VARIABLE LABELS\n");
		for ($i=0;$i<$varcnt;$i++)
		{
			if ($i>0) fwrite($file,"/ ");
			#TODO check for irregular characters
			fwrite($file,$variables[$i]->extvarname." '".$variables[$i]->varlabel."'\n");
		}
		fwrite($file,".\n\n");
	
		//Set Value Labels.
		fwrite($file,"VALUE LABELS\n");
		$slashset = false;
		for ($i=0;$i<$varcnt;$i++)
		{
			if ($variables[$i]->codes===null) continue;
			if (!$slashset) $slashset=true;
			else fwrite($file,"/ ");
			fwrite($file,$variables[$i]->extvarname." ");
			foreach ($variables[$i]->codes as $code)
			{
				fwrite($file,$code->code." '".$code->label."' ");
			}
			fwrite($file,"\n");
		}
		fwrite($file,".\n\n");
	
		//Set Value Labels.
		fwrite($file,"MISSING VALUES\n");
		$slashset = false;
		for ($i=0;$i<$varcnt;$i++)
		{
			if ($variables[$i]->codes===null) continue;
			if (!$slashset) $slashset=true;
			else fwrite($file,"/ ");
			fwrite($file,$variables[$i]->extvarname." (");
			$commaset = false;
			foreach ($variables[$i]->codes as $code)
			{
				if ($code->missval)
				{
					fwrite($file,($commaset?",":"").$code->code);
					$commaset = true;
				}
			}
			fwrite($file,")\n");
		}
		fwrite($file,".\n\n");
	
		fclose($file);
	
		return $filename;
	}


	function getVariableList($projectID)
	{
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
									$newvar->varlabel = $question->text;
									$newvar->pageID = $page->ID;
									$newvar->questionID = $question->ID;
									$this->db->setQuery('SELECT * FROM jcq_questionscales WHERE questionID = '.$question->ID);
									$scales = $this->db->loadObjectList();
									$newvar->scaleID = $scales[0]->scaleID;
									$this->db->setQuery('SELECT * FROM jcq_code WHERE scaleID = '.$scales[0]->scaleID.' ORDER BY ord');
									$newvar->codes = $this->db->loadObjectList();
									$variables[$varcnt++]=$newvar;
									break;
								}
							case 141:
								{
									$newvar = new SPSSVariable();
									$newvar->datatype = $question->datatype;
									$newvar->extvarname = $question->varname;
									$newvar->intvarname = "p".$page->ID."q".$question->ID;
									$newvar->varlabel = $question->text;
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
										$newvar->varlabel = $item->textleft." ".$item->textright;
										$newvar->pageID = $page->ID;
										$newvar->questionID = $question->ID;
										$newvar->itemID = $item->scaleID;
										$newvar->scaleID = $scales[0]->ID;
										$this->db->setQuery('SELECT * FROM jcq_code WHERE scaleID = '.$scales[0]->scaleID.' ORDER BY ord');
										$newvar->codes = $this->db->loadObjectList();
										$variables[$varcnt++]=$newvar;
									}
									break;
								}
							case 361:
								{
									$this->db->setQuery('SELECT * FROM jcq_scale, jcq_questionscales WHERE jcq_scale.ID = jcq_questionscales.scaleID AND questionID = '.$question->ID.' ORDER BY ord');
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
											$newvar->varlabel = $item->textleft." (".$scale->name.")";
											$newvar->pageID = $page->ID;
											$newvar->questionID = $question->ID;
											$newvar->itemID = $item->ID;
											$newvar->scaleID = $scale->ID;
											$this->db->setQuery('SELECT * FROM jcq_code WHERE scaleID = '.$scale->ID.' ORDER BY ord');
											$newvar->codes = $this->db->loadObjectList();
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
		
		return $variables;
	}
}

