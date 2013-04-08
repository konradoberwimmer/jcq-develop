<?php
defined('_JEXEC') or die( 'Restricted access' );

function val_is_int($input) {
	if ($input[0] == '-') {
		return ctype_digit(substr($input, 1));
	}
	return ctype_digit($input);
}

jimport('joomla.application.component.model');

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
						case 111:
							{
								if ($question->varname==$varname) $intvarname="p".$page->ID."q".$question->ID;
								//look for additional textfields
								$db->setQuery('SELECT * FROM jcq_item WHERE questionID='.$question->ID);
								$items = $db->loadObjectList();
								for ($k=0;$k<count($items);$k++)
								{
									$item=$items[$k];
									if ($item->varname==$varname) $intvarname="p".$page->ID."q".$question->ID."i".$item->ID;
								}
								break;
							}
						case 141:
							{
								if ($question->varname==$varname) $intvarname="p".$page->ID."q".$question->ID;
								break;
							}
						case 311: case 340:
							{
								$db->setQuery('SELECT * FROM jcq_item WHERE questionID = '.$question->ID.' ORDER BY ord');
								$items = $db->loadObjectList();
								for ($k=0;$k<count($items);$k++)
								{
									$item=$items[$k];
									if ($item->varname==$varname) $intvarname="p".$page->ID."q".$question->ID."i".$item->ID;
								}
								break;
							}
						case 361:
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
										if ($item->varname."_s".$scale->ID==$varname) $intvarname = "p".$page->ID."q".$question->ID."i".$item->ID."s".$scale->ID;
									}
								}
								break;
							}
						case 998: break;
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

	function __construct()
	{
		parent::__construct();
		$this->db = $this->getDBO();
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
			$this->sessionID = $sessionID;
			$this->projectID = $projectID;
			//these simple steps allow user code to access answers from the current session without violating privacy
			global $currentsession, $currentproject;
			$currentsession = $this->sessionID;
			$currentproject = $this->projectID;
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

		//case 1: no user logged in (ID is set to 0)
		if ($user->id==0)
		{
			//case 1a: anonymous answers allowed --> create new ID
			if ($project->anonymous==1)
			{
				$this->sessionID = uniqid('', true);
				$this->projectID = $projectID;
				$sqlnewsession = "INSERT INTO jcq_proj".$projectID." (sessionID, curpage, timestampBegin) VALUES ('".$this->sessionID."',0,".time().")";
				$db->setQuery($sqlnewsession);
				if (!$db->query())
				{
					$errorMessage = $this->getDBO()->getErrorMsg();
					JError::raiseError(500, 'Error inserting new session: '.$errorMessage);
				}
			}
			//case 1b: anonymous answers not allowed --> return false (error)
			else return false;
		}
		//case 2: a user is logged in
		else
		{
			$sqlsessions = "SELECT * FROM jcq_proj".$projectID." WHERE userID='".$user->username."'";
			$db->setQuery($sqlsessions);
			$sessions = $db->loadObjectList();

			//case 2a: no session exists for user or multiple answers are permitted --> create session
			if ($sessions==null || $project->multiple==1)
			{
				$this->sessionID = uniqid('', true);
				$this->projectID = $projectID;
				$sqlnewsession = "INSERT INTO jcq_proj".$projectID." (userID, sessionID, curpage, timestampBegin) VALUES ('".$user->username."','".$this->sessionID."',0,".time().")";
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
				$this->sessionID = $sessions[0]->sessionID;
				$this->projectID = $projectID;
			}
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

	//requires a session to be loaded
	function storeAndContinue()
	{
		$sqlsession = "SELECT * FROM jcq_proj".$this->projectID." WHERE sessionID='".$this->sessionID."'";
		$db = $this->getDBO();
		$db->setQuery($sqlsession);
		$session = $db->loadObject();

		$sqlpages = "SELECT * FROM jcq_page WHERE projectID=".$this->projectID." ORDER BY ord";
		$db->setQuery($sqlpages);
		$pages = $db->loadObjectList();
		$foundpage = false;
		for ($i=0;$i<count($pages);$i++)
		{
			if ($pages[$i]->ID==$session->curpage)
			{
				$foundpage = true;
				$hasmissings = false;

				$page = $pages[$i];

				//store (TODO: should be in a separate function)
				$sqlquestions = "SELECT * FROM jcq_question WHERE pageID=".$page->ID;
				$db->setQuery($sqlquestions);
				$questions = $db->loadObjectList();
				foreach ($questions as $question)
				{
					//handle questions according to questiontype
					switch ($question->questtype)
					{
						case 111:
							{
								if (JRequest::getVar('p'.$page->ID.'q'.$question->ID,null)!=null && is_numeric(JRequest::getVar('p'.$page->ID.'q'.$question->ID)))
								{
									//numeric value is posted --> store
									$sqlstore = "UPDATE jcq_proj".$this->projectID." SET p".$page->ID."q".$question->ID."=".JRequest::getVar('p'.$page->ID.'q'.$question->ID)." WHERE sessionID='".$this->sessionID."'";
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
									if ($question->mandatory==1)
									{
										$sqlgetvalue = "SELECT p".$page->ID."q".$question->ID." FROM jcq_proj".$this->projectID." WHERE sessionID='".$this->sessionID."'";
										$db->setQuery($sqlgetvalue);
										$answer = $db->loadResult();
										if ($answer["p".$page->ID."q".$question->ID]==null) $hasmissings=true;
									}
								}
								break;
							}
						case 141:
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
									$sqlgetvalue = "SELECT p".$page->ID."q".$question->ID." FROM jcq_proj".$this->projectID." WHERE sessionID='".$this->sessionID."'";
									$db->setQuery($sqlgetvalue);
									$answer = $db->loadResult();
									if ($answer==null || strlen($answer)<1) $hasmissings=true;
								}
								//if data type does not match --> set missing
								if ($question->datatype == 1 && !val_is_int(JRequest::getVar('p'.$page->ID.'q'.$question->ID))) $hasmissings=true;
								if ($question->datatype == 2 && !is_numeric(JRequest::getVar('p'.$page->ID.'q'.$question->ID))) $hasmissings=true;
								#TODO decimal seperator for locale
								break;
							}
						case 311: case 340:
							{
								// use model page to get the items to the question
								require_once( JPATH_COMPONENT.DS.'models'.DS.'page.php' );
								$modelpage = new JcqModelPage();
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
											$sqlgetvalue = "SELECT p".$page->ID."q".$question->ID."i".$item->ID." FROM jcq_proj".$this->projectID." WHERE sessionID='".$this->sessionID."'";
											$db->setQuery($sqlgetvalue);
											$answer = $db->loadResult();
											if ($answer["p".$page->ID."q".$question->ID."i".$item->ID]==null) $hasmissings=true;
										}
									}
								}
								break;
							}
						case 361:
							{
								// use model page to get the items to the question
								require_once( JPATH_COMPONENT.DS.'models'.DS.'page.php' );
								$modelpage = new JcqModelPage();
								$items = $modelpage->getItemsToQuestion($question->ID);
								$scales = $modelpage->getScalesToQuestion($question->ID);
								foreach ($items as $item)
								{
									foreach($scales as $scale)
									{
										$varname = 'p'.$page->ID.'q'.$question->ID.'i'.$item->ID.'s'.$scale->ID;
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
						case 998: break;
						default: JError::raiseError(500, 'FATAL: Code is missing for storing values of question type '.$question->questtype);
					}
				}

				//go to next page if all mandatory questions/items answered
				if (!$hasmissings)
				{
					//set timestamp on this page
					$sqlstore = "UPDATE jcq_proj".$this->projectID." SET p".$page->ID."timestamp=".time()." WHERE sessionID='".$this->sessionID."'";
					$db->setQuery($sqlstore);
					if (!$db->query())
					{
						$errorMessage = $this->getDBO()->getErrorMsg();
						JError::raiseError(500, 'Error saving timestamp: '.$errorMessage);
					}
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
						$sqlstore = "UPDATE jcq_proj".$this->projectID." SET finished=1, timestampEnd=".time()." WHERE sessionID='".$this->sessionID."'";
						$db->setQuery($sqlstore);
						if (!$db->query())
						{
							$errorMessage = $this->getDBO()->getErrorMsg();
							JError::raiseError(500, 'Error saving: '.$errorMessage);
						}
					}
					$sqlnextpage = "UPDATE jcq_proj".$this->projectID." SET curpage=".$nextpage." WHERE sessionID='".$this->sessionID."'";
					$db->setQuery($sqlnextpage);
					if (!$db->query())
					{
						$errorMessage = $this->getDBO()->getErrorMsg();
						JError::raiseError(500, 'Error going next page: '.$errorMessage);
					}
				}
				break;
			}
		}
		if (!foundpage) JError::raiseError(500, 'Error: could not find page with ID'.$session->curpage);
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

	function hasStoredValueQuestion($pageID,$questionID)
	{
		$sqlgetvalue = "SELECT p".$pageID."q".$questionID." FROM jcq_proj".$this->projectID." WHERE sessionID='".$this->sessionID."'";
		$db = $this->getDBO();
		$db->setQuery($sqlgetvalue);
		$answer = $db->loadResult();
		return ($answer!=null);
	}

	function hasStoredValueItem($pageID,$questionID,$itemID,$scaleID=null)
	{
		if ($scaleID===null) $sqlgetvalue = "SELECT p".$pageID."q".$questionID."i".$itemID." FROM jcq_proj".$this->projectID." WHERE sessionID='".$this->sessionID."'";
		else $sqlgetvalue = "SELECT p".$pageID."q".$questionID."i".$itemID."s".$scaleID." FROM jcq_proj".$this->projectID." WHERE sessionID='".$this->sessionID."'";
		$db = $this->getDBO();
		$db->setQuery($sqlgetvalue);
		$answer = $db->loadResult();
		return ($answer!=null);
	}

	function getStoredValueVariable($varname)
	{
		$sqlgetvalue = "SELECT $varname FROM jcq_proj".$this->projectID." WHERE sessionID='".$this->sessionID."'";
		$this->db->setQuery($sqlgetvalue);
		$answer = $this->db->loadResult();
		return $answer;
	}

	function getStoredValueQuestion($pageID,$questionID)
	{
		$sqlgetvalue = "SELECT p".$pageID."q".$questionID." FROM jcq_proj".$this->projectID." WHERE sessionID='".$this->sessionID."'";
		$db = $this->getDBO();
		$db->setQuery($sqlgetvalue);
		$answer = $db->loadResult();
		return $answer;
	}

	function getStoredValueItem($pageID,$questionID,$itemID,$scaleID=null)
	{
		if ($scaleID===null) $sqlgetvalue = "SELECT p".$pageID."q".$questionID."i".$itemID." FROM jcq_proj".$this->projectID." WHERE sessionID='".$this->sessionID."'";
		else $sqlgetvalue = "SELECT p".$pageID."q".$questionID."i".$itemID."s".$scaleID." FROM jcq_proj".$this->projectID." WHERE sessionID='".$this->sessionID."'";
		$db = $this->getDBO();
		$db->setQuery($sqlgetvalue);
		$answer = $db->loadResult();
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