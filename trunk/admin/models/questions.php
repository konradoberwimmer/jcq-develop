<?php
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');


class JcqModelQuestions extends JModel {

	//TODO: secure against insertion

	static function getQuestionTypes()
	{
		$questtypes = array();
		$questtypes[SINGLECHOICE]='Single selection';
		$questtypes[MULTICHOICE]='Multiple selection';
		$questtypes[TEXTFIELD]='Text field';
		$questtypes[MATRIX_LEFT]='Matrix (with single item text)';
		$questtypes[MATRIX_BOTH]='Matrix (semantical difference)';
		$questtypes[MULTISCALE]='Multiple-Scale Matrix';
		$questtypes[TEXTANDHTML]='Text and HTML-Code';
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
		$datatypes[4]='NONE';
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

		// Add default values, items and scales for different question types
		// Alter the user data table if question is new
		// Explanation: object question has ID=0 if new question, the questionTableRow is updated with the new ID after store()
		if ($question['ID']==0)
		{
			switch ($questionTableRow->questtype)
			{
				case SINGLECHOICE:
					{
						$questionTableRow->varname = 'question'.$questionTableRow->ID;
						$questionTableRow->store();
						$this->buildScalePrototype($questionTableRow->ID);
						$this->addColumnUserDataINT($questionTableRow->pageID,$questionTableRow->ID);
						break;
					}
				case TEXTFIELD:
					{
						$questionTableRow->varname = 'question'.$questionTableRow->ID;
						$questionTableRow->datatype = 3;
						$questionTableRow->prepost = "%s";
						$questionTableRow->width_items = "50";
						$questionTableRow->store();
						$this->addColumnUserDataTEXT($questionTableRow->pageID,$questionTableRow->ID);
						break;
					}
				case MATRIX_LEFT: case MATRIX_BOTH:
					{
						$this->buildScalePrototype($questionTableRow->ID);
						$this->buildItemPrototype($questionTableRow->ID,1);
						break;
					}
				case MULTISCALE:
					{
						$this->buildItemPrototype($questionTableRow->ID,1,array());
						break;						
					}
				case TEXTANDHTML:
					{
						$questionTableRow->datatype = 4;
						$questionTableRow->mandatory = false;
						$questionTableRow->store();
						break;
					}
				default: JError::raiseError(500, 'FATAL: Code for creating question of type '.$questionTableRow->questtype.' is missing!!!');
			}
		}
		
		return $questionTableRow->ID;
	}

