<?php
defined('_JEXEC') or die('Restricted Access');

// Set component style sheet
$document = JFactory::getDocument();
$document->addStyleSheet(JURI::root().'administrator/components/com_jcq/jcq.css');

// Require the controller
require_once( JPATH_COMPONENT.DS.'jcqadminController.php' );

// Create the controller
$controller   = new JCQAdminController();

// Perform the Request task
$controller->execute(JRequest::getVar('task'));

// Redirect if set by the controller
$controller->redirect();

/*
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

$viewobject->showBreadcrumbs();
$viewobject->doTask();
$viewobject->show();
*/
