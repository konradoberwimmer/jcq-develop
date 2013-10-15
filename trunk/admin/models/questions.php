<?php
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');
require_once(JPATH_COMPONENT.DS.'models'.DS.'items.php');
require_once(JPATH_COMPONENT.DS.'models'.DS.'scales.php');

class JcqModelQuestions extends JModel {

	//TODO: secure against insertion

	private $db;
	
	function __construct()
	{
		parent::__construct();
		$this->db = $this->getDBO();		
	}
	
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
		$this->db->setQuery($query);
		$question = $this->db->loadObject();
			
		if ($question === null) JError::raiseError(500, 'Question with ID: '.$ID.' not found.');
		else return $question;
	}

	function getTypeFromQuestion($ID)
	{
		$query = 'SELECT questtype FROM jcq_question WHERE ID = '.$ID;
		$this->db->setQuery($query);
		$question = $this->db->loadObject();
			
		if ($question === null) JError::raiseError(500, 'Question with ID: '.$ID.' not found.');
		else return $question->questtype;
	}
	
	function getItems($questionID)
	{
		$query = 'SELECT * FROM jcq_item WHERE questionID = '.$questionID.' ORDER BY ord';
		$this->db->setQuery($query);
		return $this->db->loadObjectList();
	}
	
	function getScales($questionID)
	{
		$query = "SELECT * FROM jcq_scale, jcq_questionscales WHERE jcq_scale.ID = jcq_questionscales.scaleID AND jcq_questionscales.questionID = $questionID ORDER BY ord";
		$this->db->setQuery($query);
		return $this->db->loadObjectList();
	}
	
	function detachScale($questionID,$scaleID)
	{
		$query = "DELETE FROM jcq_questionscales WHERE jcq_questionscales.scaleID = $scaleID AND jcq_questionscales.questionID = $questionID";
		$this->db->setQuery($query);
		if (!$this->db->query($query)) JError::raiseError(500, "FATAL: ".$this->db->getErrorMsg());
	}
	
	function getNewQuestion($pageID)
	{
		$questionTableRow =& $this->getTable('questions');
		$questionTableRow->ID = 0;
		$questionTableRow->name = '';
		$this->db->setQuery("SELECT ord FROM jcq_question WHERE pageID=$pageID ORDER BY ord DESC");
		$questions = $this->db->loadObjectList();
		if ($questions!==null) $questionTableRow->ord = $questions[0]->ord + 1;
		else $questionTableRow->ord = 1;
		$questionTableRow->pageID = $pageID;
		return $questionTableRow;
	}

	function saveQuestion($question)
	{
		$questionTableRow =& $this->getTable();
		if (!$questionTableRow->bind($question)) JError::raiseError(500, 'Error binding data');
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
			$model_items = new JcqModelItems();
			switch ($questionTableRow->questtype)
			{
				case SINGLECHOICE:
					{
						$model_items->buildNewItem($questionTableRow->ID, 1);
						$this->buildScalePrototype($questionTableRow->ID);
						break;
					}
				case MULTICHOICE:
					{
						for ($i=1;$i<=5;$i++) $model_items->buildNewItem($questionTableRow->ID, 1);
						break;
					}
				case TEXTFIELD:
					{
						$model_items->buildNewItem($questionTableRow->ID, 3);
						break;
					}
				case MATRIX_LEFT: case MATRIX_BOTH:
					{
						for ($i=1;$i<=5;$i++) $model_items->buildNewItem($questionTableRow->ID, 1);
						$this->buildScalePrototype($questionTableRow->ID);
						break;
					}
				case MULTISCALE:
					{
						for ($i=1;$i<=5;$i++) $model_items->buildNewItem($questionTableRow->ID, 1, array());
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

	function deleteQuestion($ID)
	{
		//first delete all the items belonging to the question
		$model_items = new JcqModelItems();
		$items = $this->getItems($ID);
		if ($items!==null) foreach ($items as $item) $model_items->deleteItem($item->ID);
		//then remove all but predefined scales of this question
		$model_scales = new JcqModelScales();
		$scales = $this->getScales($ID);
		if ($scales!==null) foreach ($scales as $scale) if (!$scale->predefined)
		{
			$this->detachScale($ID,$scale->ID);
			$model_scales->deleteScale($scale->ID);
		}
		//delete the question itself
		$this->db->setQuery("DELETE FROM jcq_question WHERE ID = $ID");
		if (!$this->db->query()) JError::raiseError(500, 'FATAL: '.$this->db->getErrorMsg());
	}

	function setQuestionOrder(array $questionids,array $questionord)
	{
		for ($i=0;$i<count($questionids);$i++)
		{
			$query = "UPDATE jcq_question SET ord=".$questionord[$i]." WHERE ID=".$questionids[$i];
			$this->db->setQuery($query);
			if (!$this->db->query()){
				$errorMessage = $this->getDBO()->getErrorMsg();
				JError::raiseError(500, 'Error setting question order: '.$errorMessage);
			}
		}
	}

	function getPageFromQuestion($questionID)
	{
		$query = 'SELECT * FROM jcq_question WHERE ID = '.$questionID;
		$this->db->setQuery($query);
		$question = $this->db->loadObject();
			
		if ($question === null) JError::raiseError(500, 'Question with ID: '.$questionID.' not found.');
		else
		{
			$query = 'SELECT * FROM jcq_page WHERE ID = '.$question->pageID;
			$this->db->setQuery($query);
			$page = $this->db->loadObject();

			if ($page === null) JError::raiseError(500, 'Page with ID: '.$question->pageID.' not found.');
			else return $page;
		}
	}

	function getProjectFromPage($pageID)
	{
		$query = 'SELECT * FROM jcq_page WHERE ID = '.$pageID;
		$this->db->setQuery($query);
		$page = $this->db->loadObject();
			
		if ($page === null) JError::raiseError(500, 'Page with ID: '.$pageID.' not found.');
		else
		{
			$query = 'SELECT * FROM jcq_project WHERE ID = '.$page->projectID;
			$this->db->setQuery($query);
			$project = $this->db->loadObject();

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
		$this->db->setQuery($query);
		if (!$this->db->query()){
			$errorMessage = $this->getDBO()->getErrorMsg();
			JError::raiseError(500, 'Error inserting scale: '.$errorMessage);
		}
	}

}