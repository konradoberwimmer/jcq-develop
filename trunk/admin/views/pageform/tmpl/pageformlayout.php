<?php
defined('_JEXEC') or die('Restricted access'); ?>
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
                    <tr><td>Name</td><td><input type="text" name="name" id="name" size="32" maxlength="250" value="<?php echo $this->page->name; ?>" /></td></tr>
             </table>
       </fieldset>

       <input type="hidden" name="option" value="<?php echo JRequest::getVar( 'option' );?>"/>
       <input type="hidden" name="ID" value="<?php echo $this->page->ID; ?>"/>
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
if ($this->page->ID > 0)
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
	Page will NOT be shown if the following expression is true:
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
					<td>VAR</td>
					<td>OP</td>
					<td>VAL</td>
					<td>
					<?php if ($j>0) { ?>
					<input type="button" value="Remove AND" onclick="removeConjugation(<?php echo($i.",".$j); ?>)"/></td>
					<?php } ?>
					</tr>
				<?php 
				}
				?>
				</table>
				<input type="hidden" id="cntconjugations<?php echo($i); ?>" name="cntconjugations<?php echo($i); ?>" value="<?php echo($cntconjugations); ?>"/>
				<input type="button" value="Add AND" onclick="addConjugation(<?php echo($i); ?>)"/>
			</td>
			<td><input type="button" value="Remove OR" onclick="removeDisjunction(<?php echo($i); ?>)"/></td>
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
