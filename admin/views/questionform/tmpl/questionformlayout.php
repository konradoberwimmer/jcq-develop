<?php
defined('_JEXEC') or die('Restricted access');

$isnewquestion = ($this->question->ID == 0);

if (!$isnewquestion) { ?>
<p class="breadcrumbs">
<a href="<?php echo JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=display',false);?>">JCQ</a>&nbsp;&gt;&nbsp;
<a href="<?php echo JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=editProject&cid[]='.$this->project->ID,false);?>">Project &quot;<?php echo $this->project->name; ?>&quot;</a>&nbsp;&gt;&nbsp;
<a href="<?php echo JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=editPage&cid[]='.$this->page->ID,false);?>">Page &quot;<?php echo $this->page->name; ?>&quot;</a>&nbsp;&gt;&nbsp;
<a href="<?php echo JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=editQuestion&cid[]='.$this->question->ID,false);?>">Question &quot;<?php echo $this->question->name; ?>&quot;</a>
</p>
<?php }
?>

<form action="index.php" method="POST" name="adminForm" id="adminForm" onsubmit="return false;">
	<input type="hidden" name="_question_ID" value="<?php echo $this->question->ID; ?>"/>
	<input type="hidden" name="_question_pageID" value="<?php echo $this->question->pageID; ?>"/>
	<input type="hidden" name="_question_ord" value="<?php echo $this->question->ord; ?>"/>
<?php
////////////////////////////////////////////////////////////////////////////////
// GENERAL FIELDS
////////////////////////////////////////////////////////////////////////////////
if (!$isnewquestion) {?>
	<input type="hidden" name="_question_questtype" value="<?php echo $this->question->questtype; ?>"/>
<?php
if (in_array($this->question->questtype, array(SINGLECHOICE, TEXTFIELD))) {
?>
	<input type="hidden" name="_item_<?php echo($this->mainitem->ID); ?>_ID" value="<?php echo $this->mainitem->ID; ?>"/>
	<input type="hidden" name="_item_<?php echo($this->mainitem->ID); ?>_questionID" value="<?php echo $this->question->ID; ?>"/>
	<input type="hidden" name="_item_<?php echo($this->mainitem->ID); ?>_datatype" value="<?php echo $this->mainitem->datatype; ?>"/>
<?php 
}
if (in_array($this->question->questtype, array(TEXTANDHTML))) {
?>
	<input type="hidden" name="_question_mandatory" value="0"/>
<?php 
}
}
?>
	<fieldset><legend>Question definition:</legend>
	<table class="settings">
<?php
////////////////////////////////////////////////////////////////////////////////
// QUESTION DEFINITION
////////////////////////////////////////////////////////////////////////////////
if ($isnewquestion) {?>
		<tr><td>Type</td><td><select name="_question_questtype">
<?php 
$questtypes = $this->getModel()->getQuestionTypes();
foreach ($questtypes as $typeid=>$typename)
{
	if ($this->page->isFinal && $typeid!=TEXTANDHTML) continue;
    echo '<option value="'.$typeid.'">'.$typename.'</option>';
}
?>
			</select></td></tr>
<?php
}
?>
		<tr><td>Name</td><td><input type="text" name="_question_name" size="32" maxlength="250" value="<?php echo $this->question->name; ?>"
<?php
if ($isnewquestion) {  
	echo(' autofocus="autofocus"');
    echo(' onkeyup="if (event.keyCode == 13) { submitbutton(\'saveQuestion\'); } return false;"');
}
?>
        	/></td></tr>
