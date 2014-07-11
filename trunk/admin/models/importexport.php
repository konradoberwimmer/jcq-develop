<?php
defined('_JEXEC') or die( 'Restricted access' );
require_once(JPATH_COMPONENT.DS.'tables'.DS.'programfiles.php');
require_once(JPATH_COMPONENT.DS.'tables'.DS.'tokens.php');
require_once(JPATH_COMPONENT.DS.'tables'.DS.'usergroups.php');
require_once(JPATH_COMPONENT.DS.'tables'.DS.'codes.php');
require_once(JPATH_COMPONENT.DS.'tables'.DS.'scales.php');
require_once(JPATH_COMPONENT.DS.'tables'.DS.'items.php');
require_once(JPATH_COMPONENT.DS.'tables'.DS.'questions.php');
require_once(JPATH_COMPONENT.DS.'tables'.DS.'pages.php');
require_once(JPATH_COMPONENT.DS.'tables'.DS.'projects.php');
require_once(JPATH_COMPONENT.DS.'models'.DS.'projects.php');
require_once(JPATH_COMPONENT.DS.'models'.DS.'pages.php');
require_once(JPATH_COMPONENT.DS.'models'.DS.'questions.php');

function jtableToXml ($jtable, $xmldoc, $xmlnode)
{
	foreach (get_object_vars($jtable) as $k => $v) //ok, here a scripting language makes everything simpler
	{
		if (is_array($v) or is_object($v) or $v === NULL) continue;
		if ($k[0] == '_') continue; //exclude those fields
		$element = $xmldoc->createElement($k);
		$cdata = $xmldoc->createCDATASection($v);
		$element->appendChild($cdata);
		$xmlnode->appendChild($element);
	}
}

function xmlToJTable ($xmlelement, $jtable)
{
	foreach (get_object_vars($jtable) as $k => $v)
	{
		if ($k[0] == '_') continue; //exclude those fields
		$child = $xmlelement->getElementsByTagName($k);
		if ($child->length>0)
		{
			$child=$child->item(0)->firstChild;
			$jtable->$k = $child->textContent;
		}
	}
}

abstract class JCQImportExportNode
{
	public $id;
	public $name;
	public $dbtable;
	public $jtable;
	public $parentidfield = null;
	public $childnodes = array();
	public $node;

	function exportToXML($ID, $xmldoc, $parent=null)
	{
		$this->node = $xmldoc->createElement($this->name);
		$this->jtable->load($ID);
		$this->id = $ID;
		jtableToXml($this->jtable, $xmldoc, $this->node);
		//recursively save childnodes
		foreach ($this->childnodes as $childnode)
		{
			$childrenIDs = $this->getChildrenIDs($ID, $childnode);
			if ($childrenIDs!==null) foreach ($childrenIDs as $childID) $childnode->exportToXML($childID->ID, $xmldoc, $this);
		}
		if ($parent!==null) $parent->node->appendChild($this->node);
		else $xmldoc->appendChild($this->node);
	}

	function getChildrenIDs($myID, $childnode)
	{
		$db = JFactory::getDbo();
		$db->setQuery("SELECT ID FROM ".$childnode->dbtable." WHERE ".$childnode->parentidfield." = $myID");
		return $db->loadObjectList();
	}

	function importFromXML($xmlnode, $parentID=null)
	{
		$this->jtable->reset();
		xmlToJTable($xmlnode, $this->jtable);
		$this->jtable->ID = 0;
		$parfieldname = $this->parentidfield;
		if ($this->parentidfield!==null)  $this->jtable->$parfieldname = $parentID;
		$this->jtable->store();
		$myID=$this->jtable->ID;
		foreach ($this->childnodes as $childnode)
		{
			$children = $xmlnode->childNodes;
			if ($children!==null) foreach ($children as $child)	if ($child->nodeName==$childnode->name) $childnode->importFromXML($child, $myID);
		}
	}
}

class JCQIENodeProgramfile extends JCQImportExportNode
{
	function __construct()
	{
		$this->name = "programfile";
		$this->dbtable = "jcq_programfile";
		$this->jtable = new TableProgramfiles(JFactory::getDbo());
		$this->parentidfield = "projectID";
	}
}

