<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

class JcqViewProjectlist extends JView
{
	
	function display()
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
		
		//add javascript functionality for custom buttons
		$path = 'administrator/components/com_jcq/js/';
		$filename = 'overridesubmit.js';
		JHTML::script($path.$filename, true);
		
		JToolBarHelper::title('JCQ: Projects', 'generic.png');
		if ($projects != null) JToolBarHelper::customX("exportProject","archive.png",".png","Export project",true);
		if ($projects != null) JToolBarHelper::deleteList("Do you really want to delete the selected projects?",'removeProject','Remove');
		if ($projects != null) JToolBarHelper::editList('editProject','Edit');
		JToolBarHelper::custom("showImportProject","unarchive.png",".png","Import project",false);
		JToolBarHelper::addNewX('addProject','New project');
		
		parent::display();
	}
	
}