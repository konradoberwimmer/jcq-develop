<?php
defined('_JEXEC') or die('Restricted Access');

class TableImports extends JTable {
	public $ID = null;
	public $ord = 0;
	public $filename = null;
	public $projectID = null;
	
	function TableImports(&$db)
	{
		parent::__construct('jcq_import', 'ID', $db);
	}
}
