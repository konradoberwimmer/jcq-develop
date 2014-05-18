<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');
jimport('joomla.filesystem.file');

set_include_path(JPATH_COMPONENT.DS.'includes');
include('PHPExcel'.DS.'PHPExcel.php');
include('PHPExcel'.DS.'PHPExcel'.DS.'IOFactory.php');

class JcqController extends JController
{

	function display($cachable = false, $urlparams = false)
	{
		$viewName    = JRequest::getVar( 'view', 'projectlist' );
		$viewLayout  = JRequest::getVar( 'layout', 'projectlistlayout' );
		$view = $this->getView($viewName);
			
		if ($model = $this->getModel('projects')) $view->setModel($model, true);
		else JError::raiseError(500, 'Model projects not found');
		if ($modelscales = $this->getModel('scales')) $view->setModel($modelscales, false);
		else JError::raiseError(500, 'Model scales not found');

		$view->setLayout($viewLayout);
		$view->display();
	}

	function addProject()
	{
		$view = $this->getView('projectform');
		$model = $this->getModel('projects');
		if (!$model) JError::raiseError(500, 'Model not found');
		$view->setModel($model, true);
		$view->setLayout('projectformlayout');
		$view->displayAdd();
	}

	function editProject($projectID=null,$download=null)
	{
			
		if ($projectID===null)
		{
			$projectids = JRequest::getVar('cid', null, 'default', 'array' );
			if($projectids === null) JError::raiseError(500, 'cid parameter missing');
			$projectID = (int)$projectids[0];
		}

		$view = $this->getView('projectform');

		if ($model = $this->getModel('projects')) $view->setModel($model, true);
		else JError::raiseError(500, 'Model projects not found');
		if ($modelusergroups = $this->getModel('usergroups')) $view->setModel($modelusergroups, false);
		else JError::raiseError(500, 'Model participants not found');
			
		$view->setLayout('projectformlayout');
		
		$previewSession = JRequest::getVar('preview', null);
		if ($previewSession!==null) $view->displayEdit($projectID,null,$previewSession);
		else $view->displayEdit($projectID,$download);
	}

	function saveProject()
	{
		$project = JRequest::get( 'POST' );
			
		$model = $this->getModel('projects');
		$projectid = $model->saveProject($project);

		//create usercode path and css-file for project if it does not yet exist
		if (!is_dir(JPATH_COMPONENT_SITE.DS.'usercode')) mkdir(JPATH_COMPONENT_SITE.DS.'usercode');
		if (isset($project['cssfile'])&&strlen($project['cssfile'])>0)
		{
			if (!file_exists(JPATH_COMPONENT_SITE.DS.'usercode'.DS.$project['cssfile'])) copy(JPATH_COMPONENT_SITE.DS.'jcq.css',JPATH_COMPONENT_SITE.DS.'usercode'.DS.$project['cssfile']);
		}

		//set page order if edited
		if (isset($project['pageord']))
		{
			$pagemodel = $this->getModel('pages');
			$pagemodel->setPageOrder($project['pageids'],$project['pageord']);
		}

		//save the programfiles if project has any
		if (isset($project['programfileids']))
		{
			//has to be in this order: 1. save programfiles 2. delete programfiles; otherwise errors for missing IDs
			$programfileids = JRequest::getVar('programfileids', null, 'default', 'array' );
			$programfileord = JRequest::getVar('programfileord', null, 'default', 'array' );
			$programfilename = JRequest::getVar('programfilename', null, 'default', 'array' );
			for ($i=0;$i<count($programfileids);$i++)
			{
				$programfile = array();
				$programfile['ID']=$programfileids[$i];
				$programfile['ord']=$programfileord[$i];
				$programfile['filename']=$programfilename[$i];
				$programfile['projectID']=$project['ID'];
				$model->saveProgramfile($programfile);
			}
			$programfiledeleteids = JRequest::getVar('programfiledelete', null, 'default', 'array' );
			if ($programfiledeleteids!=null) foreach ($programfiledeleteids as $programfiledeleteid) $model->deleteProgramfile($programfiledeleteid);
		}

		if ($project['previewProject']==1)
		{
			$sessionID = uniqid('', true);
			$pages = $model->getPages($projectid);
			if ($pages===null || count($pages)==1) $pageID=-1;
			else $pageID=$pages[0]->ID;
			$sqlnewsession = "INSERT INTO jcq_proj$projectid (preview, sessionID, curpage, timestampBegin) VALUES (1,'$sessionID',$pageID,".time().")";
			$db = JFactory::getDBO();
			$db->setQuery($sqlnewsession);
			if (!$db->query()) JError::raiseError(500, 'Error inserting new session: '.$this->getDBO()->getErrorMsg());
			$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editProject&cid[]='.$projectid.'&preview='.$sessionID,false);
		}
		else
		{
			$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editProject&cid[]='.$projectid,false);
		}
		$this->setRedirect($redirectTo, 'Project saved!');
	}

