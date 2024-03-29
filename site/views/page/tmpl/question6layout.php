<?php
defined('_JEXEC') or die( 'Restricted access' ); ?>
<div class="question6">
	<?php
		$items = $this->pagemodel->getItemsToQuestion($this->question->ID);
		$scales = $this->pagemodel->getScalesToQuestion($this->question->ID);
		$missings=false;
		foreach ($items as $item)
		{
			foreach($scales as $scale)
			if ($item->mandatory==1 && $scale->mandatory==1 && !$this->userdata->hasStoredValue($item->ID,$scale->ID))
			{
				$missings=true;
				break;
			}
		}
		if ($this->question->width_question>0) $width_question="width:".$this->question->width_question."px;";
		else $width_question="";
		if ($this->markmissing && $missings)
		{ ?>
			<p class="question6missing" style="<?php echo($width_question); ?>"><?php echo $this->question->text; ?></p>
		<?php 
		}  
		else 
		{ ?>
			<p class="question6text" style="<?php echo($width_question); ?>"><?php echo $this->question->text; ?></p>
		<?php 
		}
	?>
	<?php if ($this->question->advise != null) echo '<p class="question6advise" style="'.$width_question.'">'.$this->question->advise.'</p>'; ?>
	
	<table class="question6">
		<?php 
		if ($this->question->width_items>0) $width_items="width:".$this->question->width_items."px;";
		else $width_items="";
		for ($k=0;$k<count($items);$k++)
		{
			echo("<tr>");
			$itemmissing=false;
			$prevanswers=array();
			for ($j=0;$j<count($scales);$j++)
			{
				$prevanswers[$j]=$this->userdata->getStoredValue($items[$k]->ID,$scales[$j]->ID);
				if ($this->markmissing && $items[$k]->mandatory==1 && $scales[$j]->mandatory==1 && !$this->userdata->hasStoredValue($items[$k]->ID,$scales[$j]->ID)) $itemmissing=true;
			}
			if ($k%2==1 && $this->question->alternate_bg) echo('<td class="question6itemAlt" style="'.$width_items.'">');
			else echo('<td class="question6item" style="'.$width_items.'">');
			if (!$itemmissing) echo($items[$k]->textleft);
			else echo('<span class="question6itemmissing">'.$items[$k]->textleft.'</span>');
			echo('</td>');
			for ($j=0;$j<count($scales);$j++)
			{
				if ($this->question->width_scale>0) $width_scale="width:".$this->question->width_scale."px;";
				else $width_scale="";
				if ($k%2==1 && $this->question->alternate_bg) echo('<td class="question6buttonAlt" style="'.$width_scale.'">');
				else echo('<td class="question6button" style="'.$width_scale.'">');
				$possplit = strpos($scales[$j]->prepost, '%i');
				if ($possplit!==false) echo(substr($scales[$j]->prepost,0,$possplit));
				echo('<select name="i'.$items[$k]->ID.'_s'.$scales[$j]->ID.'_">');
				if ($scales[$j]->defval==null) echo('<option></option>');
				$codes = $this->pagemodel->getCodesToScale($scales[$j]->ID);
				for ($l=0;$l<count($codes);$l++)
				{
					echo('<option value="'.$codes[$l]->code.'" '.($codes[$l]->code==$prevanswers[$j]||($prevanswers[$j]==null&&$scales[$j]->defval==$codes[$l]->code)?"selected":"").'>'.$codes[$l]->label.'</option>');
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