<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

class JcqViewQuestionform extends JView
{
	function displayEdit($questionID){
		$model = $this->getModel();
		$question = $model->getQuestion($questionID);
		$this->assignRef('question', $question);
		$page = $model->getPageFromQuestion($questionID);
		$this->assignRef('page', $page);
		$project = $model->getProjectFromPage($page->ID);
		$this->assignRef('project', $project);
		
		JToolBarHelper::title('JCQ: Edit question');
		JToolBarHelper::save("saveQuestion","Save");
		JToolBarHelper::cancel("cancelAddQuestion","Cancel");
		
		//doing the breadcrumbs here so no code replication for different layouts (question types)
		if ($this->question->ID > 0) { ?>
		<p class="breadcrumbs">
		<a href="<?php echo JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=display',false);?>">JCQ</a>&nbsp;&gt;&nbsp;
		<a href="<?php echo JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=editProject&cid[]='.$this->project->ID,false);?>">Project &quot;<?php echo $this->project->name; ?>&quot;</a>&nbsp;&gt;&nbsp;
		<a href="<?php echo JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=editPage&cid[]='.$this->page->ID,false);?>">Page &quot;<?php echo $this->page->name; ?>&quot;</a>&nbsp;&gt;&nbsp;
		<a href="<?php echo JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=editQuestion&cid[]='.$this->question->ID,false);?>">Question &quot;<?php echo $this->question->name; ?>&quot;</a>
		</p>
		<?php }
				
		parent::display();
	}
	
	function displayAdd($questionID){
		$model = $this->getModel();
		$question = $model->getNewQuestion($questionID);
		$this->assignRef('question', $question);

		JToolBarHelper::title('JCQ: New question');
		JToolBarHelper::save("saveQuestion","Save");
		JToolBarHelper::cancel("cancelAddQuestion","Cancel");
		parent::display();
	}
}