<?php
defined('_JEXEC') or die( 'Restricted access' ); 

?>
<div class="question3">
	<?php
		$ismissing = false;
		if ($this->markmissing && $this->question->mandatory==1 && (!$this->userdata->hasStoredValueQuestion($this->pageID,$this->question->ID) || strlen($this->userdata->getStoredValueQuestion($this->pageID,$this->question->ID))<1))
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
		$prevanswer = $this->userdata->getStoredValueQuestion($this->pageID,$this->question->ID);
		if ($this->markmissing && !$ismissing && $prevanswer!=null && $this->question->datatype==1 && !val_is_int($prevanswer)) echo '<div class="questionalertmissing">Bitte hier eine ganze Zahl eingeben!</div>';
		if ($this->markmissing && !$ismissing && $prevanswer!=null && $this->question->datatype==2 && !is_numeric($prevanswer)) echo '<div class="questionalertmissing">Bitte hier eine Zahl eingeben!</div>';
		$width = 50;
		if ($this->question->width_items > 0) $width = $this->question->width_items;	
		$possplit = strpos($this->question->prepost, '%s');
		if ($possplit===false)
		{ 
			echo('<p><input type="text" name="p'.$this->pageID.'q'.$this->question->ID.'" value="'.$prevanswer.'" style="width: '.$width.'px;"/>');
			if (!$ismissing) echo($this->question->prepost);
			else echo('<span class="question3missing">'.$this->question->prepost.'</span>');
			echo('</p>');
		}
		else
		{
			echo('<p>');
			if (!$ismissing) echo(substr($this->question->prepost,0,$possplit));
			else echo('<span class="question3missing">'.substr($this->question->prepost,0,$possplit).'</span>');
			echo('<input type="text" name="p'.$this->pageID.'q'.$this->question->ID.'" value="'.$prevanswer.'" style="width: '.$width.'px;"/>');
			if (!$ismissing) echo(substr($this->question->prepost,$possplit+2));
			else echo('<span class="question3missing">'.substr($this->question->prepost,$possplit+2).'</span>');
			echo('</p>');
		}
	?>
</div>