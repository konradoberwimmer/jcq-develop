<?php
defined('_JEXEC') or die('Restricted access'); 

if (isset($this->download) && $this->download!==null)
{ 
	#FIXME unsave path!
?>
<script type="text/javascript">
<!--
addOnload(openDownload('components/com_jcq/userdata','<?php echo($this->download); ?>'));
//-->
</script>
<?php 
}
if (isset($this->previewSession)) { ?>
<script type="text/javascript">
<!--
addOnload(openPreview('<?php echo(JURI::root()."index.php?option=com_jcq"); ?>','<?php echo($this->project->ID); ?>','<?php echo($this->previewSession); ?>'));
//-->
</script>
<?php 
}
if ($this->project->ID > 0) { ?>
<p class="breadcrumbs">
<a href="<?php echo JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=display',false);?>">JCQ</a>&nbsp;&gt;&nbsp;
<a href="<?php echo JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=editProject&cid[]='.$this->project->ID,false);?>">Project &quot;<?php echo $this->project->name; ?>&quot;</a>
</p>
<?php } ?>
<form action="index.php" method="POST" name="adminForm" id="adminForm">
       <fieldset>
             <legend>Project definition:</legend>
             <table class="settings">
                    <tr><td>Name</td><td><input type="text" name="name" id="name" size="32" maxlength="250" value="<?php echo $this->project->name; ?>" /></td>
                    <?php if ($this->project->ID > 0) { ?>
                    	<td><input type="button" name="previewProject" value="Preview" onclick="submitbutton('previewProject')"/></td>
                    <?php } ?>
                    </tr>
                    <tr><td>CSS-File (optional)</td><td><input type="text" name="cssfile" id="cssfile" size="32" maxlength="250" value="<?php echo $this->project->cssfile; ?>" />
                    <?php if ($this->project->ID > 0 && strlen($this->project->cssfile)>0) { ?>
                    	<input style="width: 150px;" type="button" name="editCSS" value="Edit CSS" onclick="submitbutton('editCSS')"/>
                    <?php } ?>
                    </td></tr>
                    <tr><td>Description</td><td><input type="text" name="description" id="description" size="64" maxlength="5000" value="<?php echo $this->project->description; ?>" /></td></tr>
                    <tr><td>Anonymous answers</td><td><input type="checkbox" name="anonymous" id="anonymous" value="1" <?php if ($this->project->anonymous > 0) echo("checked"); ?>/></td></tr>
             		<tr><td>Allow Joomla users</td><td><input type="checkbox" name="allowjoomla" id="allowjoomla" value="1" <?php if ($this->project->allowjoomla > 0) echo("checked"); ?>/></td></tr>
             		<tr><td>Multiple answers</td><td><input type="checkbox" name="multiple" id="multiple" value="1" <?php if ($this->project->multiple > 0) echo("checked"); ?>/></td></tr>
             </table>
       </fieldset>

       <input type="hidden" name="option" value="<?php echo JRequest::getVar( 'option' );?>"/>
       <input type="hidden" name="ID" value="<?php echo $this->project->ID; ?>"/>      
       <input type="hidden" name="task" value=""/>
       <input type="hidden" name="previewProject" id="previewProject" value="0"/>
       <input type="hidden" id="editProgramfile" name="editProgramfile" value=""/>
<?php if ($this->project->ID > 0) { ?>
<fieldset>
             <legend>Pages:</legend>
		<div style="margin-top: 5px;">Project has <?php echo count($this->pages); ?> page(s).</div>
       <table class="list">
             <thead>
                    <tr>
                           <th width="10"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->pages); ?>)" /></th>
                           <th>Order</th>
                           <th>Name</th>
                           <th>Questions</th>
                           <th>Filter</th>
                           <th>Final page</th>
                    </tr>               
             </thead>
             <tbody>
                    <?php
                    $k = 0;
                    $i = 0;
                    foreach ($this->pages as $row)
                    {
                           if (!$row->isFinal) $checked = JHTML::_('grid.id', $i, $row->ID);
                           else $checked="";
                           $link = JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=editPage&cid[]='. $row->ID,false);
                    ?>
                    <tr>
                            <td><?php echo $checked; ?></td>
                            <td>
                            <?php if (!$row->isFinal) { ?>
                            <input type="text" class="orderfield" id="<?php echo("page".$row->ID."ord"); ?>" name="pageord[]" value="<?php echo $row->ord; ?>"/>
                            <?php } ?>
                            <input type="hidden" name="pageids[]" value="<?php echo $row->ID; ?>"/></td>
                            <td><a href="<?php echo $link;?>"><?php echo $row->name;?></a></td>
                            <td><?php echo $this->questioncounts[$i]; ?></td>
                            <td><input type="checkbox" <?php if ($row->filter!=null && strlen($row->filter)>0) echo("checked"); ?> disabled/></td>
                            <td><input type="checkbox" <?php if ($row->isFinal) echo("checked"); ?> disabled/></td>
                    </tr>
                    <?php
                    	$k = 1 - $k;
                    	$i++;
                    }
                    ?>
             </tbody>
       </table>
       <input type="hidden" name="boxchecked" value="0"/>    
       <input type="hidden" name="hidemainmenu" value="0"/>  
