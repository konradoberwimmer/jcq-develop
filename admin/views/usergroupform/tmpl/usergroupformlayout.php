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
    <table id="tokentable" class="list">
    	<thead>
    	<tr>
    		<th></th>
    		<th>Token</th>
    		<th>Email</th>
    	</tr>
    	</thead>
    	<tbody>
    	<?php 
    		if ($this->tokens!==null)
    		{
    		foreach ($this->tokens as $token)
    		{
    			?>
    		<tr>
    			<td></td>
    			<td><?php echo $token->token; ?></td>
    			<td><?php echo $token->email; ?></td>
    		</tr>
    			<?php 
    		}
    		}
    	?>
		</tbody>
	</table>
    <input type="text" size="8" name="numTokens" value="25"/>
    <input type="button" name="addTokens" value="Add ... tokens" onclick="submitbutton('addTokens')"/><br/>
</fieldset>
<?php } ?>
</form>