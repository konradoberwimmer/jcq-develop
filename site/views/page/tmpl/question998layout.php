<?php
defined('_JEXEC') or die( 'Restricted access' ); ?>
<div class="question998">
	<?php 
		#FIXME secure against wrong-doings with the brackets
		$snippets = explode("{",$this->question->text);
		foreach ($snippets as $snippet)
		{
			if (strpos($snippet,"}")!==false)
			{
				$parts = explode("}",$snippet);
				$return = call_user_func($parts[0]); //if used correctly it will invoke the usercode
				echo ($return);
				echo ($parts[1]);
			}
			else echo($snippet);
		}
	?>
</div>