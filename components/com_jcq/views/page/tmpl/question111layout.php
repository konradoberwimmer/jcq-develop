<?php
defined('_JEXEC') or die( 'Restricted access' ); ?>
<div>
	<p><?php echo $this->question->text; ?></p>
	<?php if ($this->question->advise != null) echo "<p>".$this->question->advise."</p>"; ?>
	<?php 
		$codes = $this->page->getScaleToQuestion($this->question->ID);
		for ($j=0;$j<count($codes);$j++)
		{
			echo('<input type="radio" name="p'.$this->pageID.'q'.$this->question->ID.'" value="'.$codes[$j]->code.'">'.$codes[$j]->label.'</input><br/>');
		}
	?>
</div>