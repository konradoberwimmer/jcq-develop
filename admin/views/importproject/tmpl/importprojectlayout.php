<?php
defined('_JEXEC') or die('Restricted access'); ?>
<form action="index.php" method="POST" enctype="multipart/form-data" name="adminForm">
<table class="settings">
<tr><td>XML File</td><td><input type="file" name="file_upload"/></td></tr>
<tr><td>Use XSL transformation</td><td><input type="checkbox" name="usexslt" value="1"/></td></tr>
<tr><td>XSLT File</td><td><input type="file" name="xslt_upload"/></td></tr>
</table>
       <input type="submit" name="importProject" value="Import"/>

       <input type="hidden" name="option" value="<?php echo JRequest::getVar( 'option' );?>"/>
       <input type="hidden" name="task" value="importProject"/> 
       <input type="hidden" name="hidemainmenu" value="0"/>  
</form>