<?php
if (!$isnewquestion) {?>
		<tr><td>Type</td><td>
<?php
$questtypes = $this->getModel()->getQuestionTypes();
echo $questtypes[$this->question->questtype];
?>
			</td></tr>
<?php
if (in_array($this->question->questtype, array(TEXTFIELD))) {
?>
		<tr><td>Data type</td><td>
			<select name="_item_<?php echo($this->mainitem->ID); ?>_datatype">
<?php 
$datatypes = $this->getModel()->getDataTypes();
for ($i=1; $i<=3; $i++) echo '<option value="'.$i.'" '.($this->mainitem->datatype==$i?'selected':'').'>'.$datatypes[$i].'</option>';
?>
            </select>
            </td></tr>
<?php 
}
if (in_array($this->question->questtype, array(SINGLECHOICE, TEXTFIELD))) {
?>
		<tr><td>Variable name</td><td><input type="text" name="_item_<?php echo($this->mainitem->ID); ?>_varname" size="32" maxlength="250" value="<?php echo $this->mainitem->varname; ?>" /></td></tr>            
<?php
}
?>
		<tr><td><?php echo(!in_array($this->question->questtype, array(TEXTANDHTML))?"Question text":"Text and HTML code"); ?></td><td><textarea name="_question_text" cols="64" rows="3"><?php echo $this->question->text; ?></textarea></tr>
<?php
if (!in_array($this->question->questtype, array(TEXTANDHTML))) {
?>
		<tr><td>Advise text</td><td><textarea name="_question_advise" cols="64" rows="3"><?php echo $this->question->advise; ?></textarea></tr>            
<?php
}
if (in_array($this->question->questtype, array(TEXTFIELD))) {
?>
		<tr><td>Text before and after the field</td><td><input type="text" name="_item_<?php echo($this->mainitem->ID); ?>_prepost" value="<?php echo $this->mainitem->prepost; ?>"/></tr>
<?php 
}
if (in_array($this->question->questtype, array(SINGLECHOICE, TEXTFIELD))) {
?>
		<tr><td>Mandatory</td><td><select name="_item_<?php echo($this->mainitem->ID); ?>_mandatory">
<?php 
$mandatorytypes = $this->getModel()->getMandatoryTypes();
foreach ($mandatorytypes as $typeid=>$typename) echo '<option value="'.$typeid.'" '.($this->mainitem->mandatory==$typeid?'selected':'').'>'.$typename.'</option>';
?>
			</select></td></tr>
<?php
}
if (in_array($this->question->questtype, array(MULTICHOICE))) {
?>
		<tr><td>Mandatory</td><td><select name="_question_mandatory">
<?php 
$mandatorytypes = $this->getModel()->getMandatoryTypes();
foreach ($mandatorytypes as $typeid=>$typename) echo '<option value="'.$typeid.'" '.($this->question->mandatory==$typeid?'selected':'').'>'.$typename.'</option>';
?>
			</select></td></tr>
<?php
}
}
?>
	</table>
	</fieldset>

