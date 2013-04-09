<?php
defined('_JEXEC') or die( 'Restricted access' ); ?>
<div class="question1">
	<?php
		if ($this->markmissing && $this->question->mandatory==1 && !$this->userdata->hasStoredValueQuestion($this->pageID,$this->question->ID))
		{ ?>
			<p class="question1missing"><?php echo $this->question->text; ?></p>
		<?php 
		}  
		else 
		{ ?>
			<p class="question1text"><?php echo $this->question->text; ?></p>
		<?php 
		}
	?>
	<?php if ($this->question->advise != null) echo '<p class="question1advise">'.$this->question->advise.'</p>'; ?>
	<?php 
		$codes = $this->pagemodel->getScaleToQuestion($this->question->ID);
		$items = $this->pagemodel->getItemsToQuestion($this->question->ID);
		$prevanswer = $this->userdata->getStoredValueQuestion($this->pageID,$this->question->ID);
		for ($j=0;$j<count($codes);$j++)
		{
			echo('<p>');
			echo('<input type="radio" name="p'.$this->pageID.'q'.$this->question->ID.'" value="'.$codes[$j]->code.'" '.($codes[$j]->code==$prevanswer?"checked":"").'>'.$codes[$j]->label.'</input>');
			foreach ($items as $item)
			{
				if ($item->bindingID == $codes[$j]->ID)
				{
					$prevtext = $this->userdata->getStoredValueItem($this->pageID,$this->question->ID,$item->ID);
					echo('<input type="text" size="16" name="p'.$this->pageID.'q'.$this->question->ID.'i'.$item->ID.'" value="'.$prevtext.'"/>');
				}
			}
			echo('</p>');
		}
	?>
</div>