class JCQIENodeToken extends JCQImportExportNode
{
	function __construct()
	{
		$this->name = "token";
		$this->dbtable = "jcq_token";
		$this->jtable = new TableTokens(JFactory::getDbo());
		$this->parentidfield = "usergroupID";
	}
}

class JCQIENodeUsergroup extends JCQImportExportNode
{
	function __construct()
	{
		$this->name = "usergroup";
		$this->dbtable = "jcq_usergroup";
		$this->jtable = new TableUsergroups(JFactory::getDbo());
		$this->parentidfield = "projectID";
		array_push($this->childnodes, new JCQIENodeToken());
	}
}

class JCQIENodeCode extends JCQImportExportNode
{
	static public $idtranslate = array();

	function __construct()
	{
		$this->name = "code";
		$this->dbtable = "jcq_code";
		$this->jtable = new TableCodes(JFactory::getDbo());
		$this->parentidfield = "scaleID";
	}

	/**
	 * override to save ID translation
	 * @see JCQImportExportNode::importFromXML()
	 */
	function importFromXML($xmlnode, $parentID=null)
	{
		$this->jtable->reset();
		xmlToJTable($xmlnode, $this->jtable);
		$oldID = $this->jtable->ID;
		$this->jtable->ID = 0;
		$parfieldname = $this->parentidfield;
		if ($this->parentidfield!==null)  $this->jtable->$parfieldname = $parentID;
		$this->jtable->store();
		$myID=$this->jtable->ID;
		JCQIENodeCode::$idtranslate[$oldID]=$myID;
		foreach ($this->childnodes as $childnode)
		{
			$children = $xmlnode->childNodes;
			if ($children!==null) foreach ($children as $child)	if ($child->nodeName==$childnode->name) $childnode->importFromXML($child, $myID);
		}
	}
}

class JCQIENodeScale extends JCQImportExportNode
{
	private $relationfields = array('ord','mandatory','layout','relpos');
	
	function __construct()
	{
		$this->name = "scale";
		$this->dbtable = "jcq_scale";
		$this->jtable = new TableScales(JFactory::getDbo());
		array_push($this->childnodes, new JCQIENodeCode());
	}

	/**
	 * override because of the n:m relationship between scales and questions
	 */
	function exportToXML($ID, $xmldoc, $parent=null)
	{
		parent::exportToXML($ID, $xmldoc, $parent);
		$db = JFactory::getDbo();
		$db->setQuery("SELECT * FROM jcq_questionscales WHERE scaleID=$ID AND questionID=".$parent->id);
		$questionscale = $db->loadAssoc();
		foreach ($this->relationfields as $relfield) if (isset($questionscale[$relfield]))
		{
			$this->node->setAttribute($relfield,$questionscale[$relfield]);
		}
	}
	
	/**
	 * override because of the n:m relationship between scales and questions
	 * @see JCQImportExportNode::importFromXML()
	 */
	function importFromXML($xmlnode, $parentID=null)
	{
		xmlToJTable($xmlnode, $this->jtable);
		$this->jtable->ID = 0;
		$this->jtable->store();
		$myID=$this->jtable->ID;
		$db = JFactory::getDbo();
		$db->setQuery("INSERT INTO jcq_questionscales (questionID, scaleID) VALUES($parentID,$myID)");
		if (!$db->query()) JError::raiseError(500, "FATAL: ".$db->getErrorMsg());
		foreach ($this->relationfields as $relfield) if ($xmlnode->getAttribute($relfield)!="")
		{
			$db->setQuery("UPDATE jcq_questionscales SET $relfield=".$xmlnode->getAttribute($relfield)." WHERE questionID=$parentID AND scaleID=$myID");
			if (!$db->query()) JError::raiseError(500, "FATAL: ".$db->getErrorMsg());
		}
		foreach ($this->childnodes as $childnode)
		{
			$children = $xmlnode->childNodes;
			if ($children!==null) foreach ($children as $child)	if ($child->nodeName==$childnode->name) $childnode->importFromXML($child, $myID);
		}
	}
}

class JCQIENodeItem extends JCQImportExportNode
{
	static public $idtranslate = array();

