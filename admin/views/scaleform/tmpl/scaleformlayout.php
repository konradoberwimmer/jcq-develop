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
                    <tr><td>Name</td><td><input type="text" name="_scale_name" size="32" maxlength="250" value="<?php echo $this->scale->name; ?>" /></td></tr>
                    <tr><td>Text before and after the field</td><td><input type="text" name="_scale_prepost" size="32" maxlength="250" value="<?php echo $this->scale->prepost; ?>" /></td></tr>
             		<tr><td>Default value</td><td><input type="text" name="_scale_defval" size="8" value="<?php echo $this->scale->defval; ?>" /></td></tr>
             </table>
       </fieldset>

<?php if ($this->scale->ID > 0) { ?>
       <fieldset>
             <legend>Scale:</legend>
             <input type="hidden" id="tmpcodeid" name="tmpcodeid" value="-1"/>
             <input type="hidden" id="scaleid" name="scaleid" value="<?php echo $this->scale->ID; ?>"/>
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
                    <?php foreach ($this->codes as $row) { ?>
                    <tr>
						<td><input type="text" name="_code_<?php echo $row->ID; ?>_ord" value="<?php echo $row->ord; ?>" class="orderfield"/>
                            <input type="hidden" name="_code_<?php echo $row->ID; ?>_ID" value="<?php echo $row->ID; ?>"/>
                            <input type="hidden" name="_code_<?php echo $row->ID; ?>_scaleID" value="<?php echo $this->scale->ID; ?>"/></td>
                        <td><input type="text" name="_code_<?php echo $row->ID; ?>_code" value="<?php echo $row->code; ?>" class="valuefield"/></td>       
                        <td><input type="text" name="_code_<?php echo $row->ID; ?>_label" value="<?php echo $row->label; ?>" size="128"/></td>
                        <td><input type="checkbox" name="_code_<?php echo $row->ID; ?>_missval" value="1" <?php if ($row->missval) echo("checked"); ?> /></td>            
                        <td><input type="checkbox" name="codedelete[]" value="<?php echo $row->ID; ?>"/></td>
                    </tr>
                    <?php 
                    }
                    ?>
             </tbody>
             </table>
             <input type="button" value="Add Code" onclick="addCode()">
       </fieldset>
<?php } ?>
       
       <input type="hidden" name="option" value="<?php echo JRequest::getVar( 'option' );?>"/>
       <input type="hidden" name="_scale_ID" value="<?php echo $this->scale->ID; ?>"/>
       <input type="hidden" name="_scale_predefined" value="<?php echo $this->scale->predefined; ?>"/>
       <input type="hidden" name="task" value=""/>
</form>
