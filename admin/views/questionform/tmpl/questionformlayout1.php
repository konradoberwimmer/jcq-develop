<?php
defined('_JEXEC') or die('Restricted access'); ?>

<form action="index.php" method="POST" name="adminForm" id="adminForm">
       <fieldset>
             <legend>Question definition:</legend>
             <table class="settings">
                    <tr><td>Name</td><td><input type="text" name="name" id="name" size="32" maxlength="250" value="<?php echo $this->question->name; ?>" /></td></tr>
                    <tr><td>Variable name</td><td><input type="text" name="varname" id="varname" size="32" maxlength="250" value="<?php echo $this->question->varname; ?>" /></td></tr>
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
             <legend>Scale:</legend>
             <input type="hidden" name="scaleID" value="<?php echo $this->scale->ID; ?>"/>
             <table class="list">
                    <thead>
                    <tr>
                           <th>Order</th>
                           <th>Value</th>
                           <th>Label</th>
                           <th>Missing value</th>
                           <th>Delete</th>
                           <th>Add/remove text field</th>
                    </tr>               
             </thead>
             <tbody id="listscalebody">
                    <?php
                    $k = 0;
                    $i = 0;
                    foreach ($this->codes as $row){
                    ?>
                    <tr>
						<td><input class="orderfield" type="text" id="<?php echo("code".$row->ID."ord"); ?>" name="codeord[]" value="<?php echo $row->ord; ?>"/>
                            <input type="hidden" name="codeids[]" value="<?php echo $row->ID; ?>"/></td>
                        <td><input class="valuefield" type="text" id="<?php echo("code".$row->ID."value"); ?>" name="codevalue[]" value="<?php echo $row->code; ?>"/></td>       
                        <td><input type="text" id="<?php echo("code".$row->ID."label"); ?>" name="codelabel[]" value="<?php echo $row->label; ?>" size="128"/></td>
                        <td><input type="checkbox" id="<?php echo("code".$row->ID."missval"); ?>" name="codemissval[]" value="<?php echo $row->ID; ?>" <?php if ($row->missval) echo("checked"); ?> /></td>            
                        <td><input type="checkbox" id="<?php echo("code".$row->ID."delete"); ?>" name="codedelete[]" value="<?php echo $row->ID; ?>"/></td>
                    	<td><input type="checkbox" id="<?php echo("code".$row->ID."addrmtf"); ?>" name="codeaddrmtf[]" value="<?php echo $row->ID; ?>"/></td>
                    </tr>
                    <?php 
                    	$bindeditems = $this->getModel('scales')->getCodebindedItems($row->ID);
                        if ($bindeditems!=null && count($bindeditems)>0)
                        {
                    ?>
                    <tr>
                    	<td colspan="2" align="right">Including textfield:</td>
                    	<td><input type="hidden" name="<?php echo("code".$row->ID."tfID"); ?>" value="<?php echo $bindeditems[0]->ID; ?>"/>
                    		<table>
                    		<tr><td>Variable name</td><td><input type="text" name="<?php echo("code".$row->ID."tfvarname"); ?>" value="<?php echo $bindeditems[0]->varname; ?>"/></td>
                    			<td>Width</td><td><input class="widthfield" type="text" name="<?php echo("code".$row->ID."tfwidthleft"); ?>" value="<?php echo $bindeditems[0]->width_left; ?>" /></td>
                    		</tr>
                    		<tr><td>Data type</td><td>
                    			<select name="<?php echo("code".$row->ID."tfdatatype"); ?>">
			                    <?php 
			                    	$datatypes = $this->getModel()->getDataTypes();
			                    	for ($i=1; $i<=3; $i++)
			                    	{
			                    		echo '<option value="'.$i.'" '.($bindeditems[0]->datatype==$i?'selected':'').'>'.$datatypes[$i].'</option>';
			                    	}
			                    ?>
                    			</select></td>
                    		    <td>Rows</td><td><input class="widthfield" type="text" name="<?php echo("code".$row->ID."tfrows"); ?>" value="<?php echo $bindeditems[0]->rows; ?>" /></td>
              				</tr>
                    		<tr><td>Text surrounding</td><td><input type="text" name="<?php echo("code".$row->ID."tfprepost"); ?>" value="<?php echo $bindeditems[0]->prepost; ?>"/>
                    			<td>Add linebreak</td><td><input type="checkbox" name="<?php echo("code".$row->ID."tflinebreak"); ?>" value="1" <?php if($bindeditems[0]->linebreak) echo "checked"; ?>/></td>
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
             <input type="button" value="Add Code" onclick="addCode()">
       </fieldset>
       
       <input type="hidden" name="option" value="<?php echo JRequest::getVar( 'option' );?>"/>
       <input type="hidden" name="ID" value="<?php echo $this->question->ID; ?>"/>
       <input type="hidden" name="questtype" value="<?php echo $this->question->questtype; ?>"/>
       <input type="hidden" name="datatype" value="<?php echo $this->question->datatype; ?>"/>
       <input type="hidden" name="pageID" value="<?php echo $this->question->pageID; ?>"/>
       <input type="hidden" name="task" value=""/>
</form>
