<?php
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');
require_once(JPATH_COMPONENT.DS.'models'.DS.'items.php');

class JcqModelScales extends JModel {

	private $db;
	
	function __construct()
	{
		parent::__construct();
		$this->db = $this->getDBO();
	}

	static function getScaleLayouts ()
	{
		$layouts = array();
		$layouts[LAYOUT_RADIOHORIZON]='Radiobuttons: horizontal';
		$layouts[LAYOUT_RADIOVERTICAL]='Radiobuttons: vertical';
		$layouts[LAYOUT_SELECTBOX]='Selectbox';
		return $layouts;
	}
	
	static function getScaleRelativePositions ()
	{
		$relpos = array();
		$relpos[RELPOS_RIGHT]='right';
		$relpos[RELPOS_BELOW]='below';
		return $relpos;
	}
	
	function getScale($scaleID)
	{
		$query = 'SELECT * FROM jcq_scale WHERE jcq_scale.ID = '.$scaleID;
		$db = $this->getDBO();
		$db->setQuery($query);
		return $db->loadObject();
	}
	
	function getScales($questionID)
	{
		$query = 'SELECT * FROM jcq_scale, jcq_questionscales WHERE jcq_scale.ID = jcq_questionscales.scaleID AND jcq_questionscales.questionID = '.$questionID.' ORDER BY ord';
		$db = $this->getDBO();
		$db->setQuery($query);
		return $db->loadObjectList();
	}

	function checkScaleUsed($scaleID)
	{
		$this->db->setQuery("SELECT scaleID FROM jcq_questionscales WHERE scaleID = $scaleID");
		if ($this->db->loadObjectList()==null) return false;
		else return true;
	}
	
	function getPredefinedScales()
	{
		$query = 'SELECT * FROM jcq_scale WHERE jcq_scale.predefined = 1';
		$this->db->setQuery($query);
		return $this->db->loadObjectList();
	}
	
	function getNewPredefinedScale()
	{
		$scaleTableRow = $this->getTable('scales');
		$scaleTableRow->ID = 0;
		$scaleTableRow->name = '';
		$scaleTableRow->prepost = '%i';
		$scaleTableRow->predefined = 1;
		return $scaleTableRow;
	}
	
	function removeDuplicateScales()
	{
		$this->db->setQuery('SELECT ID FROM jcq_scale WHERE jcq_scale.predefined = 1');
		$predefscales = $this->db->loadColumn();
		if ($predefscales===null) return array();
		
		$duplscales = array();
		for ($i=0; $i<count($predefscales);$i++)
		{
			if (isset($duplscales[$predefscales[$i]])) continue; //scale is already identified as duplicate
			for ($j=$i+1; $j<count($predefscales);$j++)
			{
				if ($this->isDuplicateScale($predefscales[$i],$predefscales[$j])) $duplscales[$predefscales[$j]]=$predefscales[$i];
			}
		}
		$this->db->setQuery('SELECT ID FROM jcq_project');
		$projects = $this->db->loadColumn();
		foreach ($duplscales as $key=>$value)
		{
			$this->db->setQuery('UPDATE jcq_questionscales SET scaleID='.$value.' WHERE scaleID='.$key);
			$this->db->query();
			//this is not efficient, but so all columns in user data tables will be surely renamed
			foreach ($projects as $project)
			{
				$this->db->setQuery("SELECT column_name FROM information_schema.columns WHERE table_name = 'jcq_proj$project' AND column_name LIKE '%_s".$key."_';");
				$columnstorename = $this->db->loadColumn();
				foreach ($columnstorename as $columntorename)
				{
					$this->db->setQuery("ALTER TABLE jcq_proj$project CHANGE $columntorename ".str_replace("s".$key."_", "s".$value."_", $columntorename)." INT");
					$this->db->query();
				}
			}
			$this->db->setQuery('DELETE FROM jcq_scale WHERE ID='.$key);
			$this->db->query();
		}
		return $duplscales;
	}

	function isDuplicateScale($id1, $id2)
	{
		//check if both scales exist
		$scale1 = $this->getTable('scales');
		if (!$scale1->load($id1)) return false;
		$scale2 = $this->getTable('scales');
		if (!$scale2->load($id2)) return false;
		//check if scale definitions match
		foreach (get_object_vars($scale1) as $k => $v)
		{
			if ($k=='ID') continue;
			if ($scale2->$k!=$v) return false;
		}
		//check if number of codes match
		$codes1 = $this->getCodes($id1);
		$codes2 = $this->getCodes($id2);
		if (count($codes1)!=count($codes2)) return false;
		//check if individual codes match
		for ($i=0;$i<count($codes1);$i++) if (!$this->isDuplicateCode($codes1[$i]->ID, $codes2[$i]->ID)) return false;
		//if no difference so far, it is a duplicate
		return true;
	}
	