	function removeProject()
	{
		$arrayIDs = JRequest::getVar('cid', null, 'default', 'array' ); //Reads cid as an array
			
		if($arrayIDs === null) JError::raiseError(500, 'cid parameter missing');
			
		$model = $this->getModel('projects');
		foreach ($arrayIDs as $oneID) $model->deleteProject($oneID);
			
		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option'));
		$this->setRedirect($redirectTo, 'Removed '.count($arrayIDs).' project(s)');
	}

	function cancel()
	{
		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option'));
		$this->setRedirect($redirectTo, 'Cancelled ...');
	}
	
	function editProgramfile()
	{
		$programfileID = JRequest::getVar('editProgramfile', null); //Reads cid as an arra
		if ($programfileID === null || !is_numeric($programfileID)) JError::raiseError(500, 'FATAL: editProgramfile parameter missing');
		
		$view = $this->getView('editprogramfile');
		
		if (($model = $this->getModel('programfiles')) && ($modelproject = $this->getModel('projects')))
		{ 
			$view->setModel($model, true);
			$view->setModel($modelproject, false);
		}
		else JError::raiseError(500, 'Model not found');
		
		$view->setLayout('editprogramfilelayout');
		$view->displayEdit($programfileID);
	}

	function editCSS()
	{
		if (($projectID = JRequest::getVar('ID',null))===null) JError::raiseError(500, 'Project ID missing');
		
		$view = $this->getView('editcss');
		if ($model = $this->getModel('projects')) $view->setModel($model, true);
		else JError::raiseError(500, 'Model not found');
	
		$view->setLayout('editcsslayout');
		$view->displayEdit($projectID);
	}
	
	function saveEditedCSS()
	{
		if (($projectID = JRequest::getVar('ID',null))===null) JError::raiseError(500, 'Project ID missing');
		
		$model = $this->getModel('projects');
		$model->saveEditedCSS($projectID,JRequest::getVar('filecontent',null,'post',null,JREQUEST_ALLOWHTML | JREQUEST_ALLOWRAW));
	
		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editCSS&ID='.$projectID,false);
		$this->setRedirect($redirectTo, 'CSS file saved!');
	}
	
	function cancelEditCSS()
	{
		$thepost = JRequest::get( 'POST' );
		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editProject&cid[]='.$thepost['ID'],false);
		$this->setRedirect($redirectTo, 'Cancelled ...');
	}
	
	function saveEditedProgramfile()
	{
		$programfileID = JRequest::getVar('programfileID', null);
		if ($programfileID === null || !is_numeric($programfileID)) JError::raiseError(500, 'FATAL: programfileID parameter missing');
				
		$model = $this->getModel('programfiles');
		$model->saveEditedProgramfile($programfileID,JRequest::getVar('filecontent',null,'post',null,JREQUEST_ALLOWHTML | JREQUEST_ALLOWRAW));
				
		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editProgramfile&editProgramfile='.$programfileID,false);
		$this->setRedirect($redirectTo, 'Program file saved!');	
	}
	
	function cancelEditProgramfile()
	{
		$programfile = JRequest::get( 'POST' );
		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editProject&cid[]='.$programfile['projectID'],false);
		$this->setRedirect($redirectTo, 'Cancelled ...');
	}
	
	function exportProject()
	{
		$project = JRequest::get( 'POST' );
		$projectid = $project['ID'];
		
		$modelimportexport = $this->getModel('importexport');
		$filename = $modelimportexport->exportProject($projectid);
		
		$app = JFactory::getApplication();
		$app->enqueueMessage("Project exported ...");
		$this->editProject($projectid, $filename);
	}

	function showImportProject()
	{
		$view = $this->getView('importproject');
		$view->setLayout('importprojectlayout');
		$view->display();
	}

