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
	} else if (pressbutton=='uploadTokens')
	{
		document.adminForm.setAttribute('enctype','multipart/form-data');
	} else if (pressbutton=='removeTokens')
	{
		var foundchecked = false;
		var tokentable = document.getElementById('tokentable');
		var chkboxes = tokentable.getElementsByTagName("input");
		for (var i=0; i<chkboxes.length; i++)
		{
			if (chkboxes[i].getAttribute('name')=='cid[]' && chkboxes[i].checked)
			{
				foundchecked = true;
				break;
			}
		}
		if (!foundchecked)
		{
			alert('Please select token(s) first!');
			return;
		}
		if (!confirm('Do you really want to remove the selected tokens?')) return;
	} else if (pressbutton=='copyUsergroup')
	{
		var selUsergroup = document.getElementById('selUsergroup');
		if (selUsergroup.options[selUsergroup.selectedIndex].value==-1)
		{
			alert("Please select a usergroup first!");
			return;
		}
	} else if (pressbutton=='removeUsergroups')
	{
		var foundchecked = false;
		var ugtable = document.getElementById('usergrouptable');
		var chkboxes = ugtable.getElementsByTagName("input");
		for (var i=0; i<chkboxes.length; i++)
		{
			if (chkboxes[i].getAttribute('name')=='ugchk[]' && chkboxes[i].checked)
			{
				foundchecked = true;
				break;
			}
		}
		if (!foundchecked)
		{
			alert('Please select usergroup(s) first!');
			return;
		}
		if (!confirm('Do you really want to remove the selected usergroup(s)?')) return;
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