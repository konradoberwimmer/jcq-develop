<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

class JcqViewProjectform extends JView
{
	function displayEdit($projectID, $download=null, $previewSession=null)
	{
		$model = $this->getModel();
		$project = $model->getProject($projectID);
		$this->assignRef('project', $project);
		$pages = $model->getPages($projectID);
		$programfiles = $model->getProgramfiles($projectID);
		$this->assignRef('pages', $pages);
		$this->assignRef('programfiles', $programfiles);
		$this->assign('usergroups', $this->getModel('usergroups'));
		$this->assignRef('download',$download);
		if ($previewSession!==null) $this->assignRef('previewSession',$previewSession);
		
		$questioncounts = array();
		$i=0;
		foreach ($pages as $page)
		{
			$questioncount = $model->getQuestionCount($page->ID);
			$questioncounts[$i] = $questioncount;
			$i++;
		}
		$this->assignRef('questioncounts', $questioncounts);
		
		//add javascript functionality for checking correctness of input
		$path = 'administrator/components/com_jcq/js/';
		$filename = 'overridesubmit.js';
		JHTML::script($path.$filename, true);
		$filename = 'manageuglist.js';
		JHTML::script($path.$filename, true);
		$filename = 'opendownload.js';
		JHTML::script($path.$filename, true);
		$filename = 'openpreview.js';
		JHTML::script($path.$filename, true);
		$filename = 'addprogramfiles.js';
		JHTML::script($path.$filename, true);
		
		JToolBarHelper::title('JCQ: Edit project');
		if ($model->getPageCount($projectID) > 0) JToolBarHelper::deleteList("Do you really want to delete the selected pages?",'removePage','Remove page(s)');
		if ($model->getPageCount($projectID) > 0) JToolBarHelper::editList('editPage','Edit page');
		JToolBarHelper::addNewX('addPage','New page');
		JToolBarHelper::divider();
		JToolBarHelper::customX("exportProject","archive.png",".png","Export project", false);
		JToolBarHelper::custom("saveProject","save.png",".png","Save",false);
		JToolBarHelper::cancel();
		parent::display();
	}
		 
	function displayAdd(){
		$model = $this->getModel();
		$project = $model->getNewProject();
		$this->assignRef('project', $project);

		//add javascript functionality for checking correctness of input
		$path = 'administrator/components/com_jcq/js/';
		$filename = 'overridesubmit.js';
		JHTML::script($filename, $path, true);
		
		JToolBarHelper::title('JCQ: New Project');
		JToolBarHelper::custom("saveProject","save.png",".png","Save",false);
		JToolBarHelper::cancel();
		parent::display();
	}
}

