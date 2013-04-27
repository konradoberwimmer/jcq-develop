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
<form action="index.php" method="POST" name="adminForm" id="adminForm">
       <fieldset>
             <legend>Page definition:</legend>
             <table>
                    <tr>
                    	<td>Name</td>
                    	<td><input type="text" name="name" id="name" size="32" maxlength="250" value="<?php echo $this->page->name; ?>" /></td>
                    	<?php if ($this->page->ID > 0) { ?>
                    	<td><input type="button" name="previewPage" value="Preview" onclick="submitbutton('previewPage')"/></td>
                    	<?php } ?>
                    </tr>
             </table>
       </fieldset>

       <input type="hidden" name="option" value="<?php echo JRequest::getVar( 'option' );?>"/>
       <input type="hidden" name="previewPage" id="previewPage" value="0"/>
       <input type="hidden" name="ID" value="<?php echo $this->page->ID; ?>"/>
       <input type="hidden" name="isFinal" value="<?php echo $this->page->isFinal; ?>"/>
       <input type="hidden" name="projectID" value="<?php echo $this->page->projectID; ?>"/>
       <input type="hidden" name="task" value=""/>
<?php if ($this->page->ID > 0 && $this->questions != null) { ?>
<fieldset>
             <legend>Questions:</legend>
Page has <?php echo count($this->questions); ?> question(s).
       <table class="list">
             <thead>
                    <tr>
                           <th width="10"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->questions); ?>)" /></th>
                           <th>Order</th>
                           <th>Name</th>
                           <th>Type</th>
                           <th>Mandatory</th>
                           <th>Variable Name</th>
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
                            <td><input type="text" id="<?php echo("question".$row->ID."ord"); ?>" name="questionord[]" value="<?php echo $row->ord; ?>"/>
                            <input type="hidden" name="questionids[]" value="<?php echo $row->ID; ?>"/></td>
                            <td><a href="<?php echo $link;?>"><?php echo $row->name;?></a></td>
                            <td><?php 
                            $questtypes = $this->getModel('questions')->getQuestionTypes();
                            if (isset($questtypes[$row->questtype])) echo $questtypes[$row->questtype];
                            else echo "Error: unknown question type";
                            ?>
                            </td>
                            <td><input type="checkbox" <?php if ($row->mandatory) echo("checked"); ?> disabled/></td>
                            <td><?php echo strlen($row->varname)>0?$row->varname:"[None or defined by items]"; ?></td>
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
<?php } else if ($this->page->ID > 0) { ?>
<fieldset>
             <legend>Questions:</legend>
Page has no questions.
</fieldset>
<?php } 
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
		<tr style="border-bottom: 1px solid grey; border-top: 1px solid grey;">
			<td><?php if ($i>0) echo("OR"); ?></td>
			<td>
			<?php 
				$conjugations = explode("&", $disjunctions[$i]);
				$cntconjugations = count($conjugations);
			?>
				<table>
				<?php 
				for ($j=0; $j<$cntconjugations; $j++)
				{
				?>
					<tr>
					<td><?php if ($j>0) echo("AND"); ?></td>
					<td>
						<select id="variable<?php echo($i."_".$j); ?>" name="variable<?php echo($i."_".$j); ?>" style="width: 250px;">
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
						<select id="operator<?php echo($i."_".$j); ?>" name="operator<?php echo($i."_".$j); ?>">
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
						<input type="text" size="8" id="val<?php echo($i."_".$j); ?>" name="val<?php echo($i."_".$j); ?>" value="<?php echo($val); ?>"/>
					</td>
					<td>
					<?php if ($j>0) { ?>
					<input type="button" id="removeANDbutton<?php echo($i."_".$j); ?>" value="Remove AND" onclick="removeConjugation(<?php echo($i.",".$j); ?>)"/></td>
					<?php } ?>
					</tr>
				<?php 
				}
				?>
				</table>
				<input type="hidden" id="cntconjugations<?php echo($i); ?>" name="cntconjugations<?php echo($i); ?>" value="<?php echo($cntconjugations); ?>"/>
				<input type="button" id="addANDbutton<?php echo($i); ?>" value="Add AND" onclick="addConjugation(<?php echo($i); ?>)"/>
			</td>
			<td><input type="button" id="removeORbutton<?php echo($i); ?>" value="Remove OR" onclick="removeDisjunction(<?php echo($i); ?>)"/></td>
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