	function importProject()
	{
		//get the file
		$file = JRequest::getVar('file_upload', null, 'files', 'array');
		$src = $file['tmp_name'];
		$filehandle = fopen($src,'r');
		$content = fread($filehandle, filesize($src));
		fclose($filehandle);
		$xmldoc = new DOMDocument('1.0', 'utf-8');
		$xmldoc->loadXML($content);
		//get xslt if necessary and parse with XSLTProcessor
		if (JRequest::getVar('usexslt',null)!==null)
		{
			$xsltfile = JRequest::getVar('xslt_upload', null, 'files', 'array');
			$src = $xsltfile['tmp_name'];
			$xsltfilehandle = fopen($src,'r');
			$xsltcontent = fread($xsltfilehandle, filesize($src));
			fclose($xsltfilehandle);
			$xsltdoc = new DOMDocument('1.0', 'utf-8');
			$xsltdoc->loadXML($xsltcontent);
			$proc = new XSLTProcessor();
			$proc->importStylesheet($xsltdoc);
			$xmldoc = $proc->transformToDoc($xmldoc);
		}
		//do the import
		$model = $this->getModel('importexport');
		$importwell = $model->importProject($xmldoc);

		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option'));
		$this->setRedirect($redirectTo, ($importwell?'Project imported ...':'Error importing ...'));
	}

	function editPage()
	{
		$pageids = JRequest::getVar('cid', null, 'default', 'array' );
		if($pageids === null) JError::raiseError(500, 'cid parameter missing');
		$pageID = (int)$pageids[0];

		$view = $this->getView('pageform');
			
		if (($model = $this->getModel('pages')) && ($modelquestions = $this->getModel('questions')) && ($modelprojects = $this->getModel('projects')))
		{
			$view->setModel($model, true);
			$view->setModel($modelquestions, false);
			$view->setModel($modelprojects, false);
		}
		else JError::raiseError(500, 'Model not found');
			
		$view->setLayout('pageformlayout');
		
		$previewSession = JRequest::getVar('preview', null);
		if ($previewSession!==null) $view->displayEdit($pageID,$previewSession);
		else $view->displayEdit($pageID);
	}

	function addPage()
	{
		$projectID = JRequest::getVar('ID');
		if($projectID === null) JError::raiseError(500, 'project id parameter missing');
		$view = $this->getView('pageform');
		$model = $this->getModel('pages');
		if (!$model) JError::raiseError(500, 'Model not found');
		$view->setModel($model, true);
		$view->setLayout('pageformlayout');
		$view->displayAdd($projectID);
	}

	function savePage()
	{
		$page = JRequest::get( 'POST' );
			
		$model = $this->getModel('pages');
		$pageid = $model->savePage($page);

		if (isset($page['questionord']))
		{
			$questionmodel = $this->getModel('questions');
			$questionmodel->setQuestionOrder($page['questionids'],$page['questionord']);
		}

		if ($page['previewPage']==1)
		{
			$sessionID = uniqid('', true);
			$projectID = $model->getProjectFromPage($pageid)->ID;
			$sqlnewsession = "INSERT INTO jcq_proj$projectID (preview, sessionID, curpage, timestampBegin) VALUES (1,'$sessionID',$pageid,".time().")";
			$db = JFactory::getDBO();
			$db->setQuery($sqlnewsession);
			if (!$db->query()) JError::raiseError(500, 'Error inserting new session: '.$this->getDBO()->getErrorMsg());
			$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editPage&cid[]='.$pageid.'&preview='.$sessionID,false);
		}
		else
		{
			$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editPage&cid[]='.$pageid,false);
		}
		$this->setRedirect($redirectTo, 'Page saved!');
	}

	function removePage()
	{
		$project = JRequest::get( 'POST' );
		$arrayIDs = JRequest::getVar('cid', null, 'default', 'array' ); //Reads cid as an array
			
		if($arrayIDs === null) JError::raiseError(500, 'cid parameter missing');
			
		$model = $this->getModel('pages');
		foreach ($arrayIDs as $pageID) $model->deletePage($pageID);
			
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

		$view = $this->getView('questionform');
			
		if (($model = $this->getModel('questions')) && ($modelscales = $this->getModel('scales')) && ($modelitems = $this->getModel('items')))
		{
			$view->setModel($model, true);
			$view->setModel($modelscales);
			$view->setModel($modelitems);
		}
		else JError::raiseError(500, 'Model not found');
			
		$view->setLayout('questionformlayout');
		$view->displayEdit($questionID);
	}

