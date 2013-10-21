<?php
defined('_JEXEC') or die( 'Restricted access' );

function jtableToXmlWithoutIDs ($jtable, $xmldoc, $xmlnode, $ownID=null, $parID=null)
{
	foreach (get_object_vars($jtable) as $k => $v) //ok, here a scripting language makes everything simpler
	{
		if (is_array($v) or is_object($v) or $v === NULL) continue;
		if ($k[0] == '_') continue;
		if ($ownID!==null && $k==$ownID) continue;
		if ($parID!==null && $k==$parID) continue;
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
		if ($k[0] == '_') continue;
		if (strpos($k,"ID")!==false) continue;
		$child = $xmlelement->getElementsByTagName($k);
		if ($child->length>0)
		{
			$child=$child->item(0)->firstChild;
			$jtable->$k = $child->textContent;
		}
	}
}

abstract class ImportExportNode
{
	public $name;
	public $dbtable;
	public $jtable;
	public $ownidfield;
	public $parentidfield;
	public $childnodes = array();

	function exportToXML($ID, $xmldoc, $parentnode=null)
	{
		$mynode = $xmldoc->createElement($this->name);
		$this->jtable->load($ID);
		jtableToXmlWithoutIDs($this->jtable, $xmldoc, $mynode, $this->ownidfield, $this->parentidfield);
		//recursively save childnodes
		foreach ($childnodes as $childnode)
		{
			$childrenIDs = $this->getChildrenIDs($ID, $childnode);
			if ($childrenIDs!==null) foreach ($childrenIDs as $childID) $childnode->exportToXML($childID, $xmldoc, $mynode);
		}
		if ($parentnode!==null) $parentnode->appendChild($mynode);
		else $xmldoc->appendChild($mynode);
	}

	abstract function getChildrenIDs($ID, $childnode);
}

class JcqModelImportexport extends JModel
{
	function exportProject($ID)
	{
		#FIXME create file in unsave path for now
		$filename = "project$ID"."_".time().".xml";
		$filehandle = fopen(JPATH_COMPONENT.DS."userdata".DS.$filename,"w") or JError::raiseError(500, 'Error creating file');

		$xmldoc = new DOMDocument('1.0', 'utf-8');

		fwrite($filehandle, $xmldoc->saveXML());
		fclose($filehandle);

		return $filename;
	}
}
