<?php
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');
require_once(JPATH_COMPONENT.DS.'models'.DS.'pages.php');
require_once(JPATH_COMPONENT.DS.'models'.DS.'questions.php');
require_once(JPATH_COMPONENT.DS.'models'.DS.'scales.php');
require_once(JPATH_COMPONENT.DS.'models'.DS.'usergroups.php');

class SPSSVariable
{
	public $intvarname;
	public $extvarname;
	public $datatype;
	public $varlabel;
	public $valuelabels;
	public $codes = null;
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
		$projectTableRow = $this->getTable('projects');
		$projectTableRow->ID = 0;
		$projectTableRow->name = '';
		$projectTableRow->allowjoomla = true;
		return $projectTableRow;
	}

	function saveProject($project)
	{
		//TODO: secure against insertion
		$projectTableRow = $this->getTable();
		if (!$projectTableRow->bind($project)) JError::raiseError(500, 'Error binding data');
		if (!$projectTableRow->check()) JError::raiseError(500, 'Invalid data');
		if (!$projectTableRow->store())	JError::raiseError(500, 'Error inserting data: '.$projectTableRow->getError());

		// if the project is new, add first page and build the user data table (using updated id after the store operation)
		if ($project['ID']==0)
		{
			$this->db->setQuery("INSERT INTO jcq_page (name, ord, projectID, isFinal) VALUES ('Final page',0,".$projectTableRow->ID.",1)");
			if (!$this->db->query()) JError::raiseError(500, 'Error creating final page entry: '.$this->getDBO()->getErrorMsg());
			$this->db->setQuery("CREATE TABLE jcq_proj".$projectTableRow->ID." (preview BOOLEAN DEFAULT 0, groupID BIGINT, tokenID BIGINT, joomlaUser VARCHAR(50), sessionID VARCHAR(50) NOT NULL, curpage BIGINT NOT NULL, finished BOOLEAN DEFAULT 0 NOT NULL, timestampBegin BIGINT, timestampEnd BIGINT, PRIMARY KEY (sessionID))");
			if (!$this->db->query()) JError::raiseError(500, 'Error creating user data database: '.$this->getDBO()->getErrorMsg());
		}
		return $projectTableRow->ID;
	}

	function deleteProject($ID)
	{
		//first delete all the pages (invoking further clean-up code)
		$model_pages = new JcqModelPages();
		$pages = $this->getPages($ID);
		if ($pages!==null) foreach ($pages as $page) $model_pages->deletePage($page->ID);
		//delete the user response table
		$this->db->setQuery("DROP TABLE jcq_proj$ID");
		if (!$this->db->query()) JError::raiseError(500, 'FATAL: '.$this->db->getErrorMsg());
		//delete project itself
		$this->db->setQuery("DELETE FROM jcq_project WHERE ID = $ID");
		if (!$this->db->query()) JError::raiseError(500, 'FATAL: '.$this->db->getErrorMsg());
	}

	function getPages($projectID)
	{
		$this->db->setQuery('SELECT * FROM jcq_page WHERE projectID = '.$projectID.' ORDER BY isFinal, ord');
		$pages = $this->db->loadObjectList();
		if ($pages === null) JError::raiseError(500, 'Error reading db');
		return $pages;
	}

	function saveEditedCSS($projectID,$content)
	{
		$project = $this->getProject($projectID);
		file_put_contents(JPATH_COMPONENT_SITE.DS.'usercode'.DS.$project->cssfile,$content);
	}

	function getProgramfiles($projectID)
	{
		$this->db->setQuery('SELECT * FROM jcq_programfile WHERE projectID = '.$projectID.' ORDER BY ord');
		$programfiles = $this->db->loadObjectList();
		if ($programfiles === null) JError::raiseError(500, 'Error reading db');
		return $programfiles;
	}

	function saveProgramfile(array $programfile)
	{
		$programfileTableRow = $this->getTable('programfiles');
		if (!$programfileTableRow->bind($programfile)) JError::raiseError(500, 'Error binding data');
		if (!$programfileTableRow->check()) JError::raiseError(500, 'Invalid data');
		if (!$programfileTableRow->store())
		{
			$errorMessage = $programfileTableRow->getError();
			JError::raiseError(500, 'Error inserting data: '.$errorMessage);
		}
	}

	function deleteProgramfile($ID)
	{
		$this->db->setQuery("DELETE FROM jcq_programfile WHERE ID = $ID");
		if (!$this->db->query()) JError::raiseError(500, 'FATAL: '.$this->db->getErrorMsg());
	}

	function saveData($projectID,$usergroupids,$includeuserdata)
	{
		$modelusergroups = new JcqModelUsergroups();
		$modelpages = new JcqModelPages();

		#FIXME just for now: create a file to write to
		$filename = "data_proj$projectID"."_".time().".sps";
		$file = fopen(JPATH_COMPONENT.DS."userdata".DS.$filename,"w") or JError::raiseError(500, 'Error creating file');
		$project = $this->getProject($projectID);

		//prepare a storage for the variables to be downloaded
		$variables = $this->getVariableList($projectID);
		$varcnt = count($variables);

		fwrite($file,iconv("UTF-8", "ISO-8859-1//TRANSLIT", "*** DATA FROM PROJECT '".$project->name."' at time ".strftime("%d.%m.%Y, %H:%M:%S",time())." ***.\n\n"));

		//Define Data.
		fwrite($file,"DATA LIST LIST (\";\") / ");
		for ($i=0;$i<$varcnt;$i++)
		{
			fwrite($file,iconv("UTF-8", "ISO-8859-1//TRANSLIT", $variables[$i]->extvarname." "));
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
						fwrite($file,"(A32767) ");
						break;
					}
				default: JError::raiseError(500,"FATAL: code for saving data of type ".$variables[$i]->datatype." is missing!!!");
			}
		}
		//write user info variables
		if ($includeuserdata) fwrite($file,iconv("UTF-8", "ISO-8859-1//TRANSLIT", "user_token (A32767) user_email (A32767) user_name (A32767) user_firstname (A32767) user_salutation (A32767) user_note (A32767)"));
		//write system variables
		fwrite($file,iconv("UTF-8", "ISO-8859-1//TRANSLIT", "sys_usergroup (F8.0) sys_finished (F8.0) sys_lastpage (A32767) sys_duration (F8.2)"));
		fwrite($file,".\n");

		//Get Data.
		fwrite($file,"BEGIN DATA\n");
		if ($usergroupids===null || count($usergroupids)==0) $this->db->setQuery("SELECT * FROM jcq_proj$projectID WHERE preview=0 ORDER BY timestampBegin");
		else $this->db->setQuery("SELECT * FROM jcq_proj$projectID WHERE preview=0 AND groupID IN (".implode($usergroupids,",").") ORDER BY timestampBegin");
		#FIXME perhaps this is not the most memory efficient procedure
		$data = $this->db->loadAssocList();
		#TODO set user-defined-missings if value is missing
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
							fwrite($file,iconv("UTF-8", "ISO-8859-1//TRANSLIT", trim(preg_replace('/\s+/', ' ', str_replace(";","",$row[$variables[$i]->intvarname])))));
							break;
						}
					default: JError::raiseError(500,"FATAL: code for saving data of type ".$variables[$i]->datatype." is missing!!!");
				}
			}
			//write user info
			if ($includeuserdata)
			{
				if ($row['groupID']<0) fwrite($file,iconv("UTF-8", "ISO-8859-1//TRANSLIT", ";;;;;;"));
				else if ($row['groupID']==0)
				{
					//get user data from Joomla
					$table = JUser::getTable();
					$tablename = $table->getTableName();
					$this->db->setQuery("SELECT * FROM $tablename WHERE username='".$row['joomlaUser']."'");
					if (($user = $this->db->loadObject())!==null)
					{
						fwrite($file,iconv("UTF-8", "ISO-8859-1//TRANSLIT", ";;".$user->email.";".$user->name.";;;"));
					}
					//if user cannot be found anymore
					else fwrite($file,iconv("UTF-8", "ISO-8859-1//TRANSLIT", ";;;;;;"));
				}
				else
				{
					//get user data from token
					$this->db->setQuery("SELECT * FROM jcq_usergroup WHERE ID=".$row['groupID']." AND projectID=".$project->ID);
					if (($ug = $this->db->loadObject())!==null)
					{
						$this->db->setQuery("SELECT * FROM jcq_token WHERE ID=".$row['tokenID']);
						if (($token = $this->db->loadObject())!==null)
						{
							fwrite($file,iconv("UTF-8", "ISO-8859-1//TRANSLIT", ";".$token->token.";".$token->email.";".$token->name.";".$token->firstname.";".$token->salutation.";".$token->note));
						}
						//if token cannot be found anymore
						else fwrite($file,iconv("UTF-8", "ISO-8859-1//TRANSLIT", ";;;;;;"));
					}
					//if user group cannot be found anymore
					else fwrite($file,iconv("UTF-8", "ISO-8859-1//TRANSLIT", ";;;;;;"));
				}
			}
			//write GroupVal
			if ($row['groupID']<=0) fwrite($file,iconv("UTF-8", "ISO-8859-1//TRANSLIT", ";".str_replace(";","",$row['groupID'])));
			else 
			{
				$this->db->setQuery("SELECT * FROM jcq_usergroup WHERE ID=".$row['groupID']." AND projectID=".$project->ID);
				if (($ug = $this->db->loadObject())!==null) fwrite($file,iconv("UTF-8", "ISO-8859-1//TRANSLIT", ";".str_replace(";","",$ug->val)));
				else fwrite($file,iconv("UTF-8", "ISO-8859-1//TRANSLIT", ";"));
			}
			//write if finished
			fwrite($file,iconv("UTF-8", "ISO-8859-1//TRANSLIT", ";".$row['finished']));
			//write last page
			if ($row['curpage']==-1) fwrite($file,iconv("UTF-8", "ISO-8859-1//TRANSLIT", ";FINAL PAGE"));
			else
			{
				$page = $modelpages->getPage($row['curpage']);
				fwrite($file,iconv("UTF-8", "ISO-8859-1//TRANSLIT", ";".str_replace(";","",$page->name)));
			}
			//write duration by searching for the highest timestamp
			$highestTSvalue = $row['timestampBegin'];
			$highestTScolumn = 'timestampBegin';
			$allcolumns = array_keys($row);
			foreach ($allcolumns as $onecolumn)
			{
				if (strpos($onecolumn, 'timestamp')!==false && $row[$onecolumn]>$row[$highestTScolumn])
				{
					$highestTSvalue=$row[$onecolumn];
					$highestTScolumn=$onecolumn;
				}
			}
			$duration = ($row[$highestTScolumn]-$row['timestampBegin'])/60.0;
			fwrite($file,iconv("UTF-8", "ISO-8859-1//TRANSLIT", ";".str_replace(".",",",$duration)));
			fwrite($file,"\n");
		}
		fwrite($file,"END DATA.\n\n");

		//Set Variable Labels.
		fwrite($file,"VARIABLE LABELS\n");
		for ($i=0;$i<$varcnt;$i++)
		{
			if ($i>0) fwrite($file,"/ ");
			#TODO check for irregular characters
			fwrite($file,iconv("UTF-8", "ISO-8859-1//TRANSLIT", $variables[$i]->extvarname." '".substr($variables[$i]->varlabel,0,256)."'\n"));
		}
		//set labels for user info variables
		if ($includeuserdata)
		{
			fwrite($file,iconv("UTF-8", "ISO-8859-1//TRANSLIT", "/ user_token 'Token'\n"));
			fwrite($file,iconv("UTF-8", "ISO-8859-1//TRANSLIT", "/ user_email 'User email'\n"));
			fwrite($file,iconv("UTF-8", "ISO-8859-1//TRANSLIT", "/ user_name 'User name (whole name of Joomla users)'\n"));
			fwrite($file,iconv("UTF-8", "ISO-8859-1//TRANSLIT", "/ user_firstname 'User first name'\n"));
			fwrite($file,iconv("UTF-8", "ISO-8859-1//TRANSLIT", "/ user_salutation 'User salutation'\n"));
			fwrite($file,iconv("UTF-8", "ISO-8859-1//TRANSLIT", "/ user_note 'User note'\n"));
		}
		//set labels for system variables
		fwrite($file,iconv("UTF-8", "ISO-8859-1//TRANSLIT", "/ sys_usergroup 'User group'\n"));
		fwrite($file,iconv("UTF-8", "ISO-8859-1//TRANSLIT", "/ sys_finished 'Was questionnaire finished?'\n"));
		fwrite($file,iconv("UTF-8", "ISO-8859-1//TRANSLIT", "/ sys_lastpage 'Name of the last page reached by user'\n"));
		fwrite($file,iconv("UTF-8", "ISO-8859-1//TRANSLIT", "/ sys_duration 'Duration in minutes (up to last page reached)'\n"));
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
				fwrite($file,iconv("UTF-8", "ISO-8859-1//TRANSLIT", $code->code." '".substr($code->label,0,120)."' "));
			}
			fwrite($file,"\n");
		}
		//set value labels for system variables
		fwrite($file,iconv("UTF-8", "ISO-8859-1//TRANSLIT", "/ sys_usergroup -1 'anonymous' 0 'Joomla' "));
		$usergroups = $modelusergroups->getUsergroups($projectID);
		if ($usergroups!==null && count($usergroups)>0)
		{
			foreach ($usergroups as $usergroup) fwrite($file,iconv("UTF-8", "ISO-8859-1//TRANSLIT", $usergroup->val." '".$usergroup->name."'"));
		}
		fwrite($file,"\n");
		fwrite($file,iconv("UTF-8", "ISO-8859-1//TRANSLIT", "/ sys_finished 0 'no' 1 'yes'\n"));
		fwrite($file,".\n\n");

		//Set Missing values.
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

		$model_pages = new JcqModelPages();
		$model_questions = new JcqModelQuestions();
		$model_scales = new JcqModelScales();

		$pages = $this->getPages($projectID);
		if ($pages!==null) for ($i=0;$i<count($pages);$i++)
		{
			$page=$pages[$i];
			$questions = $model_pages->getQuestions($page->ID);
			if ($questions!==null) for ($j=0;$j<count($questions);$j++)
			{
				$question=$questions[$j];
				$items = $model_questions->getItems($question->ID);
				$mainitem = null;
				if ($items!==null) foreach ($items as $item) if ($item->bindingType=="QUESTION") {
					$mainitem = $item; break;
				}
				$scales = $model_questions->getScales($question->ID);
				$mainscale = null;
				if ($scales!==null && count($scales)>0) $mainscale = $scales[0];
				switch ($question->questtype)
				{
					case SINGLECHOICE:
						{
							if ($mainitem===null || $mainscale===null) JError::raiseError(500, "FATAL: question '".$question->name."' incorrectly defined");
							$newvar = new SPSSVariable();
							$newvar->datatype = 1;
							$newvar->extvarname = $mainitem->varname;
							$newvar->intvarname = "i".$mainitem->ID."_";
							$newvar->varlabel = $question->text;
							$codes = $model_scales->getCodes($mainscale->ID);
							if ($codes===null || count($codes)==0) break;
							else $newvar->codes = $codes;
							$variables[$varcnt++]=$newvar;
							//look for additional textfields
							for ($k=0;$k<count($items);$k++)
							{
								$item=$items[$k];
								if ($item->bindingType!="CODE") continue;
								$newvar = new SPSSVariable();
								$newvar->datatype = 3;
								$newvar->extvarname = $item->varname;
								$newvar->intvarname = "i".$item->ID."_";
								$newvar->varlabel = $question->text; #FIXME should be more informative
								$variables[$varcnt++]=$newvar;
							}
							break;
						}
					case MULTICHOICE:
						{
							if ($items!==null) for ($k=0;$k<count($items);$k++)
							{
								$item=$items[$k];
								if ($item->bindingType!="QUESTION") continue;
								$newvar = new SPSSVariable();
								$newvar->datatype = 1;
								$newvar->extvarname = $item->varname;
								$newvar->intvarname = "i".$item->ID."_";
								$newvar->varlabel = $item->textleft." ".$item->textright;
								$code0 = $this->getTable('codes');
								$code0->code = 0;
								$code0->label = "not selected";
								$code1 = $this->getTable('codes');
								$code1->code = 1;
								$code1->label = "selected";
								$newvar->codes = array($code0,$code1);
								$variables[$varcnt++]=$newvar;
							}
							//look for additional textfields
							if ($items!==null) for ($k=0;$k<count($items);$k++)
							{
								$item=$items[$k];
								if ($item->bindingType!="ITEM") continue;
								$newvar = new SPSSVariable();
								$newvar->datatype = 3;
								$newvar->extvarname = $item->varname;
								$newvar->intvarname = "i".$item->ID."_";
								$newvar->varlabel = $question->text; #FIXME should be more informative
								$variables[$varcnt++]=$newvar;
							}
							break;
						}
					case TEXTFIELD:
						{
							if ($mainitem===null) JError::raiseError(500, "FATAL: question '".$question->name."' incorrectly defined");
							$newvar = new SPSSVariable();
							$newvar->datatype = $mainitem->datatype;
							$newvar->extvarname = $mainitem->varname;
							$newvar->intvarname = "i".$mainitem->ID."_";
							$newvar->varlabel = $question->text;
							$variables[$varcnt++]=$newvar;
							break;
						}
					case MATRIX_LEFT: case MATRIX_BOTH:
						{
							if ($mainscale===null) JError::raiseError(500, "FATAL: question '".$question->name."' incorrectly defined");
							if ($items!==null) for ($k=0;$k<count($items);$k++)
							{
								$item=$items[$k];
								$newvar = new SPSSVariable();
								$newvar->datatype = 1;
								$newvar->extvarname = $item->varname;
								$newvar->intvarname = "i".$item->ID."_";
								$newvar->varlabel = $item->textleft." ".$item->textright;
								$codes = $model_scales->getCodes($mainscale->ID);
								if ($codes===null || count($codes)==0) break;
								else $newvar->codes = $codes;
								$variables[$varcnt++]=$newvar;
							}
							break;
						}
					case MULTISCALE:
						{
							if ($items!==null) for ($k=0;$k<count($items);$k++)
							{
								$item=$items[$k];
								if ($scales!==null) for ($l=0;$l<count($scales);$l++)
								{
									$scale=$scales[$l];
									$newvar = new SPSSVariable();
									$newvar->datatype = 1;
									#TODO give external varname postfix to predefined scales
									$newvar->extvarname = $item->varname."_s".$scale->ID;
									$newvar->intvarname = "i".$item->ID."_s".$scale->ID."_";
									$newvar->varlabel = $item->textleft." (".$scale->name.")";
									$codes = $model_scales->getCodes($scale->ID);
									if ($codes===null || count($codes)==0) break;
									else $newvar->codes = $codes;
									$variables[$varcnt++]=$newvar;
								}
							}
							break;
						}
					case TEXTANDHTML: break;
					default: JError::raiseError(500, 'FATAL: Code for generating variable list for question of type '.$question->questtype.' is missing!!!');
				}
			}
		}


		return $variables;
	}
}