	function addQuestion()
	{
		$pageID = JRequest::getVar('ID');
		if($pageID === null) JError::raiseError(500, 'page id parameter missing');
		$view = $this->getView('questionform');
		$model = $this->getModel('questions');
		if (!$model) JError::raiseError(500, 'Model not found');
		$view->setModel($model, true);
		$modelpage = $this->getModel('pages');
		if (!$modelpage) JError::raiseError(500, 'Model not found');
		$view->setModel($modelpage, false);
		$view->setLayout('questionformlayout');
		$view->displayAdd($pageID);
	}

	function copyQuestion()
	{
		$thepost=JRequest::get('POST' );
	
		$model = $this->getModel('importexport');
		if (isset($thepost['selCopyquestion']) && $thepost['selCopyquestion'] > -1)
		{
			$model->copyQuestion($thepost['selCopyquestion'],$thepost['ID']);
			$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editPage&cid[]='.$thepost['ID'],false);
			$this->setRedirect($redirectTo, 'Question copied ...');
		} else
		{
			$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editPage&cid[]='.$thepost['ID'],false);
			$this->setRedirect($redirectTo, 'No question selected ...');
		}
	}
	
	function saveQuestion()
	{
		$thepost = JRequest::get('POST',JREQUEST_ALLOWHTML|JREQUEST_ALLOWRAW);
		
		//dividing the post
		$post_question = array();
		$post_items = array();
		$post_codes = array();
		$post_scales = array();
		foreach ($thepost as $key=>$value)
		{
			if (strpos($key,'_question_')!==false) $post_question[str_replace('_question_', '', $key)] = $value;
			else if (strpos($key,'_item_')!==false)
			{
				$itemid = str_replace('_item_', '', $key);
				$itemid = intval(substr($itemid, 0, strpos($itemid, '_')));
				//build new array for item if it does not yet exist in the item array
				if (!key_exists($itemid, $post_items)) $post_items[$itemid]=array();
				$newkey = str_replace('_item_'.$itemid.'_', '', $key);
				$post_items[$itemid][$newkey]=$value;
			}
			else if (strpos($key,'_code_')!==false)
			{
				$codeid = str_replace('_code_', '', $key);
				$codeid = intval(substr($codeid, 0, strpos($codeid, '_')));
				//build new array for code if it does not yet exist in the item array
				if (!key_exists($codeid, $post_codes)) $post_codes[$codeid]=array();
				$newkey = str_replace('_code_'.$codeid.'_', '', $key);
				$post_codes[$codeid][$newkey]=$value;
			}
			else if (strpos($key,'_scale_')!==false)
			{
				$scaleid = str_replace('_scale_', '', $key);
				$scaleid = intval(substr($scaleid, 0, strpos($scaleid, '_')));
				//build new array for scale if it does not yet exist in the item array
				if (!key_exists($scaleid, $post_scales)) $post_scales[$scaleid]=array();
				$newkey = str_replace('_scale_'.$scaleid.'_', '', $key);
				$post_scales[$scaleid][$newkey]=$value;
			}
		}
		
		//save question itself
		$modelquestions = $this->getModel('questions');
		$questionid = $modelquestions->saveQuestion($post_question);
		
		//save items
		$modelitems = $this->getModel('items');
		foreach ($post_items as $post_item)
		{
			if ($post_question['questtype']!=MULTISCALE) $itemid = $modelitems->saveItem($post_item);
			else
			{
				$scales = $modelquestions->getScales($questionid);
				if ($scales===null) $scales=array();
				$itemid = $modelitems->saveItem($post_item, $scales);
			}
		}

		//add/remove textfields for items
		$itemaddrmtfids = JRequest::getVar('itemaddrmtf', null, 'default', 'array' );
		if ($itemaddrmtfids!==null) foreach ($itemaddrmtfids as $itemaddrmtfid) $modelitems->addrmTextfields($itemaddrmtfid,$questionid);
		
		//delete items
		$itemdeleteids = JRequest::getVar('itemdelete', null, 'default', 'array' );
		if ($itemdeleteids!==null) foreach ($itemdeleteids as $itemdeleteid) $modelitems->deleteItem($itemdeleteid);
		
		//save codes
		$modelscales = $this->getModel('scales');
		foreach ($post_codes as $post_code) $codeid = $modelscales->saveCode($post_code);

		//add/remove textfields for codes
		$codeaddrmtfids = JRequest::getVar('codeaddrmtf', null, 'default', 'array' );
		if ($codeaddrmtfids!==null) foreach ($codeaddrmtfids as $codeaddrmtfid) $modelscales->addrmTextfields($codeaddrmtfid,$questionid);
		
		//delete codes
		$codedeleteids = JRequest::getVar('codedelete', null, 'default', 'array' );
		if ($codedeleteids!==null) foreach ($codedeleteids as $codedeleteid) $modelscales->deleteCode($codedeleteid);
		
		//handle attached scales
		foreach ($post_scales as $key=>$scale)
		{
			//add attached scale
			if ($key<0) $modelquestions->addAttachedScale($questionid,$scale['ID'],$scale['ord'],(isset($scale['mandatory'])?1:0));
			//update attached scale
			else $modelquestions->saveAttachedScale($questionid,$scale['ID'],$scale['ord'],(isset($scale['mandatory'])?1:0));
		}
		
		//delete attached scales
		$scaledeleteids = JRequest::getVar('scaledelete', null, 'default', 'array' );
		if ($scaledeleteids!==null) foreach ($scaledeleteids as $scaledeleteid) $modelquestions->clearAttachedScale($questionid,$scaledeleteid);
		
		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editQuestion&cid[]='.$questionid,false);
		$this->setRedirect($redirectTo, 'Question saved!');
	}

