<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

class JcqViewPageform extends JView
{
	function displayEdit($pageID,$previewSession=null){
		$model = $this->getModel();
		$page = $model->getPage($pageID);
		$project = $model->getProjectFromPage($pageID);
		$this->assignRef('page', $page);
		$this->assignRef('project', $project);
		$questions = $model->getQuestions($pageID);
		$this->assignRef('questions', $questions);
		$this->assignRef('variables', $this->getModel('projects')->getVariableList($project->ID));
		if ($previewSession!==null) $this->assignRef('previewSession',$previewSession);
		
		$path = 'administrator/components/com_jcq/js/';
		$filename = 'overridesubmit.js';
		JHTML::script($path.$filename, true);
		$filename = 'addfilter.js';
		JHTML::script($path.$filename, true);
		$filename = 'openpreview.js';
		JHTML::script($path.$filename, true);
		
		JToolBarHelper::title('JCQ: Edit page');
		if ($model->getQuestionCount($pageID) > 0) JToolBarHelper::deleteList("Do you really want to delete the selected questions?",'removeQuestion','Remove question(s)');
		if ($model->getQuestionCount($pageID) > 0) JToolBarHelper::editList('editQuestion','Edit question');
		JToolBarHelper::addNewX('addQuestion','New question');
		JToolBarHelper::divider();
		JToolBarHelper::save("savePage","Save");
		JToolBarHelper::cancel("cancelAddPage","Cancel");
		parent::display();
	}

	function displayAdd($projectID){
		$model = $this->getModel();
		$page = $model->getNewPage($projectID);
		$this->assignRef('page', $page);

		JToolBarHelper::title('JCQ: New page');
		JToolBarHelper::save("savePage","Save");
		JToolBarHelper::cancel("cancelAddPage","Cancel");
		parent::display();
	}
}