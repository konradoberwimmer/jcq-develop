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
		if ($this->markmissing && !$ismissing && $this->question->datatype==1 && !val_is_int($prevanswer)) echo '<div class="questionalertmissing">Bitte hier eine ganze Zahl eingeben!</div>';
		if ($this->markmissing && !$ismissing && $this->question->datatype==2 && !is_numeric($prevanswer)) echo '<div class="questionalertmissing">Bitte hier eine Zahl eingeben!</div>';
		$possplit = strpos($this->question->prepost, '%s');
		if ($possplit===false) echo('<p><input type="text" name="p'.$this->pageID.'q'.$this->question->ID.'" value="'.$prevanswer.'"/>'.$this->question->prepost.'</p>');
		else echo('<p>'.substr($this->question->prepost,0,$possplit).'<input type="text" name="p'.$this->pageID.'q'.$this->question->ID.'" value="'.$prevanswer.'"/>'.substr($this->question->prepost,$possplit+2).'</p>');
	?>
</div>