<?php
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.controller');

class JcqController extends JController
{
	function display()
	{
		//front-end can not be accessed without a given project ID
		$projectID = JRequest::getVar('projectID');
		if ($projectID==null) JError::raiseError(500, 'ProjectID not set');
		
		//get view and layout - could all be fixated in the future (what other view is there?), but for now I copy code ;-)
		$viewName    = JRequest::getVar( 'view', 'page' );
		$viewLayout  = JRequest::getVar( 'layout', 'pagelayout' );
		$view = & $this->getView($viewName);
		$view->setLayout($viewLayout);
		
		//get the data models
		$markmissing = false;
		if (JRequest::getVar('markmissing')!=null) $markmissing = true;	
		if ($model = & $this->getModel('page') && $modeluserdata = & $this->getModel('userdata') )
		{
			$view->setModel($model, true);
			$view->setModel($modeluserdata);
		}
		else JError::raiseError(500, 'Model(s) not found');
		
		//get or create session
		$sessionID = JRequest::getVar('sessionID');
		if ($sessionID==null)
		{
			//the only reason why a session could not be created is when anonymous answers are not allowed
			if (!$modeluserdata->createSession($projectID)) JError::raiseError(500, 'Not allowed - needs login!');
		}
		else
		{
			if (!$modeluserdata->loadSession($projectID,$sessionID)) JError::raiseError(500, 'Unknown session!');
		}
		//the userdata model is now linked to the participant's answers
		
		//add user css if any
		$projectmodel = & $this->getModel('project');
		$cssfilename = $projectmodel->getCSSfilename($projectID);
		if (isset($cssfilename)&&strlen($cssfilename)>0)
		{
			$document = JFactory::getDocument();
			$document->addStyleSheet(JURI::root().'components/com_jcq/usercode/'.$cssfilename);
		}
		
		$pageID = $modeluserdata->getCurrentPage();
		//display the current page for this session
		if ($pageID>0) 	$view->displayPage($pageID,$markmissing);
		//or show the results (usercode)
		else
		{
			require_once( JPATH_COMPONENT.DS.'usercode'.DS.$projectmodel->getClassfilename($projectID));
			$classname = $projectmodel->getClassname($projectID);
			$resultview = new $classname($modeluserdata);
			$resultview->printResults();
		}
	}
	
	function storeAndContinue()
	{
		//front-end can not be accessed without a given project ID
		$projectID = JRequest::getVar('projectID');
		if ($projectID==null) JError::raiseError(500, 'ProjectID not set');
		
		//a session has to exist if this is invoked
		$sessionID = JRequest::getVar('sessionID');
		if ($sessionID==null) JError::raiseError(500, 'No session to save');
		
		//load the session
		$modeluserdata = & $this->getModel('userdata');
		$modeluserdata->loadSession($projectID,$sessionID);
		
		//save participant input (redirect according to the need to display missing input)
		if (!$modeluserdata->storeAndContinue()) $redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&projectID='.$projectID.'&sessionID='.$sessionID.'&markmissing=1',false);
		else $redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&projectID='.$projectID.'&sessionID='.$sessionID,false);
		$this->setRedirect($redirectTo);
	}
}