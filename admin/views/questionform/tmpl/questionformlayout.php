<?php
defined('_JEXEC') or die('Restricted access'); ?>

<form action="index.php" method="POST" name="adminForm" id="adminForm" onsubmit="return false;">
       <fieldset>
             <legend>Question definition:</legend>
             <table class="settings">
                    <tr><td>Name</td><td><input type="text" name="name" id="name" size="32" maxlength="250" value="<?php echo $this->question->name; ?>" autofocus="autofocus"/></td></tr>
                    <tr><td>Type</td>
                    <td><select name="questtype" id="questtype">
                    <?php 
                    	$questtypes = $this->getModel()->getQuestionTypes();
                    	foreach ($questtypes as $typeid=>$typename)
                    	{
                    		if ($this->page->isFinal && $typeid!=TEXTANDHTML) continue;
                    		echo '<option value="'.$typeid.'">'.$typename.'</option>';
                    	}
                    ?>
                    </select>
                    </td></tr>
             </table>
       </fieldset>

       <input type="hidden" name="option" value="<?php echo JRequest::getVar( 'option' );?>"/>
       <input type="hidden" name="ID" value="<?php echo $this->question->ID; ?>"/>
       <input type="hidden" name="pageID" value="<?php echo $this->question->pageID; ?>"/>
       <input type="hidden" name="task" value=""/>
</form>
