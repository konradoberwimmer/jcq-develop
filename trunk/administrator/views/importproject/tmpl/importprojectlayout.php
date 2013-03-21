<?php
defined('_JEXEC') or die('Restricted access'); ?>
<form action="index.php" method="POST" enctype="multipart/form-data" name="adminForm">
       File: <input type="file" name="file_upload"/><br/>
       <input type="submit" name="importProject" value="Import"/>
       
       <input type="hidden" name="option" value="<?php echo JRequest::getVar( 'option' );?>"/>
       <input type="hidden" name="task" value="importProject"/> 
       <input type="hidden" name="hidemainmenu" value="0"/>  
</form>
