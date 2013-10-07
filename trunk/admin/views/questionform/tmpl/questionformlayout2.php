<?php
defined('_JEXEC') or die('Restricted access'); ?>

<form action="index.php" method="POST" name="adminForm" id="adminForm">
       <fieldset>
             <legend>Question definition:</legend>
             <table class="settings">
                    <tr><td>Name</td><td><input type="text" name="name" id="name" size="32" maxlength="250" value="<?php echo $this->question->name; ?>" /></td></tr>
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
                    	echo $datatypes[$this->question->datatype];
                    ?>
                    </td></tr>
                    <tr><td>Question text</td><td><textarea name="text" id="text" cols="64" rows="3"><?php echo $this->question->text; ?></textarea></tr>
                    <tr><td>Advise text</td><td><textarea name="advise" id="advise" cols="64" rows="3"><?php echo $this->question->advise; ?></textarea></tr>
                    <tr><td>Mandatory</td>
                    <td><select name="mandatory" id="mandatory">
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
                    <tr><td>Alternate background</td><td><input type="checkbox" name="alternate_bg" id="alternate_bg" value="1" <?php if ($this->question->alternate_bg > 0) echo("checked"); ?>/></td></tr>
             </table>
       </fieldset>
       
       <fieldset>
             <legend>Items:</legend>
             <input type="hidden" name="itemspresent" value="1"/>
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
						<td><input class="orderfield" type="text" id="<?php echo("item".$row->ID."ord"); ?>" name="itemord[]" value="<?php echo $row->ord; ?>"/>
                            <input type="hidden" name="itemids[]" value="<?php echo $row->ID; ?>"/></td>
                        <td><input type="text" id="<?php echo("item".$row->ID."textleft"); ?>" name="itemtextleft[]" value="<?php echo (str_replace("\"", "&quot;", $row->textleft)); ?>" size="128" /></td>
                        <td><input type="text" id="<?php echo("item".$row->ID."varname"); ?>" name="itemvarname[]" value="<?php echo $row->varname; ?>"/></td>
                        <td></td>            
                        <td><input type="checkbox" id="<?php echo("item".$row->ID."delete"); ?>" name="itemdelete[]" value="<?php echo $row->ID; ?>"/></td>
                        <td><input type="checkbox" id="<?php echo("item".$row->ID."addrmtf"); ?>" name="itemaddrmtf[]" value="<?php echo $row->ID; ?>"/></td>
                    </tr>
                    <?php
                    $bindeditems = $this->getModel('items')->getItembindedItems($row->ID);
                    if ($bindeditems!=null && count($bindeditems)>0)
                    {
                    	?>
                    <tr>
                    	<td align="right">Including textfield:</td>
                    	<td><input type="hidden" name="<?php echo("item".$row->ID."tfID"); ?>" value="<?php echo $bindeditems[0]->ID; ?>"/>
                    		<table>
                    		<tr><td>Variable name</td><td><input type="text" name="<?php echo("item".$row->ID."tfvarname"); ?>" value="<?php echo $bindeditems[0]->varname; ?>"/></td>
                    			<td>Width</td><td><input class="widthfield" type="text" name="<?php echo("item".$row->ID."tfwidthleft"); ?>" value="<?php echo $bindeditems[0]->width_left; ?>" /></td>
                    		</tr>
                    		<tr><td>Data type</td><td>
                    			<select name="<?php echo("item".$row->ID."tfdatatype"); ?>">
			                    <?php 
			                    	$datatypes = $this->getModel()->getDataTypes();
			                    	for ($i=1; $i<=3; $i++)
			                    	{
			                    		echo '<option value="'.$i.'" '.($bindeditems[0]->datatype==$i?'selected':'').'>'.$datatypes[$i].'</option>';
			                    	}
			                    ?>
                    			</select></td>
                    		    <td>Rows</td><td><input class="widthfield" type="text" name="<?php echo("item".$row->ID."tfrows"); ?>" value="<?php echo $bindeditems[0]->rows; ?>" /></td>
              				</tr>
                    		<tr><td>Text surrounding</td><td><input type="text" name="<?php echo("item".$row->ID."tfprepost"); ?>" value="<?php echo $bindeditems[0]->prepost; ?>"/>
                    			<td>Add linebreak</td><td><input type="checkbox" name="<?php echo("item".$row->ID."tflinebreak"); ?>" value="1" <?php if($bindeditems[0]->linebreak) echo "checked"; ?>/></td>
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
       <input type="hidden" name="ID" value="<?php echo $this->question->ID; ?>"/>
       <input type="hidden" name="questtype" value="<?php echo $this->question->questtype; ?>"/>
       <input type="hidden" name="datatype" value="<?php echo $this->question->datatype; ?>"/>
       <input type="hidden" name="pageID" value="<?php echo $this->question->pageID; ?>"/>
       <input type="hidden" name="task" value=""/>
</form>
