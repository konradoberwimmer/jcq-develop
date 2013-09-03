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
		$loginsuccessful = true;
		$sessionID = JRequest::getVar('sessionID');
		if ($sessionID==null)
		{
			//reasons why a session could not be created:
			// a) no token or user and anonymous answers are not allowed
			// b) invalid token
			if (!$modeluserdata->createSession($projectID)) $loginsuccessful=false;
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
		//add user imported code (if any)
		$imports = $projectmodel->getImports($projectID);
		foreach ($imports as $import) require_once(JPATH_COMPONENT_SITE.DS.'usercode'.DS.$import->filename);
		
		if ($loginsuccessful)
		{
			//display the current page for this session
			$pageID = $modeluserdata->getCurrentPage();
			$view->displayPage($pageID,$markmissing);
		}
		else
		{
			//display the login form
			#TODO ban IPs that attempt to break through
			$view = & $this->getView('loginform');
			$view->setLayout('loginformlayout');
			$view->display();
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
		$uri = & JFactory::getURI();
		if (!$modeluserdata->storeAndContinue()) $uri->setVar("markmissing","1");
		else $uri->delVar("markmissing");
		$uri->setVar("option",JRequest::getVar('option'));
		$uri->setVar("projectID",$projectID);
		$uri->setVar("sessionID",$sessionID);
		$this->setRedirect($uri->toString());
	}
}