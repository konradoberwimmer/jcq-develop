<?php
defined('_JEXEC') or die( 'Restricted access' ); ?>
<div class="question311">
	<?php
		$items = $this->page->getItemsToQuestion($this->question->ID);
		$missings=false;
		foreach ($items as $item)
		{
			if ($items->mandatory==1 && !$this->userdata->hasStoredValueItem($this->pageID,$this->question->ID,$item->ID))
			{
				$missings=true;
				break;
			}
		}
		if ($this->markmissing && $missings)
		{ ?>
			<p class="question311missing"><?php echo $this->question->text; ?></p>
		<?php 
		}  
		else 
		{ ?>
			<p class="question311text"><?php echo $this->question->text; ?></p>
		<?php 
		}
	?>
	<?php if ($this->question->advise != null) echo '<p class="question311advise">'.$this->question->advise.'</p>'; ?>
	
	<?php 
		$codes = $this->page->getScaleToQuestion($this->question->ID);
		?>
		<table class="question311">
		<tr>
			<th class="question311"/>
			<?php 
			for ($j=0;$j<count($codes);$j++) echo('<th class="question311">'.$codes[$j]->label.'</th>');
			?>
		</tr>
		<?php 
		for ($k=0;$k<count($items);$k++)
		{
			echo("<tr>");
			$prevanswer = $this->userdata->getStoredValueItem($this->pageID,$this->question->ID,$items[$k]->ID);
			if (!$this->markmissing || $prevanswer!=null || $items[$k]->mandatory==0) echo('<td class="question311item">'.$items[$k]->textleft.'</td>');
			else echo('<td class="question311itemmissing">'.$items[$k]->textleft.'</td>');
			for ($j=0;$j<count($codes);$j++)
			{
				echo('<td class="question311button"><input type="radio" name="p'.$this->pageID.'q'.$this->question->ID.'i'.$items[$k]->ID.'" value="'.$codes[$j]->code.'" '.($codes[$j]->code==$prevanswer?"checked":"").'/></td>');
			}
			echo("</tr>");
		}
	?>
		</table>
</div>