</fieldset>

<fieldset>
	<legend>User groups:</legend>
    <table id="usergrouptable" class="list">
    	<thead>
    	<tr>
    		<th></th>
    		<th>Name</th>
    		<th>Value</th>
    		<th>User count</th>
    		<th># begun</th>
    		<th># first</th>
    		<th># finished</th>
    		<th>Average time</th>
    		<th>Medium time</th>
    		<th>Last finished</th>
    	</tr>
    	</thead>
    	<tbody>
    	<!-- Anonymous answers -->
    	<tr>
    		<?php 
    			$usersbegun = $this->usergroups->getParticipantsBegun($this->project->ID,-1); 
    			$usersfinished = $this->usergroups->getParticipantsFinished($this->project->ID,-1); 
    		?>
    		<td><input type="checkbox" name="ugchk[]" value="-1"/></td>
    		<td>Anonymous</td>
    		<td>-1</td>
    		<td>-</td>
    		<td><?php echo $usersbegun;?></td>
    		<td><?php if ($usersbegun>0) echo $this->usergroups->getParticipantsFinishedFirst($this->project->ID,-1);?></td>
    		<td><?php if ($usersbegun>0) echo $this->usergroups->getParticipantsFinished($this->project->ID,-1);?></td>
    		<td><?php if ($usersbegun>0 && $usersfinished>0) echo (number_format($this->usergroups->getAverageDurationFinished($this->project->ID,-1)/60.0,2)." min.");?></td>
    		<td><?php if ($usersbegun>0 && $usersfinished>0) echo (number_format($this->usergroups->getMediumDurationFinished($this->project->ID,-1)/60.0,2)." min.");?></td>
    		<td><?php if ($usersbegun>0 && $usersfinished>0) echo (strftime("%a, %d.%m.%Y, %H:%M:%S",$this->usergroups->getLastFinished($this->project->ID,-1)));?></td>
    	</tr>
    	<!-- Joomla users -->
    	<tr>
    		<?php 
    			$usersbegun = $this->usergroups->getParticipantsBegun($this->project->ID,0); 
    			$usersfinished = $this->usergroups->getParticipantsFinished($this->project->ID,0); 
    		?>
       		<td><input type="checkbox" name="ugchk[]" value="0"/></td>
    		<td>Joomla</td>
    		<td>0</td>
    		<td><?php #TODO echo user count ?></td>
    		<td><?php echo $usersbegun;?></td>
    		<td><?php if ($usersbegun>0) echo $this->usergroups->getParticipantsFinishedFirst($this->project->ID,0);?></td>
    		<td><?php if ($usersbegun>0) echo $this->usergroups->getParticipantsFinished($this->project->ID,0);?></td>
    		<td><?php if ($usersbegun>0 && $usersfinished>0) echo (number_format($this->usergroups->getAverageDurationFinished($this->project->ID,0)/60.0,2)." min.");?></td>
    		<td><?php if ($usersbegun>0 && $usersfinished>0) echo (number_format($this->usergroups->getMediumDurationFinished($this->project->ID,0)/60.0,2)." min.");?></td>
    		<td><?php if ($usersbegun>0 && $usersfinished>0) echo (strftime("%a, %d.%m.%Y, %H:%M:%S",$this->usergroups->getLastFinished($this->project->ID,0)));?></td>
    	</tr>
    	<!-- User groups -->
    	<?php 
    		$projusergroups = $this->usergroups->getUsergroups($this->project->ID);
    		if ($projusergroups!==null)
    		{
    			foreach ($projusergroups as $usergroup)
    			{
    				$usersbegun = $this->usergroups->getParticipantsBegun($this->project->ID,$usergroup->ID); 
    				$usersfinished = $this->usergroups->getParticipantsFinished($this->project->ID,$usergroup->ID); 
		?>
    	<tr>	
    		<td><input type="checkbox" name="ugchk[]" value="<?php echo $usergroup->ID; ?>"/></td>
    		<td><a href="<?php echo JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=editUsergroup&cid[]='.$usergroup->ID,false);?>"><?php echo $usergroup->name; ?></a></td>
    		<td><?php echo $usergroup->val; ?></td>
    		<td><?php echo $this->usergroups->getTokenCount($usergroup->ID); ?></td>
    		<td><?php echo $usersbegun;?></td>
    		<td><?php if ($usersbegun>0) echo $this->usergroups->getParticipantsFinishedFirst($this->project->ID,$usergroup->ID);?></td>
    		<td><?php if ($usersbegun>0) echo $this->usergroups->getParticipantsFinished($this->project->ID,$usergroup->ID);?></td>
    		<td><?php if ($usersbegun>0 && $usersfinished>0) echo (number_format($this->usergroups->getAverageDurationFinished($this->project->ID,$usergroup->ID)/60.0,2)." min.");?></td>
    		<td><?php if ($usersbegun>0 && $usersfinished>0) echo (number_format($this->usergroups->getMediumDurationFinished($this->project->ID,$usergroup->ID)/60.0,2)." min.");?></td>
    		<td><?php if ($usersbegun>0 && $usersfinished>0) echo (strftime("%a, %d.%m.%Y, %H:%M:%S",$this->usergroups->getLastFinished($this->project->ID,$usergroup->ID)));?></td>
    	</tr>
    				<?php 
    			}
    		}
    	?>
    	<!-- All -->
    	<tr style="border-top: 1px solid black;">
    		<?php 
    			$usersbegun = $this->usergroups->getParticipantsBegun($this->project->ID); 
    			$usersfinished = $this->usergroups->getParticipantsFinished($this->project->ID); 
    		?>
       		<td><input type="checkbox" id="ug_all" name="ug_all" onclick="changeAllUGstate()"/></td>
    		<td>All</td>
    		<td>-</td>
    		<td>-</td>
    		<td><?php echo $usersbegun?></td>
    		<td><?php if ($usersbegun>0) echo $this->usergroups->getParticipantsFinishedFirst($this->project->ID);?></td>
    		<td><?php if ($usersbegun>0) echo $this->usergroups->getParticipantsFinished($this->project->ID);?></td>
    		<td><?php if ($usersbegun>0 && $usersfinished>0) echo (number_format($this->usergroups->getAverageDurationFinished($this->project->ID)/60.0,2)." min.");?></td>
    		<td><?php if ($usersbegun>0 && $usersfinished>0) echo (number_format($this->usergroups->getMediumDurationFinished($this->project->ID)/60.0,2)." min.");?></td>
    		<td><?php if ($usersbegun>0 && $usersfinished>0) echo (strftime("%a, %d.%m.%Y, %H:%M:%S",$this->usergroups->getLastFinished($this->project->ID)));?></td>
    	</tr>
    	</tbody>
    </table>
    <table>
    	<tr><td><input style="width: 150px;" type="button" name="addUsergroup" value="Add user group" onclick="submitbutton('addUsergroup')"/></td><td/></tr>
    	<tr style="border-top: 1px solid grey;"><td><input style="width: 150px;" type="button" name="copyUsergroup" value="Copy user group" onclick="submitbutton('copyUsergroup')"/></td><td><label for="selUsergroup">User group:&nbsp;</label>
		<select name="selUsergroup" id="selUsergroup">
			<option value="-1">--- SELECT ---</option>
			<?php 
			$allusergroups=$this->usergroups->getAllUsergroupsList();
			if ($allusergroups!==null)
			{
				foreach($allusergroups as $oneug) echo("<option value=\"".$oneug->ug_ID."\">".$oneug->ug_name." (Project '".$oneug->proj_name."')</option>");
			}
			?>
		</select>
		</td></tr>
		<tr style="border-top: 1px solid grey;"><td><input type="button" style="width:150px;" name="removeUsergroups" value="Remove usergroup(s)" onclick="submitbutton('removeUsergroups')"/></td><td><label><input type="checkbox" name="deleteanswers" value="1"/> Delete answers of usergroup(s)</label></td></tr>
       	<tr style="border-top: 1px solid grey;"><td><input style="width: 150px;" type="button" name="saveData" value="Save data" onclick="submitbutton('saveData')"/></td><td><label><input type="checkbox" name="includeuserdata" value="1" checked/> Include information on user</label></td></tr>
    </table>
