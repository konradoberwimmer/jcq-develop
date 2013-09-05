<?php
defined('_JEXEC') or die('Restricted access'); ?>

<p class="breadcrumbs">
<a href="<?php echo JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=display',false);?>">JCQ</a>&nbsp;&gt;&nbsp;
<a href="<?php echo JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=editProject&cid[]='.$this->project->ID,false);?>">Project &quot;<?php echo $this->project->name; ?>&quot;</a>&nbsp;&gt;&nbsp;
<a href="<?php echo JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=editUsergroup&cid[]='.$this->usergroup->ID,false);?>">User group &quot;<?php echo $this->usergroup->name; ?>&quot;</a>
</p>

<?php 
	$columns_letter = array_keys($this->sheetData[1]);
	$columns_name = array();
	if (isset($this->usergrouppost['columnnames']))
	{
		foreach ($columns_letter as $index=>$value) $columns_name[$index] = $this->sheetData[1][$value];
	} else
	{
		foreach ($columns_letter as $index=>$value) $columns_name[$index] = "Column ".$value;
	}
?>

<form action="index.php" method="POST" name="adminForm" id="adminForm">

       <fieldset>
             <legend>Upload definition:</legend>
             <table class="settings">
             	<tr><td>Column for token:</td><td>
             	<select name="columntoken">
             		<option value="-1">--- NONE ---</option>
             		<?php 
             		foreach ($columns_letter as $index=>$value)
             		{
             			if (strpos(strtolower($columns_name[$index]),"token")!==false) echo("<option value=\"".$value."\" selected>".$columns_name[$index]."</option>");
             			else echo("<option value=\"".$value."\">".$columns_name[$index]."</option>");
             		}
             		?>
             	</select>
             	</td></tr>
             	<tr><td>Column for email:</td><td>
             	<select name="columnemail">
             		<option value="-1">--- NONE ---</option>
             		<?php 
             		foreach ($columns_letter as $index=>$value)
             		{
             			if (strpos(strtolower($columns_name[$index]),"mail")!==false) echo("<option value=\"".$value."\" selected>".$columns_name[$index]."</option>");
             			else echo("<option value=\"".$value."\">".$columns_name[$index]."</option>");
             		}
             		?>
             	</select>             	
             	</td></tr>
             	<tr><td>Column for name:</td><td>
             	<select name="columnusername">
             		<option value="-1">--- NONE ---</option>
             		<?php 
             		foreach ($columns_letter as $index=>$value)
             		{
             			if (strpos(strtolower($columns_name[$index]),"name")!==false && strpos(strtolower($columns_name[$index]),"first")===false) echo("<option value=\"".$value."\" selected>".$columns_name[$index]."</option>");
             			else echo("<option value=\"".$value."\">".$columns_name[$index]."</option>");
             		}
             		?>
             	</select>             	
             	</td></tr>
             	<tr><td>Column for first name:</td><td>
             	<select name="columnfirstname">
             		<option value="-1">--- NONE ---</option>
             		<?php 
             		foreach ($columns_letter as $index=>$value)
             		{
             			if (strpos(strtolower($columns_name[$index]),"first")!==false) echo("<option value=\"".$value."\" selected>".$columns_name[$index]."</option>");
             			else echo("<option value=\"".$value."\">".$columns_name[$index]."</option>");
             		}
             		?>
             	</select>             	
             	</td></tr>
             	<tr><td>Column for salutation:</td><td>
             	<select name="columnsalutation">
             		<option value="-1">--- NONE ---</option>
             		<?php 
             		foreach ($columns_letter as $index=>$value)
             		{
             			if (strpos(strtolower($columns_name[$index]),"salutation")!==false) echo("<option value=\"".$value."\" selected>".$columns_name[$index]."</option>");
             			else echo("<option value=\"".$value."\">".$columns_name[$index]."</option>");
             		}
             		?>
             	</select>             	
             	</td></tr>
             	<tr><td>Column for note:</td><td>
             	<select name="columnnote">
             		<option value="-1">--- NONE ---</option>
             		<?php 
             		foreach ($columns_letter as $index=>$value)
             		{
             			if (strpos(strtolower($columns_name[$index]),"note")!==false) echo("<option value=\"".$value."\" selected>".$columns_name[$index]."</option>");
             			else echo("<option value=\"".$value."\">".$columns_name[$index]."</option>");
             		}
             		?>
             	</select>             	
             	</td></tr>
             </table>
       </fieldset>
       <input type="hidden" name="option" value="<?php echo JRequest::getVar( 'option' );?>"/>
       <input type="hidden" name="usergroupID" value="<?php echo $this->usergroupID; ?>"/>
       <input type="hidden" name="filename" value="<?php echo $this->filename; ?>"/>
       <input type="hidden" name="columnnames" value="<?php echo (isset($this->usergrouppost['columnnames'])?"1":"0"); ?>"/>
       <input type="hidden" name="task" value=""/>
</form>
