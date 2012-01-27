<?php
require_once(JPATH_COMPONENT_SITE.DS.'model'.DS.'jcqPage.php');
class JCQProject
{
	private $id;
	private $name;
	private $classfile;
	private $classname;
	private $description;
	private $anonymousanswers;
	private $multipleanswers;
	private $pages = array();
	
	/**
	 * Loads the project object from a joomla database of appropriate structure
	 * 
	 * @param unknown_type $db database handler
	 * @param unknown_type $ID
	 * @param unknown_type $recursive (optional) should pages be loaded too?
	 * 
	 * @return false if an error occured
	 */
	public function loadFromDatabase (&$db, $ID, $recursive=true)
	{
		$db->SetQuery("SELECT * FROM jcq_project WHERE ID=$ID;");
		$project=$db->loadAssoc();
		if (isset($project))
		{
			$this->id = $ID;
			if (isset($project['name'])) $this->name = $project['name'];
			else return false;
			if (isset($project['classfile'])) $this->classfile = $project['classfile'];
			else return false;
			if (isset($project['classname'])) $this->classname = $project['classname'];
			else return false;
			if (isset($project['description'])) $this->description = $project['description'];
			if (isset($project['anonymousanswers'])) $this->anonymousanswers = $project['anonymousanswers'];
			else return false;
			if (isset($project['multipleanswers'])) $this->multipleanswers = $project['multipleanswers'];
			else return false;
			$db->SetQuery("SELECT * FROM jcq_Page WHERE ProjectID=$ID ORDER BY ord;");
			$pages=$db->loadAssocList();
			if (isset($pages))
			{
				for ($i = 0; $i < count($pages); $i++) $this->pages = new JCQPage($pages[$i]['ID']);
				if ($recursive)
				{
					for ($i = 0; $i < count($this->pages); $i++)
					{
						if (!$this->pages[$i]->loadFromDatabase($db,$this->pages[$i]->getID())) return false;
					}
				}
			}
			return true;
		} else return false;
	}
	
	public function getName() { return $this->name; }
	public function getClassfileStr() { return $this->classfile; }
}
?>