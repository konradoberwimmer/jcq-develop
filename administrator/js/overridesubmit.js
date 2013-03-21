//Overrides the Joomla standard submitbutton function for JToolbarHelper buttons
function submitbutton(pressbutton) 
{
	if (pressbutton=='saveProject' && !checkproject())
	{
		alert('Project definition incorrect: check classfile and classname!');
		return;
	}
	document.adminForm.task.value=pressbutton;
	submitform(pressbutton);
}

function checkproject()
{
	var classfile = document.getElementById('classfile');
	var regex_classfile = new RegExp(/[a-zA-Z]+[a-zA-Z_0-9]*\.php/);
	var correct = regex_classfile.exec(classfile.value);
	if (correct==null || correct[0].length<classfile.value.length) return false;
	var classname = document.getElementById('classname');
	var regex_classname = new RegExp(/[A-Z]+[a-zA-Z]*/);
	var correct = regex_classname.exec(classname.value);
	if (correct==null || correct[0].length<classname.value.length) return false;
	var cssfile = document.getElementById('cssfile');
	if (cssfile.value.length>0)
	{
		var regex_cssfile = new RegExp(/[a-zA-Z]+[a-zA-Z_0-9]*\.css/);
		var correct = regex_cssfile.exec(cssfile.value);
		if (correct==null || correct[0].length<cssfile.value.length) return false;
	}
	return true;
}