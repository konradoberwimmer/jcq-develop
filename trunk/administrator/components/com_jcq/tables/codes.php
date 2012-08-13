<?php
defined('_JEXEC') or die('Restricted Access');

class TableCodes extends JTable {
	public $ID = null;
	public $ord = null;
	public $code = null;
	public $label = null;
	public $missval = false;
	public $scaleID = null;
	
	function TableCodes(&$db)
	{
		parent::__construct('jcq_code', 'ID', $db);
	}
}