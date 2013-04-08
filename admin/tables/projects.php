<?php
defined('_JEXEC') or die('Restricted Access');

class TableProjects extends JTable {
	public $ID = null;
	public $name = null;
	public $cssfile = null;
	public $description = null;
	public $anonymous = false;
	public $multiple = false;
	
	function TableProjects(&$db)
	{
		parent::__construct('jcq_project', 'ID', $db);
	}
}
