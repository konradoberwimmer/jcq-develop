<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

class JcqViewQuestionform extends JView
{
	function displayAdd($pageID){
		$model = $this->getModel();
		$question = $model->getNewQuestion($pageID);
		$this->assignRef('question', $question);

		JToolBarHelper::title('JCQ: New question');
		JToolBarHelper::save("saveQuestion","Save");
		JToolBarHelper::cancel("cancelAddQuestion","Cancel");
		parent::display();
	}
}