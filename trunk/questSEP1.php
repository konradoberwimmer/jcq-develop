<?php
include ("questionnaire.php");
class QuestSEP1 extends Questionnaire{
	public function printResults($questions)
	{
	?>
		<h2 style='margin-bottom: 20px;'>Herzlichen Dank f&uuml;r Ihr Interesse und Ihre Geduld!</h2>
		<p style='margin-bottom: 20px;'>Durch Ihre Mithilfe ist es m&ouml;glich, SBK stetig zu evaluieren und weiterzuentwickeln. <b>Ihre pers&ouml;nliche Auswertung erhalten Sie in den n&auml;chsten vier Wochen per Email zugesandt.</b> Im Zuge dessen werden Sie auch gebeten, Ihre Meinung und Anregungen zum SBK-Tool selbst abzugeben.</p>
		<p>F&uuml;r Fragen stehen Ihnen das SBK-Projektteam unter <a href='mailto:sbk@sbg.ac.at' target='_blank'>sbk@sbg.ac.at</a> gerne zur Verf&uuml;gung.</p>
		<p style='margin-bottom: 20px;'> F&uuml;r n&auml;here Informationen und allen Details zu SBK k&ouml;nnen Sie <a href='http://www.uni-salzburg.at/sbk'> hier </a> zur SBK-Hauptseite wechseln.</p>
		<h2 style='margin-bottom: 20px;'>Mit freundlichen Gr&uuml;&szlig;en,<br/>Michaela Neidhardt und das SBK-Projektteam</h2>
		
<?php
	}
}
?>