<?php
defined('_JEXEC') or die( 'Restricted access' ); ?>
<div class="question4">
	<?php
		$items = $this->pagemodel->getItemsToQuestion($this->question->ID);
		$missings=false;
		foreach ($items as $item)
		{
			if ($item->mandatory==1 && !$this->userdata->hasStoredValue($item->ID))
			{
				$missings=true;
				break;
			}
		}
		if ($this->question->width_question>0) $width_question="width:".$this->question->width_question."px;";
		else $width_question="";
		if ($this->markmissing && $missings)
		{ ?>
			<p class="question4missing" style="<?php echo($width_question); ?>"><?php echo $this->question->text; ?></p>
		<?php 
		}  
		else 
		{ ?>
			<p class="question4text" style="<?php echo($width_question); ?>"><?php echo $this->question->text; ?></p>
		<?php 
		}
	?>
	<?php if ($this->question->advise != null) echo '<p class="question4advise" style="'.$width_question.'">'.$this->question->advise.'</p>'; ?>
	
	<?php 
		$codes = $this->pagemodel->getScaleToQuestion($this->question->ID);
		?>
		<table class="question4">
		<tr>
			<th class="question4"/>
			<?php 
			if ($this->question->width_scale>0) $width_scale="width:".$this->question->width_scale."px;";
			else $width_scale="";
			for ($j=0;$j<count($codes);$j++)
			{
				echo('<th class="question4" style="'.$width_scale.'">');
				if ($codes[$j]->missval) echo('<span class="question4missingvalue">');
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
			$prevanswer = $this->userdata->getStoredValue($items[$k]->ID);
			if ($k%2==1 && $this->question->alternate_bg) echo('<td class="question4itemAlt" style="'.$width_items.'">');
			else echo('<td class="question4item" style="'.$width_items.'">');
			if (!$this->markmissing || $prevanswer!==null || $items[$k]->mandatory==0) echo($items[$k]->textleft);
			else echo('<span class="question4itemmissing">'.$items[$k]->textleft.'</span>');
			echo('</td>');
			for ($j=0;$j<count($codes);$j++)
			{
				if ($k%2==1 && $this->question->alternate_bg) echo('<td class="question4buttonAlt">');
				else echo('<td class="question4button">');
				echo('<input type="radio" name="i'.$items[$k]->ID.'_" value="'.$codes[$j]->code.'" '.($codes[$j]->code==$prevanswer?"checked":"").'/></td>');
			}
			echo("</tr>");
		}
	?>
		</table>
</div>