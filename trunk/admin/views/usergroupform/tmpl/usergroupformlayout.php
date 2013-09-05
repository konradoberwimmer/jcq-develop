<?php
defined('_JEXEC') or die('Restricted access'); ?>
<?php if ($this->usergroup->ID > 0) { ?>
<p class="breadcrumbs">
<a href="<?php echo JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=display',false);?>">JCQ</a>&nbsp;&gt;&nbsp;
<a href="<?php echo JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=editProject&cid[]='.$this->project->ID,false);?>">Project &quot;<?php echo $this->project->name; ?>&quot;</a>&nbsp;&gt;&nbsp;
<a href="<?php echo JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=editUsergroup&cid[]='.$this->usergroup->ID,false);?>">User group &quot;<?php echo $this->usergroup->name; ?>&quot;</a>
</p>
<?php } ?>
<form action="index.php" method="POST" name="adminForm" id="adminForm">
       <fieldset>
             <legend>User group definition:</legend>
             <table class="settings">
                    <tr><td>Name</td><td><input type="text" name="name" id="name" size="32" maxlength="250" value="<?php echo $this->usergroup->name; ?>" /></td></tr>
                    <tr><td>Value</td><td><input type="text" name="val" id="name" size="16" maxlength="250" value="<?php echo $this->usergroup->val; ?>" /></td></tr>
             </table>
       </fieldset>

       <input type="hidden" name="option" value="<?php echo JRequest::getVar( 'option' );?>"/>
       <input type="hidden" name="ID" value="<?php echo $this->usergroup->ID; ?>"/>
       <input type="hidden" name="projectID" value="<?php echo $this->usergroup->projectID; ?>"/>
       <input type="hidden" name="task" value=""/>
<?php if ($this->usergroup->ID > 0) { ?>
<fieldset>
	<legend>Tokens:</legend>
<?php 
if ($this->tokens===null || count($this->tokens)==0) echo("No tokens for this user group.");
else
{ ?>
    <table id="tokentable" class="list">
    	<thead>
    	<tr>
    		<th><input type="checkbox" id="token_all" name="token_all" onclick="changeAllTokenstate()"/></th>
    		<th>Token</th>
    		<th>Email</th>
    		<th>Name</th>
    		<th>First name</th>
    		<th>Salutation</th>
    		<th>Note</th>
    	</tr>
    	</thead>
    	<tbody>
    	<?php 
    		foreach ($this->tokens as $token)
    		{
    			?>
    		<tr>
    			<td><input type="checkbox" name="cid[]" value="<?php echo $token->ID; ?>"/></td>
    			<td><a href="<?php echo JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=editToken&cid[]='.$token->ID,false);?>"><?php echo $token->token; ?></a></td>
    			<td><?php echo $token->email; ?></td>
    			<td><?php echo $token->name; ?></td>
    			<td><?php echo $token->firstname; ?></td>
    			<td><?php echo $token->salutation; ?></td>
    			<td><?php echo $token->note; ?></td>
    		</tr>
    			<?php 
    		}
    	?>
		</tbody>
	</table>
<?php } ?>
	<table style="margin-top:10px;">
	<tr><td><input type="button" style="width:150px;" name="newToken" value="New token" onclick="submitbutton('newToken')"/></td><td></td></tr>
    <tr style="border-top: 1px solid grey;"><td><input type="button" style="width:150px;" name="removeTokens" value="Remove token(s)" onclick="submitbutton('removeTokens')"/></td><td><label><input type="checkbox" name="deleteanswers" value="1"/> Delete answers of token(s)</label></td></tr>
    <tr style="border-top: 1px solid grey;"><td><input type="button" style="width:150px;" name="addRandomTokens" value="Add random tokens" onclick="submitbutton('addRandomTokens')"/></td><td><label for="numTokens">Number of tokens:&nbsp;</label><input type="text" size="8" id="numTokens" name="numTokens" value="25"/></td></tr>
    <tr style="border-top: 1px solid grey;"><td><input type="button" style="width:150px;" name="uploadTokens" value="Upload tokens" onclick="submitbutton('uploadTokens')"/></td><td><label for="file">File:&nbsp;</label><input type="file" name="file" id="file"/><br/><label><input type="checkbox" name="columnnames" value="1" checked /> First row contains column names</label></td></tr>
    </table>
</fieldset>
<?php } ?>
</form>