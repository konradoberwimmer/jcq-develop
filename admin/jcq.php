<?php
defined('_JEXEC') or die('Restricted Access');

define("SINGLECHOICE",1);
define("MULTICHOICE",2);
define("TEXTFIELD",3);
define("MATRIX_LEFT",4);
define("MATRIX_BOTH",5);
define("MULTISCALE",6);
define("TEXTANDHTML",7);

define("LAYOUT_RADIOHORIZON",1);
define("LAYOUT_RADIOVERTICAL",2);
define("LAYOUT_SELECTBOX",3);

define("RELPOS_RIGHT",1);
define("RELPOS_BELOW",2);

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

#TODO project-specific alerts (mandatory questions, incorrect answer format, ...)
#TODO make it work with the browsers back button
#TODO layout-settings: header, footer, title, icon progress bar
#TODO allow return value of user defined functions to be used in filters
