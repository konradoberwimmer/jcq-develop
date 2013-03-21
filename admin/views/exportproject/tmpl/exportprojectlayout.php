<?php
defined('_JEXEC') or die('Restricted access'); ?>
<form action="index.php" method="POST" name="adminForm">
       Save file as ... from <a href="<?php echo JURI::root().'/components/com_jcq/usercode/project'.$this->projectID.'.xml'; ?>" target="_blank">here</a>!
       
       <input type="hidden" name="option" value="<?php echo JRequest::getVar( 'option' );?>"/>
       <input type="hidden" name="task" value=""/> 
       <input type="hidden" name="hidemainmenu" value="0"/>  
</form>