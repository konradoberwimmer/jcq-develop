<?php
defined('_JEXEC') or die('Restricted Access');

class TableQuestions extends JTable {
	public $ID = null;
	public $name = null;
	public $ord = null;
	public $questtype = null;
	public $mandatory = true;
	public $text = null;
	public $advise = null;
	public $width_question = null;
	public $width_items = null;
	public $width_scale = null;
	public $alternate_bg = false;
	public $pageID = null;
	public $filter = null;
	
	function TableQuestions(&$db)
	{
		parent::__construct('jcq_question', 'ID', $db);
	}
}
