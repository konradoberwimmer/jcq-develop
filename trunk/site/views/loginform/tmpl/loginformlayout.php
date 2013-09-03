<?php
defined('_JEXEC') or die('Restricted access'); ?>
<?php 
	$uri = JFactory::getURI();
	$uri->delVar('token');
?>
<form action="<?php echo $uri; ?>" method="POST" name="loginForm">
	<div class="alertlogin">Dieser Fragebogen ist zugriffsgeschützt. Bitte loggen Sie sich mit einem gültigen Token ein:</div>
	<input type="text" size="8" name="token"/><input type="submit" value="Login"/>
</form>
