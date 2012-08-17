//functions to submit the input form of a project (eg. checking for validity etc.)
function submitbutton(pressbutton) 
{
	document.inputForm.task.value=pressbutton;
	document.inputForm.submit();
}
