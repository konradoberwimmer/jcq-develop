<?php
defined('_JEXEC') or die('Restricted access'); ?>
<p class="breadcrumbs">
<a href="<?php echo JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=display',false);?>">JCQ</a>&nbsp;&gt;&nbsp;
<a href="<?php echo JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=editProject&cid[]='.$this->project->ID,false);?>">Project &quot;<?php echo $this->project->name; ?>&quot;</a>&nbsp;&gt;&nbsp;
<a href="<?php echo JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=editImport&editImport='.$this->importID,false);?>">Program file &quot;<?php echo $this->import->filename ?>&quot;</a>
</p>
<form action="index.php" method="POST" name="adminForm">
<?php 
if (!file_exists(JPATH_COMPONENT_SITE.DS.'usercode'.DS.$this->import->filename))
{
	$file = fopen(JPATH_COMPONENT_SITE.DS.'usercode'.DS.$this->import->filename,"w");
	fwrite($file, "<?php \n");
	echo("<p style=\"border: 1px solid red; color: red;\">File \"".$this->import->filename."\" did not exist in usercode folder and was created.</p>");
	fclose($file);
}
$filecontent = file_get_contents(JPATH_COMPONENT_SITE.DS.'usercode'.DS.$this->import->filename);
?>
       <p>Contents of file &quot;<?php echo($this->import->filename); ?>&quot;:</p>
       <textarea name="filecontent" rows="35" style="width: 100%;"><?php echo $filecontent; ?></textarea>
       <input type="hidden" name="option" value="<?php echo JRequest::getVar( 'option' );?>"/>
       <input type="hidden" name="task" value=""/> 
       <input type="hidden" name="hidemainmenu" value="0"/>
       <input type="hidden" name="projectID" value="<?php echo $this->import->projectID;; ?>"/>  
       <input type="hidden" name="importID" value="<?php echo $this->importID; ?>"/>  
</form>