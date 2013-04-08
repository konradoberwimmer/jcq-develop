<?php
defined('_JEXEC') or die( 'Restricted access' ); ?>
<div class="question311">
	<?php
		$items = $this->pagemodel->getItemsToQuestion($this->question->ID);
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
			<p class="question311missing" style="<?php echo($width_question); ?>"><?php echo $this->question->text; ?></p>
		<?php 
		}  
		else 
		{ ?>
			<p class="question311text" style="<?php echo($width_question); ?>"><?php echo $this->question->text; ?></p>
		<?php 
		}
	?>
	<?php if ($this->question->advise != null) echo '<p class="question311advise" style="'.$width_question.'">'.$this->question->advise.'</p>'; ?>
	
	<?php 
		$codes = $this->pagemodel->getScaleToQuestion($this->question->ID);
		?>
		<table class="question311">
		<tr>
			<th class="question311"/>
			<?php 
			if ($this->question->width_scale>0) $width_scale="width:".$this->question->width_scale."px;";
			else $width_scale="";
			for ($j=0;$j<count($codes);$j++)
			{
				echo('<th class="question311" style="'.$width_scale.'">');
				if ($codes[$j]->missval) echo('<span class="question311missingvalue">');
				echo($codes[$j]->label);
				if ($codes[$j]->missval) echo('</span>');
				echo('</th>');
			}
			?>
		</tr>
		<?php 
		if ($this->question->width_items>0) $width_items="width:".$this->question->width_items."px;";
		else $width_items="";
		for ($k=0;$k<count($items);$k++)
		{
			echo("<tr>");
			$prevanswer = $this->userdata->getStoredValueItem($this->pageID,$this->question->ID,$items[$k]->ID);
			if ($k%2==1 && $this->question->alternate_bg) echo('<td class="question311itemAlt" style="'.$width_items.'">');
			else echo('<td class="question311item" style="'.$width_items.'">');
			if (!$this->markmissing || $prevanswer!=null || $items[$k]->mandatory==0) echo($items[$k]->textleft);
			else echo('<span class="question311itemmissing">'.$items[$k]->textleft.'</span>');
			echo('</td>');
			for ($j=0;$j<count($codes);$j++)
			{
				if ($k%2==1 && $this->question->alternate_bg) echo('<td class="question311buttonAlt">');
				else echo('<td class="question311button">');
				echo('<input type="radio" name="p'.$this->pageID.'q'.$this->question->ID.'i'.$items[$k]->ID.'" value="'.$codes[$j]->code.'" '.($codes[$j]->code==$prevanswer?"checked":"").'/></td>');
			}
			echo("</tr>");
		}
	?>
		</table>
</div>