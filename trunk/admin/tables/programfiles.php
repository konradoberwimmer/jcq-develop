<?php
defined('_JEXEC') or die('Restricted Access');

class TableProgramfiles extends JTable {
	public $ID = null;
	public $ord = 0;
	public $filename = null;
	public $projectID = null;
	
	function TableProgramfiles(&$db)
	{
		parent::__construct('jcq_programfile', 'ID', $db);
	}
}
