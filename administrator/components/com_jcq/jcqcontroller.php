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

		if (isset($project['pageord']))
		{
			$pagemodel = & $this->getModel('pages');
			$pagemodel->setPageOrder($project['pageids'],$project['pageord']);
		}
		
		if ($project['ID']>0) $redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editProject&cid[]='.$project['ID'],false);
		else  $redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=display',false);
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
	
	function editPage(){
			
		$pageids = JRequest::getVar('cid', null, 'default', 'array' );
			
		if($pageids === null) JError::raiseError(500, 'cid parameter missing');
			
		$pageID = (int)$pageids[0]; //get the first id from the list (we can only edit one greeting at a time)
	
		$view = & $this->getView('pageform');
			
		if ($model = & $this->getModel('pages')) {
			//Push the model into the view (as default)
			//Second parameter indicates that it is the default model for the view
			$view->setModel($model, true);
		}
		else JError::raiseError(500, 'Model not found');
			
		$view->setLayout('pageformlayout');
		$view->displayEdit($pageID);
	}
	
	function addPage()
	{
		$projectID = JRequest::getVar('ID');
		if($projectID === null) JError::raiseError(500, 'project id parameter missing');
		$view = & $this->getView('pageform');
		$model = & $this->getModel('pages');
		if (!$model) JError::raiseError(500, 'Model not found');
		$view->setModel($model, true);
		$view->setLayout('pageformlayout');
		$view->displayAdd($projectID);
	}
	
	function savePage()
	{
		$page = JRequest::get( 'POST' );
			
		$model = & $this->getModel('pages');
		$model->savePage($page);
			
		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editProject&cid[]='.$page['projectID'],false);
		$this->setRedirect($redirectTo, 'Page added!');
	}
	
	function removePage()
	{
		$project = JRequest::get( 'POST' );
		$arrayIDs = JRequest::getVar('cid', null, 'default', 'array' ); //Reads cid as an array
			
		if($arrayIDs === null) JError::raiseError(500, 'cid parameter missing');
			
		$model = & $this->getModel('pages');
		$model->deletePages($arrayIDs);
			
		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editProject&cid[]='.$project['ID'],false);
		$this->setRedirect($redirectTo, 'Removed '.count($arrayIDs).' page(s)');
	}
	
	function cancelAddPage()
	{
		$page = JRequest::get( 'POST' );
		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editProject&cid[]='.$page['projectID'],false);
		$this->setRedirect($redirectTo, 'Cancelled ...');
	}
	
	function addQuestion()
	{
		$pageID = JRequest::getVar('ID');
		if($pageID === null) JError::raiseError(500, 'page id parameter missing');
		$view = & $this->getView('questionform');
		$model = & $this->getModel('questions');
		if (!$model) JError::raiseError(500, 'Model not found');
		$view->setModel($model, true);
		$view->setLayout('questionformlayout');
		$view->displayAdd($pageID);
	}
	
	function saveQuestion()
	{
		$question = JRequest::get( 'POST' );
			
		$model = & $this->getModel('questions');
		$model->saveQuestion($question);
			
		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editPage&cid[]='.$question['pageID'],false);
		$this->setRedirect($redirectTo, 'Question added!');
	}
	
	function cancelAddQuestion()
	{
		$question = JRequest::get( 'POST' );
		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editPage&cid[]='.$question['pageID'],false);
		$this->setRedirect($redirectTo, 'Cancelled ...');
	}
}