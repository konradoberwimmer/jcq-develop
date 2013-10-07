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
             </table>
       </fieldset>

       <fieldset>
             <legend>Layout:</legend>
             <table class="settings">
                    <tr><td>Width of question</td><td><input type="text" name="width_question" id="width_question" size="8" maxlength="250" value="<?php echo $this->question->width_question; ?>" /></td></tr>
                    <tr><td>Width of items</td><td><input type="text" name="width_items" id="width_items" size="8" maxlength="250" value="<?php echo $this->question->width_items; ?>" /></td></tr>
                    <tr><td>Width of scale options</td><td><input type="text" name="width_scale" id="width_scale" size="8" maxlength="250" value="<?php echo $this->question->width_scale; ?>" /></td></tr>
                    <tr><td>Alternate background</td><td><input type="checkbox" name="alternate_bg" id="alternate_bg" value="1" <?php if ($this->question->alternate_bg > 0) echo("checked"); ?>/></td></tr>
             </table>
       </fieldset>
       
       <fieldset>
             <legend>Scale(s):</legend>
             <!-- for the javascript part a template of the select box is created -->
			 <select id="scaleidTEMPLATE" name="" style="display: none;">
             <?php 
             	for ($j=0; $j<count($this->predefscales); $j++)
                {
                	echo '<option value="'.$this->predefscales[$j]->ID.'">'.$this->predefscales[$j]->name.'</option>';
                }
             ?>
	         </select>
             <table class="list">
                    <thead>
                    <tr>
                           <th>Order</th>
                           <th>Scale</th>
                           <th>Mandatory</th>
                           <th>Delete</th>
                    </tr>               
             </thead>
             <tbody id="listscalesbody">
                    <?php
                    $i = 0;
                    foreach ($this->attachedscales as $row){
                    ?>
                    <tr>
						<td><input type="text" class="orderfield" name="scaleord[]" value="<?php echo($row->ord); ?>"/>
                        <td><select name="scaleids[]">
	                    <?php 
	                    	for ($j=0; $j<count($this->predefscales); $j++)
	                    	{
	                    		echo '<option value="'.$this->predefscales[$j]->ID.'" '.($row->ID==$this->predefscales[$j]->ID?'selected':'').'>'.$this->predefscales[$j]->name.'</option>';
	                    	}
	                    ?>
	                    </select></td>
	                    <td><input type="checkbox" name="scalemandatory[]" value="<?php echo($row->ID); ?>" <?php if ($row->mandatory) echo("checked"); ?>/></td>
                        <td><input type="checkbox" name="scaledelete[]" value="<?php echo($i); ?>"/></td>
                    </tr>
                    <?php
                    	$i++;
                    }
                    ?>
             </tbody>
             </table>
             <input type="button" value="Add Scale" onclick="javascript: addScale()">
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
                           <th>Mandatory</th>
                           <th>Delete</th>
                    </tr>               
             	</thead>
             	 <tbody id="listitembody">
                    <?php
                    $k = 0;
                    $i = 0;
                    foreach ($this->items as $row){
                    ?>
                    <tr>
						<td><input type="text" class="orderfield" id="<?php echo("item".$row->ID."ord"); ?>" name="itemord[]" value="<?php echo $row->ord; ?>"/>
                            <input type="hidden" name="itemids[]" value="<?php echo $row->ID; ?>"/></td>
                        <td><input type="text" id="<?php echo("item".$row->ID."textleft"); ?>" name="itemtextleft[]" value="<?php echo (str_replace("\"", "&quot;", $row->textleft)); ?>" size="128" /></td>       
                        <td><input type="text" id="<?php echo("item".$row->ID."varname"); ?>" name="itemvarname[]" value="<?php echo $row->varname; ?>"/></td>
                        <td><input type="checkbox" id="<?php echo("item".$row->ID."mandatory"); ?>" name="itemmandatory[]" value="<?php echo $row->ID; ?>" <?php if ($row->mandatory) echo("checked"); ?> /></td>            
                        <td><input type="checkbox" id="<?php echo("item".$row->ID."delete"); ?>" name="itemdelete[]" value="<?php echo $row->ID; ?>"/></td>
                    </tr>
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
