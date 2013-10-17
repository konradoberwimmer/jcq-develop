<?php
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');
require_once( JPATH_COMPONENT.DS.'models'.DS.'page.php' );

function val_is_int($input) {
	if ($input[0] == '-') {
		return ctype_digit(substr($input, 1));
	}
	return ctype_digit($input);
}

function getStoredValue ($varname)
{
	global $currentsession, $currentproject;
	//search for the variable name
	$intvarname = null;
	$db =& JFactory::getDBO();
	$db->setQuery('SELECT * FROM jcq_page WHERE isFinal = 0 AND projectID = '.$currentproject.' ORDER BY ord');
	$pages = $db->loadObjectList();
	if ($pages!=null)
	{
		for ($i=0;$i<count($pages);$i++)
		{
			$page=$pages[$i];
			$db->setQuery('SELECT * FROM jcq_question WHERE pageID = '.$page->ID.' ORDER BY ord');
			$questions = $db->loadObjectList();
			if ($questions!=null)
			{
				for ($j=0;$j<count($questions);$j++)
				{
					$question=$questions[$j];
					switch ($question->questtype)
					{
						case SINGLECHOICE:
							{
								if ($question->varname==$varname) $intvarname="p".$page->ID."_q".$question->ID."_";
								//look for additional textfields
								$db->setQuery('SELECT * FROM jcq_item WHERE questionID='.$question->ID);
								$items = $db->loadObjectList();
								for ($k=0;$k<count($items);$k++)
								{
									$item=$items[$k];
									if ($item->varname==$varname) $intvarname="p".$page->ID."_q".$question->ID."_i".$item->ID."_";
								}
								break;
							}
						case TEXTFIELD:
							{
								if ($question->varname==$varname) $intvarname="p".$page->ID."_q".$question->ID."_";
								break;
							}
						case MULTICHOICE: case MATRIX_LEFT: case MATRIX_BOTH:
							{
								$db->setQuery('SELECT * FROM jcq_item WHERE questionID = '.$question->ID.' ORDER BY ord');
								$items = $db->loadObjectList();
								for ($k=0;$k<count($items);$k++)
								{
									$item=$items[$k];
									if ($item->varname==$varname) $intvarname="p".$page->ID."_q".$question->ID."_i".$item->ID."_";
								}
								break;
							}
						case MULTISCALE:
							{
								$db->setQuery('SELECT * FROM jcq_scale, jcq_questionscales WHERE jcq_scale.ID = jcq_questionscales.scaleID AND questionID = '.$question->ID.' ORDER BY ord');
								$scales = $db->loadObjectList();
								$db->setQuery('SELECT * FROM jcq_item WHERE questionID = '.$question->ID.' ORDER BY ord');
								$items = $db->loadObjectList();
								for ($k=0;$k<count($items);$k++)
								{
									$item=$items[$k];
									for ($l=0;$l<count($scales);$l++)
									{
										$scale=$scales[$l];
										if ($item->varname."_s".$scale->ID==$varname) $intvarname = "p".$page->ID."_q".$question->ID."_i".$item->ID."_s".$scale->ID."_";
									}
								}
								break;
							}
						case TEXTANDHTML: break;
						default: JError::raiseError(500, 'FATAL: Code for accessing data from question of type '.$question->questtype.' is missing!!!');
					}
					if ($intvarname!=null) break;
				}
			}
			if ($intvarname!=null) break;
		}
	}
	if ($intvarname==null) return null;
	//get the value from the database
	$sqlgetvalue = "SELECT $intvarname FROM jcq_proj".$currentproject." WHERE sessionID='".$currentsession."'";
	$db->setQuery($sqlgetvalue);
	$answer = $db->loadResult();
	return $answer;
}

class JcqModelUserdata extends JModel
{
	private $db;
	private $projectID = null;
	private $sessionID = null;
	private $model_page;

	function __construct()
	{
		parent::__construct();
		$this->db = $this->getDBO();
		$this->model_page = new JcqModelPage();
	}

	function setProjectID($id)
	{
		$this->projectID=$id;
		global $currentproject;
		$currentproject = $this->projectID;
	}

	function setSessionID($id)
	{
		$this->sessionID=$id;
		global $currentsession;
		$currentsession = $this->sessionID;
	}

