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
                    <select name="datatype" id="datatype">
                    <?php 
                    	$datatypes = $this->getModel()->getDataTypes();
                    	for ($i=1; $i<=3; $i++)
                    	{
                    		echo '<option value="'.$i.'" '.($this->question->datatype==$i?'selected':'').'>'.$datatypes[$i].'</option>';
                    	}
                    ?>
                    </select>
                    </td></tr>
                    <tr><td>Question text</td><td><textarea name="text" id="text" cols="64" rows="3"><?php echo $this->question->text; ?></textarea></tr>
                    <tr><td>Advise text</td><td><textarea name="advise" id="advise" cols="64" rows="3"><?php echo $this->question->advise; ?></textarea></tr>
                    <tr><td>Text before and after the field</td><td><input type="text" name="prepost" id="prepost" value="<?php echo $this->question->prepost; ?>"/></tr>
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
                    <tr><td>Width of textfield</td><td><input type="text" name="width_items" id="width_items" size="8" maxlength="250" value="<?php echo $this->question->width_items; ?>" /></td></tr>
             </table>
       </fieldset>

       <input type="hidden" name="option" value="<?php echo JRequest::getVar( 'option' );?>"/>
       <input type="hidden" name="ID" value="<?php echo $this->question->ID; ?>"/>
       <input type="hidden" name="questtype" value="<?php echo $this->question->questtype; ?>"/>
       <input type="hidden" name="pageID" value="<?php echo $this->question->pageID; ?>"/>
       <input type="hidden" name="task" value=""/>
</form>