</fieldset>

<fieldset>
	<legend>Program files:</legend>
	<?php 
		if ($this->programfiles == null || count($this->programfiles)==0) echo("No program files for this project.<br/>");
	?>
	<table id="programfilestable" class="list">
		<?php 
		if ($this->programfiles != null && count($this->programfiles)>0)
		{
			?>
			<thead><tr><th>Order</th><th>Filename</th><th>Delete</th><th/></tr></thead>
			<?php 
		}
		foreach ($this->programfiles as $programfile)
		{
			?>
			<tbody>
			<tr>
			<td><input type="hidden" name="programfileids[]" value="<?php echo $programfile->ID; ?>"/>
			<input type="text" id="<?php echo("programfile".$programfile->ID."ord"); ?>" name="programfileord[]" value="<?php echo $programfile->ord; ?>"/>
			</td>
			<td><input type="text" id="<?php echo("programfile".$programfile->ID."filename"); ?>" name="programfilename[]" value="<?php echo $programfile->filename; ?>"/></td>
			<td><input type="checkbox" id="<?php echo("programfile".$programfile->ID."delete"); ?>" name="programfiledelete[]" value="<?php echo $programfile->ID; ?>"/></td>
			<td><input type="button" name="<?php echo("programfile".$programfile->ID."edit"); ?>" value="Edit ..." onclick="editProgramFile(<?php echo $programfile->ID; ?>)"/></td>
			</tr>
			<tbody>
			<?php 	
		}
		?>
	</table>
	<input type="button" value="Add program file" onclick="addProgramfile()"/><br/>
	<br/>
	(TIP: Use getStoredValue(varname) in user defined functions to get participants answers.)
</fieldset>
<?php } ?>
</form>