	function loadSession($projectID,$sessionID)
	{
		//this is just for safety: look if session really exists
		$sqlsession = "SELECT * FROM jcq_proj".$projectID." WHERE sessionID='".$sessionID."'";
		$db = $this->getDBO();
		$db->setQuery($sqlsession);
		$session = $db->loadObject();
		if ($session==null) return false; //error because session unknown
		else
		{
			$this->setSessionID($sessionID);
			$this->setProjectID($projectID);
			return true;
		}
	}

	function createSession($projectID)
	{
		$sqlproject = "SELECT * FROM jcq_project WHERE ID=".$projectID;
		$db = $this->getDBO();
		$db->setQuery($sqlproject);
		$project = $db->loadObject();

		if ($project==null) JError::raiseError(500, 'Project with ID '.$projectID.' not found!');

		$user =& JFactory::getUser();

		//case 1: a token is in the httprequest
		if (($token = JRequest::getVar('token', null))!==null)
		{
			$foundvalidtoken = false;
			$tokenusergroup = -1;
			$db->setQuery("SELECT * FROM jcq_usergroup WHERE projectID=$projectID");
			$usergroups = $db->loadObjectList();
			foreach ($usergroups as $usergroup)
			{
				$db->setQuery("SELECT * FROM jcq_token WHERE usergroupID=".$usergroup->ID);
				$tokens = $db->loadObjectList();
				foreach ($tokens as $onetoken)
				{
					if ($onetoken->token==$token)
					{
						$foundvalidtoken = true;
						$tokenusergroup = $usergroup->val;
						break;
					}
				}
				if ($foundvalidtoken) break;
			}
			//not a valid token --> return false (error)
			if (!$foundvalidtoken) return false;
			else
			{
				$sqlsessions = "SELECT * FROM jcq_proj".$projectID." WHERE userID='$token'";
				$db->setQuery($sqlsessions);
				$sessions = $db->loadObjectList();
					
				//case 1a: no session exists for token or multiple answers are permitted --> create session
				if ($sessions==null || $project->multiple==1)
				{
					$this->setSessionID(uniqid('', true));
					$this->setProjectID($projectID);
					$sqlnewsession = "INSERT INTO jcq_proj".$projectID." (userID, groupID, sessionID, curpage, timestampBegin) VALUES ('$token',$tokenusergroup,'".$this->sessionID."',0,".time().")";
					$db->setQuery($sqlnewsession);
					if (!$db->query())
					{
						$errorMessage = $this->getDBO()->getErrorMsg();
						JError::raiseError(500, 'Error inserting new session: '.$errorMessage);
					}
				}
				//case 2b: a session exists for user and multiple answers are not permitted --> load old session
				else
				{
					$this->setSessionID($sessions[0]->sessionID);
					$this->setProjectID($projectID);
				}
			}
		}
		//case 2: a joomla user is logged in and joomla users are allowed
		else if ($user->id!=0 && $project->allowjoomla==1)
		{
			$sqlsessions = "SELECT * FROM jcq_proj".$projectID." WHERE userID='".$user->username."'";
			$db->setQuery($sqlsessions);
			$sessions = $db->loadObjectList();
				
			//case 2a: no session exists for user or multiple answers are permitted --> create session
			if ($sessions==null || $project->multiple==1)
			{
				$this->setSessionID(uniqid('', true));
				$this->setProjectID($projectID);
				$sqlnewsession = "INSERT INTO jcq_proj".$projectID." (userID, groupID, sessionID, curpage, timestampBegin) VALUES ('".$user->username."',0,'".$this->sessionID."',0,".time().")";
				$db->setQuery($sqlnewsession);
				if (!$db->query())
				{
					$errorMessage = $this->getDBO()->getErrorMsg();
					JError::raiseError(500, 'Error inserting new session: '.$errorMessage);
				}
			}
			//case 2b: a session exists for user and multiple answers are not permitted --> load old session
			else
			{
				$this->setSessionID($sessions[0]->sessionID);
				$this->setProjectID($projectID);
			}
		}
		//case 3: no joomla user or joomla not allowed
		else
		{
			//case 3a: anonymous answers allowed --> create new ID
			if ($project->anonymous==1)
			{
				$this->setSessionID(uniqid('', true));
				$this->setProjectID($projectID);
				$sqlnewsession = "INSERT INTO jcq_proj".$projectID." (groupID, sessionID, curpage, timestampBegin) VALUES (-1,'".$this->sessionID."',0,".time().")";
				$db->setQuery($sqlnewsession);
				if (!$db->query())
				{
					$errorMessage = $this->getDBO()->getErrorMsg();
					JError::raiseError(500, 'Error inserting new session: '.$errorMessage);
				}
			}
			//case 3b: anonymous answers not allowed --> return false (error)
			else return false;
		}
		return true;
	}

