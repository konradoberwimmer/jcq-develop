<?php
defined('_JEXEC') or die( 'Restricted access' ); ?>
<div class="question111">
	<?php
		if ($this->markmissing && $this->question->mandatory==1 && !$this->userdata->hasStoredValueQuestion($this->pageID,$this->question->ID))
		{ ?>
			<p class="question111missing"><?php echo $this->question->text; ?></p>
		<?php 
		}  
		else 
		{ ?>
			<p class="question111text"><?php echo $this->question->text; ?></p>
		<?php 
		}
	?>
	<?php if ($this->question->advise != null) echo '<p class="question111advise">'.$this->question->advise.'</p>'; ?>
	<?php 
		$codes = $this->page->getScaleToQuestion($this->question->ID);
		$prevanswer = $this->userdata->getStoredValueQuestion($this->pageID,$this->question->ID);
		for ($j=0;$j<count($codes);$j++)
		{
			echo('<p><input type="radio" name="p'.$this->pageID.'q'.$this->question->ID.'" value="'.$codes[$j]->code.'" '.($codes[$j]->code==$prevanswer?"checked":"").'>'.$codes[$j]->label.'</input></p>');
		}
	?>
</div>