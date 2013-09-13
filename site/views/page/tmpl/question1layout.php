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
					if ($item->linebreak) echo("<br/>");
					$width = $item->width_left==0?200:$item->width_left;
					$possplit = strpos($item->prepost, '%s');
					if ($possplit===false)
					{
						if ($item->rows<=1) echo('<input type="text" style="width: '.$width.'px;" name="p'.$this->pageID.'q'.$this->question->ID.'i'.$item->ID.'" value="'.$prevtext.'"/>');
						else echo('<textarea style="width: '.$width.'px;" rows="'.$item->rows.'" name="p'.$this->pageID.'q'.$this->question->ID.'i'.$item->ID.'" value="'.$prevtext.'"/>');
						echo($item->prepost);
					}
					else
					{
						echo(substr($item->prepost,0,$possplit));
						if ($item->rows<=1) echo('<input type="text" style="width: '.$width.'px;" name="p'.$this->pageID.'q'.$this->question->ID.'i'.$item->ID.'" value="'.$prevtext.'"/>');
						else echo('<textarea style="width: '.$width.'px;" rows="'.$item->rows.'" name="p'.$this->pageID.'q'.$this->question->ID.'i'.$item->ID.'" value="'.$prevtext.'"/>');
						echo(substr($item->prepost,$possplit+2));
					}
				}
			}
			echo('</p>');
		}
	?>
</div>