	function removeQuestion()
	{
		$page = JRequest::get( 'POST' );
		$arrayIDs = JRequest::getVar('cid', null, 'default', 'array' );
			
		if($arrayIDs === null) JError::raiseError(500, 'cid parameter missing');
			
		$model = $this->getModel('questions');
		foreach ($arrayIDs as $questionID) $model->deleteQuestion($questionID);
			
		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editPage&cid[]='.$page['ID'],false);
		$this->setRedirect($redirectTo, 'Removed '.count($arrayIDs).' question(s)');
	}


	function cancelAddQuestion()
	{
		$thepost = JRequest::get( 'POST' );
		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editPage&cid[]='.$thepost['_question_pageID'],false);
		$this->setRedirect($redirectTo, 'Cancelled ...');
	}

	function editScale(){
			
		$scaleid = JRequest::getVar('scaleid',null);
		if($scaleid == null) JError::raiseError(500, 'scaleid parameter missing');
			
		$view = $this->getView('scaleform');
			
		if ($model = $this->getModel('scales')) $view->setModel($model, true);
		else JError::raiseError(500, 'Model not found');
			
		$view->setLayout('scaleformlayout');
		$view->displayEdit($scaleid);
	}

	function addScale()
	{
		$view = $this->getView('scaleform');
		$model = $this->getModel('scales');
		if (!$model) JError::raiseError(500, 'Model not found');
		$view->setModel($model, true);
		$view->setLayout('scaleformlayout');
		$view->displayAdd();
	}


	function saveScale()
	{
		$thepost = JRequest::get('POST',JREQUEST_ALLOWHTML|JREQUEST_ALLOWRAW);
		
		//dividing the post
		$post_scale = array();
		$post_codes = array();
		foreach ($thepost as $key=>$value)
		{
			if (strpos($key,'_scale_')!==false) $post_scale[str_replace('_scale_', '', $key)] = $value;
			else if (strpos($key,'_code_')!==false)
			{
				$codeid = str_replace('_code_', '', $key);
				$codeid = intval(substr($codeid, 0, strpos($codeid, '_')));
				//build new array for code if it does not yet exist in the item array
				if (!key_exists($codeid, $post_codes)) $post_codes[$codeid]=array();
				$newkey = str_replace('_code_'.$codeid.'_', '', $key);
				$post_codes[$codeid][$newkey]=$value;
			}
		}
		
		//save scale itself
		$modelscales = $this->getModel('scales');
		$scaleid = $modelscales->saveScale($post_scale);

		//save codes
		$modelscales = $this->getModel('scales');
		foreach ($post_codes as $post_code) $codeid = $modelscales->saveCode($post_code);
		
		//delete codes
		$codedeleteids = JRequest::getVar('codedelete', null, 'default', 'array' );
		if ($codedeleteids!==null) foreach ($codedeleteids as $codedeleteid) $modelscales->deleteCode($codedeleteid);
		
		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editScale&scaleid='.$scaleid,false);
		$this->setRedirect($redirectTo, 'Scale saved!');
	}