	function isDuplicateCode($id1,$id2)
	{
		//check if both codes exist
		$code1 = $this->getTable('codes');
		if (!$code1->load($id1)) return false;
		$code2 = $this->getTable('codes');
		if (!$code2->load($id2)) return false;
		//check if code definitions match
		foreach (get_object_vars($code1) as $k => $v)
		{
			if ($k=='ID' || $k=='ord' || $k=='scaleID') continue;
			if ($code2->$k!=$v) return false;
		}
		//if no difference so far, it is a duplicate
		return true;
	}
	
	function getCodes($scaleID)
	{
		$query = 'SELECT * FROM jcq_code WHERE scaleID = '.$scaleID.' ORDER BY ord';
		$db = $this->getDBO();
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	function getCodeCount($scaleID)
	{
		$query = 'SELECT * FROM jcq_code WHERE scaleID = '.$scaleID.' ORDER BY ord';
		$db = $this->getDBO();
		$db->setQuery($query);
		$codes = $db->loadResultArray();
		 
		if ($codes == null) return 0;
		else return count($codes);
	}
	
	function saveScale($scale)
	{
		
		//TODO: secure against insertion
		
		$scaleTableRow = $this->getTable();
			
		// Bind the form fields to the greetings table
		if (!$scaleTableRow->bind($scale)) JError::raiseError(500, 'Error binding data');
	
		// Make sure the greetings record is valid
		if (!$scaleTableRow->check()) JError::raiseError(500, 'Invalid data');
			
		if (!$scaleTableRow->store())
		{
			$errorMessage = $scaleTableRow->getError();
			JError::raiseError(500, 'Error inserting data: '.$errorMessage);
		}
	
		// if a new page is created, create 5 codes
		if ($scale['ID']==0)
		{
			for ($i=1;$i<=5;$i++)
			{
				$newcode = $this->getTable('codes');
				$newcode->scaleID = $scaleTableRow->ID;
				$newcode->ord = $i;
				$newcode->code = $i;
				if (!$newcode->store())
				{
					$errorMessage = $newcode->getError();
					JError::raiseError(500, 'Error inserting data: '.$errorMessage);
				}
			}
		}
		
		return $scaleTableRow->ID;
	}
	
	function saveCode(array $code)
	{
		if ($code['ID']<0) $code['ID']=0;
		$codeTableRow = $this->getTable('codes');
		if (!$codeTableRow->bind($code)) JError::raiseError(500, 'Error binding data');
		if (!$codeTableRow->check()) JError::raiseError(500, 'Invalid data');
		if (!$codeTableRow->store())
		{
			$errorMessage = $codeTableRow->getError();
			JError::raiseError(500, 'Error inserting data: '.$errorMessage);
		}
	}
	
	function deleteScale($ID)
	{
		//first delete all the codes from scale
		$codes = $this->getCodes($ID);
		if ($codes!==null) foreach ($codes as $code) $this->deleteCode($code->ID);
		//then delete the scale itself
		$this->db->setQuery("DELETE FROM jcq_scale WHERE ID = $ID");
		if (!$this->db->query()) JError::raiseError(500, 'FATAL: '.$this->db->getErrorMsg());
	}
	
	function deleteCode($ID)
	{
		//first delete binded items (if any)
		$model_items = new JcqModelItems();
		$bindeditems = $this->getCodebindedItems($ID);
		if ($bindeditems!==null) foreach ($bindeditems as $bindeditem) $model_items->deleteItem($bindeditem->ID);
		//then delete the code itself
		$this->db->setQuery("DELETE FROM jcq_code WHERE ID = $ID");
		if (!$this->db->query()) JError::raiseError(500, 'FATAL: '.$this->db->getErrorMsg());
	}
	
	function getCodebindedItems($codeID)
	{
		$this->db->setQuery("SELECT * FROM jcq_item WHERE bindingType='CODE' AND bindingID=".$codeID);
		$sqlresult = $this->db->loadObjectList();
		if ($sqlresult===false) JError::raiseError(500, 'Error fetching textfields: '.$this->getDBO()->getErrorMsg());
		else return $sqlresult;
	}
	
	function addrmTextfields($codeID,$questionID)
	{
		$model_items = new JcqModelItems();
		$bindeditems = $this->getCodebindedItems($codeID);
		//Delete if a textfield is already there
		if ($bindeditems!==null && count($bindeditems)>0)
		{
			foreach ($bindeditems as $bindeditem) $model_items->deleteItem($bindeditem->ID);
		}
		else //insert a textfield
		{
			$newitem = $model_items->buildNewItem($questionID, 3);
			$newitem->mandatory = 0;
			$newitem->bindingType = "CODE";
			$newitem->bindingID = $codeID;
			if (!$newitem->store()) JError::raiseError(500, 'FATAL: '.$newitem->getError());
		}
	}
}