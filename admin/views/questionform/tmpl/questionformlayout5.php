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
                    </tr>               
             </thead>
             <tbody id="listscalebody">
                    <?php
                    $k = 0;
                    $i = 0;
                    foreach ($this->codes as $row){
                    ?>
                    <tr>
						<td><input type="text" class="orderfield" id="<?php echo("code".$row->ID."ord"); ?>" name="codeord[]" value="<?php echo $row->ord; ?>"/>
                            <input type="hidden" name="codeids[]" value="<?php echo $row->ID; ?>"/></td>
                        <td><input type="text" class="valuefield" id="<?php echo("code".$row->ID."value"); ?>" name="codevalue[]" value="<?php echo $row->code; ?>"/></td>       
                        <td><input type="text" id="<?php echo("code".$row->ID."label"); ?>" name="codelabel[]" value="<?php echo $row->label; ?>" size="128"/></td>
                        <td><input type="checkbox" id="<?php echo("code".$row->ID."missval"); ?>" name="codemissval[]" value="<?php echo $row->ID; ?>" <?php if ($row->missval) echo("checked"); ?> /></td>            
                        <td><input type="checkbox" id="<?php echo("code".$row->ID."delete"); ?>" name="codedelete[]" value="<?php echo $row->ID; ?>"/></td>
                    </tr>
                    <?php
                    	$k = 1 - $k;
                    	$i++;
                    }
                    ?>
             </tbody>
             </table>
             <input type="button" value="Add Code" onclick="addCode()">
       </fieldset>
       
       <fieldset>
             <legend>Items:</legend>
             <input type="hidden" name="itemspresent" value="1"/>
             <table class="list">
                    <thead>
                    <tr>
                           <th>Order</th>
                           <th>Item text left</th>
                           <th>Item text right</th>
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
                        <td><input type="text" id="<?php echo("item".$row->ID."textleft"); ?>" name="itemtextleft[]" value="<?php echo $row->textleft; ?>" size="64" /></td>       
                        <td><input type="text" id="<?php echo("item".$row->ID."textright"); ?>" name="itemtextright[]" value="<?php echo $row->textright; ?>" size="64" /></td>       
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
             <input type="button" value="Add Item" onclick="addItem(true)">
       </fieldset>
       
       <input type="hidden" name="option" value="<?php echo JRequest::getVar( 'option' );?>"/>
       <input type="hidden" name="ID" value="<?php echo $this->question->ID; ?>"/>
       <input type="hidden" name="questtype" value="<?php echo $this->question->questtype; ?>"/>
       <input type="hidden" name="datatype" value="<?php echo $this->question->datatype; ?>"/>
       <input type="hidden" name="pageID" value="<?php echo $this->question->pageID; ?>"/>
       <input type="hidden" name="task" value=""/>
</form>
