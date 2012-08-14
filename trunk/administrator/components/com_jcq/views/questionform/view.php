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
		
		//attach scale(s) according to questiontype
		switch ($this->question->questtype)
		{
			case 111:
				{
					$scale = $this->getModel('scales')->getScales($this->question->ID);
					if ($scale==null) JError::raiseError(500, 'Error: No scale for question of type 111');
					else
					{
						$scale=$scale[0]; //only one scale for this type of question
						$this->assignRef('scale', $scale);
						$codes = $this->getModel('scales')->getCodes($this->scale->ID);
						if ($codes===null) JError::raiseError(500, 'Error: No scale for question of type 111');
						else $this->assignRef('codes', $codes);
					}
					break;
				}
			default: JError::raiseError(500, 'FATAL: Code for viewing question of type '.$this->question->questtype.' is missing!!!');
		}
		
		//add javascript functionality according to questtype
		$path = 'administrator/components/com_jcq/js/';
		switch ($this->question->questtype)
		{
			case 111:
				{
					$filename = 'addcodes.js';
					break;
				}
			default: JError::raiseError(500, 'FATAL: Code for viewing question of type '.$this->question->questtype.' is missing!!!');
		}
		if (isset($filename)) JHTML::script($filename, $path, true);
		
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