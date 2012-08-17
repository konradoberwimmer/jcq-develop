<?php
defined('_JEXEC') or die('Restricted Access');

// Require the controller
require_once( JPATH_COMPONENT.DS.'jcqcontroller.php' );

// Create the controller
$controller   = new JcqController();

// Perform the Request task
$controller->execute(JRequest::getVar('task'));

// Redirect if set by the controller
$controller->redirect();
