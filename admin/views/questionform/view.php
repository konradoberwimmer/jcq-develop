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
		
		//attach scale(s)
		$scales = $this->getModel('scales')->getScales($this->question->ID);
		$predefscales = $this->getModel('scales')->getPredefinedScales();
		$mainscale = null;
		if ($scales!==null && count($scales)>0) $mainscale = $scales[0];
		$mainscalecodes = null;
		if ($mainscale!==null) $mainscalecodes = $this->getModel('scales')->getCodes($mainscale->ID);
		$this->assignRef("scales", $scales);
		$this->assignRef("predefscales", $predefscales);
		$this->assignRef("mainscale", $mainscale);
		$this->assignRef("mainscalecodes", $mainscalecodes);
		
		//attach item(s)
		$items = $this->getModel()->getItems($this->question->ID);
		$mainitem = null;
		if ($items!==null && count($items)>0) foreach ($items as $oneitem) if ($oneitem->bindingType=='QUESTION') { $mainitem = $oneitem; break; }
		$this->assignRef('items', $items);
		$this->assignRef('mainitem', $mainitem);
		
		//add javascript functionality
		$parser = JFactory::getXMLParser('Simple');
		$parser->loadFile(JPATH_ADMINISTRATOR .'/components/com_jcq/jcq.xml');
		$version = $parser->document->getElementByPath('version')->data();
		$path = 'components/com_jcq/js/';
		$filenames=array('overridesubmit.js','additems.js','addscales.js','addcodes.js','addfilter.js');
		$document = JFactory::getDocument();
		foreach ($filenames as $filename) $document->addScript($path.$filename.'?version='.$version,'text/javascript',true);
		
		parent::display();
	}
	
	function displayAdd($pageID){
		$model = $this->getModel();
		$modelpage = $this->getModel('pages');;
		$question = $model->getNewQuestion($pageID);
		$this->assignRef('question', $question);
		$this->assign('page',$modelpage->getPage($pageID));

		JToolBarHelper::title('JCQ: New question');
		JToolBarHelper::save("saveQuestion","Save");
		JToolBarHelper::cancel("cancelAddQuestion","Cancel");
		parent::display();
	}
}