<?php
////////////////////////////////////////////////////////////////////////////////
// LAYOUT OPTIONS
////////////////////////////////////////////////////////////////////////////////
if (!$isnewquestion) {?>
	<fieldset><legend>Layout:</legend>
	<table class="settings">
<?php
if (!in_array($this->question->questtype, array(TEXTFIELD, TEXTANDHTML))) {
?>
		<tr><td>Alternate background</td><td><input type="checkbox" name="_question_alternate_bg" value="1" <?php if ($this->question->alternate_bg > 0) echo("checked"); ?>/></td></tr>
<?php
}
if (in_array($this->question->questtype, array(TEXTFIELD))) {
?>
		<tr><td>Width of textfield</td><td><input type="text" name="_question_width_items" size="8" maxlength="250" value="<?php echo $this->question->width_items; ?>" /></td></tr>
		<tr><td>Number of rows</td><td><input type="text" name="_item_<?php echo($this->mainitem->ID); ?>_rows" size="8" maxlength="250" value="<?php echo $this->mainitem->rows; ?>" /></td></tr>
<?php
}
if (in_array($this->question->questtype, array(MATRIX_LEFT, MATRIX_BOTH, MULTISCALE))) {
	?>
		<tr><td>Width of question</td><td><input type="text" name="_question_width_question" size="8" maxlength="250" value="<?php echo $this->question->width_question; ?>" /></td></tr>
		<tr><td>Width of items</td><td><input type="text" name="_question_width_items" size="8" maxlength="250" value="<?php echo $this->question->width_items; ?>" /></td></tr>
		<tr><td>Width of scale options</td><td><input type="text" name="_question_width_scale" size="8" maxlength="250" value="<?php echo $this->question->width_scale; ?>" /></td></tr>
<?php
}
?>  
    </table>
    </fieldset>
<?php
}
?>
<?php
////////////////////////////////////////////////////////////////////////////////
// SCALE DEFINITION
////////////////////////////////////////////////////////////////////////////////
if (!$isnewquestion && in_array($this->question->questtype, array(SINGLECHOICE,MATRIX_LEFT,MATRIX_BOTH))) {
?>
	<fieldset><legend>Scale:</legend>
    <input type="hidden" id="tmpcodeid" name="tmpcodeid" value="-1"/>
    <input type="hidden" id="scaleid" name="scaleid" value="<?php echo $this->mainscale->ID; ?>"/>
    <table class="list">
    	<thead><tr>
			<th>Order</th>
			<th>Value</th>
			<th>Label</th>
			<th>Missing value</th>
			<th>Delete</th>
<?php 
if (in_array($this->question->questtype, array(SINGLECHOICE))) { ?>
			<th>Add/remove text field</th>
<?php 
} else {
?>
			<th/>
<?php 
}
?>
		</tr></thead>
		<tbody id="listscalebody">
<?php 
foreach ($this->mainscalecodes as $row) { 
?>
		<tr>
			<td><input type="text" name="_code_<?php echo $row->ID; ?>_ord" value="<?php echo $row->ord; ?>" class="orderfield"/>
				<input type="hidden" name="_code_<?php echo $row->ID; ?>_ID" value="<?php echo $row->ID; ?>"/>
				<input type="hidden" name="_code_<?php echo $row->ID; ?>_scaleID" value="<?php echo $this->mainscale->ID; ?>"/></td>
			<td><input type="text" name="_code_<?php echo $row->ID; ?>_code" value="<?php echo $row->code; ?>" class="valuefield"/></td>       
			<td><input type="text" name="_code_<?php echo $row->ID; ?>_label" value="<?php echo $row->label; ?>" size="128"/></td>
			<td><input type="checkbox" name="_code_<?php echo $row->ID; ?>_missval" value="1" <?php if ($row->missval) echo("checked"); ?> /></td>            
			<td><input type="checkbox" name="codedelete[]" value="<?php echo $row->ID; ?>"/></td>
<?php 
if (in_array($this->question->questtype, array(SINGLECHOICE))) { ?>
			<td><input type="checkbox" name="codeaddrmtf[]" value="<?php echo $row->ID; ?>"/></td>
<?php 
} else {
?>
			<td/>
<?php 
}
?>
		</tr>
<?php 
if (in_array($this->question->questtype, array(SINGLECHOICE))) {
$bindeditems = $this->getModel('scales')->getCodebindedItems($row->ID);
if ($bindeditems!=null && count($bindeditems)>0)
{
	$bindeditem = $bindeditems[0];
?>
		<tr>
			<td colspan="2" align="right">Including textfield:</td>
			<td><input type="hidden" name="_item_<?php echo($bindeditem->ID); ?>_ID" value="<?php echo($bindeditem->ID); ?>"/>
				<input type="hidden" name="_item_<?php echo($bindeditem->ID); ?>_questionID" value="<?php echo($bindeditem->questionID); ?>"/>
				<input type="hidden" name="_item_<?php echo($bindeditem->ID); ?>_bindingType" value="<?php echo($bindeditem->bindingType); ?>"/>
				<input type="hidden" name="_item_<?php echo($bindeditem->ID); ?>_bindingID" value="<?php echo($bindeditem->bindingID); ?>"/>
				<input type="hidden" name="_item_<?php echo($bindeditem->ID); ?>_ord" value="<?php echo($bindeditem->ord); ?>"/>
				<input type="hidden" name="_item_<?php echo($bindeditem->ID); ?>_mandatory" value="<?php echo($bindeditem->mandatory); ?>"/>
				<table>
					<tr><td>Variable name</td><td><input type="text" name="_item_<?php echo($bindeditem->ID); ?>_varname" value="<?php echo($bindeditem->varname); ?>"/></td>
						<td>Width</td><td><input class="widthfield" type="text" name="_item_<?php echo($bindeditem->ID); ?>_width_left" value="<?php echo($bindeditem->width_left); ?>" /></td>
					</tr>
					<tr><td>Data type</td><td>
						<select name="_item_<?php echo($bindeditem->ID); ?>_datatype">
<?php 
$datatypes = $this->getModel()->getDataTypes();
for ($i=1; $i<=3; $i++) echo '<option value="'.$i.'" '.($bindeditem->datatype==$i?'selected':'').'>'.$datatypes[$i].'</option>';
?>
						</select></td>
						<td>Rows</td><td><input class="widthfield" type="text" name="_item_<?php echo($bindeditem->ID); ?>_rows" value="<?php echo $bindeditem->rows; ?>" /></td>
					</tr>
					<tr><td>Text surrounding</td><td><input type="text" name="_item_<?php echo($bindeditem->ID); ?>_prepost" value="<?php echo $bindeditem->prepost; ?>"/>
						<td>Add linebreak</td><td><input type="checkbox" name="_item_<?php echo($bindeditem->ID); ?>_linebreak" value="1" <?php if($bindeditem->linebreak) echo "checked"; ?>/></td>
					</tr>
				</table>
			</td>
			<td/>
			<td/>
			<td/>
		</tr>
<?php 
}
}
}
?>
		</tbody>
	</table>
	<input type="button" value="Add Code" onclick="addCode()">
	</fieldset>
<?php
}
////////////////////////////////////////////////////////////////////////////////
// MULTIPLE SCALES
////////////////////////////////////////////////////////////////////////////////
if (!$isnewquestion && in_array($this->question->questtype, array(MULTICHOICE,MATRIX_LEFT,MATRIX_BOTH,MULTISCALE))) {
?>
	<fieldset><legend>Scale(s):</legend>
		<input type="hidden" id="numpredefscales" name="numpredefscales" value="<?php echo(count($this->predefscales)); ?>"/>
		<input type="hidden" id="tmpscaleid" name="tmpscaleid" value="-1"/>
		<!-- for the javascript part a template of the select box is created -->
		<select id="scaleidTEMPLATE" name="" style="display: none;">
<?php 
for ($j=0; $j<count($this->predefscales); $j++) echo '<option value="'.$this->predefscales[$j]->ID.'">'.$this->predefscales[$j]->name.'</option>';
?>
		</select>
		<table class="list">
		<thead><tr>
			<th>Order</th>
			<th>Scale</th>
			<th>Mandatory</th>
			<th>Delete</th>
		</tr></thead>
		<tbody id="listscalesbody">
<?php
foreach ($this->scales as $row) { 
?>
		<tr>
			<td><input type="text" name="_scale_<?php echo($row->ID); ?>_ord" value="<?php echo($row->ord); ?>" class="orderfield"/>
				<input type="hidden" name="_scale_<?php echo($row->ID); ?>_ID" value="<?php echo($row->ID); ?>"/></td>
			<td><?php for ($j=0; $j<count($this->predefscales); $j++) if ($row->ID==$this->predefscales[$j]->ID) echo($this->predefscales[$j]->name); ?></td>
			<td><input type="checkbox" name="_scale_<?php echo($row->ID); ?>_mandatory" value="1" <?php if ($row->mandatory) echo("checked"); ?>/></td>
			<td><input type="checkbox" name="scaledelete[]" value="<?php echo($row->ID); ?>"/></td>
		</tr>
<?php 
} 
?>
		</tbody>
		</table>
		<input type="button" value="Add Scale" onclick="javascript: addScale()">
	</fieldset>
<?php
}
////////////////////////////////////////////////////////////////////////////////
// ITEM DEFINITION
////////////////////////////////////////////////////////////////////////////////
if (!$isnewquestion && in_array($this->question->questtype, array(MULTICHOICE,MATRIX_LEFT,MATRIX_BOTH,MULTISCALE))) {
?>
	<fieldset><legend>Items:</legend>
		<input type="hidden" id="tmpitemid" name="tmpitemid" value="-1"/>
		<input type="hidden" id="questionid" name="questionid" value="<?php echo $this->question->ID; ?>"/>
		<table class="list">
		<thead><tr>
			<th>Order</th>
<?php 
if (in_array($this->question->questtype, array(MATRIX_BOTH))) {
?>
			<th>Text left</th>
			<th>Text right</th>
<?php 
} else {
?>			
			<th>Item text</th>
<?php 
}
?>
			<th>Variable name</th>
<?php 
if (in_array($this->question->questtype, array(MATRIX_LEFT,MATRIX_BOTH,MULTISCALE))) {
?>
			<th>Mandatory</th>
<?php 
} else {
?>
			<th></th>
<?php 
}
?>
			<th>Delete</th>
			<th>Add/remove text field</th>
		</tr></thead>
		<tbody id="listitembody">
<?php
$i = 0;
foreach ($this->items as $row) {
	if ($row->bindingType != "QUESTION") continue;
?>
		<tr>
			<td><input type="text" name="_item_<?php echo $row->ID; ?>_ord" value="<?php echo $row->ord; ?>" class="orderfield"/>
				<input type="hidden" name="_item_<?php echo $row->ID; ?>_ID" value="<?php echo $row->ID; ?>"/>
				<input type="hidden" name="_item_<?php echo $row->ID; ?>_questionID" value="<?php echo $row->questionID; ?>"/>
				<input type="hidden" name="_item_<?php echo $row->ID; ?>_datatype" value="1"/>
				<input type="hidden" name="_item_<?php echo $row->ID; ?>_mandatory" value="0"/>
				<input type="hidden" name="_item_<?php echo $row->ID; ?>_bindingtype" value="QUESTION"/></td>
			<td><input type="text" name="_item_<?php echo $row->ID; ?>_textleft" value="<?php echo (str_replace("\"", "&quot;", $row->textleft)); ?>" size="<?php echo($this->question->questtype==MATRIX_BOTH?"64":"128"); ?>" /></td>
