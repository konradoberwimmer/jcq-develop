<?php
defined('_JEXEC') or die('Restricted Access');

class TableItems extends JTable {
	public $ID = null;
	public $ord = null;
	public $datatype = 1;
	public $varname = NULL;
	public $mandatory = 1;
	public $textleft = null;
	public $width_left = 0;
	public $textright = null;
	public $width_right = 0;
	public $rows = 1;
	public $linebreak = null;
	public $prepost = null;
	public $questionID = null;
	public $filter = null;
	public $bindingType = "QUESTION";
	public $bindingID = null;
	
	function TableItems(&$db)
	{
		parent::__construct('jcq_item', 'ID', $db);
	}
}
