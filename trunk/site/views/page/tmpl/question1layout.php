<?php
defined('_JEXEC') or die( 'Restricted access' ); 
$codes = $this->pagemodel->getScaleToQuestion($this->question->ID);
$items = $this->pagemodel->getItemsToQuestion($this->question->ID);
$mainitem = null;
foreach ($items as $item) if ($item->bindingType=="QUESTION") { $mainitem = $item; break; }
if ($mainitem===null) JError::raiseError(500, "FATAL: corrupt question definition for '".$this->question->name."'");
$prevanswer = $this->userdata->getStoredValue($mainitem->ID);
?>
<div class="question1">
	<?php
		if ($this->markmissing && $mainitem->mandatory==1 && !$this->userdata->hasStoredValue($mainitem->ID))
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
		for ($j=0;$j<count($codes);$j++)
		{
			echo('<p>');
			echo('<input type="radio" name="i'.$mainitem->ID.'_" value="'.$codes[$j]->code.'" '.($codes[$j]->code==$prevanswer?"checked":"").'>'.$codes[$j]->label.'</input>');
			foreach ($items as $item)
			{
				if ($item->bindingType=="CODE" && $item->bindingID == $codes[$j]->ID)
				{
					$prevtext = $this->userdata->getStoredValue($item->ID);
					if ($item->linebreak) echo("<br/>");
					$width = $item->width_left==0?200:$item->width_left;
					$possplit = strpos($item->prepost, '%s');
					if ($possplit===false)
					{
						if ($item->rows<=1) echo('<input type="text" style="width: '.$width.'px;" name="i'.$item->ID.'_" value="'.$prevtext.'"/>');
						else echo('<textarea style="width: '.$width.'px;" rows="'.$item->rows.'" name="i'.$item->ID.'_">'.$prevtext.'</textarea>');
						echo($item->prepost);
					}
					else
					{
						echo(substr($item->prepost,0,$possplit));
						if ($item->rows<=1) echo('<input type="text" style="width: '.$width.'px;" name="i'.$item->ID.'_" value="'.$prevtext.'"/>');
						else echo('<textarea style="width: '.$width.'px;" rows="'.$item->rows.'" name="i'.$item->ID.'_">'.$prevtext.'</textarea>');
						echo(substr($item->prepost,$possplit+2));
					}
				}
			}
			echo('</p>');
		}
	?>
</div>