	//requires a session to be loaded
	function getCurrentPage()
	{
		$sqlsession = "SELECT * FROM jcq_proj".$this->projectID." WHERE sessionID='".$this->sessionID."'";
		$db = $this->getDBO();
		$db->setQuery($sqlsession);
		$session = $db->loadObject();

		//case: if at the beginning, set to first page and return that value
		if ($session->curpage==0)
		{
			$sqlpages = "SELECT * FROM jcq_page WHERE projectID=".$this->projectID." ORDER BY isFinal, ord";
			$db->setQuery($sqlpages);
			$pages = $db->loadObjectList();
			$curpage = $pages[0]->ID;
			$sqlnextpage = "UPDATE jcq_proj".$this->projectID." SET curpage=".$curpage." WHERE sessionID='".$this->sessionID."'";
			$db->setQuery($sqlnextpage);
			if (!$db->query())
			{
				$errorMessage = $this->getDBO()->getErrorMsg();
				JError::raiseError(500, 'Error going next page: '.$errorMessage);
			}
			return $curpage;
		}
		//case: else if at the end, display the final page
		elseif ($session->curpage==-1)
		{
			$this->db->setQuery("SELECT * FROM jcq_page WHERE projectID=".$this->projectID." AND isFinal=1");
			$page = $db->loadObject();
			return $page->ID;
		}
		//case: else return current page to display
		else return $session->curpage;
	}

