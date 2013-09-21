<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

class JcqViewPage extends JView
{
	function displayPage($pageID,$markmissing)
	{
		//add javascript functionality for inputForm
		$path = 'components/com_jcq/js/';
		$filename = 'submitinputform.js';
		JHTML::script($path.$filename, true);
		
		$modelpage = $this->getModel();
		$modelpage->setPage($pageID);
		$this->assignRef('page',$modelpage->getPage());
		$this->assignRef('pagemodel', $modelpage);
		$modeluserdata = $this->getModel('userdata');
		$this->assignRef('userdata', $modeluserdata);
		
		$this->assignRef('markmissing',$markmissing);
		$this->assignRef('pageID',$pageID);
		
		if ($this->markmissing)
		{
			echo '<div class="questionalertmissing">Bitte beantworten Sie noch die rot markierten Fragen!</div>';
		}
		parent::display();
	}
	
	function drawProgessbar($filename,$progress)
	{
		$image = imagecreate(150, 30);
		$white = imagecolorallocate($image, 255, 255, 255);
		$white = imagecolortransparent($image,$white);
		$black = imagecolorallocate($image, 0, 0, 0);
		$blue = imagecolorallocate($image, 0, 0, 255);
		imagefilledrectangle($image, 1, 1, 150, 30, $white);
		//draw the frame
		imageline($image,0,2,0,22,$black);
		imagesetpixel($image, 1, 1, $black);
		imageline($image,2,0,147,0,$black);
		imagesetpixel($image, 148, 1, $black);
		imageline($image,149,2,149,22,$black);
		imagesetpixel($image, 148, 23, $black);
		imageline($image,2,24,147,24,$black);
		imagesetpixel($image, 1, 23, $black);
		//draw the bar
		if ($progress>0) imagefilledrectangle($image, 2, 2, 2+((float)$progress)/100.0*145, 22, $blue);
		//write the text
		imagestring($image, 3, 75-(imagefontwidth(3)*strlen($progress."%"))/2, 5, $progress."%", $black);
		imagepng($image,"tmp".DS.$filename);
		imagedestroy($image);
	}
	
	function getProgressbar($pageID,$progress)
	{
		$modelpage = $this->getModel();
		$modelpage->setPage($pageID);
		$projectID = $modelpage->getPage()->projectID;
		$progress = round($progress*100,0);
		$filename = "pb_proj$projectID"."_"."$progress.png";
		if (!file_exists("tmp".DS.$filename)) $this->drawProgessbar($filename,$progress);
		return (JURI::root()."tmp/".$filename);
	}
}