<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

function jtableToXmlWithoutIDs ($jtable, $xmldoc, $xmlnode)
{
	foreach (get_object_vars($jtable) as $k => $v) //ok, here a scripting language makes everything simpler
	{
		if (is_array($v) or is_object($v) or $v === NULL) continue;
		if ($k[0] == '_') continue;
		if (strpos($k,"ID")!==false) continue;
		$element = $xmldoc->createElement($k,'<![CDATA[' . $v . ']]>');
		$xmlnode->appendChild($element);
	}
}

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
		 
		$projectID = (int)$projectids[0]; //get the first id from the list (we can only edit one project at a time)
	
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
		
		//create php-file for project with basic class definition if it does not yet exist
		if (!is_dir(JPATH_COMPONENT_SITE.DS.'usercode')) mkdir(JPATH_COMPONENT_SITE.DS.'usercode');
		//FIXME this is not save because several projects may use the same php-file
		if (!file_exists(JPATH_COMPONENT_SITE.DS.'usercode'.DS.$project['classfile']))
		{
			$filehandle = fopen(JPATH_COMPONENT_SITE.DS.'usercode'.DS.$project['classfile'], 'w');
			fwrite($filehandle,"<?php\n");
			fwrite($filehandle,"defined( '_JEXEC' ) or die( 'Restricted access' );\n");
			fwrite($filehandle,"\n");
			fwrite($filehandle,"class ".$project['classname']."\n");
			fwrite($filehandle,"{\n");
			fwrite($filehandle,"\n");
			fwrite($filehandle,"}\n");
			fclose($filehandle);
		}
		
		//set page order if edited
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
	
	function exportProject()
	{
		$projectids = JRequest::getVar('cid', null, 'default', 'array' );
		if($projectids === null) JError::raiseError(500, 'cid parameter missing');
		$projectID = (int)$projectids[0]; //get the first id from the list (we can only export one project at a time)
		
		//FIXME storing the xml-file in the usercode folder is totally insecure, but for now it is easier to achieve :-(
		
		//create php-file for project with basic class definition if it does not yet exist
		if (!is_dir(JPATH_COMPONENT_SITE.DS.'usercode')) mkdir(JPATH_COMPONENT_SITE.DS.'usercode');
		$filehandle = fopen(JPATH_COMPONENT_SITE.DS.'usercode'.DS.'project'.$projectID.'.xml', 'w');
		//I am doing the data access here because it does not really fit in any of the models
		$xmldoc = new DOMDocument('1.0', 'utf-8');
		$projectnode = $xmldoc->createElement("project");
		//adding project settings
		$tableProject =& $this->getModel("projects")->getTable("projects");
		$tableProject->load($projectID);
		jtableToXmlWithoutIDs($tableProject, $xmldoc, $projectnode);
		//adding pages
		$pages =& $this->getModel("projects")->getPages($projectID);
		foreach ($pages as $page)
		{
			$pagenode=$xmldoc->createElement("page");
			$tablePage =& $this->getModel("pages")->getTable("pages");
			$tablePage->load($page->ID);
			jtableToXmlWithoutIDs($tablePage, $xmldoc, $pagenode);
			//adding questions
			$questions =& $this->getModel("pages")->getQuestions($page->ID);
			foreach ($questions as $question)
			{
				$questionnode=$xmldoc->createElement("question");
				$tableQuestion =& $this->getModel("questions")->getTable("questions");
				$tableQuestion->load($question->ID);
				jtableToXmlWithoutIDs($tableQuestion, $xmldoc, $questionnode);
				//adding items
				$items =& $this->getModel("items")->getItems($question->ID);
				foreach ($items as $item)
				{
					$itemnode=$xmldoc->createElement("item");
					$tableItem =& $this->getModel("items")->getTable("items");
					$tableItem->load($item->ID);
					jtableToXmlWithoutIDs($tableItem, $xmldoc, $itemnode);
					//TODO scales from items
					$questionnode->appendChild($itemnode);
				}
				//adding scale(s)
				$scales =& $this->getModel("scales")->getScales($question->ID);
				foreach ($scales as $scale)
				{
					$scalenode=$xmldoc->createElement("scale");
					$tableScale =& $this->getModel("scales")->getTable("scales");
					$tableScale->load($scale->ID);
					jtableToXmlWithoutIDs($tableScale, $xmldoc, $scalenode);
					//adding codes
					$codes =& $this->getModel("scales")->getCodes($scale->ID);
					foreach ($codes as $code)
					{
						$codenode=$xmldoc->createElement("code");
						$tableCode =& $this->getModel("scales")->getTable("codes");
						$tableCode->load($code->ID);
						jtableToXmlWithoutIDs($tableCode, $xmldoc, $codenode);
						$scalenode->appendChild($codenode);
					}
					$questionnode->appendChild($scalenode);
				}
				$pagenode->appendChild($questionnode);
			}		
			$projectnode->appendChild($pagenode);
		}
		//finishing
		$xmldoc->appendChild($projectnode);
		fwrite($filehandle, $xmldoc->saveXML());
		fclose($filehandle);
		
		$view = & $this->getView('exportproject');
		$view->setLayout('exportprojectlayout');
		$view->display($projectID);
	}	

	function editPage(){
			
		$pageids = JRequest::getVar('cid', null, 'default', 'array' );
			
		if($pageids === null) JError::raiseError(500, 'cid parameter missing');
			
		$pageID = (int)$pageids[0]; //get the first id from the list (we can only edit one greeting at a time)
	
		$view = & $this->getView('pageform');
			
		if ($model = & $this->getModel('pages') && $modelquestions = & $this->getModel('questions')) {
			//Push the model into the view (as default)
			//Second parameter indicates that it is the default model for the view
			$view->setModel($model, true);
			$view->setModel($modelquestions, false);
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
		
		if (isset($page['questionord']))
		{
			$questionmodel = & $this->getModel('questions');
			$questionmodel->setQuestionOrder($page['questionids'],$page['questionord']);
		}
				
		if ($page['ID']>0) $redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editPage&cid[]='.$page['ID'],false);
		else  $redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editProject&cid[]='.$page['projectID'],false);
		$this->setRedirect($redirectTo, 'Page saved!');
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
	
	function editQuestion(){
			
		$questionids = JRequest::getVar('cid', null, 'default', 'array' );
			
		if($questionids === null) JError::raiseError(500, 'cid parameter missing');
			
		$questionID = (int)$questionids[0]; //get the first id from the list (we can only edit one greeting at a time)
	
		$view = & $this->getView('questionform');
			
		if ($model = & $this->getModel('questions') && $modelscales = & $this->getModel('scales') && $modelitems = & $this->getModel('items'))
		{
			$view->setModel($model, true);
			$view->setModel($modelscales);
			$view->setModel($modelitems);
		}
		else JError::raiseError(500, 'Model not found');
			
		$questtype = $model->getTypeFromQuestion($questionID);
		$view->setLayout('questionformlayout'.$questtype);
		$view->displayEdit($questionID);
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
			
		//save the scale if question has any
		if (isset($question['scaleID']))
		{
			$scalemodel = & $this->getModel('scales');
			//has to be in this order: 1. save codes 2. delete codes; otherwise errors for missing IDs
			$codeids = JRequest::getVar('codeids', null, 'default', 'array' );
			$codeord = JRequest::getVar('codeord', null, 'default', 'array' );
			$codevalue = JRequest::getVar('codevalue', null, 'default', 'array' );
			$codelabel = JRequest::getVar('codelabel', null, 'default', 'array' );
			$codemissval = JRequest::getVar('codemissval', null, 'default', 'array' );
			for ($i=0;$i<count($codeids);$i++)
			{
				$code = array();
				$code['ID']=$codeids[$i];
				$code['ord']=$codeord[$i];
				$code['code']=$codevalue[$i];
				$code['label']=$codelabel[$i];
				if ($codemissval!=null && in_array($codeids[$i],$codemissval)) $code['missval']=1;
				else $code['missval']=0;
				$code['scaleID']=$question['scaleID'];
				$scalemodel->saveCode($code);
			}
			$codedelete = JRequest::getVar('codedelete', null, 'default', 'array' );
			if ($codedelete!=null) $scalemodel->deleteCodes($codedelete);
		}
		
		//save the items if question has any
		if (isset($question['itemspresent']))
		{
			$itemsmodel = & $this->getModel('items');
			//has to be in this order: 1. save codes 2. delete codes; otherwise errors for missing IDs
			$itemids = JRequest::getVar('itemids', null, 'default', 'array' );
			$itemord = JRequest::getVar('itemord', null, 'default', 'array' );
			$itemtextleft = JRequest::getVar('itemtextleft', null, 'default', 'array' );
			$itemvarname = JRequest::getVar('itemvarname', null, 'default', 'array' );
			$itemmandatory = JRequest::getVar('itemmandatory', null, 'default', 'array' );
			for ($i=0;$i<count($itemids);$i++)
			{
				$item = array();
				$item['ID']=$itemids[$i];
				$item['ord']=$itemord[$i];
				$item['textleft']=$itemtextleft[$i];
				$item['varname']=$itemvarname[$i];
				if ($itemmandatory!=null && in_array($itemids[$i],$itemmandatory)) $item['mandatory']=1;
				else $item['mandatory']=0;
				$item['questionID']=$question['ID'];
				$itemsmodel->saveItem($item);
			}
			$itemdelete = JRequest::getVar('itemdelete', null, 'default', 'array' );
			if ($itemdelete!=null) $itemsmodel->deleteItems($itemdelete);
		}
		
		if ($question['ID']>0) $redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editQuestion&cid[]='.$question['ID'],false);
		else  $redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editPage&cid[]='.$question['pageID'],false);
		$this->setRedirect($redirectTo, 'Question saved!');
	}
	
	function removeQuestion()
	{
		$page = JRequest::get( 'POST' );
		$arrayIDs = JRequest::getVar('cid', null, 'default', 'array' );
			
		if($arrayIDs === null) JError::raiseError(500, 'cid parameter missing');
			
		$model = & $this->getModel('questions');
		$model->deleteQuestions($arrayIDs);
			
		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editPage&cid[]='.$page['ID'],false);
		$this->setRedirect($redirectTo, 'Removed '.count($arrayIDs).' question(s)');
	}
	
	
	function cancelAddQuestion()
	{
		$question = JRequest::get( 'POST' );
		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editPage&cid[]='.$question['pageID'],false);
		$this->setRedirect($redirectTo, 'Cancelled ...');
	}
}