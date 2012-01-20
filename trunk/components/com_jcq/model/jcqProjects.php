<?php
/**
 * Class serves as an entry point to the model. One static function simply returns a list of project IDs.
 * 
 * @author Konrad Daemon
 */
class JCQProjects
{
	function __construct()
	{
		die('Fatal Internal Error');
	}
	
	public static function getProjectIDs(&$db)
	{
		$db->SetQuery("SELECT ID FROM jcq_Project ORDER BY name;");
		return $db->loadAssocList();
	}
}
