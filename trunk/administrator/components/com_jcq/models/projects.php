<?php
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');

class JcqModelProjects extends JModel {
       
       
       function getProjects()
       {
             $db = $this->getDBO();
             
             $db->setQuery('SELECT * FROM jcq_project');
             $projects = $db->loadObjectList();

             
             if ($projects === null) JError::raiseError(500, 'Error reading db');
                    
             return $projects;
       }
       
       function getProject($ID)
       {
             $query = 'SELECT * FROM jcq_project WHERE id = '.$ID;
             $db = $this->getDBO();
             $db->setQuery($query);
             $project = $db->loadObject();          
             
             if ($project === null) JError::raiseError(500, 'Project with ID: '.$ID.' not found.');
             else return $project;          
       }

       function getNewProject()
       {
             $projectTableRow =& $this->getTable('projects');
             $projectTableRow->ID = 0;
             $projectTableRow->name = '';
             return $projectTableRow;
       }
       
       function saveProject($project)
       {
           $projectTableRow =& $this->getTable(); 
           
           // Bind the form fields to the greetings table
           if (!$projectTableRow->bind($project)) JError::raiseError(500, 'Error binding data');

       		// Make sure the greetings record is valid
       		if (!$projectTableRow->check()) JError::raiseError(500, 'Invalid data');
       
       		if (!$projectTableRow->store()) 
       		{
             	$errorMessage = $projectTableRow->getError();
                JError::raiseError(500, 'Error inserting data: '.$errorMessage);                           
       		}
       }

       function deleteProjects($arrayIDs)
       {
       		$query = "DELETE FROM jcq_project WHERE ID IN (".implode(',', $arrayIDs).")";
            $db = $this->getDBO();
            $db->setQuery($query);
            if (!$db->query()){
                            $errorMessage = $this->getDBO()->getErrorMsg();
                            JError::raiseError(500, 'Error deleting projects: '.$errorMessage);  
                   }                  
         }


}

