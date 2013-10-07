<?php
defined('_JEXEC') or die('Restricted access'); ?>
<p class="breadcrumbs">
<a href="<?php echo JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=display',false);?>">JCQ</a>&nbsp;&gt;&nbsp;
<a href="<?php echo JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=editProject&cid[]='.$this->project->ID,false);?>">Project &quot;<?php echo $this->project->name; ?>&quot;</a>&nbsp;&gt;&nbsp;
<a href="<?php echo JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=editCSS&ID='.$this->project->ID,false);?>">Edit CSS file &quot;<?php echo $this->project->cssfile ?>&quot;</a>
</p>
<form action="index.php" method="POST" name="adminForm">
<?php 
$filecontent = file_get_contents(JPATH_COMPONENT_SITE.DS.'usercode'.DS.$this->project->cssfile);
?>
       <p>Contents of file &quot;<?php echo($this->project->cssfile); ?>&quot;:</p>
       <textarea name="filecontent" rows="35" style="width: 100%;"><?php echo $filecontent; ?></textarea>
       <input type="hidden" name="option" value="<?php echo JRequest::getVar( 'option' );?>"/>
       <input type="hidden" name="task" value=""/> 
       <input type="hidden" name="hidemainmenu" value="0"/>
       <input type="hidden" name="ID" value="<?php echo $this->project->ID;; ?>"/>
</form>