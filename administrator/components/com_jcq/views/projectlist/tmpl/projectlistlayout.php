<?php
defined('_JEXEC') or die('Restricted access'); ?>
<p class="breadcrumbs"><a href="<?php echo JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=display',false);?>">JCQ</a></p>
<?php 
if ($this->projects != null) { ?>
<fieldset>
             <legend>Projects:</legend>
JCQ component has <?php echo count($this->projects); ?> project(s).
<form action="index.php" method="POST" name="adminForm">
       <table class="list">
             <thead>
                    <tr>
                           <th width="10"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->projects); ?>)" /></th>
                           <th>Name</th>
                           <th>Classname</th>
                           <th>Classfile</th>
                           <th>Description</th>
                           <th>Anonymous</th>
                           <th>Multiple</th>
                           <th>Pages</th>
                    </tr>               
             </thead>
             <tbody>
                    <?php
                    $k = 0;
                    $i = 0;
                    foreach ($this->projects as $row){
                           $checked = JHTML::_('grid.id', $i, $row->ID);
                           $link = JRoute::_( 'index.php?option='.JRequest::getVar('option').'&task=editProject&cid[]='. $row->ID,false);
                    ?>
                    <tr>
                            <td><?php echo $checked; ?></td>
                            <td><a href="<?php echo $link;?>"><?php echo $row->name;?></a></td>
                            <td><?php echo $row->classname; ?></td>
                            <td><?php echo $row->classfile; ?></td>
                            <td><?php echo $row->description; ?></td>
                            <td><input type="checkbox" <?php if ($row->anonymous) echo("checked"); ?> disabled/></td>
                            <td><input type="checkbox" <?php if ($row->multiple) echo("checked"); ?> disabled /></td>
                    		<td><?php echo $this->pagecounts[$i]; ?></td>
                    </tr>
                    <?php
                    	$k = 1 - $k;
                    	$i++;
                    }
                    ?>
             </tbody>
       </table>
       
       <input type="hidden" name="option" value="<?php echo JRequest::getVar( 'option' );?>"/>
       <input type="hidden" name="task" value=""/>
       <input type="hidden" name="boxchecked" value="0"/>    
       <input type="hidden" name="hidemainmenu" value="0"/>  
</form>
</fieldset>
<?php } else { ?>
<fieldset>
<legend>Projects:</legend>
JCQ component has no projects.
<form action="index.php" method="POST" name="adminForm">   
       <input type="hidden" name="option" value="<?php echo JRequest::getVar( 'option' );?>"/>
       <input type="hidden" name="task" value=""/>
</form>
</fieldset>
<?php } ?>

