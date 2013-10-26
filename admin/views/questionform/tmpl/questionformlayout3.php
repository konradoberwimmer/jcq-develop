<?php
defined('_JEXEC') or die('Restricted access');

$mainitem = null;
foreach ($this->items as $oneitem) if ($oneitem->bindingType=='QUESTION') {	$mainitem = $oneitem; break; }
?>

<form action="index.php" method="POST" name="adminForm" id="adminForm">
       <fieldset>
             <legend>Question definition:</legend>
             <table class="settings">
                    <tr><td>Name</td><td><input type="text" name="_question_name" size="32" maxlength="250" value="<?php echo $this->question->name; ?>" /></td></tr>
                    <tr><td>Variable name</td><td><input type="text" name="_item_<?php echo($mainitem->ID); ?>_varname" size="32" maxlength="250" value="<?php echo $mainitem->varname; ?>" /></td></tr>
                    <tr><td>Type</td>
                    <td>
                    <?php 
                    	$questtypes = $this->getModel()->getQuestionTypes();
                    	echo $questtypes[$this->question->questtype];
                    ?>
                    </td></tr>
                    <tr><td>Data type</td>
                    <td>
                    <select name="_item_<?php echo($mainitem->ID); ?>_datatype">
                    <?php 
                    	$datatypes = $this->getModel()->getDataTypes();
                    	for ($i=1; $i<=3; $i++)
                    	{
                    		echo '<option value="'.$i.'" '.($mainitem->datatype==$i?'selected':'').'>'.$datatypes[$i].'</option>';
                    	}
                    ?>
                    </select>
                    </td></tr>
                    <tr><td>Question text</td><td><textarea name="_question_text" cols="64" rows="3"><?php echo $this->question->text; ?></textarea></tr>
                    <tr><td>Advise text</td><td><textarea name="_question_advise" cols="64" rows="3"><?php echo $this->question->advise; ?></textarea></tr>
                    <tr><td>Text before and after the field</td><td><input type="text" name="_item_<?php echo($mainitem->ID); ?>_prepost" value="<?php echo $mainitem->prepost; ?>"/></tr>
                    <tr><td>Mandatory</td>
                    <td><select name="_item_<?php echo($mainitem->ID); ?>_mandatory">
                    <?php 
                    	$mandatorytypes = $this->getModel()->getMandatoryTypes();
                    	foreach ($mandatorytypes as $typeid=>$typename)
                    	{
                    		echo '<option value="'.$typeid.'" '.($mainitem->mandatory==$typeid?'selected':'').'>'.$typename.'</option>';
                    	}
                    ?>
                    </select>
                    </td></tr>
             </table>
       </fieldset>

	   <fieldset>
             <legend>Layout:</legend>
             <table class="settings">
                    <tr><td>Number of rows</td><td><input type="text" name="_item_<?php echo($mainitem->ID); ?>_rows" size="8" maxlength="250" value="<?php echo $mainitem->rows; ?>" /></td></tr>
                    <tr><td>Width of textfield</td><td><input type="text" name="_question_width_items" size="8" maxlength="250" value="<?php echo $this->question->width_items; ?>" /></td></tr>
             </table>
       </fieldset>

       <input type="hidden" name="option" value="<?php echo JRequest::getVar( 'option' );?>"/>
       <input type="hidden" name="_question_ID" value="<?php echo $this->question->ID; ?>"/>
       <input type="hidden" name="_question_questtype" value="<?php echo $this->question->questtype; ?>"/>
       <input type="hidden" name="_question_pageID" value="<?php echo $this->question->pageID; ?>"/>
       <input type="hidden" name="_question_ord" value="<?php echo $this->question->ord; ?>"/>
       <input type="hidden" name="_item_<?php echo($mainitem->ID); ?>_ID" value="<?php echo $mainitem->ID; ?>"/>
       <input type="hidden" name="_item_<?php echo($mainitem->ID); ?>_questionID" value="<?php echo $this->question->ID; ?>"/>
       <input type="hidden" name="task" value=""/>
</form>