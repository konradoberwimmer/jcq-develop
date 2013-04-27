<?php
defined('_JEXEC') or die( 'Restricted access' ); ?>
<div class="question2">
	<?php
		$items = $this->pagemodel->getItemsToQuestion($this->question->ID);
		$foundchecked = false;
		foreach ($items as $item) if ($this->userdata->getStoredValueItem($this->pageID,$this->question->ID,$item->ID)==1) $foundchecked = true;
		if ($this->markmissing && $this->question->mandatory==1 && !$foundchecked)
		{ ?>
			<p class="question2missing"><?php echo $this->question->text; ?></p>
		<?php 
		}  
		else 
		{ ?>
			<p class="question2text"><?php echo $this->question->text; ?></p>
		<?php 
		}
	?>
	<?php if ($this->question->advise != null) echo '<p class="question2advise">'.$this->question->advise.'</p>'; ?>
	<?php 
		for ($j=0;$j<count($items);$j++)
		{
			$item=$items[$j];
			$prevanswer = $this->userdata->getStoredValueItem($this->pageID,$this->question->ID,$item->ID);
			echo('<p>');
			echo('<input type="checkbox" name="p'.$this->pageID.'q'.$this->question->ID.'i'.$item->ID.'" '.($prevanswer==1?"checked":"").'>'.$item->textleft.'</input>');
			echo('</p>');
		}
	?>
</div>