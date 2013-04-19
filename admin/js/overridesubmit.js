//Overrides the Joomla standard submitbutton function for JToolbarHelper buttons
function submitbutton(pressbutton) 
{
	if (pressbutton=='saveProject' && !checkproject())
	{
		alert('Project definition incorrect: check classfile and classname!');
		return;
	} else if (pressbutton=='previewPage')
	{
		var previewPage = document.getElementById('previewPage');
		previewPage.setAttribute("value","1");
		pressbutton = "savePage";
	}
	
	document.adminForm.task.value=pressbutton;
	submitform(pressbutton);
}

function editImport(id)
{
	var editImport = document.getElementById('editImport');
	editImport.setAttribute("value",id);
	
	submitbutton('editImport');
}

function checkproject()
{
	var cssfile = document.getElementById('cssfile');
	if (cssfile.value.length>0)
	{
		var regex_cssfile = new RegExp(/[a-zA-Z]+[a-zA-Z_0-9]*\.css/);
		var correct = regex_cssfile.exec(cssfile.value);
		if (correct==null || correct[0].length<cssfile.value.length) return false;
	}
	return true;
}