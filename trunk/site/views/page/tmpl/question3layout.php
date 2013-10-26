<?php
defined('_JEXEC') or die( 'Restricted access' ); 
$items = $this->pagemodel->getItemsToQuestion($this->question->ID);
$mainitem = null;
foreach ($items as $item) if ($item->bindingType=="QUESTION") {
	$mainitem = $item; break;
}
if ($mainitem===null) JError::raiseError(500, "FATAL: corrupt question definition for '".$this->question->name."'");
$prevanswer = $this->userdata->getStoredValue($mainitem->ID);
?>
<div class="question3">
	<?php
		$ismissing = false;
		if ($this->markmissing && $mainitem->mandatory==1 && (!$this->userdata->hasStoredValue($mainitem->ID) || strlen($prevanswer)==0))
		{ 
			$ismissing = true;
			?>
			<p class="question3missing"><?php echo $this->question->text; ?></p>
		<?php 
		}  
		else 
		{ ?>
			<p class="question3text"><?php echo $this->question->text; ?></p>
		<?php 
		}
	?>
	<?php if ($this->question->advise != null) echo '<p class="question3advise">'.$this->question->advise.'</p>'; ?>
	<?php 
		if ($this->markmissing && !$ismissing && $prevanswer!=null && $mainitem->datatype==1 && !val_is_int($prevanswer)) echo '<div class="questionalertmissing">Bitte hier eine ganze Zahl eingeben!</div>';
		if ($this->markmissing && !$ismissing && $prevanswer!=null && $mainitem->datatype==2 && !is_numeric($prevanswer)) echo '<div class="questionalertmissing">Bitte hier eine Zahl eingeben!</div>';
		$width = 50;
		if ($this->question->width_items > 0) $width = $this->question->width_items;	
		$possplit = strpos($mainitem->prepost, '%s');
		if ($possplit===false)
		{ 
			echo('<p>');
			if ($mainitem->rows==1) echo('<input type="text" name="i'.$mainitem->ID.'_" value="'.$prevanswer.'" style="width: '.$width.'px;"/>');
			else echo('<textarea name="i'.$mainitem->ID.'_" rows="'.$mainitem->rows.'" style="width: '.$width.'px;">'.$prevanswer.'</textarea>');
			if (!$ismissing) echo($mainitem->prepost);
			else echo('<span class="question3missing">'.$mainitem->prepost.'</span>');
			echo('</p>');
		}
		else
		{
			echo('<p>');
			if (!$ismissing) echo(substr($mainitem->prepost,0,$possplit));
			else echo('<span class="question3missing">'.substr($mainitem->prepost,0,$possplit).'</span>');
			if ($mainitem->rows==1) echo('<input type="text" name="i'.$mainitem->ID.'_" value="'.$prevanswer.'" style="width: '.$width.'px;"/>');
			else echo('<textarea name="i'.$mainitem->ID.'_" rows="'.$mainitem->rows.'" style="width: '.$width.'px;">'.$prevanswer.'</textarea>');
			if (!$ismissing) echo(substr($mainitem->prepost,$possplit+2));
			else echo('<span class="question3missing">'.substr($mainitem->prepost,$possplit+2).'</span>');
			echo('</p>');
		}
	?>
</div>