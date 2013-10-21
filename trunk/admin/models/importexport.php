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
	public $name;
	public $dbtable;
	public $jtable;
	public $parentidfield = null;
	public $childnodes = array();

	function exportToXML($ID, $xmldoc, $parentnode=null)
	{
		$mynode = $xmldoc->createElement($this->name);
		$this->jtable->load($ID);
		jtableToXml($this->jtable, $xmldoc, $mynode);
		//recursively save childnodes
		foreach ($this->childnodes as $childnode)
		{
			$childrenIDs = $this->getChildrenIDs($ID, $childnode);
			if ($childrenIDs!==null) foreach ($childrenIDs as $childID) $childnode->exportToXML($childID->ID, $xmldoc, $mynode);
		}
		if ($parentnode!==null) $parentnode->appendChild($mynode);
		else $xmldoc->appendChild($mynode);
	}

	function getChildrenIDs($myID, $childnode)
	{
		$db = JFactory::getDbo();
		$db->setQuery("SELECT ID FROM ".$childnode->dbtable." WHERE ".$childnode->parentidfield." = $myID");
		return $db->loadObjectList();
	}
	
	function importFromXML($xmlnode, $parentID=null)
	{
		xmlToJTable($xmlnode, $this->jtable);
		$this->jtable->ID = 0;
		$parfieldname = $this->parentidfield;
		if ($this->parentidfield!==null)  $this->jtable->$parfieldname = $parentID;
		$this->jtable->store();
		$myID=$this->jtable->ID;
		foreach ($this->childnodes as $childnode)
		{
			$children = $xmlnode->getElementsByTagName($childnode->name);
			if ($children!==null) foreach ($children as $child) $childnode->importFromXML($child, $myID);
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
	function __construct()
	{
		$this->name = "code";
		$this->dbtable = "jcq_code";
		$this->jtable = new TableCodes(JFactory::getDbo());
		$this->parentidfield = "scaleID";
	}
}

class JCQIENodeScale extends JCQImportExportNode
{
	function __construct()
	{
		$this->name = "scale";
		$this->dbtable = "jcq_scale";
		$this->jtable = new TableScales(JFactory::getDbo());
		array_push($this->childnodes, new JCQIENodeCode());
	}
	
	function importFromXML($xmlnode, $parentID=null)
	{
		xmlToJTable($xmlnode, $this->jtable);
		$this->jtable->ID = 0;
		$this->jtable->store();
		$myID=$this->jtable->ID;
		$db = JFactory::getDbo();
		$db->setQuery("INSERT INTO jcq_questionscales (questionID, scaleID) VALUES($parentID,$myID)");
		if (!$db->query()) JError::raiseError(500, "FATAL: ".$db->getErrorMsg());
		foreach ($this->childnodes as $childnode)
		{
			$children = $xmlnode->getElementsByTagName($childnode->name);
			if ($children!==null) foreach ($children as $child) $childnode->importFromXML($child, $myID);
		}
	}
}

class JCQIENodeItem extends JCQImportExportNode
{
	function __construct()
	{
		$this->name = "item";
		$this->dbtable = "jcq_item";
		$this->jtable = new TableItems(JFactory::getDbo());
		$this->parentidfield = "questionID";
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
		array_push($this->childnodes, new JCQIENodeItem());
		array_push($this->childnodes, new JCQIENodeScale());
	}
	
	function getChildrenIDs($myID, $childnode)
	{
		if ($childnode->name=="item") return parent::getChildrenIDs($myID, $childnode);
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
}

class JCQIENodeProject extends JCQImportExportNode
{
	function __construct()
	{
		$this->name = "project";
		$this->dbtable = "jcq_project";
		$this->jtable = new TableProjects(JFactory::getDbo());
		//array_push($this->childnodes, new JCQIENodePage());
		array_push($this->childnodes, new JCQIENodeUsergroup());
		array_push($this->childnodes, new JCQIENodeProgramfile());
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
		if ($xmlnode===null || count($xmlnode)==0) return false;
		$projectnode = new JCQIENodeProject();
		$projectnode->importFromXML($xmlnode->item(0));
		return true;
	}
}
