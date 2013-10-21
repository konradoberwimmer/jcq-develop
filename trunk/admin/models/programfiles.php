<?php
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');

class JcqModelProgramfiles extends JModel {
	
	private $db;
	
	function __construct()
	{
		parent::__construct();
		$this->db = $this->getDBO();
	}
	
	function getProgramfile($programfileID)
	{
		$this->db->setQuery("SELECT * FROM jcq_programfile WHERE ID=$programfileID");
		$programfile = $this->db->loadObject();
		
		if ($programfile === null) JError::raiseError(500, "Program file with ID: $programfileID not found.");
		else return $programfile;
	}
	
	function saveEditedProgramfile($programfileID,$content)
	{
		$programfile = $this->getProgramfile($programfileID);
		file_put_contents(JPATH_COMPONENT_SITE.DS.'usercode'.DS.$programfile->filename,$content);
	}
}