<?php 
if (in_array($this->question->questtype, array(MATRIX_BOTH))) {
?>
			<td><input type="text" name="_item_<?php echo $row->ID; ?>_textright" value="<?php echo (str_replace("\"", "&quot;", $row->textright)); ?>" size="64" /></td>
<?php 
}
?>			
			<td><input type="text" name="_item_<?php echo $row->ID; ?>_varname" value="<?php echo $row->varname; ?>"/></td>
<?php 
if (in_array($this->question->questtype, array(MATRIX_LEFT,MATRIX_BOTH,MULTISCALE))) {
?>
			<td><input type="checkbox" name="_item_<?php echo $row->ID; ?>_mandatory" value="1" <?php if ($row->mandatory) echo("checked"); ?> /></td>        
<?php 
} else {
?>
			<td/>
<?php 
}
?>           
			<td><input type="checkbox" name="itemdelete[]" value="<?php echo $row->ID; ?>"/></td>
			<td><input type="checkbox" name="itemaddrmtf[]" value="<?php echo $row->ID; ?>"/></td>
		</tr>
<?php
$bindeditems = $this->getModel('items')->getItembindedItems($row->ID);
if ($bindeditems!==null && count($bindeditems)>0) {
	$bindeditem = $bindeditems[0];
?>
		<tr>
			<td align="right">Including textfield:</td>
			<td><input type="hidden" name="_item_<?php echo($bindeditem->ID); ?>_ID" value="<?php echo($bindeditem->ID); ?>"/>
				<input type="hidden" name="_item_<?php echo($bindeditem->ID); ?>_questionID" value="<?php echo($bindeditem->questionID); ?>"/>
				<input type="hidden" name="_item_<?php echo($bindeditem->ID); ?>_bindingType" value="<?php echo($bindeditem->bindingType); ?>"/>
				<input type="hidden" name="_item_<?php echo($bindeditem->ID); ?>_bindingID" value="<?php echo($bindeditem->bindingID); ?>"/>
				<input type="hidden" name="_item_<?php echo($bindeditem->ID); ?>_ord" value="<?php echo($bindeditem->ord); ?>"/>
				<input type="hidden" name="_item_<?php echo($bindeditem->ID); ?>_mandatory" value="<?php echo($bindeditem->mandatory); ?>"/>
				<table>
				<tr><td>Variable name</td><td><input type="text" name="_item_<?php echo($bindeditem->ID); ?>_varname" value="<?php echo($bindeditem->varname); ?>"/></td>
					<td>Width</td><td><input class="widthfield" type="text" name="_item_<?php echo($bindeditem->ID); ?>_width_left" value="<?php echo($bindeditem->width_left); ?>" /></td>
				</tr>
				<tr><td>Data type</td><td>
					<select name="_item_<?php echo($bindeditem->ID); ?>_datatype">
<?php 
$datatypes = $this->getModel()->getDataTypes();
for ($i=1; $i<=3; $i++)	echo '<option value="'.$i.'" '.($bindeditem->datatype==$i?'selected':'').'>'.$datatypes[$i].'</option>';
?>
					</select></td>
					<td>Rows</td><td><input class="widthfield" type="text" name="_item_<?php echo($bindeditem->ID); ?>_rows" value="<?php echo $bindeditem->rows; ?>" /></td>
				</tr>
				<tr><td>Text surrounding</td><td><input type="text" name="_item_<?php echo($bindeditem->ID); ?>_prepost" value="<?php echo $bindeditem->prepost; ?>"/>
					<td>Add linebreak</td><td><input type="checkbox" name="_item_<?php echo($bindeditem->ID); ?>_linebreak" value="1" <?php if($bindeditem->linebreak) echo "checked"; ?>/></td>
				</tr>
				</table>
			</td>
			<td/>
			<td/>
			<td/>
		</tr>
<?php 
	}
$i++;
}
                    ?>
		</tbody>
		</table>
		<input type="button" value="Add Item" onclick="addItem(<?php echo($this->question->questtype==MATRIX_BOTH?"true":"false"); ?>)">
	</fieldset>
<?php 
}
////////////////////////////////////////////////////////////////////////////////
// FORM BASICS
////////////////////////////////////////////////////////////////////////////////
?>
	<input type="hidden" name="option" value="<?php echo JRequest::getVar( 'option' );?>"/>
	<input type="hidden" name="task" value=""/>
</form>
