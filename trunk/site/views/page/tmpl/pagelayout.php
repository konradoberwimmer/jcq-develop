<?php
defined('_JEXEC') or die('Restricted access'); ?>
<table class="pageheader">
	<tr>
		<td>
		<?php 
			if ($this->userdata->isPreview()) echo('<span class="preview">PREVIEW: </span>');
			echo($this->pagemodel->getProjectName()); 
		?>
		</td>
		<td align="right"><img src="<?php echo($this->getProgressbar($this->page->ID,$this->pagemodel->getPagePosition())); ?>" alt="<?php echo($this->pagemodel->getPagePosition()); ?>"/></td>
	</tr>
</table>
<form action="<?php echo(JFactory::getURI()->toString()); ?>" method="POST" name="inputForm">
	<?php 
		if ($this->markmissing)
		{
			echo '<div class="questionalertmissing">Bitte beantworten Sie noch die rot markierten Fragen!</div>';
		}
		$questions = $this->pagemodel->getQuestions();
		for ($i=0;$i<count($questions);$i++)
		{
			$question = $questions[$i];
			$this->assignRef('question',$question);
			require(JPATH_COMPONENT.DS.'views'.DS.'page'.DS.'tmpl'.DS.'question'.$question->questtype.'layout.php');
		}
	?>
	<?php if (!$this->page->isFinal) { ?>
	<input type="button" name="storeAndContinue" value="Weiter" onclick="javascript: submitbutton('storeAndContinue');" style="margin-top: 10px;"/>
	<?php } ?>
    <input type="hidden" name="option" value="<?php echo JRequest::getVar( 'option' );?>"/>
    <input type="hidden" name="projectID" value="<?php echo JRequest::getVar( 'projectID' ); ?>"/>
    <input type="hidden" name="sessionID" value="<?php echo $this->userdata->getSessionID(); //because it may be a new one! ?>"/>
    <input type="hidden" name="task" value=""/>
</form>
