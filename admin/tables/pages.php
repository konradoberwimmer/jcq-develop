<?php
defined('_JEXEC') or die('Restricted Access');

class TablePages extends JTable {
	public $ID = null;
	public $name = null;
	public $ord = null;
	public $projectID = null;
	public $filter = null;
	public $isFinal = 0;
	
	function TablePages($db)
	{
		parent::__construct('jcq_page', 'ID', $db);
	}
}
