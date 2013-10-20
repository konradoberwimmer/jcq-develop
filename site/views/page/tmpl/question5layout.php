<?php
defined('_JEXEC') or die( 'Restricted access' ); ?>
<div class="question5">
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
			<p class="question5missing" style="<?php echo($width_question); ?>"><?php echo $this->question->text; ?></p>
		<?php 
		}  
		else 
		{ ?>
			<p class="question5text" style="<?php echo($width_question); ?>"><?php echo $this->question->text; ?></p>
		<?php 
		}
	?>
	<?php if ($this->question->advise != null) echo '<p class="question5advise" style="'.$width_question.'">'.$this->question->advise.'</p>'; ?>
	
	<?php 
		$codes = $this->pagemodel->getScaleToQuestion($this->question->ID);
		?>
		<table class="question5">
		<tr>
			<th class="question5"/>
			<?php 
			if ($this->question->width_scale>0) $width_scale="width:".$this->question->width_scale."px;";
			else $width_scale="";
			for ($j=0;$j<count($codes);$j++)
			{
				echo('<th class="question5" style="'.$width_scale.'">');
				if ($codes[$j]->missval) echo('<span class="question5missingvalue">');
				echo($codes[$j]->label);
				if ($codes[$j]->missval) echo('</span>');
				echo('</th>');
			}
			?>
			<th class="question5"/>
		</tr>
		<?php 
		if ($this->question->width_items>0) $width_items="width:".$this->question->width_items."px;";
		else $width_items="";
		for ($k=0;$k<count($items);$k++)
		{
			echo("<tr>");
			$prevanswer = $this->userdata->getStoredValue($items[$k]->ID);
			//text left
			if ($k%2==1 && $this->question->alternate_bg) echo('<td class="question5itemAlt" style="'.$width_items.'">');
			else echo('<td class="question5item" style="'.$width_items.'">');
			if (!$this->markmissing || $prevanswer!==null || $items[$k]->mandatory==0) echo($items[$k]->textleft);
			else echo('<span class="question5itemmissing">'.$items[$k]->textleft.'</span>');
			echo('</td>');
			//scale
			for ($j=0;$j<count($codes);$j++)
			{
				if ($k%2==1 && $this->question->alternate_bg) echo('<td class="question5buttonAlt">');
				else echo('<td class="question5button">');
				echo('<input type="radio" name="i'.$items[$k]->ID.'_" value="'.$codes[$j]->code.'" '.($codes[$j]->code==$prevanswer?"checked":"").'/></td>');
			}
			//text right
			if ($k%2==1 && $this->question->alternate_bg) echo('<td class="question5itemAlt" style="'.$width_items.'">');
			else echo('<td class="question5item" style="'.$width_items.'">');
			if (!$this->markmissing || $prevanswer!==null || $items[$k]->mandatory==0) echo($items[$k]->textright);
			else echo('<span class="question5itemmissing">'.$items[$k]->textright.'</span>');
			echo('</td>');
			echo("</tr>");
		}
	?>
		</table>
</div>