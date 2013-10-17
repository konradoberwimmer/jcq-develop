<?php
defined('_JEXEC') or die( 'Restricted access' ); 
$items = $this->pagemodel->getItemsToQuestion($this->question->ID);
?>
<div class="question2">
	<?php
		$foundchecked = false;
		foreach ($items as $item)
		{
			if ($item->bindingType!="QUESTION") continue;
			if ($this->userdata->getStoredValue($item->ID)!=0) { $foundchecked = true; break; }
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
			$prevanswer = $this->userdata->getStoredValue($item->ID);
			echo('<p>');
			echo('<input type="checkbox" name="i'.$item->ID.'_" '.($prevanswer!=0?"checked":"").'>'.$item->textleft.'</input>');
			foreach ($items as $oneitem)
			{
				if ($oneitem->bindingType=="ITEM" && $oneitem->bindingID==$item->ID)
				{
					$prevtext = $this->userdata->getStoredValue($oneitem->ID);
					if ($oneitem->linebreak) echo("<br/>");
					$width = $oneitem->width_left==0?200:$oneitem->width_left;
					$possplit = strpos($oneitem->prepost, '%s');
					if ($possplit===false)
					{
						if ($oneitem->rows<=1) echo('<input type="text" style="width: '.$width.'px;" name="i'.$oneitem->ID.'_" value="'.$prevtext.'"/>');
						else echo('<textarea style="width: '.$width.'px;" rows="'.$oneitem->rows.'" name="i'.$oneitem->ID.'_">'.$prevtext.'</textarea>');
						echo($oneitem->prepost);
					}
					else
					{
						echo(substr($oneitem->prepost,0,$possplit));
						if ($oneitem->rows<=1) echo('<input type="text" style="width: '.$width.'px;" name="i'.$oneitem->ID.'_" value="'.$prevtext.'"/>');
						else echo('<textarea style="width: '.$width.'px;" rows="'.$oneitem->rows.'" name="i'.$oneitem->ID.'_">'.$prevtext.'</textarea>');
						echo(substr($oneitem->prepost,$possplit+2));
					}
				}
			}
			echo('</p>');
		}
	?>
</div>