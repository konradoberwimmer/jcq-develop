<?php
defined('_JEXEC') or die('Restricted access'); ?>

<form action="index.php" method="POST" name="adminForm" id="adminForm">
       <fieldset>
             <legend>Question definition:</legend>
             <table class="settings">
                    <tr><td>Name</td><td><input type="text" name="_question_name" size="32" maxlength="250" value="<?php echo $this->question->name; ?>" /></td></tr>
                    <tr><td>Type</td>
                    <td>
                    <?php 
                    	$questtypes = $this->getModel()->getQuestionTypes();
                    	echo $questtypes[$this->question->questtype];
                    ?>
                    </td></tr>
                    <tr><td>Data type</td>
                    <td>
                    <?php 
                    	$datatypes = $this->getModel()->getDataTypes();
                    	echo $datatypes[1];
                    ?>
                    </td></tr>
                    <tr><td>Question text</td><td><textarea name="_question_text" cols="64" rows="3"><?php echo $this->question->text; ?></textarea></tr>
                    <tr><td>Advise text</td><td><textarea name="_question_advise" cols="64" rows="3"><?php echo $this->question->advise; ?></textarea></tr>
                    <tr><td>Mandatory</td>
                    <td><select name="_question_mandatory">
                    <?php 
                    	$mandatorytypes = $this->getModel()->getMandatoryTypes();
                    	foreach ($mandatorytypes as $typeid=>$typename)
                    	{
                    		echo '<option value="'.$typeid.'" '.($this->question->mandatory==$typeid?'selected':'').'>'.$typename.'</option>';
                    	}
                    ?>
                    </select>
                    </td></tr>
             </table>
       </fieldset>

       <fieldset>
             <legend>Layout:</legend>
             <table class="settings">
                    <tr><td>Alternate background</td><td><input type="checkbox" name="_question_alternate_bg" value="1" <?php if ($this->question->alternate_bg > 0) echo("checked"); ?>/></td></tr>
             </table>
       </fieldset>
       
       <fieldset>
             <legend>Items:</legend>
             <input type="hidden" id="tmpitemid" name="tmpitemid" value="-1"/>
             <input type="hidden" id="questionid" name="questionid" value="<?php echo $this->question->ID; ?>"/>
             <table class="list">
                    <thead>
                    <tr>
                           <th>Order</th>
                           <th>Item text</th>
                           <th>Variable name</th>
                           <th></th>
                           <th>Delete</th>
                           <th>Add/remove text field</th>
                    </tr>               
             	</thead>
             	 <tbody id="listitembody">
                    <?php
                    $k = 0;
                    $i = 0;
                    foreach ($this->items as $row)
                    {
                    	if ($row->bindingType != "QUESTION") continue;
                    ?>
                    <tr>
						<td><input type="text" name="_item_<?php echo $row->ID; ?>_ord" value="<?php echo $row->ord; ?>" class="orderfield"/>
                            <input type="hidden" name="_item_<?php echo $row->ID; ?>_ID" value="<?php echo $row->ID; ?>"/>
                            <input type="hidden" name="_item_<?php echo $row->ID; ?>_questionID" value="<?php echo $row->questionID; ?>"/>
                            <input type="hidden" name="_item_<?php echo $row->ID; ?>_datatype" value="1"/>
                            <input type="hidden" name="_item_<?php echo $row->ID; ?>_mandatory" value="0"/>
                            <input type="hidden" name="_item_<?php echo $row->ID; ?>_bindingtype" value="QUESTION"/></td>
                        <td><input type="text" name="_item_<?php echo $row->ID; ?>_textleft" value="<?php echo (str_replace("\"", "&quot;", $row->textleft)); ?>" size="128" /></td>
                        <td><input type="text" name="_item_<?php echo $row->ID; ?>_varname" value="<?php echo $row->varname; ?>"/></td>
                        <td></td>            
                        <td><input type="checkbox" name="itemdelete[]" value="<?php echo $row->ID; ?>"/></td>
                        <td><input type="checkbox" name="itemaddrmtf[]" value="<?php echo $row->ID; ?>"/></td>
                    </tr>
                    <?php
                    $bindeditems = $this->getModel('items')->getItembindedItems($row->ID);
                    if ($bindeditems!=null && count($bindeditems)>0)
                    {
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
			                    	for ($i=1; $i<=3; $i++)
			                    	{
			                    		echo '<option value="'.$i.'" '.($bindeditem->datatype==$i?'selected':'').'>'.$datatypes[$i].'</option>';
			                    	}
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
                    ?>
                    <?php
                    	$k = 1 - $k;
                    	$i++;
                    }
                    ?>
             </tbody>
             </table>
             <input type="button" value="Add Item" onclick="addItem(false)">
       </fieldset>
       
       <input type="hidden" name="option" value="<?php echo JRequest::getVar( 'option' );?>"/>
       <input type="hidden" name="_question_ID" value="<?php echo $this->question->ID; ?>"/>
       <input type="hidden" name="_question_questtype" value="<?php echo $this->question->questtype; ?>"/>
       <input type="hidden" name="_question_pageID" value="<?php echo $this->question->pageID; ?>"/>
       <input type="hidden" name="_question_ord" value="<?php echo $this->question->ord; ?>"/>
       <input type="hidden" name="task" value=""/>
</form>
