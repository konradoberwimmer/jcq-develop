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
             </table>
       </fieldset>

       <fieldset>
             <legend>Layout:</legend>
             <table class="settings">
                    <tr><td>Width of question</td><td><input type="text" name="_question_width_question" size="8" maxlength="250" value="<?php echo $this->question->width_question; ?>" /></td></tr>
                    <tr><td>Width of items</td><td><input type="text" name="_question_width_items" size="8" maxlength="250" value="<?php echo $this->question->width_items; ?>" /></td></tr>
                    <tr><td>Width of scale options</td><td><input type="text" name="_question_width_scale" size="8" maxlength="250" value="<?php echo $this->question->width_scale; ?>" /></td></tr>
                    <tr><td>Alternate background</td><td><input type="checkbox" name="_question_alternate_bg" value="1" <?php if ($this->question->alternate_bg > 0) echo("checked"); ?>/></td></tr>
             </table>
       </fieldset>
       
       <fieldset>
             <legend>Scale(s):</legend>
             <input type="hidden" id="numpredefscales" name="numpredefscales" value="<?php echo(count($this->predefscales)); ?>"/>
             <input type="hidden" id="tmpscaleid" name="tmpscaleid" value="-1"/>
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
                    <?php foreach ($this->attachedscales as $row) { ?>
                    <tr>
						<td><input type="text" name="_scale_<?php echo($row->ID); ?>_ord" value="<?php echo($row->ord); ?>" class="orderfield"/>
						    <input type="hidden" name="_scale_<?php echo($row->ID); ?>_ID" value="<?php echo($row->ID); ?>"/></td>
                        <td><?php for ($j=0; $j<count($this->predefscales); $j++) if ($row->ID==$this->predefscales[$j]->ID) echo($this->predefscales[$j]->name); ?></td>
	                    <td><input type="checkbox" name="_scale_<?php echo($row->ID); ?>_mandatory" value="1" <?php if ($row->mandatory) echo("checked"); ?>/></td>
                        <td><input type="checkbox" name="scaledelete[]" value="<?php echo($row->ID); ?>"/></td>
                    </tr>
                    <?php } ?>
             	</tbody>
             </table>
             <input type="button" value="Add Scale" onclick="javascript: addScale()">
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
                           <th>Mandatory</th>
                           <th>Delete</th>
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
                            <input type="hidden" name="_item_<?php echo $row->ID; ?>_bindingtype" value="QUESTION"/></td>
                        <td><input type="text" name="_item_<?php echo $row->ID; ?>_textleft" value="<?php echo (str_replace("\"", "&quot;", $row->textleft)); ?>" size="128" /></td>
                        <td><input type="text" name="_item_<?php echo $row->ID; ?>_varname" value="<?php echo $row->varname; ?>"/></td>
                        <td><input type="checkbox" name="_item_<?php echo $row->ID; ?>_mandatory" value="1" <?php if ($row->mandatory) echo("checked"); ?> /></td>            
                        <td><input type="checkbox" name="itemdelete[]" value="<?php echo $row->ID; ?>"/></td>
                    </tr>
                    <?php
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
