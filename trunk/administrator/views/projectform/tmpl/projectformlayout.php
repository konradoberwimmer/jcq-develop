<?php
defined('_JEXEC') or die('Restricted access'); ?>
<?php if ($this->project->ID > 0) { ?>
<p class="breadcrumbs">
<a href="<?php echo JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=display',false);?>">JCQ</a>&nbsp;&gt;&nbsp;
<a href="<?php echo JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=editProject&cid[]='.$this->project->ID,false);?>">Project &quot;<?php echo $this->project->name; ?>&quot;</a>
</p>
<?php } ?>
<form action="index.php" method="POST" name="adminForm" id="adminForm">
       <fieldset>
             <legend>Project definition:</legend>
             <table>
                    <tr><td>Name</td><td><input type="text" name="name" id="name" size="32" maxlength="250" value="<?php echo $this->project->name; ?>" /></td></tr>
                    <tr><td>Classfile</td><td><input type="text" name="classfile" id="classfile" size="32" maxlength="250" value="<?php echo $this->project->classfile; ?>" /></td></tr>
                    <tr><td>Classname</td><td><input type="text" name="classname" id="classname" size="32" maxlength="250" value="<?php echo $this->project->classname; ?>" /></td></tr>
                    <tr><td>CSS-File (optional)</td><td><input type="text" name="cssfile" id="cssfile" size="32" maxlength="250" value="<?php echo $this->project->cssfile; ?>" /></td></tr>
                    <tr><td>Description</td><td><input type="text" name="description" id="description" size="64" maxlength="5000" value="<?php echo $this->project->description; ?>" /></td></tr>
                    <tr><td>Anonymous answers</td><td><input type="checkbox" name="anonymous" id="anonymous" value="1" <?php if ($this->project->anonymous > 0) echo("checked"); ?>/></td></tr>
             		<tr><td>Multiple answers</td><td><input type="checkbox" name="multiple" id="multiple" value="1" <?php if ($this->project->multiple > 0) echo("checked"); ?>/></td></tr>
             </table>
       </fieldset>

       <input type="hidden" name="option" value="<?php echo JRequest::getVar( 'option' );?>"/>
       <input type="hidden" name="ID" value="<?php echo $this->project->ID; ?>"/>      
       <input type="hidden" name="task" value=""/>
<?php if ($this->project->ID > 0 && $this->pages != null) { ?>
<fieldset>
             <legend>Pages:</legend>
Project has <?php echo count($this->pages); ?> page(s).
       <table class="list">
             <thead>
                    <tr>
                           <th width="10"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->pages); ?>)" /></th>
                           <th>Order</th>
                           <th>Name</th>
                           <th>Questions</th>
                    </tr>               
             </thead>
             <tbody>
                    <?php
                    $k = 0;
                    $i = 0;
                    foreach ($this->pages as $row){
                           $checked = JHTML::_('grid.id', $i, $row->ID);
                           $link = JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=editPage&cid[]='. $row->ID,false);
                    ?>
                    <tr>
                            <td><?php echo $checked; ?></td>
                            <td><input type="text" id="<?php echo("page".$row->ID."ord"); ?>" name="pageord[]" value="<?php echo $row->ord; ?>"/>
                            <input type="hidden" name="pageids[]" value="<?php echo $row->ID; ?>"/></td>
                            <td><a href="<?php echo $link;?>"><?php echo $row->name;?></a></td>
                            <td><?php echo $this->questioncounts[$i]; ?></td>
                    </tr>
                    <?php
                    	$k = 1 - $k;
                    	$i++;
                    }
                    ?>
             </tbody>
       </table>
       <input type="hidden" name="boxchecked" value="0"/>    
       <input type="hidden" name="hidemainmenu" value="0"/>  
       </fieldset>
<?php } else if ($this->project->ID > 0) { ?>
<fieldset>
             <legend>Pages:</legend>
Project has no pages.
</fieldset>
<?php } ?>
</form>