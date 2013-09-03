function changeAllUGstate() 
{
	var table = document.getElementById("usergrouptable");
	var chkboxes = table.getElementsByTagName("input");
	var allchkbox = document.getElementById("ug_all");
	for (var i=0; i<chkboxes.length; i++)
	{
		if (chkboxes[i].getAttribute('type')=='checkbox' && chkboxes[i].getAttribute('name')!='ug_all') chkboxes[i].checked=allchkbox.checked;
	}
}