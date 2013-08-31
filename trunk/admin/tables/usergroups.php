<?php
defined('_JEXEC') or die('Restricted Access');

class TableUsergroups extends JTable {
	public $ID = null;
	public $name = null;
	public $value = null;
	
	function TableUsergroups(&$db, $projectID)
	{
		parent::__construct('jcq_projusergroup'.$projectID, 'ID', $db);
	}
}