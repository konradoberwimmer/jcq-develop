<?php
defined('_JEXEC') or die('Restricted Access');

class TableUsergroups extends JTable {
	public $ID = null;
	public $name = null;
	public $val = null;
	public $projectID = null;
	
	function TableUsergroups(&$db)
	{
		parent::__construct('jcq_usergroup', 'ID', $db);
	}
}