<?php
defined('_JEXEC') or die( 'Restricted access' ); ?>
<div class="question340">
	<?php
		$items = $this->page->getItemsToQuestion($this->question->ID);
		$missings=false;
		foreach ($items as $item)
		{
			if ($item->mandatory==1 && !$this->userdata->hasStoredValueItem($this->pageID,$this->question->ID,$item->ID))
			{
				$missings=true;
				break;
			}
		}
		if ($this->question->width_question>0) $width_question="width:".$this->question->width_question."px;";
		else $width_question="";
		if ($this->markmissing && $missings)
		{ ?>
			<p class="question340missing" style="<?php echo($width_question); ?>"><?php echo $this->question->text; ?></p>
		<?php 
		}  
		else 
		{ ?>
			<p class="question340text" style="<?php echo($width_question); ?>"><?php echo $this->question->text; ?></p>
		<?php 
		}
	?>
	<?php if ($this->question->advise != null) echo '<p class="question340advise" style="'.$width_question.'">'.$this->question->advise.'</p>'; ?>
	
	<?php 
		$codes = $this->page->getScaleToQuestion($this->question->ID);
		?>
		<table class="question340">
		<tr>
			<th class="question340"/>
			<?php 
			if ($this->question->width_scale>0) $width_scale="width:".$this->question->width_scale."px;";
			else $width_scale="";
			for ($j=0;$j<count($codes);$j++)
			{
				echo('<th class="question340" style="'.$width_scale.'">');
				if ($codes[$j]->missval) echo('<span class="question340missingvalue">');
				echo($codes[$j]->label);
				if ($codes[$j]->missval) echo('</span>');
				echo('</th>');
			}
			?>
			<th class="question340"/>
		</tr>
		<?php 
		if ($this->question->width_items>0) $width_items="width:".$this->question->width_items."px;";
		else $width_items="";
		for ($k=0;$k<count($items);$k++)
		{
			echo("<tr>");
			$prevanswer = $this->userdata->getStoredValueItem($this->pageID,$this->question->ID,$items[$k]->ID);
			//text left
			if ($k%2==1 && $this->question->alternate_bg) echo('<td class="question340itemAlt" style="'.$width_items.'">');
			else echo('<td class="question340item" style="'.$width_items.'">');
			if (!$this->markmissing || $prevanswer!=null || $items[$k]->mandatory==0) echo($items[$k]->textleft);
			else echo('<span class="question340itemmissing">'.$items[$k]->textleft.'</span>');
			echo('</td>');
			//scale
			for ($j=0;$j<count($codes);$j++)
			{
				if ($k%2==1 && $this->question->alternate_bg) echo('<td class="question340buttonAlt">');
				else echo('<td class="question340button">');
				echo('<input type="radio" name="p'.$this->pageID.'q'.$this->question->ID.'i'.$items[$k]->ID.'" value="'.$codes[$j]->code.'" '.($codes[$j]->code==$prevanswer?"checked":"").'/></td>');
			}
			//text right
			if ($k%2==1 && $this->question->alternate_bg) echo('<td class="question340itemAlt" style="'.$width_items.'">');
			else echo('<td class="question340item" style="'.$width_items.'">');
			if (!$this->markmissing || $prevanswer!=null || $items[$k]->mandatory==0) echo($items[$k]->textright);
			else echo('<span class="question340itemmissing">'.$items[$k]->textright.'</span>');
			echo('</td>');
			echo("</tr>");
		}
	?>
		</table>
</div>