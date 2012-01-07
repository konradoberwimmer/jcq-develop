<?php
class JCQPage
{
	private $ID;
	private $name;
	private $projectID;
	private $ord;
	private $questions = array();
	
	function __construct($ID) 
	{
       $this->ID = $ID;
   	}
	
	/**
	 * Loads the page object from a joomla database of appropriate structure
	 * 
	 * @param unknown_type $db database handler
	 * @param unknown_type $ID
	 * @param unknown_type $recursive (optional) should questions be loaded too?
	 * 
	 * @return false if an error occured
	 */
	public function loadFromDatabase (&$db, $ID, $recursive=true)
	{
		
	}
	
	public function getID() { return $this->ID; }
}
?>