	function __construct()
	{
		$this->name = "item";
		$this->dbtable = "jcq_item";
		$this->jtable = new TableItems(JFactory::getDbo());
		$this->parentidfield = "questionID";
	}

	/**
	 * override, because ID translation needs to be saved and bindingIDs set correct
	 * adds the functionality to add the answer columns to the userdata table
	 * @see JCQImportExportNode::importFromXML()
	 */
	function importFromXML($xmlnode, $parentID=null)
	{
		$this->jtable->reset();
		xmlToJTable($xmlnode, $this->jtable);
		$oldID = $this->jtable->ID;
		$this->jtable->ID = 0;
		$parfieldname = $this->parentidfield;
		if ($this->parentidfield!==null)  $this->jtable->$parfieldname = $parentID;
		//special code for items: set the bindingID right (requires that codes and other items be loaded first)
		if ($this->jtable->bindingType=="CODE") $this->jtable->bindingID = JCQIENodeCode::$idtranslate[$this->jtable->bindingID];
		if ($this->jtable->bindingType=="ITEM") $this->jtable->bindingID = JCQIENodeItem::$idtranslate[$this->jtable->bindingID];
		$this->jtable->store();
		$myID=$this->jtable->ID;
		JCQIENodeItem::$idtranslate[$oldID]=$myID;

		$model_questions = new JcqModelQuestions();
		$myquestion = $model_questions->getQuestion($parentID);
		$mypage = $model_questions->getPageFromQuestion($parentID);
		$myproject = $model_questions->getProjectFromPage($mypage->ID);
		$db=JFactory::getDbo();
		if ($myquestion->questtype!=MULTISCALE)
		{
			$fieldtype = "INT";
			if ($this->jtable->datatype!=1 || $myquestion->questtype==TEXTFIELD || $this->jtable->bindingType!="QUESTION") $fieldtype = "TEXT";
			$db->setQuery("ALTER TABLE jcq_proj".$myproject->ID." ADD COLUMN i".$this->jtable->ID."_ ".$fieldtype);
			if (!$db->query()) JError::raiseError(500, "FATAL: ".$db->getErrorMsg());
		} else
		{
			$scales = $model_questions->getScales($myquestion->ID);
			if ($scales!==null) foreach ($scales as $scale)
			{
				$db->setQuery("ALTER TABLE jcq_proj".$myproject->ID." ADD COLUMN i".$this->jtable->ID."_s".$scale->ID."_ INT");
				if (!$db->query()) JError::raiseError(500, "FATAL: ".$db->getErrorMsg());
			}
		}
	}
}

class JCQIENodeQuestion extends JCQImportExportNode
{
	function __construct()
	{
		$this->name = "question";
		$this->dbtable = "jcq_question";
		$this->jtable = new TableQuestions(JFactory::getDbo());
		$this->parentidfield = "pageID";
		//the order of the childnodes is important here, because items may be bound to codes!!!
		array_push($this->childnodes, new JCQIENodeScale());
		array_push($this->childnodes, new JCQIENodeItem());
	}

	/**
	 * overridden, because scales and items for the question have to be retrieved in special ways
	 * @see JCQImportExportNode::getChildrenIDs()
	 */
	function getChildrenIDs($myID, $childnode)
	{
		if ($childnode->name=="item")
		{
			$db = JFactory::getDbo();
			//the order of items is important here because items with a bindingID may be bound to another item
			$db->setQuery("SELECT ID FROM jcq_item WHERE questionID = $myID ORDER BY bindingID");
			return $db->loadObjectList();
		}
		else
		{
			$db = JFactory::getDbo();
			$db->setQuery("SELECT scaleID AS ID FROM jcq_questionscales WHERE questionID = $myID");
			return $db->loadObjectList();
		}
	}
}

class JCQIENodePage extends JCQImportExportNode
{
	function __construct()
	{
		$this->name = "page";
		$this->dbtable = "jcq_page";
		$this->jtable = new TablePages(JFactory::getDbo());
		$this->parentidfield = "projectID";
		array_push($this->childnodes, new JCQIENodeQuestion());
	}

