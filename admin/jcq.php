<?php
defined('_JEXEC') or die('Restricted Access');

// Set component style sheet
$document = JFactory::getDocument();
$document->addStyleSheet(JURI::root().'administrator/components/com_jcq/jcq.css');

// Require the controller
require_once( JPATH_COMPONENT.DS.'jcqcontroller.php' );

// Create the controller
$controller   = new JcqController();

// Perform the Request task
$taskresult = $controller->execute(JRequest::getVar('task'));
if ($taskresult===false) JError::raiseError(500, 'No code for this task!');

// Redirect if set by the controller
$controller->redirect();
