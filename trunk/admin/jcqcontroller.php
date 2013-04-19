<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');
jimport('joomla.filesystem.file');

function jtableToXmlWithoutIDs ($jtable, $xmldoc, $xmlnode)
{
	foreach (get_object_vars($jtable) as $k => $v) //ok, here a scripting language makes everything simpler
	{
		if (is_array($v) or is_object($v) or $v === NULL) continue;
		if ($k[0] == '_') continue;
		if (strpos($k,"ID")!==false) continue;
		$element = $xmldoc->createElement($k);
		$cdata = $xmldoc->createCDATASection($v);
		$element->appendChild($cdata);
		$xmlnode->appendChild($element);
	}
}

function xmlToJTable ($xmlelement, $jtable)
{
	foreach (get_object_vars($jtable) as $k => $v)
	{
		if ($k[0] == '_') continue;
		if (strpos($k,"ID")!==false) continue;
		$child = $xmlelement->getElementsByTagName($k);
		if ($child->length>0)
		{
			$child=$child->item(0)->firstChild;
			$jtable->$k = $child->textContent;
		}
	}
}

class JcqController extends JController
{

	function display()
	{
		$viewName    = JRequest::getVar( 'view', 'projectlist' );
		$viewLayout  = JRequest::getVar( 'layout', 'projectlistlayout' );
		$view = & $this->getView($viewName);
			
		if ($model = & $this->getModel('projects')) $view->setModel($model, true);
		else JError::raiseError(500, 'Model projects not found');
		if ($modelscales = & $this->getModel('scales')) $view->setModel($modelscales, false);
		else JError::raiseError(500, 'Model scales not found');

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

	function editProject($projectID=null,$download=null)
	{
			
		if ($projectID===null)
		{
			$projectids = JRequest::getVar('cid', null, 'default', 'array' );
			if($projectids === null) JError::raiseError(500, 'cid parameter missing');
			$projectID = (int)$projectids[0];
		}

		$view = & $this->getView('projectform');

		if ($model = & $this->getModel('projects'))	$view->setModel($model, true);
		else JError::raiseError(500, 'Model projects not found');
		if ($modelparticipants = & $this->getModel('participants'))	$view->setModel($modelparticipants, false);
		else JError::raiseError(500, 'Model participants not found');
			
		$view->setLayout('projectformlayout');
		$view->displayEdit($projectID, $download);
	}

	function saveProject()
	{
		$project = JRequest::get( 'POST' );
			
		$model = & $this->getModel('projects');
		$projectid = $model->saveProject($project);

		//create usercode path and css-file for project if it does not yet exist
		if (!is_dir(JPATH_COMPONENT_SITE.DS.'usercode')) mkdir(JPATH_COMPONENT_SITE.DS.'usercode');
		if (isset($project['cssfile'])&&strlen($project['cssfile'])>0)
		{
			if (!file_exists(JPATH_COMPONENT_SITE.DS.'usercode'.DS.$project['cssfile']))
			{
				$filehandle = fopen(JPATH_COMPONENT_SITE.DS.'usercode'.DS.$project['cssfile'], 'w');
				fwrite($filehandle,"@CHARSET \"UTF-8\";\n");
				fclose($filehandle);
			}
		}

		//set page order if edited
		if (isset($project['pageord']))
		{
			$pagemodel = & $this->getModel('pages');
			$pagemodel->setPageOrder($project['pageids'],$project['pageord']);
		}

		//save the importss if project has any
		if (isset($project['importids']))
		{
			//has to be in this order: 1. save imports 2. delete imports; otherwise errors for missing IDs
			$importids = JRequest::getVar('importids', null, 'default', 'array' );
			$importord = JRequest::getVar('importord', null, 'default', 'array' );
			$importfilename = JRequest::getVar('importfilename', null, 'default', 'array' );
			for ($i=0;$i<count($importids);$i++)
			{
				$import = array();
				$import['ID']=$importids[$i];
				$import['ord']=$importord[$i];
				$import['filename']=$importfilename[$i];
				$import['projectID']=$project['ID'];
				$model->saveImport($import);
			}
			$importdelete = JRequest::getVar('importdelete', null, 'default', 'array' );
			if ($importdelete!=null) $model->deleteImports($importdelete);
		}

		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editProject&cid[]='.$projectid,false);
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
	
	function editImport()
	{
		$importID = JRequest::getVar('editImport', null); //Reads cid as an arra
		if ($importID === null || !is_numeric($importID)) JError::raiseError(500, 'editImport parameter missing');
		
		$view = & $this->getView('editimport');
		
		if ($model = & $this->getModel('imports') && $modelproject = & $this->getModel('projects'))
		{ 
			$view->setModel($model, true);
			$view->setModel($modelproject, false);
		}
		else JError::raiseError(500, 'Model not found');
		
		$view->setLayout('editimportlayout');
		$view->display($importID);
	}

	function saveEditedImport()
	{
		$importID = JRequest::getVar('importID', null); //Reads cid as an arra
		if ($importID === null || !is_numeric($importID)) JError::raiseError(500, 'editImport parameter missing');
				
		$model = & $this->getModel('imports');
		$model->saveEditedImport($importID,JRequest::getVar('filecontent',null,'post',null,JREQUEST_ALLOWHTML | JREQUEST_ALLOWRAW));
				
		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editImport&editImport='.$importID,false);
		$this->setRedirect($redirectTo, 'Program file saved!');	
	}
	
	function cancelEditImport()
	{
		$import = JRequest::get( 'POST' );
		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editProject&cid[]='.$import['projectID'],false);
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

	function showImportProject()
	{
		$view = & $this->getView('importproject');
		$view->setLayout('importprojectlayout');
		$view->display();
	}

	function importProject()
	{
		$importwell=true;
		//TODO has to be secured against many risks of false file
		$xmldoc = new DOMDocument('1.0', 'utf-8');
		$file = JRequest::getVar('file_upload', null, 'files', 'array');
		$src = $file['tmp_name'];
		$filehandle = fopen($src,'r');
		$content = fread($filehandle, filesize($src));
		$xmldoc = new DOMDocument('1.0', 'utf-8');
		$xmldoc->loadXML($content);
		fclose($filehandle);
		$projectDef = $xmldoc->getElementsByTagName('project');
		if ($projectDef!=null)
		{
			$projectDef=$projectDef->item(0);
			//import project definition
			$tableProject =& $this->getModel('projects')->getTable('projects');
			$tableProject->ID = 0;
			xmlToJTable($projectDef, $tableProject);
			$tableProject->store();
			$projectID = $tableProject->ID;
			//TODO add user table
			$pages = $projectDef->getElementsByTagName('page');
			foreach ($pages as $pageDef)
			{
				$tablePage =& $this->getModel('pages')->getTable('pages');
				$tablePage->ID = 0;
				$tablePage->projectID = $projectID;
				xmlToJTable($pageDef, $tablePage);
				$tablePage->store();
				$pageID = $tablePage->ID;
				//TODO alter user table
				//TODO store questions
			}
		}

		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option'));
		$this->setRedirect($redirectTo, ($importwell?'Project imported ...':'Error importing ...'));
	}

	function editPage()
	{
		$pageids = JRequest::getVar('cid', null, 'default', 'array' );
		if($pageids === null) JError::raiseError(500, 'cid parameter missing');
		$pageID = (int)$pageids[0];

		$view = & $this->getView('pageform');
			
		if ($model = & $this->getModel('pages') && $modelquestions = & $this->getModel('questions') && $modelprojects = & $this->getModel('projects'))
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
		$pageid = $model->savePage($page);

		if (isset($page['questionord']))
		{
			$questionmodel = & $this->getModel('questions');
			$questionmodel->setQuestionOrder($page['questionids'],$page['questionord']);
		}

		if ($page['previewPage']==1)
		{
			$sessionID = uniqid('', true);
			$projectID = $model->getProjectFromPage($pageid)->ID;
			$sqlnewsession = "INSERT INTO jcq_proj$projectID (preview, sessionID, curpage, timestampBegin) VALUES (1,'$sessionID',$pageid,".time().")";
			$db =& JFactory::getDBO();
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
		$modelpage = & $this->getModel('pages');
		if (!$modelpage) JError::raiseError(500, 'Model not found');
		$view->setModel($modelpage, false);
		$view->setLayout('questionformlayout');
		$view->displayAdd($pageID);
	}

	function saveQuestion()
	{
		$question = JRequest::get( 'POST' , JREQUEST_ALLOWHTML | JREQUEST_ALLOWRAW);
			
		$model = & $this->getModel('questions');
		$questionid = $model->saveQuestion($question);
		$pageid = $model->getPageFromQuestion($questionid)->ID;
		$projectid = $model->getProjectFromPage($pageid)->ID;

		$scalemodel = & $this->getModel('scales');

		//save the scale(s) if question has any
		if (isset($question['scaleID']))
		{
			//has to be in this order: 1. save codes 2. add/remove textfields 3. delete codes; otherwise errors for missing IDs
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
				//save binded items if any
				$bindeditem = JRequest::getVar('code'.$code['ID'].'tfID', null);
				if ($bindeditem!=null)
				{
					$tableItem =& $this->getModel("items")->getTable("items");
					$tableItem->load($bindeditem);
					$tableItem->varname = JRequest::getVar('code'.$code['ID'].'tfvarname', null);
					$tableItem->store();
				}
			}
			$codeaddrmtf = JRequest::getVar('codeaddrmtf', null, 'default', 'array' );
			if ($codeaddrmtf!=null) $scalemodel->addrmTextfields($codeaddrmtf,$questionid);
			$codedelete = JRequest::getVar('codedelete', null, 'default', 'array' );
			if ($codedelete!=null) $scalemodel->deleteCodes($codedelete);
		}

		//special case question type MULTISCALE: save the attached scales
		if ($question['questtype']==MULTISCALE)
		{
			$scalemodel->clearAttachedScales($question['ID']);
			$scaleids = JRequest::getVar('scaleids', null, 'default', 'array' );
			$scaleord = JRequest::getVar('scaleord', null, 'default', 'array' );
			$scaledelete = JRequest::getVar('scaledelete', null, 'default', 'array' );
			$scalemandatory = JRequest::getVar('scalemandatory', null, 'default', 'array' );
			if ($scaleids!=null)
			{
				for ($i=0;$i<count($scaleids);$i++)
				{
					//(re-)attach if scaleid is not on delete list
					if (!in_array($i, $scaledelete)) $scalemodel->addAttachedScale($question['ID'],$scaleids[$i],$scaleord[$i],in_array($scaleids[$i], $scalemandatory));
					//else destroy any userdata columns there may be
					else
					{
						$statementquery = "SELECT CONCAT('ALTER TABLE jcq_proj$projectid ', GROUP_CONCAT('DROP COLUMN ',column_name)) AS statement FROM information_schema.columns WHERE table_name = 'jcq_proj$projectid' AND column_name LIKE 'p".$pageid."q".$questionid."i%s".$scaleids[$i]."';";
						$db = JFactory::getDBO();
						$db->setQuery($statementquery);
						$sqlresult = $db->loadResult();
						if ($sqlresult!=null)
						{
							$db->setQuery($sqlresult);
							if (!$db->query()){
								$errorMessage = $this->getDBO()->getErrorMsg();
								JError::raiseError(500, 'Error altering user data table: '.$errorMessage);
							}
						}
					}
				}
			}
		}

		//save the items if question has any
		if (isset($question['itemspresent']))
		{
			$itemsmodel = & $this->getModel('items');
			//has to be in this order: 1. save items 2. delete items; otherwise errors for missing IDs
			$itemids = JRequest::getVar('itemids', null, 'default', 'array' );
			$itemord = JRequest::getVar('itemord', null, 'default', 'array' );
			$itemtextleft = JRequest::getVar('itemtextleft', null, 'default', 'array' );
			$itemtextright = JRequest::getVar('itemtextright', null, 'default', 'array' );
			$itemvarname = JRequest::getVar('itemvarname', null, 'default', 'array' );
			$itemmandatory = JRequest::getVar('itemmandatory', null, 'default', 'array' );
			for ($i=0;$i<count($itemids);$i++)
			{
				$item = array();
				$item['ID']=$itemids[$i];
				$item['ord']=$itemord[$i];
				$item['textleft']=$itemtextleft[$i];
				if ($itemtextright!=null) $item['textright']=$itemtextright[$i];
				$item['varname']=$itemvarname[$i];
				if ($itemmandatory!=null && in_array($itemids[$i],$itemmandatory)) $item['mandatory']=1;
				else $item['mandatory']=0;
				$item['questionID']=$question['ID'];
				if ($question['questtype']==MULTISCALE)
				{
					$scales=$scalemodel->getScales($question['ID']);
					$itemsmodel->saveItem($item, $scales);
				}
				else $itemsmodel->saveItem($item);
			}
			$itemdelete = JRequest::getVar('itemdelete', null, 'default', 'array' );
			if ($itemdelete!=null) $itemsmodel->deleteItems($itemdelete);
		}

		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editQuestion&cid[]='.$questionid,false);
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

	function editScale(){
			
		$scaleid = JRequest::getVar('scaleid',null);
		if($scaleid == null) JError::raiseError(500, 'scaleid parameter missing');
			
		$view = & $this->getView('scaleform');
			
		if ($model = & $this->getModel('scales')) $view->setModel($model, true);
		else JError::raiseError(500, 'Model not found');
			
		$view->setLayout('scaleformlayout');
		$view->displayEdit($scaleid);
	}

	function addScale()
	{
		$view = & $this->getView('scaleform');
		$model = & $this->getModel('scales');
		if (!$model) JError::raiseError(500, 'Model not found');
		$view->setModel($model, true);
		$view->setLayout('scaleformlayout');
		$view->displayAdd();
	}


	function saveScale()
	{
		$scale = JRequest::get( 'POST' , JREQUEST_ALLOWHTML);
			
		$scalemodel = & $this->getModel('scales');
		$scaleid = $scalemodel->saveScale($scale);

		//also save the codes if it is no new scale;
		if ($scale['ID']!=0)
		{
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
				$code['scaleID']=$scaleid;
				$scalemodel->saveCode($code);
			}
			$codedelete = JRequest::getVar('codedelete', null, 'default', 'array' );
			if ($codedelete!=null) $scalemodel->deleteCodes($codedelete);
		}

		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=editScale&scaleid='.$scaleid,false);
		$this->setRedirect($redirectTo, 'Scale saved!');
	}

	function removeScale()
	{
		$scaleIDs = JRequest::getVar('scaledelid', null, 'default', 'array' );
		if($scaleIDs === null) JError::raiseError(500, 'scaledelid parameter missing');
			
		$model = & $this->getModel('scales');
		$model->deleteScales($scaleIDs);
			
		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=display',false);
		$this->setRedirect($redirectTo, 'Removed '.count($scaleIDs).' scale(s)');
	}

	function cancelAddScale()
	{
		$question = JRequest::get( 'POST' );
		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=display',false);
		$this->setRedirect($redirectTo, 'Cancelled ...');
	}

	function saveData()
	{
		$project = JRequest::get( 'POST' );
		$projectid = $project['ID'];

		$model = & $this->getModel('projects');
		$filename = $model->saveData($projectid);

		$app = &JFactory::getApplication();
		$app->enqueueMessage("Data saved ...");
		$this->editProject($projectid, $filename);
	}
}