	/**
	 * Adds the functionality to create the timestamp for the page in the user data table.
	 * @see JCQImportExportNode::importFromXML()
	 */
	function importFromXML($xmlnode, $parentID=null)
	{
		$this->jtable->reset();
		xmlToJTable($xmlnode, $this->jtable);
		$this->jtable->ID = 0;
		$parfieldname = $this->parentidfield;
		if ($this->parentidfield!==null)  $this->jtable->$parfieldname = $parentID;
		$this->jtable->store();
		$myID=$this->jtable->ID;

		if (!$this->jtable->isFinal) //add the timestamp for the page
		{
			$db = JFactory::getDbo();
			$db->setQuery("ALTER TABLE jcq_proj".$parentID." ADD COLUMN p".$this->jtable->ID."_timestamp BIGINT");
			if (!$db->query()) JError::raiseError(500, "FATAL: ".$db->getErrorMsg());
		}

		foreach ($this->childnodes as $childnode)
		{
			$children = $xmlnode->childNodes;
			if ($children!==null) foreach ($children as $child)	if ($child->nodeName==$childnode->name) $childnode->importFromXML($child, $myID);
		}
	}
}

class JCQIENodeProject extends JCQImportExportNode
{
	function __construct()
	{
		$this->name = "project";
		$this->dbtable = "jcq_project";
		$this->jtable = new TableProjects(JFactory::getDbo());
		array_push($this->childnodes, new JCQIENodePage());
		array_push($this->childnodes, new JCQIENodeUsergroup());
		array_push($this->childnodes, new JCQIENodeProgramfile());
	}

	/**
	 * Adds the functionality to create the user data table.
	 * @see JCQImportExportNode::importFromXML()
	 */
	function importFromXML($xmlnode, $parentID=null)
	{
		$this->jtable->reset();
		xmlToJTable($xmlnode, $this->jtable);
		$this->jtable->ID = 0;
		$parfieldname = $this->parentidfield;
		if ($this->parentidfield!==null)  $this->jtable->$parfieldname = $parentID;
		$this->jtable->store();
		$myID=$this->jtable->ID;

		$db = JFactory::getDbo();
		$db->setQuery("CREATE TABLE jcq_proj".$this->jtable->ID." (preview BOOLEAN DEFAULT 0, groupID BIGINT, tokenID BIGINT, joomlaUser VARCHAR(50), sessionID VARCHAR(50) NOT NULL, curpage BIGINT NOT NULL, finished BOOLEAN DEFAULT 0 NOT NULL, timestampBegin BIGINT, timestampEnd BIGINT, PRIMARY KEY (sessionID))");
		if (!$db->query()) JError::raiseError(500, "FATAL: ".$db->getErrorMsg());

		foreach ($this->childnodes as $childnode)
		{
			$children = $xmlnode->childNodes;
			if ($children!==null) foreach ($children as $child)	if ($child->nodeName==$childnode->name) $childnode->importFromXML($child, $myID);
		}
		
		return $myID;
	}
}

class JcqModelImportexport extends JModel
{
	function exportProject($ID)
	{
		#FIXME create file in unsave path for now
		$filename = "project$ID"."_".time().".xml";
		$filehandle = fopen(JPATH_COMPONENT.DS."userdata".DS.$filename,"w") or JError::raiseError(500, 'Error creating file');
	
		$xmldoc = new DOMDocument('1.0', 'utf-8');
		$projectnode = new JCQIENodeProject();
		$projectnode->exportToXML($ID, $xmldoc);
	
		fwrite($filehandle, $xmldoc->saveXML());
		fclose($filehandle);
	
		return $filename;
	}

	function importProject($xmldoc)
	{
		$xmlnode = $xmldoc->getElementsByTagName('project');
		if ($xmlnode->length==0) return false;
		$projectnode = new JCQIENodeProject();
		return ($projectnode->importFromXML($xmlnode->item(0)));
		#TODO error handling
	}

	function copyQuestion($questionID,$targetPageID)
	{
		$xmldoc = new DOMDocument('1.0', 'utf-8');
		$questionnode = new JCQIENodeQuestion();
		$questionnode->exportToXML($questionID, $xmldoc);

		$questionnodenew = new JCQIENodeQuestion();
		$questionnodenew->importFromXML($xmldoc->getElementsByTagName('question')->item(0),$targetPageID);
	}
}
