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
                    <tr><td>Text and HTML code</td><td><textarea name="_question_text" cols="128" rows="10"><?php echo $this->question->text; ?></textarea></tr>
                    </table>
       </fieldset>
       
       <input type="hidden" name="option" value="<?php echo JRequest::getVar( 'option' );?>"/>
       <input type="hidden" name="_question_ID" value="<?php echo $this->question->ID; ?>"/>
       <input type="hidden" name="_question_questtype" value="<?php echo $this->question->questtype; ?>"/>
       <input type="hidden" name="_question_pageID" value="<?php echo $this->question->pageID; ?>"/>
       <input type="hidden" name="_question_ord" value="<?php echo $this->question->ord; ?>"/>
       <input type="hidden" name="_question_mandatory" value="<?php echo $this->question->mandatory; ?>"/>
       <input type="hidden" name="task" value=""/>
</form>
