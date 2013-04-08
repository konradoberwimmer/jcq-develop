<?php
defined('_JEXEC') or die('Restricted access'); ?>
<?php if ($this->project->ID > 0) { ?>
<p class="breadcrumbs">
<a href="<?php echo JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=display',false);?>">JCQ</a>&nbsp;&gt;&nbsp;
<a href="<?php echo JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=editProject&cid[]='.$this->project->ID,false);?>">Project &quot;<?php echo $this->project->name; ?>&quot;</a>
</p>
<?php } ?>
<form action="index.php" method="POST" name="adminForm" id="adminForm">
       <fieldset>
             <legend>Project definition:</legend>
             <table>
                    <tr><td>Name</td><td><input type="text" name="name" id="name" size="32" maxlength="250" value="<?php echo $this->project->name; ?>" /></td></tr>
                    <tr><td>CSS-File (optional)</td><td><input type="text" name="cssfile" id="cssfile" size="32" maxlength="250" value="<?php echo $this->project->cssfile; ?>" /></td></tr>
                    <tr><td>Description</td><td><input type="text" name="description" id="description" size="64" maxlength="5000" value="<?php echo $this->project->description; ?>" /></td></tr>
                    <tr><td>Anonymous answers</td><td><input type="checkbox" name="anonymous" id="anonymous" value="1" <?php if ($this->project->anonymous > 0) echo("checked"); ?>/></td></tr>
             		<tr><td>Multiple answers</td><td><input type="checkbox" name="multiple" id="multiple" value="1" <?php if ($this->project->multiple > 0) echo("checked"); ?>/></td></tr>
             </table>
       </fieldset>

       <input type="hidden" name="option" value="<?php echo JRequest::getVar( 'option' );?>"/>
       <input type="hidden" name="ID" value="<?php echo $this->project->ID; ?>"/>      
       <input type="hidden" name="task" value=""/>
<?php if ($this->project->ID > 0) { ?>
<fieldset>
             <legend>Pages:</legend>
Project has <?php echo count($this->pages); ?> page(s).
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
                            <input type="text" id="<?php echo("page".$row->ID."ord"); ?>" name="pageord[]" value="<?php echo $row->ord; ?>"/>
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
	<legend>Program files:</legend>
	<?php 
		if ($this->imports == null || count($this->imports)==0) echo("No files imported.<br/>");
	?>
	<table id="importstable">
		<?php 
		if ($this->imports != null && count($this->imports)>0)
		{
			?>
			<tr><th>Order</th><th>Filename</th><th>Delete</th></tr>
			<?php 
		}
		foreach ($this->imports as $import)
		{
			?>
			<tr>
			<td><input type="hidden" name="importids[]" value="<?php echo $import->ID; ?>"/>
			<input type="text" id="<?php echo("import".$import->ID."ord"); ?>" name="importord[]" value="<?php echo $import->ord; ?>"/>
			</td>
			<td><input type="text" id="<?php echo("import".$import->ID."filename"); ?>" name="importfilename[]" value="<?php echo $import->filename; ?>"/></td>
			<td><input type="checkbox" id="<?php echo("code".$import->ID."delete"); ?>" name="importdelete[]" value="<?php echo $import->ID; ?>"/></td>
			</tr>
			<?php 	
		}
		?>
	</table>
	<input type="button" name="addImport" value="Add program file" onclick="addImportfile()"/><br/>
	<br/>
	(TIP: Use getStoredValue(varname) in user defined functions to get participants answers.)
</fieldset>
<fieldset>
	<legend>Participants:</legend>
	<table>
		<tr>
			<td># begun questionnaire:</td>
			<td><?php echo($this->participants->getParticipantsBegun($this->project->ID)); ?></td>
			<td><input type="button" value="Save data" onclick="javascript: submitbutton('saveData')"/></td>
		</tr>
		<tr>
			<td># finished first page:</td>
			<td><?php echo($this->participants->getParticipantsFinishedFirst($this->project->ID)); ?></td>
			<td>
			<?php
				if ($this->download!==null)
				{
					?>
					<a href="<?php echo(JURI::base().'components/com_jcq/userdata/'.$this->download); ?>" target="blank">Download data</a>
					<?php 
				}
			?>
			</td>
		</tr>
		<tr>
			<td># finished all:</td>
			<td><?php echo($this->participants->getParticipantsFinished($this->project->ID)); ?></td>
		</tr>
		<tr>
			<td>Average duration to finish (minutes):</td>
			<td><?php echo(number_format($this->participants->getAverageDurationFinished($this->project->ID)/60.0,1)); ?></td>
		</tr>
		<tr>
			<td>Medium duration to finish (minutes):</td>
			<td><?php echo(number_format($this->participants->getMediumDurationFinished($this->project->ID)/60.0,1)); ?></td>
		</tr>
		<tr>
			<td>Last begun questionnaire:</td>
			<td><?php if ($this->participants->getLastBegun($this->project->ID)!=null) echo(strftime("%d.%m.%Y, %H:%M:%S",$this->participants->getLastBegun($this->project->ID))); ?></td>
		</tr>
		<tr>
			<td>Last finished questionnaire:</td>
			<td><?php if ($this->participants->getLastFinished($this->project->ID)!=null) echo(strftime("%d.%m.%Y, %H:%M:%S",$this->participants->getLastFinished($this->project->ID))); ?></td>
		</tr>
	</table>
</fieldset>
<?php } ?>
</form>