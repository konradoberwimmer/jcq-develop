<?php
defined('_JEXEC') or die('Restricted Access');

class TableItems extends JTable {
	public $ID = null;
	public $ord = null;
	public $varname = NULL;
	public $mandatory = 1;
	public $textleft = null;
	public $textright = null;
	public $questionID = null;
	
	function TableItems(&$db)
	{
		parent::__construct('jcq_item', 'ID', $db);
	}
}
