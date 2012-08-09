<?php
defined('_JEXEC') or die('Restricted Access');

class TableQuestions extends JTable {
	public $ID = null;
	public $name = null;
	public $ord = null;
	public $questtype = null;
	public $datatype = 1;
	public $varname = null;
	public $mandatory = true;
	public $text = null;
	public $advise = null;
	public $prepost = null;
	public $width_scale = null;
	public $alternate_bg = false;
	public $pageID = null;

	function TableQuestions(&$db)
	{
		parent::__construct('jcq_question', 'ID', $db);
	}
}
