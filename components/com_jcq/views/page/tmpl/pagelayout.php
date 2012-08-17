<?php
defined('_JEXEC') or die('Restricted access'); ?>
<form action="index.php" method="POST" name="inputForm">
	Displaying page with ID <?php echo($this->pageID); ?><br/>
	<input type="button" name="storeAndContinue" value="Weiter" onclick="javascript: submitbutton('storeAndContinue');"/>
	
    <input type="hidden" name="option" value="<?php echo JRequest::getVar( 'option' );?>"/>
    <input type="hidden" name="projectID" value="<?php echo JRequest::getVar( 'projectID' ); ?>"/>
    <input type="hidden" name="sessionID" value="<?php echo $this->userdata->getSessionID(); //because it may be a new one! ?>"/>
    <input type="hidden" name="task" value=""/>
</form>
