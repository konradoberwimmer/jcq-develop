<?php
class JCQQuestion
{
	//general
	private $ID;
	private $name;
	private $pageID;
	private $ord;
	private $type;
	private $datatype;
	private $varname;
	private $mandatory;
	private $text;
	private $advise;
	private $prepost;
	//view suggestions
	private $width_scale;
	private $alternate_bg;
	//items and scales
	private $items = array();
	private $scales = array();
	
	/**
	 * Loads the question object from a joomla database of appropriate structure
	 * 
	 * @param unknown_type $db database handler
	 * @param unknown_type $ID
	 * @param unknown_type $recursive (optional) should items and scales be loaded too?
	 * 
	 * @return false if an error occured
	 */
	public function loadFromDatabase (&$db, $ID, $recursive=true)
	{
		
	}
}
?>