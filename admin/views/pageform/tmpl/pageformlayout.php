<?php
defined('_JEXEC') or die('Restricted access'); ?>
<?php if (isset($this->previewSession)) { ?>
<script type="text/javascript">
<!--
addOnload(openPreview('<?php echo(JURI::root()."index.php?option=com_jcq"); ?>','<?php echo($this->project->ID); ?>','<?php echo($this->previewSession); ?>'));
//-->
</script>
<?php } ?>
<?php if ($this->page->ID > 0) { ?>
<p class="breadcrumbs">
<a href="<?php echo JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=display',false);?>">JCQ</a>&nbsp;&gt;&nbsp;
<a href="<?php echo JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=editProject&cid[]='.$this->project->ID,false);?>">Project &quot;<?php echo $this->project->name; ?>&quot;</a>&nbsp;&gt;&nbsp;
<a href="<?php echo JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=editPage&cid[]='.$this->page->ID,false);?>">Page &quot;<?php echo $this->page->name; ?>&quot;</a>
</p>
<?php } ?>
<form action="index.php" method="POST" name="adminForm" id="adminForm" onsubmit="return false;">
       <fieldset>
             <legend>Page definition:</legend>
             <table class="settings">
                    <tr>
                    	<td>Name</td>
                    	<td><input type="text" name="name" id="name" size="32" maxlength="250" value="<?php echo $this->page->name; ?>"
                    	<?php
                    		if ($this->page->ID == 0) 
                    		{  
                    			echo(' autofocus="autofocus"');
                    			echo(' onkeyup="if (event.keyCode == 13) { submitbutton(\'savePage\'); } return false;"');
                    		}
                    	?>/></td>
                    	<?php if ($this->page->ID > 0) { ?>
                    	<td><input type="button" name="previewPage" value="Preview" onclick="submitbutton('previewPage')"/></td>
                    	<?php } ?>
                    </tr>
             </table>
       </fieldset>

       <input type="hidden" name="option" value="<?php echo JRequest::getVar( 'option' );?>"/>
       <input type="hidden" name="previewPage" id="previewPage" value="0"/>
       <input type="hidden" name="ID" value="<?php echo $this->page->ID; ?>"/>
       <input type="hidden" name="ord" value="<?php echo $this->page->ord; ?>"/>
       <input type="hidden" name="isFinal" value="<?php echo $this->page->isFinal; ?>"/>
       <input type="hidden" name="projectID" value="<?php echo $this->page->projectID; ?>"/>
       <input type="hidden" name="task" value=""/>