	function removeScale()
	{
		$scaleIDs = JRequest::getVar('scaledelid', null, 'default', 'array' );
		if($scaleIDs === null) JError::raiseError(500, 'scaledelid parameter missing');
			
		$model = $this->getModel('scales');
		$cntremoved = 0;
		foreach ($scaleIDs as $scaleID)
		{
			if (!$model->checkScaleUsed($scaleID))
			{
				$model->deleteScale($scaleID);
				$cntremoved++;
			}
			else JFactory::getApplication()->enqueueMessage("ERROR: Cannot remove a predefined scale that is in use.");
		}
			
		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=display',false);
		$this->setRedirect($redirectTo, 'Removed '.$cntremoved.' scale(s)');
	}

	function cancelAddScale()
	{
		$question = JRequest::get( 'POST' );
		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=display',false);
		$this->setRedirect($redirectTo, 'Cancelled ...');
	}

	function addUsergroup()
	{
		$projectID = JRequest::getVar('ID');
		if($projectID === null) JError::raiseError(500, 'project id parameter missing');
		$view = $this->getView('usergroupform');
		$model = $this->getModel('usergroups');
		if (!$model) JError::raiseError(500, 'Model not found');
		$view->setModel($model, true);
		$view->setLayout('usergroupformlayout');
		$view->displayAdd($projectID);
	}
	
	function copyUsergroup()
	{
		$thepost=JRequest::get('POST' );
		
		$model = $this->getModel('usergroups');
		$model->copyUsergroup($thepost['ID'],$thepost['selUsergroup']);
		
		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editProject&cid[]='.$thepost['ID'],false);
		$this->setRedirect($redirectTo, 'Usergroup copied ...');
	}
	
	function editUsergroup()
	{
		$usergroupids = JRequest::getVar('cid', null, 'default', 'array' );
		if($usergroupids === null) JError::raiseError(500, 'cid parameter missing');
		$usergroupID = (int)$usergroupids[0];
	
		$view = $this->getView('usergroupform');
			
		if (($model = $this->getModel('usergroups')) && ($modelprojects = $this->getModel('projects')))
		{
			$view->setModel($model, true);
			$view->setModel($modelprojects, false);
		}
		else JError::raiseError(500, 'Model not found');
			
		$view->setLayout('usergroupformlayout');
		$view->displayEdit($usergroupID);
	}
	
	function cancelAddUsergroup()
	{
		$usergroup = JRequest::get( 'POST' );
		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editProject&cid[]='.$usergroup['projectID'],false);
		$this->setRedirect($redirectTo, 'Cancelled ...');
	}
	
	function removeUsergroups()
	{
		$thepost = JRequest::get( 'POST' );
	
		$ugIDs = JRequest::getVar('ugchk', null, 'default', 'array' );
		if($ugIDs === null) JError::raiseError(500, 'ugchk parameter missing');
		//delete Anonymous or Joomla group from list if necessary
		$errorID = false;
		if (array_search(-1,$ugIDs,false)!==false)
		{
			$errorID = true;
			unset($ugIDs[array_search(-1,$ugIDs,false)]);
		}
		if (array_search(0,$ugIDs,false)!==false)
		{
			$errorID = true;
			unset($ugIDs[array_search(0,$ugIDs,false)]);
		}
		
		if (count($ugIDs)>0)
		{
			$model = $this->getModel('usergroups');
			$model->removeUsergroups($ugIDs,(isset($thepost['deleteanswers'])?true:false));
		}
	
		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editProject&cid[]='.$thepost['ID'],false);
		$this->setRedirect($redirectTo, ($errorID?'Cannot delete user groups Anonymous or Joomla. ':'').'Removed '.count($ugIDs).' usergroup(s)');
	}
	
	function saveUsergroup()
	{
		$usergroup = JRequest::get( 'POST' );
			
		$model = $this->getModel('usergroups');
		$usergroupid = $model->saveUsergroup($usergroup);
	
		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editUsergroup&cid[]='.$usergroupid,false);
		$this->setRedirect($redirectTo, 'Usergroup saved!');
	}
	
