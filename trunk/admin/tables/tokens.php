<?php
defined('_JEXEC') or die('Restricted Access');

class TableTokens extends JTable {
	public $ID = null;
	public $token = null;
	public $email = null;
	public $name = null;
	public $firstname = null;
	public $salutation = null;
	public $note = null;
	public $usergroupID = null;
	
	function TableTokens($db)
	{
		parent::__construct('jcq_token', 'ID', $db);
	}
}
