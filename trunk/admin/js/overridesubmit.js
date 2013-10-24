//Overrides the Joomla standard submitbutton function for JToolbarHelper buttons
Joomla.submitbutton = function(pressbutton)
{
	if (pressbutton=='saveProject' && !checkproject()) return;
	else if (pressbutton=='saveQuestion' && !checkvarnames()) return;
	
	document.adminForm.task.value=pressbutton;
	submitform(pressbutton);
}

//function for non-toolbar submitbuttons
function submitbutton(pressbutton)
{
	if (pressbutton=='previewPage')
	{
		var previewPage = document.getElementById('previewPage');
		previewPage.setAttribute("value","1");
		pressbutton = "savePage";
	} else if (pressbutton=='previewProject')
	{
		var previewProject = document.getElementById('previewProject');
		previewProject.setAttribute("value","1");
		pressbutton = "saveProject";
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
	} else if (pressbutton=='sendEmails' && !checkemail())
	{
		return;
	}
	
	document.adminForm.task.value=pressbutton;
	submitform(pressbutton);
}

function editProgramFile(id)
{
	var editProgramfile = document.getElementById('editProgramfile');
	editProgramfile.setAttribute("value",id);
	
	submitbutton('editProgramfile');
}

function checkproject()
{
	var cssfile = document.getElementById('cssfile');
	if (cssfile.value.length>0)
	{
		var regex_cssfile = new RegExp(/[a-zA-Z]+[a-zA-Z_0-9]*\.css/);
		var correct = regex_cssfile.exec(cssfile.value);
		if (correct==null || correct[0].length<cssfile.value.length)
		{
			alert("User error: incorrect css filename!");
			return false;
		}
	}
	return true;
}

function checkvarnames()
{
	var inputfields = document.getElementsByTagName("input");
	var regex_varname = new RegExp(/[a-zA-Z]+[a-zA-Z0-9_$]*/);
	for (var i=0; i<inputfields.length; i++)
	{
		if (inputfields[i].getAttribute('name') != null && inputfields[i].getAttribute('name').indexOf('varname')!=-1)
		{
			var correct = regex_varname.exec(inputfields[i].value);
			if (correct==null || correct[0].length<inputfields[i].value.length)
			{
				alert("User error: incorrect or missing variable name '"+inputfields[i].value+"'!");
				return false;
			}
		}
	}
	return true;
}

function checkemail()
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
		return false;
	}
	var from = document.getElementById('email_from');
	if (from.value.length==0)
	{
		alert('Please enter an email address to send from!')
		return false;
	} else
	{
		var regex_email = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		if (!regex_email.test(from.value))
		{
			alert('Email address of sender (from) is invalid!');
			return false;
		}
	}
	var subject = document.getElementById('email_subject');
	if (subject.value.length==0)
	{
		alert('Please enter a subject for the email!')
		return false;
	}
	var text = document.getElementById('email_text');
	if (text.value.length==0)
	{
		alert('Please enter a text for the email!')
		return false;
	}
	return true;
}
