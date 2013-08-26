<?php
defined('_JEXEC') or die( 'Restricted access' ); ?>
<div class="question2">
	<?php
		$items = $this->pagemodel->getItemsToQuestion($this->question->ID);
		$foundchecked = false;
		foreach ($items as $item)
		{
			if ($item->bindingType!="QUESTION") continue;
			if ($this->userdata->getStoredValueItem($this->pageID,$this->question->ID,$item->ID)==1) $foundchecked = true;
		}
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
			if ($item->bindingType!="QUESTION") continue;
			$prevanswer = $this->userdata->getStoredValueItem($this->pageID,$this->question->ID,$item->ID);
			echo('<p>');
			echo('<input type="checkbox" name="p'.$this->pageID.'q'.$this->question->ID.'i'.$item->ID.'" '.($prevanswer==1?"checked":"").'>'.$item->textleft.'</input>');
			foreach ($items as $oneitem)
			{
				if ($oneitem->bindingType=="ITEM" && $oneitem->bindingID==$item->ID)
				{
					$prevtext = $this->userdata->getStoredValueItem($this->pageID,$this->question->ID,$oneitem->ID);
					echo('<input type="text" size="16" name="p'.$this->pageID.'q'.$this->question->ID.'i'.$oneitem->ID.'" value="'.$prevtext.'"/>');
				}
			}
			echo('</p>');
		}
	?>
</div>