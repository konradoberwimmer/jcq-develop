<?php
defined('_JEXEC') or die('Restricted Access');

// Set component style sheet
$document = JFactory::getDocument();
$document->addStyleSheet(JURI::root().'administrator/components/com_jcq/jcq.css');

// Require the controller
require_once( JPATH_COMPONENT.DS.'jcqController.php' );

// Create the controller
$controller   = new JcqController();

// Perform the Request task
$controller->execute(JRequest::getVar('task'));

// Redirect if set by the controller
$controller->redirect();
