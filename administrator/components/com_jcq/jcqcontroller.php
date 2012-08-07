<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

class JcqController extends JController
{

	function display()
	{
		//This sets the default view (second argument)
		$viewName    = JRequest::getVar( 'view', 'projectlist' );
		//This sets the default layout/template for the view
		$viewLayout  = JRequest::getVar( 'layout', 'projectlistlayout' );

		$view = & $this->getView($viewName);
			
		// Get/Create the model
		if ($model = & $this->getModel('projects')) {
			//Push the model into the view (as default)
			//Second parameter indicates that it is the default model for the view
			$view->setModel($model, true);
		}
		else JError::raiseError(500, 'Model not found');
		
		$view->setLayout($viewLayout);
		$view->display();

	}
	
	function addProject()
	{
		$view = & $this->getView('projectform');
		$model = & $this->getModel('projects');
		if (!$model) JError::raiseError(500, 'Model not found');
		$view->setModel($model, true);
		$view->setLayout('projectformlayout');
		$view->displayAdd();
	}
	
	function editProject(){
		 
		$projectids = JRequest::getVar('cid', null, 'default', 'array' );
		 
		if($projectids === null) JError::raiseError(500, 'cid parameter missing');
		 
		$projectID = (int)$projectids[0]; //get the first id from the list (we can only edit one greeting at a time)
	
		$view = & $this->getView('projectform');
		 
		if ($model = & $this->getModel('projects')) {
			//Push the model into the view (as default)
			//Second parameter indicates that it is the default model for the view
			$view->setModel($model, true);
		}
		else JError::raiseError(500, 'Model not found');
				 
		$view->setLayout('projectformlayout');
		$view->displayEdit($projectID);
	}
	
	function saveProject()
	{
		$project = JRequest::get( 'POST' );
		 
		$model = & $this->getModel('projects');
		$model->saveProject($project);
		 
		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=display');
		$this->setRedirect($redirectTo, 'Project saved!');
	}
	
	function removeProject()
	{
		$arrayIDs = JRequest::getVar('cid', null, 'default', 'array' ); //Reads cid as an array
		 
		if($arrayIDs === null) JError::raiseError(500, 'cid parameter missing');
		 
		$model = & $this->getModel('projects');
		$model->deleteProjects($arrayIDs);
		 
		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option'));
		$this->setRedirect($redirectTo, 'Removed '.count($arrayIDs).' project(s)');
	}
	
	function cancel()
	{
		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option'));
		$this->setRedirect($redirectTo, 'Cancelled ...');
	}
}