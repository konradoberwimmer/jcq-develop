<?php
defined('_JEXEC') or die('Restricted access'); ?>

<?php if ($this->scale->ID > 0) { ?>
<p class="breadcrumbs">
<a href="<?php echo JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=display',false);?>">JCQ</a>&nbsp;&gt;&nbsp;
<a href="<?php echo JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=editScale&scaleid='.$this->scale->ID,false);?>">Scale &quot;<?php echo $this->scale->name; ?>&quot;</a>
</p>
<?php } ?>

<form action="index.php" method="POST" name="adminForm" id="adminForm">
       <fieldset>
             <legend>Scale definition:</legend>
             <table>
                    <tr><td>Name</td><td><input type="text" name="name" id="name" size="32" maxlength="250" value="<?php echo $this->scale->name; ?>" /></td></tr>
                    <tr><td>Text before and after the field</td><td><input type="text" name="prepost" id="prepost" size="32" maxlength="250" value="<?php echo $this->scale->prepost; ?>" /></td></tr>
             		<tr><td>Default value</td><td><input type="text" name="defval" id="defval" size="8" value="<?php echo $this->scale->defval; ?>" /></td></tr>
             </table>
       </fieldset>

<?php if ($this->scale->ID > 0) { ?>
       <fieldset>
             <legend>Scale:</legend>
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
						<td><input type="text" id="<?php echo("code".$row->ID."ord"); ?>" name="codeord[]" value="<?php echo $row->ord; ?>"/>
                            <input type="hidden" name="codeids[]" value="<?php echo $row->ID; ?>"/></td>
                        <td><input type="text" id="<?php echo("code".$row->ID."value"); ?>" name="codevalue[]" value="<?php echo $row->code; ?>"/></td>       
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
<?php } ?>
       
       <input type="hidden" name="option" value="<?php echo JRequest::getVar( 'option' );?>"/>
       <input type="hidden" name="ID" value="<?php echo $this->scale->ID; ?>"/>
       <input type="hidden" name="scaleid" value="<?php echo $this->scale->ID; ?>"/>
       <input type="hidden" name="predefined" value="<?php echo $this->scale->predefined; ?>"/>
       <input type="hidden" name="task" value=""/>
</form>
