<?php
class JCQScale
{
	private $ID;
	private $name;
	private $prepost;
	private $codes = array();
	
	/**
	 * Loads the scale object from a joomla database of appropriate structure
	 * 
	 * @param unknown_type $db database handler
	 * @param unknown_type $ID
	 * @param unknown_type $recursive (optional) should codes be loaded too?
	 * 
	 * @return false if an error occured
	 */
	public function loadFromDatabase (&$db, $ID, $recursive=true)
	{
		
	}
}
?>