	function newToken()
	{
		$usergroupID = JRequest::getVar('ID');
		if($usergroupID === null) JError::raiseError(500, 'user group id parameter missing');
		$view = $this->getView('tokenform');
		$model = $this->getModel('tokens');
		if (!$model) JError::raiseError(500, 'Model not found');
		$view->setModel($model, true);
		$view->setLayout('tokenformlayout');
		$view->displayAdd($usergroupID);
	}
	
	function editToken()
	{
		$tokenids = JRequest::getVar('cid', null, 'default', 'array' );
		if($tokenids === null) JError::raiseError(500, 'cid parameter missing');
		$tokenID = (int)$tokenids[0];
		
		$view = $this->getView('tokenform');
			
		if ($model = $this->getModel('tokens')) $view->setModel($model, true);
		else JError::raiseError(500, 'Model not found');
			
		$view->setLayout('tokenformlayout');
		$view->displayEdit($tokenID);
	}

	function saveToken()
	{
		$thepost = JRequest::get( 'POST' );
			
		$model = $this->getModel('tokens');
		$tokenid = $model->saveToken($thepost);
	
		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editToken&cid[]='.$tokenid,false);
		$this->setRedirect($redirectTo, 'Token saved!');
	}
	
	function cancelAddToken()
	{
		$token = JRequest::get( 'POST' );
		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editUsergroup&cid[]='.$token['usergroupID'],false);
		$this->setRedirect($redirectTo, 'Cancelled ...');
	}
	
	function addRandomTokens()
	{
		$usergroup = JRequest::get( 'POST' );
		$model = $this->getModel('usergroups');
		$model->addTokens($usergroup);
		
		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editUsergroup&cid[]='.$usergroup['ID'],false);
		$this->setRedirect($redirectTo, 'Tokens added!');
	}
	
	function uploadTokens()
	{
		$usergroup = JRequest::get('POST');
		$projectID = $usergroup['projectID'];
		if ($_FILES["file"]["error"] > 0)
		{
			$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editUsergroup&cid[]='.$usergroup['ID'],false);
			$this->setRedirect($redirectTo, 'Upload failed!');
		}
		else
		{
			$filename = "importtokens_proj$projectID"."_".time().".".end(explode(".", $_FILES['file']['name']));
			$file = fopen(JPATH_COMPONENT.DS."userdata".DS.$filename,"w") or JError::raiseError(500, 'Error creating file');
			$fileContent = file_get_contents($_FILES['file']['tmp_name']);
			fwrite($file,$fileContent);
			fclose($file);
			
			# ATTENTION: uses PHPExcel by Mark Baker - many thanks!!!
			$objPHPExcel = PHPExcel_IOFactory::load(JPATH_COMPONENT.DS."userdata".DS.$filename);
			#FIXME should allow other sheets as well
			$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
			$view = $this->getView('uploadtokensform');
			$view->setLayout('uploadtokensformlayout');
			$model = $this->getModel('usergroups');
			$view->setModel($model,true);
			$view->display($usergroup, $sheetData, $filename);
		}
	}
	
	function removeTokens()
	{
		$thepost = JRequest::get( 'POST' );
		
		$tokenIDs = JRequest::getVar('cid', null, 'default', 'array' );
		if($tokenIDs === null) JError::raiseError(500, 'cid parameter missing');
			
		$model = $this->getModel('tokens');
		$model->removeTokens($tokenIDs,(isset($thepost['deleteanswers'])?true:false));
		
		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editUsergroup&cid[]='.$thepost['ID'],false);
		$this->setRedirect($redirectTo, 'Removed '.count($tokenIDs).' token(s)');
	}
	
