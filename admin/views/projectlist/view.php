<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

class JcqViewProjectlist extends JView
{
	
	function display($tpl = NULL)
	{
		$model = $this->getModel();
		$projects = $model->getProjects();
		$this->assignRef('projects', $projects);
		$pagecounts = array();
		$i=0;
		foreach ($projects as $project)
		{
			$pagecount = $model->getPageCount($project->ID);
			$pagecounts[$i] = $pagecount;
			$i++;
		}
		$this->assignRef('pagecounts', $pagecounts);
		$scales = $this->getModel('scales')->getPredefinedScales();
		$this->assignRef('scales', $scales);
		$codecounts = array();
		$i=0;
		foreach ($scales as $scale)
		{
			$codecount = $this->getModel('scales')->getCodeCount($scale->ID);
			$codecounts[$i] = $codecount;
			$i++;
		}
		$this->assignRef('codecounts', $codecounts);
		
		//add javascript functionality
		$parser = JFactory::getXMLParser('Simple');
		$parser->loadFile(JPATH_ADMINISTRATOR .'/components/com_jcq/jcq.xml');
		$version = $parser->document->getElementByPath('version')->data();
		$path = 'components/com_jcq/js/';
		$filenames=array('overridesubmit.js');
		$document = JFactory::getDocument();
		foreach ($filenames as $filename) $document->addScript($path.$filename.'?version='.$version,'text/javascript',true);
		
		JToolBarHelper::title('JCQ: Projects', 'generic.png');
		if ($projects != null) JToolBarHelper::deleteList("Do you really want to delete the selected projects?",'removeProject','Remove');
		if ($projects != null) JToolBarHelper::editList('editProject','Edit');
		JToolBarHelper::custom("showImportProject","unarchive.png",".png","Import project",false);
		JToolBarHelper::addNewX('addProject','New project');
		
		parent::display();
	}
	
}