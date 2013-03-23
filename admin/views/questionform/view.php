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
			case 111: case 311: case 340:
				{
					$scale = $this->getModel('scales')->getScales($this->question->ID);
					if ($scale==null) JError::raiseError(500, 'Error: No scale for question of type 111');
					else
					{
						$scale=$scale[0]; //only one scale for this type of question
						$this->assignRef('scale', $scale);
						$codes = $this->getModel('scales')->getCodes($this->scale->ID);
						$this->assignRef('codes', $codes);
					}
					break;
				}
			case 141: case 998: break; //necessary to prevent fatal error warning when code has not been written for this questtype!
			default: JError::raiseError(500, 'FATAL: Code for viewing question of type '.$this->question->questtype.' is missing!!!');
		}
		
		//attach item(s) according to questiontype
		switch ($this->question->questtype)
		{
			case 311: case 340:
				{
					$items = $this->getModel('items')->getItems($this->question->ID);
					$this->assignRef('items', $items);
					break;
				}
			case 111: case 141: case 998: break; //necessary to prevent fatal error warning when code has not been written for this questtype!
			default: JError::raiseError(500, 'FATAL: Code for viewing question of type '.$this->question->questtype.' is missing!!!');
		}
		
		//add javascript functionality according to questtype
		$path = 'administrator/components/com_jcq/js/';
		$filenames=array();
		switch ($this->question->questtype)
		{
			case 111:
				{
					$filenames[0] = 'addcodes.js';
					break;
				}
			case 311: case 340:
				{
					$filenames[0] = 'addcodes.js';
					$filenames[1] = 'additems.js';
					break;
				}
			case 141: case 998: break;
			default: JError::raiseError(500, 'FATAL: Code for viewing question of type '.$this->question->questtype.' is missing!!!');
		}
		foreach ($filenames as $filename) JHTML::script($path.$filename, true);
		
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