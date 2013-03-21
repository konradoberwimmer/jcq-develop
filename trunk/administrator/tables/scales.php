<?php
defined('_JEXEC') or die('Restricted Access');

class TableScales extends JTable {
	public $ID = null;
	public $name = null;
	public $prepost = null;
	
	function TableScales(&$db)
	{
		parent::__construct('jcq_scale', 'ID', $db);
	}
}
