<?php
	defined('_JEXEC') or die('Restricted Access');
	
	//set component style sheet
	$document = JFactory::getDocument();
	$document->addStyleSheet(JPATH_COMPONENT_ADMINISTRATOR.DS.'jcq.css');
	
	//if not specified in GET, assume the standard administrator view
	$view = JRequest::getVar('view','vwadminProjects','get');
	$id = JRequest::getVar('id','','get');
	try
	{
		require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'view'.DS.$view.'.php');
		$viewobject = new $view($id);
	}
	catch (Exception $e)
	{
		die('Fatal Internal Error');
	}	
		
	$viewobject->doTask();
	$viewobject->show();
	