<?php if ($this->page->ID > 0) { ?>
<fieldset>
             <legend>Questions:</legend>
<?php if ($this->questions != null) { ?>
Page has <?php echo count($this->questions); ?> question(s).
       <table class="list">
             <thead>
                    <tr>
                           <th width="10"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->questions); ?>)" /></th>
                           <th>Order</th>
                           <th>Name</th>
                           <th>Type</th>
                           <th>Mandatory</th>
                           <th>Variable Name(s)</th>
                    </tr>               
             </thead>
             <tbody>
                    <?php
                    $k = 0;
                    $i = 0;
                    foreach ($this->questions as $row){
                           $checked = JHTML::_('grid.id', $i, $row->ID);
                           $link = JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=editQuestion&cid[]='. $row->ID,false);
                    ?>
                    <tr>
                            <td><?php echo $checked; ?></td>
                            <td><input type="text" class="orderfield" id="<?php echo("question".$row->ID."ord"); ?>" name="questionord[]" value="<?php echo $row->ord; ?>"/>
                            <input type="hidden" name="questionids[]" value="<?php echo $row->ID; ?>"/></td>
                            <td><a href="<?php echo $link;?>"><?php echo $row->name;?></a></td>
                            <td><?php 
                            $questtypes = $this->getModel('questions')->getQuestionTypes();
                            if (isset($questtypes[$row->questtype])) echo $questtypes[$row->questtype];
                            else echo "Error: unknown question type";
                            ?>
                            </td>
                            <td><input type="checkbox" <?php if ($this->getModel('questions')->isMandatory($row->ID)) echo("checked"); ?> disabled/></td>
                            <td>
                            <?php 
                            	$varnames = $this->getModel('questions')->getVariableNamesString($row->ID);
                            	$printvarnames = $varnames;
                            	if (strlen($varnames)>75) $printvarnames = substr($varnames,0,72)." ...";
                            	echo("<span title='$varnames'>$printvarnames</span>");  
                            ?>
                            </td>
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
<?php } else { ?>
Page has no questions.
<?php } 
	if (!$this->page->isFinal) {
?>
    <table>
    	<tr style="border-top: 1px solid grey;"><td><input style="width: 150px;" type="button" name="copyQuestion" value="Copy question" onclick="submitbutton('copyQuestion')"/></td><td><label for="selCopyquestion">Question:&nbsp;</label>
		<select name="selCopyquestion" id="selCopyquestion">
			<option value="-1">--- SELECT ---</option>
			<?php 
			$allquestions=$this->getModel()->getAllQuestionsList();
			if ($allquestions!==null)
			{
				foreach($allquestions as $onequestion) echo("<option value=\"".$onequestion->question_ID."\">".$onequestion->question_name." (Page '".$onequestion->page_name."', Project '".$onequestion->proj_name."')</option>");
			}
			?>
		</select>
		</td></tr>
	</table>
</fieldset>
<?php
	}
}
if ($this->page->ID > 0 && !$this->page->isFinal)
{
	$cntdisjunctions = 0;
	if ($this->page->filter!=null && strlen($this->page->filter)>0)
	{
		$disjunctions = explode("|", $this->page->filter);
		$cntdisjunctions = count($disjunctions);
	}
	?>
<fieldset>
	<legend>Filter:</legend>
	Page will only be shown if the following expression is true:
	<select id="varidTEMPLATE" name="" style="display: none;">
	<?php 
		foreach ($this->variables as $variable)
		{
			$label = htmlentities($variable->varlabel,ENT_COMPAT,"UTF-8");			
			echo("<option value=\"".$variable->intvarname."\">".$variable->extvarname." (".$label.")</option>");
		}
	?>
	</select>
	<input type="hidden" id="cntdisjunctions" name="cntdisjunctions" value="<?php echo($cntdisjunctions); ?>"/>
	<table id="filtertable" style="border-collapse:collapse;">
	<?php 
	for ($i=0; $i<$cntdisjunctions; $i++)
	{
		?>
		<tr id="tableORrow<?php echo($i+1); ?>" style="border-bottom: 1px solid grey; border-top: 1px solid grey;">
			<td><?php if ($i>0) echo("OR"); ?></td>
			<td>
			<?php 
				$conjugations = explode("&", $disjunctions[$i]);
				$cntconjugations = count($conjugations);
			?>
				<table id="tableAND<?php echo($i+1); ?>">
				<?php 
				for ($j=0; $j<$cntconjugations; $j++)
				{
				?>
					<tr id="tableAND<?php echo($i+1); ?>row<?php echo($j+1); ?>">
					<td><?php if ($j>0) echo("AND"); ?></td>
					<td>
						<select name="variable<?php echo(($i+1)."_".($j+1)); ?>" style="width: 250px;">
						<?php 
							foreach ($this->variables as $variable)
							{
								$firstdelim = strpos($conjugations[$j], "$");
								$seconddelim = strpos($conjugations[$j], "$", $firstdelim+1);
								$varselected = substr($conjugations[$j], $firstdelim+1, $seconddelim-$firstdelim-1);
								echo("<option value=\"".$variable->intvarname."\"".($variable->intvarname==$varselected?" selected":"").">".$variable->extvarname." (".$variable->varlabel.")</option>");
							}
						?>
						</select>
					</td>
					<td>
						<?php 
							$opval = 0;
							if (strpos($conjugations[$j],"==")!==false) $opval=1;
							if (strpos($conjugations[$j],"!=")!==false) $opval=2;
							if (strpos($conjugations[$j],"<")!==false) $opval=3;
							if (strpos($conjugations[$j],"<=")!==false) $opval=4;
							if (strpos($conjugations[$j],">=")!==false) $opval=5;
							elseif (strpos($conjugations[$j],">")!==false) $opval=6;
						?>
						<select name="operator<?php echo(($i+1)."_".($j+1)); ?>">
							<option value="1" <?php if ($opval==1) echo("selected"); ?>>==</option>
							<option value="2" <?php if ($opval==2) echo("selected"); ?>>!=</option>
							<option value="3" <?php if ($opval==3) echo("selected"); ?>>&lt;</option>
							<option value="4" <?php if ($opval==4) echo("selected"); ?>>&lt;=</option>
							<option value="5" <?php if ($opval==5) echo("selected"); ?>>&gt;=</option>
							<option value="6" <?php if ($opval==6) echo("selected"); ?>>&gt;</option>
						</select>
					</td>
					<td>
						<?php 
							$val = "";
							if ($opval==1) $val = substr($conjugations[$j], strpos($conjugations[$j],"==")+2);
							if ($opval==2) $val = substr($conjugations[$j], strpos($conjugations[$j],"!=")+2);
							if ($opval==3) $val = substr($conjugations[$j], strpos($conjugations[$j],"<")+1);
							if ($opval==4) $val = substr($conjugations[$j], strpos($conjugations[$j],"<=")+2);
							if ($opval==5) $val = substr($conjugations[$j], strpos($conjugations[$j],">=")+2);
							if ($opval==6) $val = substr($conjugations[$j], strpos($conjugations[$j],">")+1);
							$val = str_replace(")", "", $val);
						?>
						<input type="text" size="8" name="<?php echo(($i+1)."_".($j+1)); ?>" value="<?php echo($val); ?>"/>
					</td>
					<td>
					<?php if ($j>0) { ?>
					<input type="button" value="Remove AND" onclick="removeConjugation(<?php echo(($i+1).",".($j+1)); ?>)"/></td>
					<?php } ?>
					</tr>
				<?php 
				}
				?>
				</table>
				<input type="hidden" id="cntconjugations<?php echo($i+1); ?>" name="cntconjugations<?php echo($i+1); ?>" value="<?php echo($cntconjugations); ?>"/>
				<input type="button" value="Add AND" onclick="addConjugation(<?php echo($i+1); ?>)"/>
			</td>
			<td><input type="button" value="Remove OR" onclick="removeDisjunction(<?php echo($i+1); ?>)"/></td>
		</tr>
		<?php 
	}
	?>
	</table>
	<input type="button" value="Add OR" onclick="addDisjunction()"/><br/>
</fieldset>
	<?php 
}
?>
</form>