	function insertUploadedTokens()
	{
		$thepost = JRequest::get('POST');
		$model = $this->getModel('usergroups');
		
		# ATTENTION: uses PHPExcel by Mark Baker - many thanks!!!
		$objPHPExcel = PHPExcel_IOFactory::load(JPATH_COMPONENT.DS."userdata".DS.$thepost['filename']);
		#FIXME should allow other sheets as well
		$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
		$i = $thepost['columnnames']==1?2:1;
		while (array_key_exists($i, $sheetData))
		{
			$token_token = ($thepost['columntoken']!=-1?$sheetData[$i][$thepost['columntoken']]:"");
			$token_email = ($thepost['columnemail']!=-1?$sheetData[$i][$thepost['columnemail']]:"");
			$token_name = ($thepost['columnusername']!=-1?$sheetData[$i][$thepost['columnusername']]:"");
			$token_firstname = ($thepost['columnfirstname']!=-1?$sheetData[$i][$thepost['columnfirstname']]:"");
			$token_salutation = ($thepost['columnsalutation']!=-1?$sheetData[$i][$thepost['columnsalutation']]:"");
			$token_note = ($thepost['columnnote']!=-1?$sheetData[$i][$thepost['columnnote']]:"");
			$token = array(
						"token"=>$token_token,
						"email"=>$token_email,
						"name"=>$token_name,
						"firstname"=>$token_firstname,
						"salutation"=>$token_salutation,
						"note"=>$token_note,
			);
			$model->addToken($thepost['usergroupID'],$token);
			$i++;
		}
		
		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editUsergroup&cid[]='.$thepost['usergroupID'],false);
		$this->setRedirect($redirectTo, (count($sheetData)-($thepost['columnnames']==1?1:0))." tokens uploaded");		
	}
	
	function cancelInsertUploadedTokens()
	{
		$usergroup = JRequest::get('POST');
		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editUsergroup&cid[]='.$usergroup['usergroupID'],false);
		$this->setRedirect($redirectTo, 'Cancelled ...');		
	}
	
	function sendEmails()
	{
		$thepost = JRequest::get('POST');
		$tokenmodel = $this->getModel('tokens');
		$app = JFactory::getApplication();
		
		$tokenIDs = JRequest::getVar('cid', null, 'default', 'array' );
		if($tokenIDs === null) JError::raiseError(500, 'cid parameter missing');
		$numemails=0;
		
		foreach ($tokenIDs as $onetokenID)
		{
			$token = $tokenmodel->getToken($onetokenID);
			//check for valid email before sending
			if ($token->email == null || strlen($token->email)==0)
			{
				$app->enqueueMessage("ERROR: Token '".$token->token."' is missing an email address");
				continue;
			}
			if (filter_var($token->email,FILTER_VALIDATE_EMAIL)===false)
			{
				$app->enqueueMessage("ERROR: Token '".$token->token."' has invalid email address: '".$token->email."'");
				continue;
			}
			//now try to send message
			$mailer = JFactory::getMailer();
			$mailer->setSender($thepost['email_from']);
			$mailer->addRecipient($token->email);
			if (isset($thepost['email_copy'])) $mailer->addBCC($thepost['email_from']);
			$mailer->setSubject($thepost['email_subject']);
			$mailer->isHTML(false);
			//prepare the text by replacing placeholders
			$mailtext = $thepost['email_text'];
			$mailtext = str_replace("#token#", $token->token, $mailtext);
			$mailtext = str_replace("#email#", $token->email, $mailtext);
			$mailtext = str_replace("#name#", $token->name, $mailtext);
			$mailtext = str_replace("#first#", $token->firstname, $mailtext);
			$mailtext = str_replace("#salutation#", $token->salutation, $mailtext);
			$mailtext = str_replace("#note#", $token->note, $mailtext);
			//prepare the text by replacing the link placeholder
			if ($thepost['email_linkbase']==-1) $mailtext = str_replace("#link#", JURI::root()."?option=com_jcq&projectID=".$thepost['projectID']."&token=".$token->token, $mailtext);
			else $mailtext = str_replace("#link#", JURI::root()."?Itemid=".$thepost['email_linkbase']."&option=com_jcq&projectID=".$thepost['projectID']."&token=".$token->token, $mailtext);
			//send the email				
			$mailer->setBody($mailtext);
			$send = $mailer->Send();
			if ( $send !== true ) {
				$app->enqueueMessage('ERROR sending email: ' . $send->__toString());
			} else $numemails++;
		} 
		
		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editUsergroup&cid[]='.$thepost['ID'],false);
		$this->setRedirect($redirectTo, $numemails.' emails sent ...');
	}
	
	function saveData()
	{
		$project = JRequest::get( 'POST' );
		$projectid = $project['ID'];
		$usergroupids = JRequest::getVar('ugchk', null, 'default', 'array' );
		$includeuserdata = isset($_POST['includeuserdata']);
		
		$model = $this->getModel('projects');
		$filename = $model->saveData($projectid,$usergroupids,$includeuserdata);

		$app = JFactory::getApplication();
		$app->enqueueMessage("Data saved ...");
		$this->editProject($projectid, $filename);
	}
}