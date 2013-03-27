<?php
defined('_JEXEC') or die( 'Restricted access' ); ?>
<div class="question361">
	<?php
		$items = $this->page->getItemsToQuestion($this->question->ID);
		$scales = $this->page->getScalesToQuestion($this->question->ID);
		$missings=false;
		foreach ($items as $item)
		{
			foreach($scales as $scale)
			if ($item->mandatory==1 && $scale->mandatory==1 && !$this->userdata->hasStoredValueItem($this->pageID,$this->question->ID,$item->ID,$scale->ID))
			{
				$missings=true;
				break;
			}
		}
		if ($this->question->width_question>0) $width_question="width:".$this->question->width_question."px;";
		else $width_question="";
		if ($this->markmissing && $missings)
		{ ?>
			<p class="question361missing" style="<?php echo($width_question); ?>"><?php echo $this->question->text; ?></p>
		<?php 
		}  
		else 
		{ ?>
			<p class="question361text" style="<?php echo($width_question); ?>"><?php echo $this->question->text; ?></p>
		<?php 
		}
	?>
	<?php if ($this->question->advise != null) echo '<p class="question361advise" style="'.$width_question.'">'.$this->question->advise.'</p>'; ?>
	
	<table class="question361">
		<?php 
		if ($this->question->width_items>0) $width_items="width:".$this->question->width_items."px;";
		else $width_items="";
		for ($k=0;$k<count($items);$k++)
		{
			echo("<tr>");
			$itemmissing=false;
			$prevanswers=array();
			for ($i=0;$i<count($scales);$i++)
			{
				$prevanswers[$i]=$this->userdata->getStoredValueItem($this->pageID,$this->question->ID,$items[$k]->ID,$scales[$i]->ID);
				if ($this->markmissing && $items[$k]->mandatory==1 && $scales[$i]->mandatory==1 && !$this->userdata->hasStoredValueItem($this->pageID,$this->question->ID,$items[$k]->ID,$scales[$i]->ID)) $itemmissing=true;
			}
			if ($k%2==1 && $this->question->alternate_bg) echo('<td class="question361itemAlt" style="'.$width_items.'">');
			else echo('<td class="question361item" style="'.$width_items.'">');
			if (!$itemmissing) echo($items[$k]->textleft);
			else echo('<span class="question361itemmissing">'.$items[$k]->textleft.'</span>');
			echo('</td>');
			for ($j=0;$j<count($scales);$j++)
			{
				if ($this->question->width_scale>0) $width_scale="width:".$this->question->width_scale."px;";
				else $width_scale="";
				if ($k%2==1 && $this->question->alternate_bg) echo('<td class="question361buttonAlt" style="'.$width_scale.'">');
				else echo('<td class="question361button" style="'.$width_scale.'">');
				$possplit = strpos($scales[$j]->prepost, '%i');
				if ($possplit!==false) echo(substr($scales[$j]->prepost,0,$possplit));
				echo('<select name="p'.$this->pageID.'q'.$this->question->ID.'i'.$items[$k]->ID.'s'.$scales[$j]->ID.'">');
				echo('<option></option>');
				$codes = $this->page->getCodesToScale($scales[$j]->ID);
				for ($i=0;$i<count($codes);$i++)
				{
					echo('<option value="'.$codes[$i]->code.'" '.($codes[$i]->code==$prevanswers[$j]?"selected":"").'>'.$codes[$i]->label.'</option>');
				}
				echo('</select>');
				if ($possplit!==false) echo(substr($scales[$j]->prepost,$possplit+2));
				else echo($scales[$j]->prepost);
				echo('</td>');
			}
			echo("</tr>");
		}
	?>
		</table>
</div>