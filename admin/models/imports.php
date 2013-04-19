<?php
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');

class JcqModelImports extends JModel {
	
	private $db;
	
	function __construct()
	{
		parent::__construct();
		$this->db = $this->getDBO();
	}
	
	function getImport($importID)
	{
		$this->db->setQuery("SELECT * FROM jcq_import WHERE ID=$importID");
		$import = $this->db->loadObject();
		
		if ($import === null) JError::raiseError(500, "Program file with ID: $importID not found.");
		else return $import;
	}
	
	function saveEditedImport($importID,$content)
	{
		$import = $this->getImport($importID);
		file_put_contents(JPATH_COMPONENT_SITE.DS.'usercode'.DS.$import->filename,$content);
	}
}