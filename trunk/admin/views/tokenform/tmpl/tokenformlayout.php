<?php
defined('_JEXEC') or die('Restricted access'); ?>
<?php if ($this->token->ID > 0) { ?>
<p class="breadcrumbs">
<a href="<?php echo JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=display',false);?>">JCQ</a>&nbsp;&gt;&nbsp;
<a href="<?php echo JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=editProject&cid[]='.$this->project->ID,false);?>">Project &quot;<?php echo $this->project->name; ?>&quot;</a>&nbsp;&gt;&nbsp;
<a href="<?php echo JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=editUsergroup&cid[]='.$this->usergroup->ID,false);?>">User group &quot;<?php echo $this->usergroup->name; ?>&quot;</a>&nbsp;&gt;&nbsp;
<a href="<?php echo JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=editToken&cid[]='.$this->token->ID,false);?>">Token &quot;<?php echo $this->token->token; ?>&quot;</a>
</p>
<?php } ?>
<form action="index.php" method="POST" name="adminForm" id="adminForm">
       <fieldset>
             <legend>Token definition:</legend>
             <table class="settings">
                    <tr><td>Token</td><td><input type="text" name="token" size="16" maxlength="250" value="<?php echo $this->token->token; ?>" /></td></tr>
                    <tr><td>Email</td><td><input type="text" name="email" size="32" maxlength="250" value="<?php echo $this->token->email; ?>" /></td></tr>
                    <tr><td>Name</td><td><input type="text" name="name" size="32" maxlength="250" value="<?php echo $this->token->name; ?>" /></td></tr>
                    <tr><td>First name</td><td><input type="text" name="firstname" size="32" maxlength="250" value="<?php echo $this->token->firstname; ?>" /></td></tr>
                    <tr><td>Salutation</td><td><input type="text" name="salutation" size="64" maxlength="250" value="<?php echo $this->token->salutation; ?>" /></td></tr>
                    <tr><td>Note</td><td><input type="text" name="note" size="64" maxlength="250" value="<?php echo $this->token->note; ?>" /></td></tr>
             </table>
       </fieldset>

       <input type="hidden" name="option" value="<?php echo JRequest::getVar( 'option' );?>"/>
       <input type="hidden" name="ID" value="<?php echo $this->token->ID; ?>"/>
       <input type="hidden" name="usergroupID" value="<?php echo $this->token->usergroupID; ?>"/>
       <input type="hidden" name="task" value=""/>
</form>