	function deleteQuestions($arrayIDs)
	{
		// beforehand delete the user data columns if necessary (otherwise page ID is unknown)
		foreach ($arrayIDs as $oneID)
		{
			$question = $this->getQuestion($oneID);
			$page = $this->getPageFromQuestion($oneID);
			$project = $this->getProjectFromPage($page->ID);

			switch ($question->questtype)
			{
				case TEXTFIELD:
					{
						$query = "ALTER TABLE jcq_proj".$project->ID." DROP COLUMN p".$page->ID."q".$oneID;
						$db = $this->getDBO();
						$db->setQuery($query);
						if (!$db->query()){
							$errorMessage = $this->getDBO()->getErrorMsg();
							JError::raiseError(500, 'Error altering user data table: '.$errorMessage);
						}
						break;
					}
				case SINGLECHOICE: case MATRIX_LEFT: case MATRIX_BOTH: case MULTISCALE:
					{
						$statementquery = "SELECT CONCAT('ALTER TABLE jcq_proj".$project->ID." ', GROUP_CONCAT('DROP COLUMN ',column_name)) AS statement FROM information_schema.columns WHERE table_name = 'jcq_proj".$project->ID."' AND column_name LIKE 'p".$page->ID."q".$question->ID."%';";
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
						break;
					}
				case TEXTANDHTML:
					{
						break;
					}
				default: JError::raiseError(500, 'FATAL: Code for deleting question of type '.$question->questtype.' is missing!!!');
			}
		}

		$query = "DELETE FROM jcq_question WHERE ID IN (".implode(',', $arrayIDs).")";
		$db = $this->getDBO();
		$db->setQuery($query);
		if (!$db->query()){
			$errorMessage = $this->getDBO()->getErrorMsg();
			JError::raiseError(500, 'Error deleting questions: '.$errorMessage);
		}
		//TODO with more question types it will be necessary to delete user data columns from items!
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

	function buildScalePrototype($questionID)
	{
		$newscale =& $this->getTable('scales');
		$newscale->name = 'question'.$questionID.'scale';
		if (!$newscale->store())
		{
			$errorMessage = $newscale->getError();
			JError::raiseError(500, 'Error inserting data: '.$errorMessage);
		}
		for ($i=1;$i<=5;$i++)
		{
			$newcode =& $this->getTable('codes');
			$newcode->scaleID = $newscale->ID;
			$newcode->ord = $i;
			$newcode->code = $i;
			if (!$newcode->store())
			{
				$errorMessage = $newcode->getError();
				JError::raiseError(500, 'Error inserting data: '.$errorMessage);
			}
		}
		$query = "INSERT INTO jcq_questionscales (questionID, scaleID) VALUES (".$questionID.",".$newscale->ID.")";
		$db = $this->getDBO();
		$db->setQuery($query);
		if (!$db->query()){
			$errorMessage = $this->getDBO()->getErrorMsg();
			JError::raiseError(500, 'Error inserting scale: '.$errorMessage);
		}
	}

	function buildItemPrototype($questionID,$datatype,$scales=null)
	{
		for ($i=1;$i<=5;$i++)
		{
			$newitem =& $this->getTable('items');
			$newitem->questionID = $questionID;
			$newitem->ord = $i;
			$newitem->varname = "question".$questionID;
			if (!$newitem->store())
			{
				$errorMessage = $newitem->getError();
				JError::raiseError(500, 'Error inserting data: '.$errorMessage);
			}
			else
			{
				//refine varname now that ID is known
				$newitem->varname = "question".$questionID."item".$newitem->ID;
				$newitem->store();
				//use model items to add user data columns
				require_once( JPATH_COMPONENT.DS.'models'.DS.'items.php' );
				$modelitems = new JcqModelItems();
				switch ($datatype)
				{
					case 1:
						{
							if ($scales===null) $modelitems->addColumnUserDataINT($this->getPageFromQuestion($questionID)->ID, $questionID, $newitem->ID);
							else foreach ($scales as $scale) $modelitems->addColumnUserDataINT($this->getPageFromQuestion($questionID)->ID, $questionID, $newitem->ID, $scale->ID);
							break;
						}
					default: JError::raiseError(500, 'FATAL: code for adding user data column of datatype '.$datatype.' is missing!');
				}
			}
		}
	}

	function addColumnUserDataINT($pageID,$questionID)
	{
		$project = $this->getProjectFromPage($pageID);
		$query = "ALTER TABLE jcq_proj".$project->ID." ADD COLUMN p".$pageID."q".$questionID." INT";
		$db = $this->getDBO();
		$db->setQuery($query);
		if (!$db->query())
		{
			$errorMessage = $this->getDBO()->getErrorMsg();
			JError::raiseError(500, 'Error altering user data table: '.$errorMessage);
		}
	}
	
	function addColumnUserDataTEXT($pageID,$questionID)
	{
		$project = $this->getProjectFromPage($pageID);
		$query = "ALTER TABLE jcq_proj".$project->ID." ADD COLUMN p".$pageID."q".$questionID." TEXT";
		$db = $this->getDBO();
		$db->setQuery($query);
		if (!$db->query())
		{
			$errorMessage = $this->getDBO()->getErrorMsg();
			JError::raiseError(500, 'Error altering user data table: '.$errorMessage);
		}
	}
}