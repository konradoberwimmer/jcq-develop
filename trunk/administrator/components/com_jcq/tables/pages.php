<?php
defined('_JEXEC') or die('Restricted Access');

class TablePages extends JTable {
	public $ID = null;
	public $name = null;
	public $ord = null;
	public $projectID = null;
	
	function TablePages(&$db)
	{
		parent::__construct('jcq_page', 'ID', $db);
	}
}