	function storeValue($intvarname, $response, $datatype=1)
	{
		if ($datatype==1) $this->db->setQuery("UPDATE jcq_proj".$this->projectID." SET $intvarname = $response WHERE sessionID='".$this->sessionID."'");
		elseif ($datatype==3) $this->db->setQuery("UPDATE jcq_proj".$this->projectID." SET $intvarname = '$response' WHERE sessionID='".$this->sessionID."'");
		else JError::raiseError(500, "FATAL: code for storing value of datatype $datatype is missing");
		if (!$this->db->query()) JError::raiseError(500, 'FATAL: '.$this->db->getErrorMsg());
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $questions
	 * @return boolean true if all mandatory items are answered
	 */
	function storeResponses($questions)
	{
		$allthere = true;
		foreach ($questions as $question)
		{
			$items = $this->model_page->getItemsToQuestion($question->ID);
			switch ($question->questtype)
			{
				case SINGLECHOICE:
					{
						$mainitem = null;
						foreach ($items as $item) if ($item->bindingType=="QUESTION") { $mainitem = $item; break; }
						if ($mainitem===null) JError::raiseError(500, "FATAL: corrupt question definition for '".$this->question->name."'");
						
						$intvarname = 'i'.$mainitem->ID.'_';
						$response = JRequest::getVar($intvarname,null);
						if ($response!=null && is_numeric($response)) $this->storeValue($intvarname, $response);
						else if ($mainitem->mandatory==1 && $this->getStoredValue($mainitem->ID)===null) $allthere=false;
						//handle textfields
						#TODO control if text is only entered when corresponding choice has been made
						#TODO check that a text is entered if mandatory
						#TODO check datatype of this text
						foreach ($items as $item)
						{
							if ($item->bindingType!="CODE") continue;
							$intvarname = 'i'.$item->ID.'_';
							$response = JRequest::getVar($intvarname,null);
							if ($response!==null && strlen($response)>0) $this->storeValue($intvarname, $response, 3);
						}
						break;
					}
				case MULTICHOICE:
					{
						$foundchecked = false;
						foreach ($items as $item)
						{
							if ($item->bindingType!="QUESTION") continue;
							$intvarname = 'i'.$item->ID.'_';
							$response = 0;
							if (JRequest::getVar($intvarname,null)!=null) { $response = 1; $foundchecked = true; }
							$this->storeValue($intvarname, $response);
						}
						//if mandatory and no item checked --> set missing
						if ($question->mandatory==1 && !$foundchecked) $allthere=false;
						//handle textfields
						#TODO control if text is only entered when corresponding choice has been made
						#TODO check that a text is entered if mandatory
						#TODO check datatype of this text
						foreach ($items as $item)
						{
							if ($item->bindingType!="ITEM") continue;
							$intvarname = 'i'.$item->ID.'_';
							$response = JRequest::getVar($intvarname,null);
							if ($response!==null && strlen($response)>0) $this->storeValue($intvarname, $response, 3);
						}
						break;
					}
				case TEXTFIELD:
					{
						//always store
						$sqlstore = "UPDATE jcq_proj".$this->projectID." SET p".$page->ID."q".$question->ID."='".JRequest::getVar('p'.$page->ID.'q'.$question->ID)."' WHERE sessionID='".$this->sessionID."'";
						$db->setQuery($sqlstore);
						if (!$db->query())
						{
							$errorMessage = $this->getDBO()->getErrorMsg();
							JError::raiseError(500, 'Error saving value: '.$errorMessage);
						}
						//if mandatory and no value stored so far --> set missing
						if ($question->mandatory==1)
						{
							$sqlgetvalue = "SELECT p".$page->ID."_q".$question->ID."_ FROM jcq_proj".$this->projectID." WHERE sessionID='".$this->sessionID."'";
							$db->setQuery($sqlgetvalue);
							$answer = $db->loadResult();
							if ($answer==null || strlen($answer)<1) $hasmissings=true;
						}
						//if data type does not match --> set missing
						if ($question->datatype == 1 && JRequest::getVar('p'.$page->ID.'_q'.$question->ID.'_') != null && !val_is_int(JRequest::getVar('p'.$page->ID.'q'.$question->ID))) $hasmissings=true;
						if ($question->datatype == 2 && JRequest::getVar('p'.$page->ID.'_q'.$question->ID.'_') != null && !is_numeric(JRequest::getVar('p'.$page->ID.'q'.$question->ID))) $hasmissings=true;
						#TODO decimal seperator for locale
						break;
					}
				case MATRIX_LEFT: case MATRIX_BOTH:
					{
						$items = $modelpage->getItemsToQuestion($question->ID);
						foreach ($items as $item)
						{
							if (JRequest::getVar('p'.$page->ID.'q'.$question->ID.'i'.$item->ID,null)!=null && is_numeric(JRequest::getVar('p'.$page->ID.'q'.$question->ID.'i'.$item->ID)))
							{
								//numeric value is posted --> store
								$sqlstore = "UPDATE jcq_proj".$this->projectID." SET p".$page->ID."q".$question->ID."i".$item->ID."=".JRequest::getVar('p'.$page->ID.'q'.$question->ID.'i'.$item->ID)." WHERE sessionID='".$this->sessionID."'";
								$db->setQuery($sqlstore);
								if (!$db->query())
								{
									$errorMessage = $this->getDBO()->getErrorMsg();
									JError::raiseError(500, 'Error saving value: '.$errorMessage);
								}
							}
							else
							{
								//if mandatory and no value stored so far --> set missing
								if ($item->mandatory==1)
								{
									$sqlgetvalue = "SELECT p".$page->ID."_q".$question->ID."_i".$item->ID."_ FROM jcq_proj".$this->projectID." WHERE sessionID='".$this->sessionID."'";
									$db->setQuery($sqlgetvalue);
									$answer = $db->loadResult();
									if ($answer["p".$page->ID."q".$question->ID."i".$item->ID]==null) $hasmissings=true;
								}
							}
						}
						break;
					}
				case MULTISCALE:
					{
						$items = $modelpage->getItemsToQuestion($question->ID);
						$scales = $modelpage->getScalesToQuestion($question->ID);
						foreach ($items as $item)
						{
							foreach($scales as $scale)
							{
								$varname = 'p'.$page->ID.'_q'.$question->ID.'_i'.$item->ID.'_s'.$scale->ID.'_';
								if (JRequest::getVar($varname,null)!=null && is_numeric(JRequest::getVar($varname)))
								{
									//numeric value is posted --> store
									$sqlstore = "UPDATE jcq_proj".$this->projectID." SET $varname =".JRequest::getVar($varname)." WHERE sessionID='".$this->sessionID."'";
									$db->setQuery($sqlstore);
									if (!$db->query())
									{
										$errorMessage = $this->getDBO()->getErrorMsg();
										JError::raiseError(500, 'Error saving value: '.$errorMessage);
									}
								}
								else
								{
									//if mandatory and no value stored so far --> set missing
									if ($item->mandatory==1 && $scale->mandatory==1)
									{
										$sqlgetvalue = "SELECT $varname FROM jcq_proj".$this->projectID." WHERE sessionID='".$this->sessionID."'";
										$db->setQuery($sqlgetvalue);
										$answer = $db->loadResult();
										if ($answer==null) $hasmissings=true;
									}
								}
							}
						}
						break;
					}
				case TEXTANDHTML: break;
				default: JError::raiseError(500, 'FATAL: Code is missing for storing values of question type '.$question->questtype);
			}
		}
		return $allthere;
	}

	//requires a session to be loaded
	function storeAndContinue()
	{
		$this->db->setQuery("SELECT * FROM jcq_proj".$this->projectID." WHERE sessionID='".$this->sessionID."'");
		$session = $this->db->loadObject();
		$this->db->setQuery("SELECT * FROM jcq_page WHERE projectID=".$this->projectID." ORDER BY ord");
		$pages = $this->db->loadObjectList();

		$foundpage = false;
		for ($i=0;$i<count($pages);$i++)
		{
			if ($pages[$i]->ID==$session->curpage)
			{
				$foundpage = true;
				$page = $pages[$i];

				$this->db->setQuery("SELECT * FROM jcq_question WHERE pageID=".$page->ID);
				$questions = $this->db->loadObjectList();

				$hasmissings = !$this->storeResponses($questions);
				
				//go to next page if all mandatory questions/items answered
				if (!$hasmissings)
				{
					//set timestamp on this page
					$this->db->setQuery("UPDATE jcq_proj".$this->projectID." SET p".$page->ID."_timestamp=".time()." WHERE sessionID='".$this->sessionID."'");
					if (!$this->db->query()) JError::raiseError(500, 'FATAL: '.$this->db->getErrorMsg());
					//search for next unfiltered page
					$foundnextpage = false;
					while (++$i < count($pages))
					{
						if ($this->checkPageFilter($pages[$i]->ID))
						{
							$nextpage = $pages[$i]->ID;
							$foundnextpage = true;
							break;
						}
					}
					//no next page --> user code has to be invoked
					if (!$foundnextpage)
					{
						$nextpage = -1;
						//FIXME minor bug: finished=1 is not set, when projet has only the final page
						$this->db->setQuery("UPDATE jcq_proj".$this->projectID." SET finished=1, timestampEnd=".time()." WHERE sessionID='".$this->sessionID."'");
						if (!$this->db->query()) JError::raiseError(500, 'FATAL: '.$this->db->getErrorMsg());
					}
					$this->db->setQuery("UPDATE jcq_proj".$this->projectID." SET curpage=".$nextpage." WHERE sessionID='".$this->sessionID."'");
					if (!$this->db->query()) JError::raiseError(500, 'FATAL: '.$this->db->getErrorMsg());
				}
				break;
			}
		}
		if (!foundpage) JError::raiseError(500, 'FATAL: could not find page with ID '.$session->curpage);
		return !$hasmissings;
	}

	function checkPageFilter ($pageID)
	{
		$this->db->setQuery("SELECT * FROM jcq_page WHERE ID=$pageID");
		$page = $this->db->loadObject();
		if ($page==null) JError::raiseError(500, "Error: could not find page with ID $pageID");
		if ($page->filter==null || strlen($page->filter)<1) return true;

		$return = false;
		$filter = $page->filter;
		$disjunctions = explode("|",$filter);
		foreach ($disjunctions as $disjunction)
		{
			$inner = true;
			$disjunction = str_replace(array("(",")"), "", $disjunction); //strip the brackets
			$conjugations = explode("&",$disjunction);
			foreach ($conjugations as $conjugation)
			{
				$firstdelim = strpos($conjugation, "$");
				$seconddelim = strpos($conjugation, "$", $firstdelim+1);
				$varname = substr($conjugation, $firstdelim+1, $seconddelim-$firstdelim-1);
				//all comparisons against missing data yield false
				if (!$this->hasStoredValueVariable($varname)) $inner = $inner && false;
				else
				{
					if (strpos($conjugation,"==")!==false) $inner = $inner && ($this->getStoredValueVariable($varname)==substr($conjugation,strpos($conjugation,"==")+2));
					if (strpos($conjugation,"!=")!==false) $inner = $inner && ($this->getStoredValueVariable($varname)!=substr($conjugation,strpos($conjugation,"!=")+2));
					if (strpos($conjugation,"<")!==false) $inner = $inner && ($this->getStoredValueVariable($varname)<substr($conjugation,strpos($conjugation,"<")+1));
					if (strpos($conjugation,"<=")!==false) $inner = $inner && ($this->getStoredValueVariable($varname)<=substr($conjugation,strpos($conjugation,"<=")+2));
					if (strpos($conjugation,">=")!==false) $inner = $inner && ($this->getStoredValueVariable($varname)>=substr($conjugation,strpos($conjugation,">=")+2));
					elseif (strpos($conjugation,">")!==false) $inner = $inner && ($this->getStoredValueVariable($varname)>substr($conjugation,strpos($conjugation,">")+1));
				}
			}
			$return = $return || $inner;
		}
		return $return;
	}

	function getSessionID()
	{
		return $this->sessionID;
	}

	function hasStoredValueVariable($varname)
	{
		$sqlgetvalue = "SELECT $varname FROM jcq_proj".$this->projectID." WHERE sessionID='".$this->sessionID."'";
		$this->db->setQuery($sqlgetvalue);
		$answer = $this->db->loadResult();
		return ($answer!=null);
	}

	function hasStoredValue($itemID,$scaleID=null)
	{
		if ($scaleID===null) $sqlgetvalue = "SELECT i".$itemID."_ FROM jcq_proj".$this->projectID." WHERE sessionID='".$this->sessionID."'";
		else $sqlgetvalue = "SELECT i".$itemID."_s".$scaleID."_ FROM jcq_proj".$this->projectID." WHERE sessionID='".$this->sessionID."'";
		$this->db->setQuery($sqlgetvalue);
		$answer = $this->db->loadResult();
		return ($answer!=null);
	}

	function getStoredValueVariable($varname)
	{
		$sqlgetvalue = "SELECT $varname FROM jcq_proj".$this->projectID." WHERE sessionID='".$this->sessionID."'";
		$this->db->setQuery($sqlgetvalue);
		$answer = $this->db->loadResult();
		return $answer;
	}

	function getStoredValue($itemID,$scaleID=null)
	{
		if ($scaleID===null) $sqlgetvalue = "SELECT i".$itemID."_ FROM jcq_proj".$this->projectID." WHERE sessionID='".$this->sessionID."'";
		else $sqlgetvalue = "SELECT i".$itemID."_s".$scaleID."_ FROM jcq_proj".$this->projectID." WHERE sessionID='".$this->sessionID."'";
		$this->db->setQuery($sqlgetvalue);
		$answer = $this->db->loadResult();
		return $answer;
	}

	/**
	 * Fetches a value from the result set using the human-readable variable name.
	 * Requires an existing and unique variable name.
	 * @param string $varname The variable name.
	 */
	function getValuePerName($varname)
	{
		$getallquestions = "SELECT jcq_question.ID AS ID, jcq_question.varname AS varname, jcq_page.ID AS pageID FROM jcq_question JOIN jcq_page ON jcq_question.pageID=jcq_page.ID WHERE jcq_page.projectID=".$this->projectID;
		$getallitems = "SELECT jcq_item.ID AS ID, jcq_item.varname AS varname, jcq_question.ID AS questionID, jcq_page.ID AS pageID FROM (jcq_item JOIN jcq_question ON jcq_item.questionID=jcq_question.ID) JOIN jcq_page ON jcq_question.pageID=jcq_page.ID WHERE jcq_page.projectID=".$this->projectID;
		$db = $this->getDBO();
		$db->setQuery($getallquestions);
		$allquestions = $db->loadObjectList();
		$db->setQuery($getallitems);
		$allitems = $db->loadObjectList();
		#FIXME collation unknown
		foreach($allquestions as $question)
		{
			if (strcmp($question->varname,$varname)==0) return $this->getStoredValueQuestion($question->pageID,$question->ID);
		}
		foreach($allitems as $item)
		{
			if (strcmp($item->varname,$varname)==0)	return $this->getStoredValueItem($item->pageID,$item->questionID,$item->ID);
		}
		die();
		JError::raiseError(500, 'Error: could not find variable "'.$varname.'"');
	}
}