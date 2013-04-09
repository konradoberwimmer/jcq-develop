<?php
defined('_JEXEC') or die('Restricted Access');

define("SINGLECHOICE",1);
define("MULTICHOICE",2);
define("TEXTFIELD",3);
define("MATRIX_LEFT",4);
define("MATRIX_BOTH",5);
define("MULTISCALE",6);
define("TEXTANDHTML",7);

// Set component style sheet
$document = JFactory::getDocument();
$document->addStyleSheet(JURI::root().'components/com_jcq/jcq.css');

// Require the controller
require_once( JPATH_COMPONENT.DS.'jcqcontroller.php' );

// Create the controller
$controller   = new JcqController();

// Perform the Request task
$controller->execute(JRequest::getVar('task'));

// Redirect if set by the controller
$controller->redirect();
