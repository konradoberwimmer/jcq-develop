<?php
class JCQItem
{
	private $ID;
	private $questionID;
	private $ord;
	private $varname;
	private $mandatory;
	private $textleft;
	private $textright;
	private $scales = array();
	
	/**
	 * Loads the item object from a joomla database of appropriate structure
	 * 
	 * @param unknown_type $db database handler
	 * @param unknown_type $ID
	 * @param unknown_type $recursive (optional) should scales be loaded too?
	 * 
	 * @return false if an error occured
	 */
	public function loadFromDatabase (&$db, $ID, $recursive=true)
	{
		
	}
}
?>